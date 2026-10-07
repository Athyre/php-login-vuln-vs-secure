<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Lab Login (RENTAN)</title>
<style>
body{font-family:Arial,sans-serif;max-width:720px;margin:0 auto;padding:16px}
.warn{background:#ffe0e0;border:1px solid #c00;padding:8px;margin-bottom:12px;font-size:14px}
nav{margin-bottom:16px}
nav a{margin-right:12px}
input,textarea{display:block;margin:6px 0;padding:6px;width:100%;box-sizing:border-box}
button{padding:6px 14px}
.comment{border-bottom:1px solid #ddd;padding:6px 0}
</style>
</head>
<body>
<div class="warn"><b>PERINGATAN:</b> Aplikasi ini SENGAJA RENTAN untuk belajar keamanan. Jalankan hanya di localhost / jaringan lab. Jangan di-deploy ke internet.</div>
<nav>
    <a href="index.php">Beranda</a>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="profile.php">Profil</a>
        <a href="comments.php">Komentar</a>
        <a href="logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a>
    <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    <?php endif; ?>
</nav>
