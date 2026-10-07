-- Skema database untuk versi AMAN
-- Perbaikan dari schema_vulnerable.sql:
--   - password disimpan dalam bentuk hash (diisi lewat aplikasi, bukan lewat INSERT biasa)
--   - struktur tabel sama, supaya perbandingan dengan versi rentan tetap adil
-- Jalankan lewat phpMyAdmin (tab SQL) atau: mysql -u root -p < schema_secure.sql

CREATE DATABASE IF NOT EXISTS lab_login_secure CHARACTER SET utf8mb4;
USE lab_login_secure;

DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,   -- diisi dengan hasil password_hash(), bukan plaintext
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Perbaikan celah #5 (brute force): mencatat percobaan login gagal per username,
-- supaya login.php bisa menerapkan penguncian sementara (lockout).
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username_time (username, attempted_at)
);

-- User database khusus aplikasi (jangan pakai root!)
-- GANTI password di bawah, lalu samakan di secure/config.php
CREATE USER IF NOT EXISTS 'lab_user_secure'@'localhost' IDENTIFIED BY 'PasswordLab123';
ALTER USER 'lab_user_secure'@'localhost' IDENTIFIED BY 'PasswordLab123';
GRANT SELECT, INSERT, UPDATE, DELETE ON lab_login_secure.* TO 'lab_user_secure'@'localhost';
FLUSH PRIVILEGES;
