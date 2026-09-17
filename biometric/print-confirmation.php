<?php
require __DIR__.'/../include/config.php';
require __DIR__.'/../class/Database.php';
require __DIR__.'/../include/session.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');
try { $actor=currentOfficer(); } catch (Throwable $e) { http_response_code(403); exit(json_encode(['message'=>'Authorized ID Card Officer login is required.'])); }
session_write_close();
try {
    echo json_encode(officerLifecycle(new Database())->printConfirmation($actor,filter_input(INPUT_GET,'batch_id',FILTER_VALIDATE_INT) ?: 0));
} catch (DomainException $e) { http_response_code(409); echo json_encode(['message'=>$e->getMessage()]); }
catch (Throwable $e) { http_response_code(500); echo json_encode(['message'=>'Unable to load confirmation.']); }
