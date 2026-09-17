<?php
$collectionSearch=trim((string)(filter_input(INPUT_GET,'search',FILTER_UNSAFE_RAW) ?: ''));
$collectionRows=[];
$collectionError='';
try {
    if (!$officer) throw new DomainException('Sign in to view replacement cards awaiting collection.');
    $collectionRows=officerLifecycle(new Database())->collectionQueue($officer,$collectionSearch);
} catch (DomainException $e) { $collectionError=$e->getMessage(); }
catch (Throwable $e) { $collectionError='Unable to load the collection queue.'; }
