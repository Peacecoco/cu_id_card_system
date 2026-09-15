<?php
$reportRows = [];
$reportError = '';
$reportCollegeId = filter_input(INPUT_GET, 'report_college_id', FILTER_VALIDATE_INT) ?: 0;
$reportStatus = filter_input(INPUT_GET, 'report_status', FILTER_UNSAFE_RAW) ?: '';
$reportFromInput = trim((string) (filter_input(INPUT_GET, 'report_from', FILTER_UNSAFE_RAW) ?: ''));
$reportToInput = trim((string) (filter_input(INPUT_GET, 'report_to', FILTER_UNSAFE_RAW) ?: ''));
$reportFrom = '';
$reportTo = '';
foreach ([['input' => $reportFromInput, 'output' => 'reportFrom'], ['input' => $reportToInput, 'output' => 'reportTo']] as $reportDate) {
    if ($reportDate['input'] === '') {
        continue;
    }

    $parsedDate = DateTime::createFromFormat('d/m/Y', $reportDate['input']);
    $dateErrors = DateTime::getLastErrors();
    if ($parsedDate !== false && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0))) {
        ${$reportDate['output']} = $parsedDate->format('Y-m-d');
    }
}

try {
    $database = new Database();
    $collegeRows = $database->getAllColleges();
    $reportRows = $database->getBatchReports($reportCollegeId ?: null, $reportStatus, $reportFrom, $reportTo);
} catch (Throwable $exception) {
    $reportError = 'Unable to load report data right now.';
    $collegeRows = [];
}
