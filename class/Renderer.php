<?php
// ============================================================
// class/Renderer.php
//
// Handles rendering front/back HTML for one student and driving
// the batch PDF generation for an entire college.
// ============================================================

use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class Renderer
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Render the front-of-card HTML for one student.
     * $photoPath must already be the PROCESSED photo path.
     */
    private function renderFront(array $student, array $college, string $photoPath, bool $isTemporary = false): string
    {
        // Layout is shared across all colleges, only primary_color differs.
        // If a specific college ever needs a genuinely different layout,
        // drop a file named assets/idcardtemplates/front/{template_key}.php and it will
        // be used automatically instead of the shared template below.
        $overrideFile = TEMPLATES_PATH . '/front/' . $college['template_key'] . '.php';
        $templateFile = file_exists($overrideFile)
            ? $overrideFile
            : TEMPLATES_PATH . '/front/shared_front.php';

        ob_start();
        include $templateFile;
        return ob_get_clean();
    }

    /**
     * Render the back-of-card HTML for one student.
     * Same file for every college.
     */
    private function renderBack(array $student): string
    {
        $templateFile = TEMPLATES_PATH . '/back/shared_back.php';

        ob_start();
        include $templateFile;
        return ob_get_clean();
    }

    /**
     * Generate the full batch PDF for one college.
     * Front and back pages are interleaved per student
     * (front, back, front, back...) to match duplex print order.
     * Verify this ordering against the Magicard 300 driver's
     * expected page sequence before running a full production batch.
     */
    public function generateCollegeBatch(int $collegeId, ?string $generatedBy = null, bool $temporary = false, ?int $level = null, ?int $programmeId = null): array
    {
        return $this->generateStudentsBatch($this->db->getStudentsByCollege($collegeId, $level, $programmeId), $generatedBy, $collegeId, $temporary);
    }

    public function generateReplacementBatch(array $selection, CU\IdCard\Lifecycle $lifecycle, CU\IdCard\Identity $actor, string $window): array
    {
        $students=[];
        foreach ($selection as $pair) {
            $student=$pair['student'];
            $student['_application']=$pair['application'];
            $students[]=$student;
        }
        return $this->generateStudentsBatch($students,'awaiting-print',null,false,$lifecycle,$actor,$window);
    }

    public function generateStudentsBatch(array $students, ?string $generatedBy = null, ?int $batchCollegeId = null, bool $temporary = false, ?CU\IdCard\Lifecycle $lifecycle = null, ?CU\IdCard\Identity $actor = null, string $window = 'after'): array
    {
        if (empty($students)) {
            throw new RuntimeException('No active students were selected.');
        }

        $batchCollegeId = $batchCollegeId ?: (int) ($students[0]['college_id'] ?? 0);
        $college = $this->db->getCollege($batchCollegeId);

        if (!is_dir(MPDF_TEMP_PATH) && !mkdir(MPDF_TEMP_PATH, 0775, true) && !is_dir(MPDF_TEMP_PATH)) {
            throw new RuntimeException('Unable to create the mPDF temporary directory.');
        }
        if (!is_writable(MPDF_TEMP_PATH)) {
            throw new RuntimeException('The mPDF temporary directory is not writable.');
        }

        $mpdf = new Mpdf([
            'format'       => [CARD_WIDTH_MM, CARD_HEIGHT_MM],
            'tempDir'      => MPDF_TEMP_PATH,
            'margin_left'  => 0,
            'margin_right' => 0,
            'margin_top'   => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
        ]);

        $outputPrefix = $generatedBy === 'selective' ? 'SEL' : $college['code'];
        if (!is_dir(OUTPUT_PATH) && !mkdir(OUTPUT_PATH,0775,true) && !is_dir(OUTPUT_PATH)) throw new RuntimeException('Unable to create output directory.');
        $outputFile = OUTPUT_PATH . '/' . $outputPrefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.pdf';
        $batchId = $this->db->createBatch($batchCollegeId, count($students), $outputFile, $generatedBy);

        $successCount = 0;
        $failures = [];
        $isFirstPage = true;

        foreach ($students as $student) {
            try {
                $studentCollege = $this->db->getCollege((int) $student['college_id']);
                $photoPath = $student['photo_processed_path'] ?: $student['photo_path'];
                if ($lifecycle) {
                    $application=$student['_application'];
                    $relative=$application['photopath'];
                    if (!preg_match('#^uploads/idcard/([A-Za-z0-9_-]+\.(?:jpg|jpeg|png))$#i',$relative,$match)) throw new RuntimeException('Replacement photo path is invalid.');
                    $root=realpath(REPLACEMENT_PHOTOS_PATH);
                    $source=$root ? realpath($root.DIRECTORY_SEPARATOR.$match[1]) : false;
                    if (!$source || strcasecmp(dirname($source),$root)!==0) throw new RuntimeException('Replacement application photo is unavailable.');
                    // A separate subdirectory prevents any collision with a master matric photo.
                    $photoPath=PhotoProcessor::process($source,'application-'.$application['applicationid'],PROCESSED_PHOTOS_PATH.'/replacements');
                }

                if (!file_exists($photoPath)) {
                    throw new RuntimeException("Photo missing for matric {$student['matric_no']}");
                }

                $frontHtml = $this->renderFront($student, $studentCollege, $photoPath, $temporary);
                $backHtml = $this->renderBack($student);

                if (!$isFirstPage) {
                    $mpdf->AddPage();
                }
                $isFirstPage = false;

                $mpdf->WriteHTML($frontHtml);
                $mpdf->AddPage();
                $mpdf->WriteHTML($backHtml);

                if ($lifecycle) {
                    $lifecycle->recordBatchItem($actor,$batchId,(int)$student['id'],$student['_application']['referencenumber'],'success',$window);
                } else {
                    $this->db->logBatchItem($batchId, (int) $student['id'], 'success');
                }
                $successCount++;
            } catch (Throwable $e) {
                if ($lifecycle) {
                    try { $lifecycle->recordBatchItem($actor,$batchId,(int)$student['id'],$student['_application']['referencenumber'],'failed',$window,'Replacement card could not be generated.'); }
                    catch (Throwable $auditError) { error_log('Replacement batch item rejected: '.$auditError->getMessage()); }
                    $this->db->failBatch($batchId);
                    // Never publish partially rendered replacement pages or an unaudited PDF.
                    throw new RuntimeException('Replacement batch failed. Refresh the queue and check the application photo.',0,$e);
                }
                // One bad student record must not kill the whole batch.
                $this->db->logBatchItem($batchId, (int) $student['id'], 'failed', $e->getMessage());
                $failures[] = [
                    'matric_no' => $student['matric_no'],
                    'error'     => $e->getMessage(),
                ];
            }
        }

        if ($successCount === 0) {
            $this->db->failBatch($batchId);
            throw new RuntimeException('Batch failed: no cards were generated.');
        }

        try {
            $mpdf->Output($outputFile, Destination::FILE);
            $this->db->completeBatch($batchId);
        } catch (Throwable $e) {
            $this->db->failBatch($batchId);
            throw $e;
        }

        return [
            'batch_id'      => $batchId,
            'pdf_path'      => $outputFile,
            'success_count' => $successCount,
            'failures'      => $failures,
        ];
    }
}
