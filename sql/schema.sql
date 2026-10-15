CREATE DATABASE IF NOT EXISTS lepestok_practice CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lepestok_practice;
CREATE TABLE users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 login VARCHAR(64) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 full_name VARCHAR(200) NOT NULL,
 phone VARCHAR(20) NOT NULL,
 email VARCHAR(254) NOT NULL,
 role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_users_email(email)
) ENGINE=InnoDB;
CREATE TABLE bouquets (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 description VARCHAR(500) NOT NULL,
 composition VARCHAR(200) NOT NULL,
 category ENUM('roses','mixed','seasonal') NOT NULL,
 price DECIMAL(10,2) NOT NULL CHECK(price > 0),
 image VARCHAR(100) NOT NULL,
 active BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;
CREATE TABLE statuses (
 id INT UNSIGNED PRIMARY KEY,
 code VARCHAR(30) NOT NULL UNIQUE,
 name VARCHAR(60) NOT NULL
) ENGINE=InnoDB;
CREATE TABLE orders (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 bouquet_id INT UNSIGNED NOT NULL,
 status_id INT UNSIGNED NOT NULL DEFAULT 1,
 quantity SMALLINT UNSIGNED NOT NULL CHECK(quantity BETWEEN 1 AND 10),
 unit_price DECIMAL(10,2) NOT NULL CHECK(unit_price > 0),
 delivery_date DATE NOT NULL,
 delivery_slot ENUM('10:00–13:00','13:00–16:00','16:00–19:00') NOT NULL,
 address VARCHAR(300) NOT NULL,
 recipient VARCHAR(150) NOT NULL,
 recipient_phone VARCHAR(20) NOT NULL,
 payment_method ENUM('cash','card') NOT NULL,
 note VARCHAR(500) NOT NULL DEFAULT '',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id),
 FOREIGN KEY(bouquet_id) REFERENCES bouquets(id),
 FOREIGN KEY(status_id) REFERENCES statuses(id),
 INDEX idx_orders_user_created(user_id,created_at),
 INDEX idx_orders_status_date(status_id,delivery_date)
) ENGINE=InnoDB;
