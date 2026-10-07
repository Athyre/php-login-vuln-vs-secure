<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Lab Login (AMAN)</title>
<style>
body{font-family:Arial,sans-serif;max-width:720px;margin:0 auto;padding:16px}
.info{background:#e0ffe0;border:1px solid #0a0;padding:8px;margin-bottom:12px;font-size:14px}
nav{margin-bottom:16px}
nav a{margin-right:12px}
input,textarea{display:block;margin:6px 0;padding:6px;width:100%;box-sizing:border-box}
button{padding:6px 14px}
.comment{border-bottom:1px solid #ddd;padding:6px 0}
</style>
</head>
<body>
<div class="info"><b>Versi AMAN:</b> Perbaikan dari <code>vulnerable/</code>. Bandingkan kode keduanya untuk melihat selisih mitigasinya.</div>
<nav>
    <a href="index.php">Beranda</a>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="profile.php">Profil</a>
        <a href="comments.php">Komentar</a>
        <!-- Perbaikan: htmlspecialchars() pada semua output yang berasal dari data pengguna -->
        <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>)</a>
    <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    <?php endif; ?>
</nav>
