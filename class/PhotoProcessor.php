<?php
// ============================================================
// class/PhotoProcessor.php
//
// Prepares a raw uploaded passport photo for printing:
//   - resizes to the actual print size (no point embedding a
//     4000x4000 photo into a 22mm x 26mm box)
//   - normalizes basic brightness so dark photos don't print
//     unnecessarily heavy
//   - re-compresses to a sane JPEG quality
//
// Run as a separate step BEFORE batch generation, so a single
// corrupt photo fails one student, not the whole batch.
// ============================================================

class PhotoProcessor
{
    /**
     * Process one photo and return the path to the processed file.
     * Throws on failure so the caller can log and skip that student.
     */
    public static function process(string $sourcePath, string $studentMatricNo): string
    {
        if (!file_exists($sourcePath)) {
            throw new RuntimeException("Photo not found: {$sourcePath}");
        }

        $imageInfo = getimagesize($sourcePath);
        if ($imageInfo === false) {
            throw new RuntimeException("File is not a valid image: {$sourcePath}");
        }

        $mime = $imageInfo['mime'];
        $source = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png'  => imagecreatefrompng($sourcePath),
            default      => throw new RuntimeException("Unsupported image type: {$mime}"),
        };

        if ($source === false) {
            throw new RuntimeException("Failed to read image: {$sourcePath}");
        }

        $targetW = PHOTO_TARGET_WIDTH_PX;
        $targetH = PHOTO_TARGET_HEIGHT_PX;

        $resized = imagecreatetruecolor($targetW, $targetH);

        // Preserve aspect ratio, crop to fill (avoids distortion/stretching)
        $srcW = imagesx($source);
        $srcH = imagesy($source);
        $srcRatio = $srcW / $srcH;
        $targetRatio = $targetW / $targetH;

        if ($srcRatio > $targetRatio) {
            $cropH = $srcH;
            $cropW = (int) ($srcH * $targetRatio);
            $cropX = (int) (($srcW - $cropW) / 2);
            $cropY = 0;
        } else {
            $cropW = $srcW;
            $cropH = (int) ($srcW / $targetRatio);
            $cropX = 0;
            $cropY = (int) (($srcH - $cropH) / 2);
        }

        imagecopyresampled(
            $resized, $source,
            0, 0, $cropX, $cropY,
            $targetW, $targetH, $cropW, $cropH
        );

        // Light brightness/contrast normalization so under-lit photos
        // don't print artificially dark (dark photos cost more ribbon/ink).
        imagefilter($resized, IMG_FILTER_BRIGHTNESS, 5);
        imagefilter($resized, IMG_FILTER_CONTRAST, -3);

        if (!is_dir(PROCESSED_PHOTOS_PATH)) {
            mkdir(PROCESSED_PHOTOS_PATH, 0755, true);
        }

        $outputPath = PROCESSED_PHOTOS_PATH . '/' . preg_replace('/[^A-Za-z0-9_-]/', '_', $studentMatricNo) . '.jpg';

        imagejpeg($resized, $outputPath, PHOTO_JPEG_QUALITY);

        imagedestroy($source);
        imagedestroy($resized);

        return $outputPath;
    }

    /**
     * Batch-process all students for a college who don't yet have
     * a processed photo. Returns [success_count, failures[]].
     */
    public static function processCollegeBatch(Database $db, array $students): array
    {
        $successCount = 0;
        $failures = [];

        foreach ($students as $student) {
            if (!empty($student['photo_processed_path']) && file_exists($student['photo_processed_path'])) {
                continue; // already processed
            }

            try {
                $processedPath = self::process($student['photo_path'], $student['matric_no']);
                $db->updateStudentProcessedPhoto((int) $student['id'], $processedPath);
                $successCount++;
            } catch (Throwable $e) {
                $failures[] = [
                    'student_id' => $student['id'],
                    'matric_no'  => $student['matric_no'],
                    'error'      => $e->getMessage(),
                ];
            }
        }

        return [$successCount, $failures];
    }
}
