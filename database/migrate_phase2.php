<?php
declare(strict_types=1);
// CLI only. Back up every existing table before additive DDL; verify legacy rows afterward.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/../include/config.php';
$db=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$db->exec("SET time_zone = '+01:00'");
if (!$db->query("SELECT GET_LOCK('cu_idcard_migration_002',10)")->fetchColumn()) throw new RuntimeException('Another migration is running.');
try {
    $backup=[];
    foreach ($db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
        $backup[$table]=['schema'=>$db->query('SHOW CREATE TABLE `'.$table.'`')->fetch(PDO::FETCH_NUM)[1],'rows'=>$db->query('SELECT * FROM `'.$table.'`')->fetchAll()];
    }
    $path=tempnam(sys_get_temp_dir(),'cu-phase2-backup-');
    if (!$path || file_put_contents($path,json_encode($backup,JSON_THROW_ON_ERROR|JSON_PRETTY_PRINT))===false) throw new RuntimeException('Backup failed; migration not started.');
    echo "Backup: {$path}\n";
    $migration=file_get_contents(__DIR__.'/migrations/002_replacement_lifecycle.sql');
    foreach (explode(';',$migration) as $statement) {
        if (trim($statement)==='') continue;
        $q=$db->query($statement);$q->closeCursor();
    }
    foreach ($backup as $table=>$data) {
        if (!$data['rows']) continue;
        $columns=array_keys($data['rows'][0]);
        $current=$db->query('SELECT `'.implode('`,`',$columns).'` FROM `'.$table.'`')->fetchAll();
        $normalize=static function(array $rows): array {$rows=array_map(static fn($r)=>json_encode($r,JSON_THROW_ON_ERROR),$rows);sort($rows);return $rows;};
        if ($normalize($current)!==$normalize($data['rows'])) throw new RuntimeException("Legacy rows changed in {$table}; review backup before proceeding.");
    }
    echo "Migration 002 applied. All pre-existing column values preserved.\n";
    foreach($db->query("SELECT applicationid,status,printedat FROM idcardapplications WHERE paymentstatus IS NULL") as $row) echo 'Legacy reconciliation: '.json_encode($row)."\n";
} finally { $db->query("SELECT RELEASE_LOCK('cu_idcard_migration_002')"); }
