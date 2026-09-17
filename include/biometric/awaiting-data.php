<?php
$awaitingPrintSearch = trim((string) (filter_input(INPUT_GET, 'awaiting_print_search', FILTER_UNSAFE_RAW) ?: ''));
$awaitingPrintApplications = [];
$awaitingPrintError = '';
$awaitingPrintMessage = '';
$printingWindow=filter_input(INPUT_GET,'window',FILTER_UNSAFE_RAW) ?: 'after';
try {
    if (!$officer) throw new DomainException('Sign in to view replacement applications.');
    $database = new Database();
    $awaitingPrintApplications = officerLifecycle($database)->printingQueue($officer,$awaitingPrintSearch,$printingWindow);
} catch (DomainException $exception) {
    $awaitingPrintError=$exception->getMessage();
} catch (Throwable $exception) {
    $awaitingPrintError = 'Unable to load the awaiting-print queue right now.';
}
