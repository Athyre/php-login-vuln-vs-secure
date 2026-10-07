-- Skema database untuk versi RENTAN (sengaja menyimpan password plaintext)
-- Jalankan lewat phpMyAdmin (tab SQL) atau: mysql -u root -p < schema_vulnerable.sql

CREATE DATABASE IF NOT EXISTS lab_login_vuln CHARACTER SET utf8mb4;
USE lab_login_vuln;

DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,   -- SENGAJA plaintext (celah #2)
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

-- Data uji (akun milikmu sendiri untuk lab)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@lab.local', 'admin123', 'admin'),
('budi',  'budi@lab.local',  'budi123',  'user');

-- User database khusus aplikasi (jangan pakai root!)
-- GANTI password di bawah, lalu samakan di vulnerable/config.php
CREATE USER IF NOT EXISTS 'lab_user'@'localhost' IDENTIFIED BY 'PasswordLab123';
ALTER USER 'lab_user'@'localhost' IDENTIFIED BY 'PasswordLab123';
GRANT SELECT, INSERT, UPDATE, DELETE ON lab_login_vuln.* TO 'lab_user'@'localhost';
FLUSH PRIVILEGES;
