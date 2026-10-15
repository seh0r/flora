<?php require __DIR__ . "/app.php"; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titles[$page]) ?> — Лепесток</title>
<meta name="description" content="Лепесток — цветочный магазин. Выберите букет, оформите доставку и отслеживайте заказ в личном кабинете.">
<link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="assets/style.min.css?v=3"><script src="assets/app.min.js?v=3" defer></script>
</head>
<body>
<a class="skip" href="#main">Перейти к содержимому</a>
<div class="announcement">Цветы для ваших маленьких и больших поводов <span>Доставка по Москве · 10:00–19:00</span></div>
<header class="header wrap">
 <a class="brand" href="index.php"><span class="brand-icon" aria-hidden="true">✳</span> лепесток<span class="brand-sub">ЦВЕТОЧНАЯ МАСТЕРСКАЯ</span></a>
 <button class="menu-toggle" aria-expanded="false" aria-controls="navigation" type="button">Меню ☰</button>
 <nav id="navigation" aria-label="Основная навигация"><a href="index.php#catalog">Букеты</a><a href="index.php#delivery">Доставка и оплата</a>
 <?php if ($user): ?><a href="index.php?page=account">Мои заказы</a><?php if (
    $user["role"] === "admin"
): ?><a href="index.php?page=admin">Управление</a><?php endif; ?><form method="post"><?= csrf() ?><input type="hidden" name="action" value="logout"><button type="submit" class="link-button">Выйти</button></form>
 <?php else: ?><a class="nav-account" href="index.php?page=login">Личный кабинет ↗</a><?php endif; ?></nav>
</header>
<main id="main" class="wrap">
<?php if (isset($_SESSION["flash"])): ?><div class="toast" role="status"><?= e(
    $_SESSION["flash"],
) ?><button type="button" aria-label="Закрыть уведомление">×</button></div><?php unset(
    $_SESSION["flash"],
);endif; ?>
<?php if (
    $errors
): ?><div class="error-summary" role="alert"><strong>Проверьте введённые данные</strong><ul><?php foreach (
    $errors
    as $key => $err
): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if ($page === "home"): ?>
<section class="hero">
 <div class="hero-copy"><p class="eyebrow">СОБРАНО С ЗАБОТОЙ</p><h1>Когда хочется<br>сказать <em>цветами.</em></h1><p>Нежное «спасибо», большое «люблю»<br>или просто повод улыбнуться.</p><a class="button" href="#catalog">Выбрать букет <span aria-hidden="true">↗</span></a><div class="hero-note"><span aria-hidden="true">✳</span> Свежие цветы. Тёплые чувства.</div></div>
 <section class="slider" aria-roledescription="карусель" aria-label="Коллекция букетов">
 <?php foreach ($bouquets as $i => $b): ?><figure class="slide <?= $i === 0
    ? "active"
    : "" ?>" <?= $i ? "hidden" : "" ?>><img src="assets/images/<?= e(
    $b["image"],
) ?>" alt="<?= e(
    $b["name"],
) ?> — цветочная композиция" width="1200" height="900" <?= $i === 0
     ? 'fetchpriority="high"'
     : 'loading="lazy"' ?>><figcaption><span>КОЛЛЕКЦИЯ ЛЕПЕСТОК</span><strong><?= e(
    $b["name"],
) ?></strong></figcaption></figure><?php endforeach; ?>
 <div class="slider-controls"><button type="button" data-slide="prev" aria-label="Предыдущий слайд">←</button><span id="slide-count">01 / 04</span><button type="button" data-slide="next" aria-label="Следующий слайд">→</button><button type="button" id="pause-slider" aria-label="Приостановить слайдер">Ⅱ</button></div>
 </section>
</section>
<div class="benefits"><span>✳ <strong>Собираем вручную</strong> каждый букет</span><span>↗ <strong>Доставляем бережно</strong> по Москве</span><span>♡ <strong>Открытка с вашим текстом</strong> бесплатно</span></div>
<section id="catalog" class="catalog"><div class="section-heading"><div><p class="eyebrow">ВЫБЕРИТЕ СВОЁ НАСТРОЕНИЕ</p><h2>Букеты, которые радуют</h2></div><span class="muted">4 повода для улыбки</span></div>
<div class="filters" role="group" aria-label="Фильтр каталога"><button type="button" data-category="all" aria-pressed="true">Все букеты</button><button type="button" data-category="roses" aria-pressed="false">Розы</button><button type="button" data-category="mixed" aria-pressed="false">Сборные</button><button type="button" data-category="seasonal" aria-pressed="false">Сезонные</button></div>
<div class="product-grid"><?php foreach (
    $bouquets
    as $b
): ?><article class="product" data-product-category="<?= e(
    $b["category"],
) ?>"><a class="product-image" href="index.php?page=order&amp;bouquet=<?= e(
    $b["id"],
) ?>"><img src="assets/images/<?= e($b["image"]) ?>" alt="Букет «<?= e(
    $b["name"],
) ?>»" width="1200" height="900" loading="lazy"><span class="product-arrow" aria-hidden="true">↗</span></a><div class="product-title"><h3><?= e(
    $b["name"],
) ?></h3><strong><?= money($b["price"]) ?></strong></div><p><?= e(
    $b["composition"],
) ?></p><a class="text-link" href="index.php?page=order&amp;bouquet=<?= e(
    $b["id"],
) ?>">Заказать букет →</a></article><?php endforeach; ?></div></section>
<section id="delivery" class="delivery"><div><p class="eyebrow">ПРОСТО И С ЗАБОТОЙ</p><h2>От нашего сердца<br>до вашей двери.</h2></div><div><h3>Доставка по Москве</h3><p>Бесплатно в пределах МКАД. Выберите дату начиная с завтрашнего дня и удобный интервал с 10:00 до 19:00.</p><h3>Оплата при получении</h3><p>Наличными или картой курьеру. Добавьте текст открытки в комментарии — мы передадим ваши слова вместе с букетом.</p><p class="muted">Доставка только в пределах МКАД. Оплата после получения букета.</p></div></section>
<?php elseif (in_array($page, ["register", "login"], true)): ?>
<section class="auth-layout"><div class="auth-aside"><p class="eyebrow">ВАШ МАЛЕНЬКИЙ ЦВЕТОЧНЫЙ МИР</p><h2>Хороший день<br>начинается<br><em>с цветов.</em></h2><img src="assets/images/flowers-2.webp" width="1200" height="900" alt="Нежные розовые цветы"></div><div class="auth-form"><p class="eyebrow">ЛИЧНЫЙ КАБИНЕТ</p><h1><?= e(
    $titles[$page],
) ?></h1><p class="muted"><?= $page === "register"
    ? "Сохраняйте заказы и следите за их доставкой."
    : "Войдите, чтобы выбрать что-то прекрасное." ?></p><form method="post" class="stack"><?= csrf() ?><input type="hidden" name="action" value="<?= e(
    $page,
) ?>">
<?php
field(
    "login",
    "Логин",
    "text",
    "",
    'required minlength="6" maxlength="64" pattern="[A-Za-z0-9]{6,64}" autocomplete="username"',
);
if (
    $page === "register"
): ?><small class="muted">6–64 латинских букв или цифр.</small><?php endif;
?>
<?php
field(
    "password",
    "Пароль",
    "password",
    "",
    'required minlength="8" maxlength="72" autocomplete="' .
        ($page === "register" ? "new-password" : "current-password") .
        '"',
);
if (
    $page === "register"
): ?><small class="muted">От 8 символов, латинская буква и цифра. Максимум 72 байта.</small>
<?php
field(
    "full_name",
    "ФИО",
    "text",
    "",
    'required maxlength="200" autocomplete="name"',
);
field(
    "phone",
    "Телефон",
    "tel",
    "",
    'required placeholder="8(999)123-45-67" maxlength="20" autocomplete="tel"',
);
field(
    "email",
    "Email",
    "email",
    "",
    'required maxlength="254" autocomplete="email"',
);
endif;
?>
<button class="button" type="submit"><?= $page === "register"
    ? "Создать аккаунт"
    : "Войти" ?> <span aria-hidden="true">↗</span></button></form><p class="auth-switch"><?= $page ===
 "register"
     ? "Уже есть аккаунт?"
     : "Ещё нет аккаунта?" ?> <a href="index.php?page=<?= $page === "register"
     ? "login"
     : "register" ?>"><?= $page === "register"
    ? "Войти"
    : "Зарегистрироваться" ?></a></p></div></section>
<?php elseif ($page === "order"):
    $selected = (int) ($_POST["bouquet_id"] ?? ($_GET["bouquet"] ?? 1)); ?>
<div class="page-heading"><p class="eyebrow">ПОРА РАДОВАТЬ</p><h1>Оформление заказа</h1><p class="muted">Один букет или несколько одинаковых — всё в одном заказе.</p></div>
<form method="post" class="order-layout"><?= csrf() ?><input type="hidden" name="action" value="order"><div class="form-panel stack"><h2>Ваш букет</h2><div class="field"><label for="bouquet_id">Букет</label><select id="bouquet_id" name="bouquet_id" required><option value="">Выберите букет</option><?php foreach (
    $bouquets
    as $b
): ?><option value="<?= $b["id"] ?>" data-price="<?= e(
    $b["price"],
) ?>" <?= $selected === $b["id"] ? "selected" : "" ?>><?= e(
    $b["name"],
) ?> — <?= money($b["price"]) ?></option><?php endforeach; ?></select></div>
<?php field(
    "quantity",
    "Количество",
    "number",
    "1",
    'required min="1" max="10"',
); ?><h2>Доставка</h2><div class="form-row"><?php field(
    "delivery_date",
    "Дата",
    "date",
    date("Y-m-d", strtotime("+1 day")),
    'required min="' .
        date("Y-m-d", strtotime("+1 day")) .
        '" max="' .
        date("Y-m-d", strtotime("+90 days")) .
        '"',
); ?><div class="field"><label for="delivery_slot">Время</label><select id="delivery_slot" name="delivery_slot"><?php foreach (
    ["10:00–13:00", "13:00–16:00", "16:00–19:00"]
    as $slot
): ?><option <?= ($_POST["delivery_slot"] ?? "") === $slot
    ? "selected"
    : "" ?>><?= e($slot) ?></option><?php endforeach; ?></select></div></div>
<?php
field(
    "address",
    "Адрес в пределах МКАД",
    "text",
    "",
    'required minlength="10" maxlength="300" placeholder="Улица, дом, квартира, подъезд" autocomplete="address-line1"',
);
field(
    "recipient",
    "Имя получателя",
    "text",
    $user["full_name"],
    'required minlength="2" maxlength="150"',
);
field(
    "recipient_phone",
    "Телефон получателя",
    "tel",
    $user["phone"],
    'required placeholder="8(999)123-45-67" maxlength="20"',
);
?><div class="field"><label for="note">Комментарий или текст открытки <span class="muted">(необязательно)</span></label><textarea id="note" name="note" rows="3" maxlength="500"><?= $value(
    "note",
) ?></textarea></div></div>
<aside class="order-summary"><p class="eyebrow">ВАШ ЗАКАЗ</p><h2>Немного счастья<br>уже в пути</h2><div class="summary-line"><span>Букеты</span><strong id="order-total"></strong></div><div class="summary-line"><span>Доставка</span><span>Бесплатно</span></div><div class="field"><label for="payment_method">Оплата при получении</label><select id="payment_method" name="payment_method"><option value="cash">Наличными курьеру</option><option value="card" <?= ($_POST[
    "payment_method"
] ??
    "") ===
"card"
    ? "selected"
    : "" ?>>Картой курьеру</option></select></div><p class="muted">Статус заказа появится в личном кабинете. Состав сезонного букета может меняться.</p><button class="button" type="submit">Оформить заказ ↗</button><a href="index.php#catalog" class="text-link">Вернуться к букетам</a></aside></form>
<?php
elseif (in_array($page, ["account", "admin"], true)): ?>
<div class="page-heading"><p class="eyebrow"><?= $page === "admin"
    ? "ПАНЕЛЬ АДМИНИСТРАТОРА"
    : e($user["full_name"]) ?></p><h1><?= e(
    $titles[$page],
) ?></h1><p class="muted"><?= $page === "admin"
    ? "Управляйте доставкой и следите за каждым заказом."
    : "Всё, что вы выбрали с любовью, — здесь." ?></p></div>
<?php
$where = [];
$params = [];
if ($page === "account") {
    $where[] = "o.user_id=?";
    $params[] = $user["id"];
}
$filter = (int) ($_GET["status"] ?? 0);
if (isset($statusNames[$filter])) {
    $where[] = "o.status_id=?";
    $params[] = $filter;
}
$search = trim($_GET["q"] ?? "");
if ($search !== "") {
    $where[] = "(u.full_name LIKE ? OR b.name LIKE ? OR CAST(o.id AS CHAR)=?)";
    $params[] = "%" . $search . "%";
    $params[] = "%" . $search . "%";
    $params[] = $search;
}
foreach (["from" => ">=", "to" => "<="] as $key => $op) {
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/D', $_GET[$key] ?? "")) {
        $where[] = "o.delivery_date $op ?";
        $params[] = $_GET[$key];
    }
}
$condition = $where ? " WHERE " . implode(" AND ", $where) : "";
$joins =
    " FROM orders o JOIN bouquets b ON b.id=o.bouquet_id JOIN users u ON u.id=o.user_id";
$total = (int) query(
    "SELECT COUNT(*)" . $joins . $condition,
    $params,
)->fetchColumn();
$pages = max(1, (int) ceil($total / 6));
$current = max(1, min($pages, (int) ($_GET["p"] ?? 1)));
$offset = ($current - 1) * 6;
$orders = query(
    "SELECT o.*,b.name AS bouquet_name,b.image,u.full_name" .
        $joins .
        $condition .
        " ORDER BY o.created_at DESC,o.id DESC LIMIT 6 OFFSET $offset",
    $params,
)->fetchAll();
?>
<form method="get" class="order-filters"><input type="hidden" name="page" value="<?= e(
    $page,
) ?>"><div class="field"><label for="q">Поиск</label><input type="search" id="q" name="q" value="<?= e(
    $search,
) ?>" placeholder="Номер, букет или имя" maxlength="100"></div><div class="field"><label for="status">Статус</label><select id="status" name="status"><option value="0">Все статусы</option><?php foreach (
    $statuses
    as $s
): ?><option value="<?= $s["id"] ?>" <?= $filter === $s["id"]
    ? "selected"
    : "" ?>><?= e(
    $s["name"],
) ?></option><?php endforeach; ?></select></div><?php foreach (
    ["from" => "Доставка с", "to" => "Доставка по"]
    as $k => $label
): ?><div class="field"><label for="<?= $k ?>"><?= $label ?></label><input type="date" id="<?= $k ?>" name="<?= $k ?>" value="<?= e(
    $_GET[$k] ?? "",
) ?>"></div><?php endforeach; ?><button type="submit" class="button">Найти</button><a href="index.php?page=<?= e(
    $page,
) ?>">Сбросить</a></form>
<p class="muted">Найдено заказов: <?= $total ?></p><div class="orders">
<?php foreach (
    $orders
    as $o
): ?><article class="order-card"><div class="order-top"><strong>Заказ №<?= $o[
    "id"
] ?></strong><span class="status status-<?= $o["status_id"] ?>"><?= e(
    $statusNames[$o["status_id"]],
) ?></span></div><div class="order-body"><img src="assets/images/<?= e(
    $o["image"],
) ?>" alt="<?= e(
    $o["bouquet_name"],
) ?>" width="120" height="120" loading="lazy"><div><h2><?= e(
    $o["bouquet_name"],
) ?></h2><p><?= $o["quantity"] ?> шт. × <?= money(
     $o["unit_price"],
 ) ?> <strong>· <?= money(
     $o["quantity"] * $o["unit_price"],
 ) ?></strong></p><p>Доставка <?= date(
    "d.m.Y",
    strtotime($o["delivery_date"]),
) ?> · <?= e($o["delivery_slot"]) ?></p><p><?= e(
    $o["address"],
) ?></p></div></div><details><summary>Детали заказа</summary><p>Получатель: <?= e(
    $o["recipient"],
) ?>, <?= e($o["recipient_phone"]) ?></p><p>Оплата: <?= $o["payment_method"] ===
"cash"
    ? "наличными"
    : "картой" ?> при получении.</p><?php
 if ($o["note"]): ?><p>Комментарий: <?= e($o["note"]) ?></p><?php endif;
 if ($page === "admin"): ?><p>Покупатель: <?= e(
    $o["full_name"],
) ?></p><?php endif;
 if (function_exists("render_history")) {
     render_history((int) $o["id"]);
 }
 ?></details>
<?php if ($page === "admin"):
    $nexts = [1 => [2, 5], 2 => [3, 5], 3 => [4], 4 => [], 5 => []];
    if (
        $nexts[$o["status_id"]]
    ): ?><form method="post" class="status-form"><?= csrf() ?><input type="hidden" name="action" value="status"><input type="hidden" name="order_id" value="<?= $o[
    "id"
] ?>"><label for="status-<?= $o[
    "id"
] ?>">Новый статус</label><select name="status_id" id="status-<?= $o[
    "id"
] ?>"><?php foreach (
    $nexts[$o["status_id"]]
    as $s
): ?><option value="<?= $s ?>"><?= e(
    $statusNames[$s],
) ?></option><?php endforeach; ?></select><button type="submit" class="button small">Сохранить</button></form><?php endif;
endif; ?></article><?php endforeach; ?>
<?php if (
    !$orders
): ?><div class="empty"><span aria-hidden="true">✳</span><h2>Здесь пока нет заказов</h2><p>Выберите букет или измените условия поиска.</p><a class="button" href="index.php#catalog">Посмотреть букеты ↗</a></div><?php endif; ?></div>
<?php if (
    $pages > 1
): ?><nav class="pagination" aria-label="Страницы заказов"><?php for (
    $i = 1;
    $i <= $pages;
    $i++
):
    $link = http_build_query(
        array_merge($_GET, ["p" => $i]),
    ); ?><a href="index.php?<?= e($link) ?>" <?= $i === $current
    ? 'aria-current="page"'
    : "" ?>><?= $i ?></a><?php
endfor; ?></nav><?php endif; ?>
<?php else: ?><div class="empty"><h1><?= e(
    $titles[$page],
) ?></h1><a class="button" href="index.php">На главную</a></div><?php endif; ?>
</main><footer class="footer wrap"><a class="brand" href="index.php">✳ лепесток</a><p>Цветы говорят, когда слов недостаточно.</p><small>Лепесток · Цветочная мастерская<br>Москва · Ежедневно с 10:00 до 19:00</small></footer>
</body></html>
