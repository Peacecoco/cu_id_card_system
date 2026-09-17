<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../../class/Database.php';
require_once __DIR__ . '/../session.php';

$batchPrintError = '';
$batchPrintMessage = '';
$officer=null;
$officerCsrf='';
try { $officer=currentOfficer(); $officerCsrf=officerCsrfToken($officer); }
catch (Throwable $e) { $batchPrintError='Authorized ID Card Officer login is required.'; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!$officer) { http_response_code(403); throw new DomainException('Authorized ID Card Officer login is required.'); }
        requireOfficerCsrf($officer);
        session_write_close();
        $database = new Database();
        $lifecycle=officerLifecycle($database);
        if (($_POST['action'] ?? '')==='confirm-batch-printed') {
            $batchId=filter_input(INPUT_POST,'batch_id',FILTER_VALIDATE_INT) ?: 0;
            $batch=$database->getBatch($batchId);
            if (!$batch) throw new DomainException('Batch not found.');
            if ($batch['generated_by']==='awaiting-print') {
                $lifecycle->confirmPrinted($officer,$batchId);
            } elseif (!$database->markBatchAsPrinted($batchId)) {
                throw new DomainException('This batch is not available for print confirmation.');
            }
            $batchPrintMessage='Batch confirmed as physically printed.';
        } elseif (($_POST['action'] ?? '')==='confirm-collection') {
            $reference=$_POST['reference'] ?? '';
            if (!is_string($reference)) throw new DomainException('Invalid application reference.');
            $lifecycle->collect($officer,$reference);
            $batchPrintMessage='Replacement ID card confirmed as collected.';
        } else {
            throw new DomainException('Unknown officer action.');
        }
    } catch (DomainException $exception) {
        if (http_response_code()!==403) http_response_code(409);
        $batchPrintError=$exception->getMessage();
    } catch (Throwable $exception) {
        http_response_code(500);
        error_log($exception->getMessage());
        $batchPrintError = 'Unable to complete this action. Refresh and try again.';
    }
}
if (session_status()===PHP_SESSION_ACTIVE) session_write_close();

$currentUser = [
    'name' => $officer ? $officer->id : 'Sign in required',
    'role' => $officer ? 'ID Card Officer' : '',
];

$menuGroups = [
    [
        'label' => 'Biometric',
        'expanded' => true,
        'items' => [
            ['label' => 'Awaiting Printing', 'active' => ($pageKey === 'awaiting-printing'), 'children' => [], 'href' => 'awaiting-printing.php'],
            ['label' => 'Awaiting Collection', 'active' => ($pageKey === 'collection'), 'children' => [], 'href' => 'collection.php'],
            ['label' => 'Temporary ID card', 'active' => ($pageKey === 'temporary-id'), 'children' => [], 'href' => 'temporary-id.php'],
            ['label' => 'Permanent ID card', 'active' => ($pageKey === 'permanent-id'), 'children' => [], 'href' => 'permanent-id.php'],
            ['label' => 'Selective Printing', 'active' => ($pageKey === 'selective-printing'), 'children' => [], 'href' => 'selective-printing.php'],
        ],
    ],
    ['label' => 'Report', 'expanded' => ($pageKey === 'reports'), 'href' => 'reports.php', 'items' => [
        ['label' => 'Reports and Audit', 'active' => ($pageKey === 'reports'), 'children' => [], 'href' => 'reports.php'],
    ]],
];
