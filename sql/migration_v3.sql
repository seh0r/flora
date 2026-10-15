USE lepestok_practice;
CREATE TABLE IF NOT EXISTS order_history (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id INT UNSIGNED NOT NULL,
 old_status_id INT UNSIGNED NULL,
 new_status_id INT UNSIGNED NOT NULL,
 changed_by INT UNSIGNED NOT NULL,
 changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(order_id) REFERENCES orders(id),
 FOREIGN KEY(old_status_id) REFERENCES statuses(id),
 FOREIGN KEY(new_status_id) REFERENCES statuses(id),
 FOREIGN KEY(changed_by) REFERENCES users(id),
 INDEX idx_history_order_time(order_id,changed_at)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS login_attempts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 login VARCHAR(64) NOT NULL,
 succeeded BOOLEAN NOT NULL,
 attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_attempt_login_time(login,attempted_at)
) ENGINE=InnoDB;
