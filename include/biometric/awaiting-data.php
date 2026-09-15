<?php
$awaitingPrintSearch = trim((string) (filter_input(INPUT_GET, 'awaiting_print_search', FILTER_UNSAFE_RAW) ?: ''));
$awaitingPrintApplications = [];
$awaitingPrintError = '';
$awaitingPrintMessage = '';
try {
    $database = new Database();

    $awaitingPrintApplications = $database->getAwaitingPrintApplications($awaitingPrintSearch);
} catch (Throwable $exception) {
    $awaitingPrintError = 'Unable to load the awaiting-print queue right now.';
}
