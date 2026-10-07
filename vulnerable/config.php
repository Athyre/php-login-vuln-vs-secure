<?php
// config.php - versi RENTAN
// Celah: error ditampilkan apa adanya (information disclosure),
//        session dimulai tanpa pengaturan keamanan apa pun.

mysqli_report(MYSQLI_REPORT_OFF); // agar mysqli_error() bisa kita tampilkan sendiri

$conn = mysqli_connect('localhost', 'lab_user', 'PasswordLab123', 'lab_login_vuln');
if (!$conn) {
    die('Koneksi gagal: ' . mysqli_connect_error());
}

session_start(); // tanpa HttpOnly/SameSite/Secure, tanpa timeout (celah #4)
