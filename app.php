<?php
declare(strict_types=1);
date_default_timezone_set("Europe/Moscow");
ini_set("session.use_strict_mode", "1");
session_name("lepestok_session");
session_set_cookie_params([
    "httponly" => true,
    "samesite" => "Lax",
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
]);
session_start();
header(
    "Content-Security-Policy: default-src 'self'; img-src 'self'; style-src 'self'; script-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'",
);
header("Cache-Control: no-store");
$c = require __DIR__ . "/config.php";
try {
    $db = new PDO(
        "mysql:host={$c["host"]};port={$c["port"]};dbname={$c["database"]};charset=utf8mb4",
        $c["user"],
        $c["password"],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    );
} catch (PDOException $e) {
    http_response_code(503);
    exit(
        "База данных недоступна. Запустите MySQL в XAMPP и выполните установку по README."
    );
}
function query(string $sql, array $args = []): PDOStatement
{
    global $db;
    $s = $db->prepare($sql);
    $s->execute($args);
    return $s;
}
function e(mixed $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, "UTF-8");
}
function redirect(string $page): never
{
    header("Location: index.php?page=" . $page);
    exit();
}
function money(mixed $n): string
{
    return number_format((float) $n, 0, ",", " ") . " ₽";
}
function csrf(): string
{
    return '<input type="hidden" name="csrf" value="' .
        e($_SESSION["csrf"]) .
        '">';
}
function phone_ok(string $s): bool
{
    return (bool) preg_match(
        '/^8\([0-9]{3}\)[0-9]{3}-[0-9]{2}-[0-9]{2}$/D',
        $s,
    );
}
function flash(string $message): void
{
    $_SESSION["flash"] = $message;
}
require __DIR__ . "/history.php";
$_SESSION["csrf"] ??= bin2hex(random_bytes(32));
$user = isset($_SESSION["uid"])
    ? query(
        "SELECT id,login,full_name,phone,email,role FROM users WHERE id=?",
        [$_SESSION["uid"]],
    )->fetch()
    : false;
$page = $_GET["page"] ?? "home";
$allowed = ["home", "login", "register", "order", "account", "admin"];
if (!in_array($page, $allowed, true)) {
    http_response_code(404);
    $page = "404";
}
if (in_array($page, ["order", "account", "admin"], true) && !$user) {
    if ($page === "order") {
        $_SESSION["pending_bouquet"] = max(1, (int) ($_GET["bouquet"] ?? 1));
    }
    flash("Войдите, чтобы продолжить.");
    redirect("login");
}
if ($page === "admin" && $user["role"] !== "admin") {
    http_response_code(403);
    $page = "403";
}
$errors = [];
$value = fn($name, $default = "") => e($_POST[$name] ?? $default);
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!hash_equals($_SESSION["csrf"], (string) ($_POST["csrf"] ?? ""))) {
        http_response_code(403);
        exit("Сессия формы устарела. Обновите страницу.");
    }
    $action = $_POST["action"] ?? "";
    if ($action === "logout") {
        $_SESSION = [];
        session_regenerate_id(true);
        redirect("home");
    }
    if ($action === "register" && $page === "register") {
        $login = trim($_POST["login"] ?? "");
        $password = $_POST["password"] ?? "";
        $name = trim($_POST["full_name"] ?? "");
        $phone = trim($_POST["phone"] ?? "");
        $email = trim($_POST["email"] ?? "");
        if (!preg_match('/^[A-Za-z0-9]{6,64}$/D', $login)) {
            $errors["login"] = "От 6 до 64 латинских букв или цифр.";
        }
        if (
            strlen($password) < 8 ||
            strlen($password) > 72 ||
            !preg_match("/[a-zA-Z]/", $password) ||
            !preg_match("/[0-9]/", $password)
        ) {
            $errors["password"] =
                "От 8 до 72 байт, минимум одна латинская буква и цифра.";
        }
        if (
            !preg_match('/^[\p{L}]+(?:[ -][\p{L}]+){1,5}$/u', $name) ||
            mb_strlen($name) > 200
        ) {
            $errors["full_name"] =
                "Укажите фамилию и имя, отчество — при наличии. Только буквы, пробелы и дефисы.";
        }
        if (!phone_ok($phone)) {
            $errors["phone"] = "Формат: 8(999)123-45-67.";
        }
        if (
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            strlen($email) > 254
        ) {
            $errors["email"] = "Укажите корректный email.";
        }
        if (query("SELECT id FROM users WHERE login=?", [$login])->fetch()) {
            $errors["login"] = "Этот логин уже занят.";
        }
        if (!$errors) {
            try {
                query(
                    "INSERT INTO users(login,password_hash,full_name,phone,email) VALUES(?,?,?,?,?)",
                    [
                        $login,
                        password_hash($password, PASSWORD_DEFAULT),
                        $name,
                        $phone,
                        $email,
                    ],
                );
                flash("Аккаунт создан. Теперь войдите.");
                redirect("login");
            } catch (PDOException $ex) {
                if ($ex->getCode() === "23000") {
                    $errors["login"] = "Этот логин уже занят.";
                } else {
                    throw $ex;
                }
            }
        }
    }
    if ($action === "login" && $page === "login") {
        $login = mb_substr(trim($_POST["login"] ?? ""), 0, 64);
        $attempts = (int) query(
            "SELECT COUNT(*) FROM login_attempts WHERE login=? AND succeeded=0 AND attempted_at > NOW() - INTERVAL 15 MINUTE",
            [$login],
        )->fetchColumn();
        $candidate = query("SELECT * FROM users WHERE login=?", [
            $login,
        ])->fetch();
        if ($attempts >= 10) {
            http_response_code(429);
            $errors["general"] =
                "Слишком много попыток. Повторите вход через 15 минут.";
        } elseif (
            !$candidate ||
            !password_verify(
                $_POST["password"] ?? "",
                $candidate["password_hash"],
            )
        ) {
            query("INSERT INTO login_attempts(login,succeeded) VALUES(?,0)", [
                $login,
            ]);
            $errors["general"] = "Неверный логин или пароль.";
        } else {
            query("INSERT INTO login_attempts(login,succeeded) VALUES(?,1)", [
                $login,
            ]);
            session_regenerate_id(true);
            $_SESSION["uid"] = $candidate["id"];
            $_SESSION["csrf"] = bin2hex(random_bytes(32));
            flash("Добро пожаловать!");
            if (isset($_SESSION["pending_bouquet"])) {
                $pending = $_SESSION["pending_bouquet"];
                unset($_SESSION["pending_bouquet"]);
                redirect("order&bouquet=" . (int) $pending);
            }
            redirect($candidate["role"] === "admin" ? "admin" : "account");
        }
    }
    if ($action === "order" && $page === "order" && $user) {
        $bouquet = query("SELECT * FROM bouquets WHERE id=? AND active=1", [
            (int) ($_POST["bouquet_id"] ?? 0),
        ])->fetch();
        $quantity = filter_var($_POST["quantity"] ?? "", FILTER_VALIDATE_INT, [
            "options" => ["min_range" => 1, "max_range" => 10],
        ]);
        $date = $_POST["delivery_date"] ?? "";
        $parsed = DateTimeImmutable::createFromFormat("!Y-m-d", $date);
        if (!$bouquet) {
            $errors["bouquet_id"] = "Выберите доступный букет.";
        }
        if (!$quantity) {
            $errors["quantity"] = "Количество от 1 до 10.";
        }
        if (
            !$parsed ||
            $parsed->format("Y-m-d") !== $date ||
            $date < date("Y-m-d", strtotime("+1 day")) ||
            $date > date("Y-m-d", strtotime("+90 days"))
        ) {
            $errors["delivery_date"] =
                "Выберите дату от завтра до 90 дней вперёд.";
        }
        if (
            !in_array(
                $_POST["delivery_slot"] ?? "",
                ["10:00–13:00", "13:00–16:00", "16:00–19:00"],
                true,
            )
        ) {
            $errors["delivery_slot"] = "Выберите время доставки.";
        }
        foreach (
            ["address" => [10, 300], "recipient" => [2, 150]]
            as $key => $limits
        ) {
            $len = mb_strlen(trim($_POST[$key] ?? ""));
            if ($len < $limits[0] || $len > $limits[1]) {
                $errors[$key] = "От {$limits[0]} до {$limits[1]} символов.";
            }
        }
        if (!phone_ok(trim($_POST["recipient_phone"] ?? ""))) {
            $errors["recipient_phone"] = "Формат: 8(999)123-45-67.";
        }
        if (!in_array($_POST["payment_method"] ?? "", ["cash", "card"], true)) {
            $errors["payment_method"] = "Выберите способ оплаты.";
        }
        if (mb_strlen($_POST["note"] ?? "") > 500) {
            $errors["note"] = "Не более 500 символов.";
        }
        if (!$errors) {
            $db->beginTransaction();
            try {
                query(
                    "INSERT INTO orders(user_id,bouquet_id,quantity,unit_price,delivery_date,delivery_slot,address,recipient,recipient_phone,payment_method,note) VALUES(?,?,?,?,?,?,?,?,?,?,?)",
                    [
                        $user["id"],
                        $bouquet["id"],
                        $quantity,
                        $bouquet["price"],
                        $date,
                        $_POST["delivery_slot"],
                        trim($_POST["address"]),
                        trim($_POST["recipient"]),
                        trim($_POST["recipient_phone"]),
                        $_POST["payment_method"],
                        trim($_POST["note"] ?? ""),
                    ],
                );
                $id = $db->lastInsertId();
                if (function_exists("log_status")) {
                    log_status((int) $id, null, 1, (int) $user["id"]);
                }
                $db->commit();
                flash(
                    "Заказ №" .
                        $id .
                        " оформлен. Следите за статусом в кабинете.",
                );
                redirect("account");
            } catch (Throwable $ex) {
                $db->rollBack();
                throw $ex;
            }
        }
    }
    if (
        $action === "status" &&
        $page === "admin" &&
        $user &&
        $user["role"] === "admin"
    ) {
        $id = (int) ($_POST["order_id"] ?? 0);
        $next = (int) ($_POST["status_id"] ?? 0);
        $db->beginTransaction();
        try {
            $o = query("SELECT * FROM orders WHERE id=? FOR UPDATE", [
                $id,
            ])->fetch();
            $transitions = [
                1 => [2, 5],
                2 => [3, 5],
                3 => [4],
                4 => [],
                5 => [],
            ];
            if (!$o || !in_array($next, $transitions[$o["status_id"]], true)) {
                $errors["general"] =
                    "Переход недоступен: статус уже изменился или заказ завершён.";
            } else {
                query("UPDATE orders SET status_id=? WHERE id=?", [$next, $id]);
                if (function_exists("log_status")) {
                    log_status(
                        $id,
                        (int) $o["status_id"],
                        $next,
                        (int) $user["id"],
                    );
                }
            }
            $db->commit();
            if (!$errors) {
                flash("Статус заказа №" . $id . " обновлён.");
                redirect("admin");
            }
        } catch (Throwable $ex) {
            $db->rollBack();
            throw $ex;
        }
    }
}
$bouquets = query(
    "SELECT * FROM bouquets WHERE active=1 ORDER BY id",
)->fetchAll();
$statuses = query("SELECT * FROM statuses ORDER BY id")->fetchAll();
$statusNames = array_column($statuses, "name", "id");
$titles = [
    "home" => "Букеты с теплом",
    "register" => "Создать аккаунт",
    "login" => "С возвращением",
    "order" => "Оформление заказа",
    "account" => "Мои заказы",
    "admin" => "Заказы магазина",
    "403" => "Нет доступа",
    "404" => "Страница не найдена",
];
function field(
    string $name,
    string $label,
    string $type = "text",
    string $default = "",
    string $extra = "",
): void {
    global $errors, $value;
    echo '<div class="field"><label for="' .
        e($name) .
        '">' .
        e($label) .
        '</label><input id="' .
        e($name) .
        '" name="' .
        e($name) .
        '" type="' .
        e($type) .
        '" value="' .
        ($type === "password" ? "" : $value($name, $default)) .
        '" ' .
        $extra .
        " " .
        (isset($errors[$name])
            ? 'aria-invalid="true" aria-describedby="err-' . e($name) . '"'
            : "") .
        ">";
    if (isset($errors[$name])) {
        echo '<small class="error" id="err-' .
            e($name) .
            '">' .
            e($errors[$name]) .
            "</small>";
    }
    echo "</div>";
}
