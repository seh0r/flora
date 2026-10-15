<?php
if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit();
}
$c = require dirname(__DIR__) . "/config.php";
$db = new PDO(
    "mysql:host={$c["host"]};port={$c["port"]};dbname={$c["database"]};charset=utf8mb4",
    $c["user"],
    $c["password"],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
);
$db->exec(file_get_contents(dirname(__DIR__) . "/sql/migration_v3.sql"));
echo "Миграция модуля 3 применена.\n";
