<?php
require __DIR__.'/../include/config.php';
require __DIR__.'/../class/Database.php';
require __DIR__.'/../include/session.php';
try { currentOfficer(); } catch (Throwable $e) { http_response_code(403); exit('Authorized ID Card Officer login is required.'); }
session_write_close();
$id=filter_input(INPUT_GET,'batch_id',FILTER_VALIDATE_INT) ?: 0;
$batch=(new Database())->getBatch($id);
$root=realpath(OUTPUT_PATH);
$path=$batch ? realpath($batch['pdf_path']) : false;
if (!$batch || $batch['status']!=='completed' || !$root || !$path || strcasecmp(dirname($path),$root)!==0 || !is_file($path)) { http_response_code(404); exit('Batch PDF unavailable.'); }
header('Content-Type: application/pdf');
header('Content-Disposition: '.(isset($_GET['download']) ? 'attachment' : 'inline').'; filename="'.basename($path).'"');
header('Cache-Control: private, no-store');
header('Content-Length: '.filesize($path));
readfile($path);
