<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../../class/Database.php';

$batchPrintError = '';
$batchPrintMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'confirm-batch-printed') {
    try {
        $database = new Database();
        $batchId = filter_input(INPUT_POST, 'batch_id', FILTER_VALIDATE_INT);
        $referenceNumbers = isset($_POST['reference_numbers']) && is_array($_POST['reference_numbers'])
            ? $_POST['reference_numbers']
            : [];

        if (!$batchId || !$database->markBatchAsPrinted($batchId)) {
            $batchPrintError = 'This batch is not available for print confirmation.';
        } else {
            $applicationCount = $database->markApplicationsAsPrinted($referenceNumbers, 'local');
            $batchPrintMessage = 'Batch confirmed as physically printed.' . ($applicationCount > 0 ? ' ' . $applicationCount . ' application(s) moved to printed.' : '');
        }
    } catch (Throwable $exception) {
        $batchPrintError = 'Unable to confirm the batch as printed.';
    }
}

$currentUser = [
    'name' => 'Mr. Adeshina',
    'role' => 'Administrator',
];

$menuGroups = [
    [
        'label' => 'Biometric',
        'expanded' => true,
        'items' => [
            ['label' => 'Awaiting Printing', 'active' => ($pageKey === 'awaiting-printing'), 'children' => [], 'href' => 'awaiting-printing.php'],
            ['label' => 'Temporary ID card', 'active' => ($pageKey === 'temporary-id'), 'children' => [], 'href' => 'temporary-id.php'],
            ['label' => 'Permanent ID card', 'active' => ($pageKey === 'permanent-id'), 'children' => [], 'href' => 'permanent-id.php'],
            ['label' => 'Selective Printing', 'active' => ($pageKey === 'selective-printing'), 'children' => [], 'href' => 'selective-printing.php'],
        ],
    ],
    ['label' => 'Report', 'expanded' => ($pageKey === 'reports'), 'href' => 'reports.php', 'items' => [
        ['label' => 'Reports and Audit', 'active' => ($pageKey === 'reports'), 'children' => [], 'href' => 'reports.php'],
    ]],
];
