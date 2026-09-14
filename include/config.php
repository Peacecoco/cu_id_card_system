<?php
// config.php - central configuration

// --- Database ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'idcard_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// --- Paths ---
define('BASE_PATH', dirname(__DIR__));
define('TEMPLATES_PATH', BASE_PATH . '/assets/idcardtemplates');
define('OUTPUT_PATH', BASE_PATH . '/output');
define('PROCESSED_PHOTOS_PATH', BASE_PATH . '/uploads/photos_processed');
define('MPDF_TEMP_PATH', BASE_PATH . '/tmp/mpdf');

/**
 * Convert a local Windows path into a URI that mPDF can reliably load from
 * HTML/CSS. mPDF otherwise treats some absolute drive-letter paths as URLs.
 */
function mpdfAssetUri(string $path): string
{
    if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $path)) {
        return $path;
    }

    return 'file:///' . ltrim(str_replace('\\', '/', $path), '/');
}

// --- Card dimensions (CR80 standard, in mm) ---
// Adjust these if the university's card stock differs.
define('CARD_WIDTH_MM', 54);
define('CARD_HEIGHT_MM', 86);
define('HEADER_HEIGHT_MM', 20);
define('MIDDLE_HEIGHT_MM', 42);
define('FOOTER_HEIGHT_MM', 24);
// define('HEADER_HEIGHT_MM', 22.26);
// define('MIDDLE_HEIGHT_MM', 27.39);
// define('FOOTER_HEIGHT_MM', 35.95);


// --- Photo processing target size (matches print size, avoids over-embedding resolution) ---
define('PHOTO_TARGET_WIDTH_PX', 260);   // ~22mm at 300dpi
define('PHOTO_TARGET_HEIGHT_PX', 307);  // ~26mm at 300dpi
define('PHOTO_JPEG_QUALITY', 88);       // visually lossless, keeps file size sane

// --- Error reporting (disable display_errors in production) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
