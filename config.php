<?php
declare(strict_types=1);

$config = [
    "host" => "127.0.0.1",
    "port" => 3306,
    "database" => "lepestok_practice",
    "user" => "root",
    "password" => "",
];
if (is_file(__DIR__ . "/config.local.php")) {
    $config = array_replace($config, require __DIR__ . "/config.local.php");
}
return $config;
