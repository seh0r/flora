<?php

if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit();
}
$c = require dirname(__DIR__) . "/config.php";
$db = new PDO(
    "mysql:host={$c["host"]};port={$c["port"]};charset=utf8mb4",
    $c["user"],
    $c["password"],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
);
$exists = $db->prepare(
    "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=?",
);
$exists->execute([$c["database"]]);
if ($exists->fetchColumn() > 0) {
    fwrite(
        STDERR,
        "База уже содержит таблицы. Установка остановлена, данные сохранены.\n",
    );
    exit(1);
}
if ($c["database"] !== "lepestok_practice") {
    throw new RuntimeException(
        "Для установки используйте имя lepestok_practice из SQL.",
    );
}
foreach (["schema.sql", "seed.sql", "migration_v3.sql"] as $file) {
    $db->exec(file_get_contents(dirname(__DIR__) . "/sql/" . $file));
}
$insert = $db->prepare(
    "INSERT INTO users(login,password_hash,full_name,phone,email,role) VALUES(?,?,?,?,?,?)",
);
$insert->execute([
    "adminmark",
    password_hash("FlowerAdmin2026!", PASSWORD_DEFAULT),
    "Администратор магазина",
    "8(999)000-00-01",
    "admin@example.test",
    "admin",
]);
$insert->execute([
    "demouser",
    password_hash("FlowerDemo2026!", PASSWORD_DEFAULT),
    "Анна Соколова",
    "8(999)000-00-02",
    "demo@example.test",
    "customer",
]);
echo "Установка завершена. adminmark / FlowerAdmin2026!; demouser / FlowerDemo2026!\n";
