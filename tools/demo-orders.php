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
$uid = $db->query("SELECT id FROM users WHERE login='demouser'")->fetchColumn();
if (!$uid) {
    exit("Сначала setup.php\n");
}
$check = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id=?");
$check->execute([$uid]);
if ($check->fetchColumn()) {
    exit(
        "У демопользователя уже есть заказы. Повторное заполнение пропущено.\n"
    );
}
$q = $db->prepare(
    "INSERT INTO orders(user_id,bouquet_id,status_id,quantity,unit_price,delivery_date,delivery_slot,address,recipient,recipient_phone,payment_method,note) VALUES(?,?,?,?,?,DATE_ADD(CURDATE(),INTERVAL ? DAY),?,?,?,?,?,?)",
);
$log = $db->prepare(
    "INSERT INTO order_history(order_id,old_status_id,new_status_id,changed_by) VALUES(?,?,?,?)",
);
$admin = $db
    ->query("SELECT id FROM users WHERE login='adminmark'")
    ->fetchColumn();
$db->beginTransaction();
for ($i = 0; $i < 8; $i++) {
    $bid = ($i % 4) + 1;
    $status = ($i % 5) + 1;
    $price = [3290, 3890, 2990, 4590][$i % 4];
    $q->execute([
        $uid,
        $bid,
        $status,
        1,
        $price,
        $i + 1,
        "13:00–16:00",
        "Москва, улица Арбат, дом 10",
        "Анна Соколова",
        "8(999)000-00-03",
        $i % 2 ? "card" : "cash",
        "Хорошего дня!",
    ]);
    $id = $db->lastInsertId();
    $log->execute([$id, null, 1, $uid]);
    if ($status === 5) {
        $log->execute([$id, 1, 5, $admin]);
    } else {
        for ($s = 2; $s <= $status; $s++) {
            $log->execute([$id, $s - 1, $s, $admin]);
        };
    }
}
$db->commit();
echo "Добавлено 8 демонстрационных заказов.\n";
