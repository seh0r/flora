<?php

function log_status(int $orderId, ?int $old, int $new, int $actor): void
{
    query(
        "INSERT INTO order_history(order_id,old_status_id,new_status_id,changed_by) VALUES(?,?,?,?)",
        [$orderId, $old, $new, $actor],
    );
}

function render_history(int $orderId): void
{
    $rows = query(
        "SELECT h.changed_at,s.name FROM order_history h JOIN statuses s ON s.id=h.new_status_id WHERE h.order_id=? ORDER BY h.id",
        [$orderId],
    )->fetchAll();
    if (!$rows) {
        return;
    }
    echo '<h3>История статусов</h3><ol class="history">';
    foreach ($rows as $r) {
        echo "<li>" .
            e(date("d.m.Y H:i", strtotime($r["changed_at"]))) .
            " — " .
            e($r["name"]) .
            "</li>";
    }
    echo "</ol>";
}
