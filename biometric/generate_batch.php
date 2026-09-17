<?php
// ============================================================
// biometric/generate_batch.php
//
// Entry point. Run from CLI:
//   php biometric/generate_batch.php <college_id>
//
// Or adapt the bottom section for a web-triggered version
// (e.g. called from an admin panel button).
// ============================================================

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../include/config.php';
require __DIR__ . '/../class/Database.php';
require __DIR__ . '/../class/PhotoProcessor.php';
require __DIR__ . '/../class/Renderer.php';
require __DIR__ . '/../include/session.php';

$isCli = PHP_SAPI === 'cli';
try {
    $actor=currentOfficer();
} catch (Throwable $e) {
    if ($isCli) { fwrite(STDERR,"Authorized ID Card Officer identity is required.\n"); exit(1); }
    http_response_code(403); header('Content-Type: application/json'); exit(json_encode(['message'=>'Authorized ID Card Officer login is required.']));
}
if (!$isCli) {
    if (($_SERVER['REQUEST_METHOD'] ?? '')!=='POST') { http_response_code(405); header('Allow: POST'); exit('Use POST to generate a batch.'); }
    try { requireOfficerCsrf($actor); } catch (DomainException $e) { header('Content-Type: application/json'); exit(json_encode(['message'=>$e->getMessage()])); }
    session_write_close();
}
$request = $isCli ? [] : $_POST;
$collegeId = $isCli
    ? (isset($argv[1]) ? (int) $argv[1] : null)
    : (isset($request['college_id']) ? (int) $request['college_id'] : null);
$level = $isCli
    ? (isset($argv[2]) ? (int) $argv[2] : null)
    : (isset($request['level']) ? (int) $request['level'] : null);
$programmeId = $isCli
    ? (isset($argv[3]) ? (int) $argv[3] : null)
    : (isset($request['programme_id']) ? (int) $request['programme_id'] : null);
$studentIds = !$isCli && isset($_POST['student_ids']) && is_array($_POST['student_ids'])
    ? $_POST['student_ids']
    : [];
$preview = !$isCli && isset($_POST['preview']) && $_POST['preview'] === '1';
$inline = !$isCli && isset($_GET['inline']) && $_GET['inline'] === '1';
$temporary = !$isCli && isset($request['temporary']) && $request['temporary'] === '1';
$generatedByInput = !$isCli && isset($_POST['generated_by']) && is_string($_POST['generated_by'])
    ? $_POST['generated_by']
    : null;
$generatedBy = in_array($generatedByInput, ['selective', 'awaiting-print', 'temporary', 'permanent'], true)
    ? $generatedByInput
    : null;

if (!$collegeId && empty($studentIds) && $generatedBy!=='awaiting-print') {
    $message = $isCli
        ? "Usage: php biometric/generate_batch.php <college_id>\n"
        : 'Missing or invalid college_id.';

    if ($isCli) {
        fwrite(STDERR, $message);
    } else {
        http_response_code(400);
        echo $message;
    }
    exit(1);
}

try {
    $db = new Database();
    $renderer = new Renderer($db);
    if ($generatedBy==='awaiting-print') {
        $references=$_POST['reference_numbers'] ?? [];
        $window=$_POST['window'] ?? 'after';
        if (!is_array($references) || count($references)>200 || array_filter($references,static fn($v)=>!is_string($v)) || !is_string($window) || $studentIds || $collegeId || $temporary) throw new DomainException('Invalid replacement selection.');
        $lifecycle=officerLifecycle($db);
        $selection=$lifecycle->printingSelection($actor,$references,$window);
        $result=$renderer->generateReplacementBatch($selection,$lifecycle,$actor,$window);
    } else {
    if (!empty($_POST['reference_numbers'])) throw new DomainException('Use the replacement printing queue for applications.');

    // Step 1: pre-process photos (skips already-processed ones)
    $students = empty($studentIds)
        ? $db->getStudentsByCollege($collegeId, $level, $programmeId)
        : $db->getActiveStudentsByIds($studentIds);
    if (empty($students)) {
        throw new RuntimeException('No active students were selected.');
    }
    [$processedCount, $photoFailures] = PhotoProcessor::processCollegeBatch($db, $students);

    if ($isCli) {
        echo "Photos processed: {$processedCount}\n";
        if (!empty($photoFailures)) {
            echo "Photo failures:\n";
            foreach ($photoFailures as $f) {
                echo "  - {$f['matric_no']}: {$f['error']}\n";
            }
        }
    }

    // Step 2: render the batch PDF
    $renderer = new Renderer($db);
    $result = empty($studentIds)
        ? $renderer->generateCollegeBatch($collegeId, generatedBy: $isCli ? 'cli' : ($generatedBy ?: 'web'), temporary: $temporary, level: $level, programmeId: $programmeId)
        : $renderer->generateStudentsBatch($db->getActiveStudentsByIds($studentIds), generatedBy: $generatedBy ?: 'selective', temporary: $temporary);
    }

    if ($isCli) {
        echo "\nBatch complete.\n";
        echo "Batch ID: {$result['batch_id']}\n";
        echo "PDF: {$result['pdf_path']}\n";
        echo "Cards generated: {$result['success_count']}\n";

        if (!empty($result['failures'])) {
            echo "Card generation failures:\n";
            foreach ($result['failures'] as $f) {
                echo "  - {$f['matric_no']}: {$f['error']}\n";
            }
        }

        exit(0);
    }

    if (!is_file($result['pdf_path']) || !is_readable($result['pdf_path'])) {
        throw new RuntimeException('The batch was generated but its PDF file could not be read.');
    }

    $downloadName = basename($result['pdf_path']);
    if ($preview) {
        $pdfUrl = 'batch-pdf.php?batch_id=' . (int)$result['batch_id'];
        header('Content-Type: application/json');
        echo json_encode([
            'pdf_url' => $pdfUrl,
            'download_name' => $downloadName,
            'batch_id' => $result['batch_id'],
            'success_count' => $result['success_count'],
        ]);
        exit(0);
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . addcslashes($downloadName, "\\\"") . '"');
    header('Content-Length: ' . filesize($result['pdf_path']));
    header('Cache-Control: private, no-store');
    readfile($result['pdf_path']);
    exit(0);
} catch (Throwable $e) {
    $message = "Fatal error: {$e->getMessage()}\n";

    if ($isCli) {
        fwrite(STDERR, $message);
    } else {
        error_log($message);
        if ($e->getPrevious()) error_log('Generation cause: '.$e->getPrevious()->getMessage());
        http_response_code($e instanceof DomainException ? 409 : 500);
        header('Content-Type: application/json');
        echo json_encode(['message'=>$e instanceof DomainException ? $e->getMessage() : 'Unable to generate this batch. Check the selected application photo and refresh the queue.']);
    }
    exit(1);
}
