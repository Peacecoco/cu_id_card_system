<?php
// Compatibility entry point only; page rendering belongs to dedicated files.
$legacyPages = [
    'permanent-id' => 'permanent-id.php',
    'temporary-id' => 'temporary-id.php',
    'selective-printing' => 'selective-printing.php',
    'awaiting-print' => 'awaiting-printing.php',
    'awaiting-printing' => 'awaiting-printing.php',
    'reports' => 'reports.php',
];
$legacySection = $_GET['section'] ?? '';
$target = is_string($legacySection) ? ($legacyPages[$legacySection] ?? 'permanent-id.php') : 'permanent-id.php';
$query = $_GET;
unset($query['section']);
if ($query) {
    $target .= '?' . http_build_query($query);
}
// Preserve bodies from older open pages submitting print confirmations.
header('Location: ' . $target, true, $_SERVER['REQUEST_METHOD'] === 'POST' ? 307 : 302);
exit;
