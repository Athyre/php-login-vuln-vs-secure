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

-- Data uji dengan password HASH (hasil password_hash('admin123', PASSWORD_DEFAULT) dan
-- password_hash('budi123', PASSWORD_DEFAULT) pada saat skema ini dibuat).
-- Hash ini valid untuk dipakai langsung, tapi kamu juga bisa membuat ulang lewat register.php.


-- CATATAN: hash contoh di atas hanyalah PLACEHOLDER (bukan hash asli dari 'admin123'/'budi123'),
-- karena hash password_hash() berisi salt acak yang berbeda setiap kali dibuat dan tidak bisa
-- ditulis manual dengan aman di file SQL statis. Cara paling benar dan dianjurkan:
--   1. Jalankan bagian CREATE TABLE/CREATE USER di skema ini dulu (kosongkan dua baris INSERT di atas).
--   2. Daftar ulang akun admin dan budi lewat halaman register.php pada versi secure/,
--      supaya password ter-hash otomatis dan benar oleh PHP (password_hash()).
--   3. Kalau perlu role 'admin', ubah manual kolom role user itu lewat phpMyAdmin setelah daftar.

-- User database khusus aplikasi (jangan pakai root!)
-- GANTI password di bawah, lalu samakan di secure/config.php
CREATE USER IF NOT EXISTS 'lab_user_secure'@'localhost' IDENTIFIED BY 'PasswordLab123';
ALTER USER 'lab_user_secure'@'localhost' IDENTIFIED BY 'PasswordLab123';
GRANT SELECT, INSERT, UPDATE, DELETE ON lab_login_secure.* TO 'lab_user_secure'@'localhost';
FLUSH PRIVILEGES;
