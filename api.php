<?php
declare(strict_types=1);
require __DIR__ . "/app.php";
header("Content-Type: application/json; charset=utf-8");
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    header("Allow: GET");
    echo json_encode(
        ["error" => "Метод не поддерживается"],
        JSON_UNESCAPED_UNICODE,
    );
    exit();
}
$resource = $_GET["resource"] ?? "";
if ($resource === "login-available") {
    $login = trim($_GET["login"] ?? "");
    $valid = (bool) preg_match('/^[A-Za-z0-9]{6,64}$/D', $login);
    echo json_encode([
        "valid" => $valid,
        "available" =>
            $valid &&
            !query("SELECT id FROM users WHERE login=?", [$login])->fetch(),
    ]);
} elseif ($resource === "orders") {
    if (!$user) {
        http_response_code(401);
        echo '{"error":"Authentication required"}';
        exit();
    }
    $pageNumber = max(1, (int) ($_GET["p"] ?? 1));
    $offset = min(100000, ($pageNumber - 1) * 20);
    $args = [];
    $conditions = [];
    if ($user["role"] !== "admin") {
        $conditions[] = "o.user_id=?";
        $args[] = $user["id"];
    }
    if (isset($_GET["status"]) && ctype_digit($_GET["status"])) {
        $conditions[] = "o.status_id=?";
        $args[] = (int) $_GET["status"];
    }
    $where = $conditions ? " WHERE " . implode(" AND ", $conditions) : "";
    $rows = query(
        "SELECT o.id,o.quantity,o.unit_price,o.delivery_date,b.name AS bouquet,s.name AS status FROM orders o JOIN bouquets b ON b.id=o.bouquet_id JOIN statuses s ON s.id=o.status_id" .
            $where .
            " ORDER BY o.id DESC LIMIT 20 OFFSET $offset",
        $args,
    )->fetchAll();
    echo json_encode(
        ["orders" => $rows, "page" => $pageNumber],
        JSON_UNESCAPED_UNICODE,
    );
} else {
    http_response_code(404);
    echo '{"error":"Resource not found"}';
}
