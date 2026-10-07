<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    // Perbaikan celah #6 (CSRF)
    if (!csrf_verify()) {
        http_response_code(403);
        die('Request ditolak: token keamanan tidak valid. Muat ulang halaman dan coba lagi.');
    }

    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Format email tidak valid.';
    } else {
        // Perbaikan celah #1 (SQL Injection): prepared statement.
        // Perbaikan implisit celah #8: UPDATE hanya menyasar baris milik user yang sedang
        // login ($_SESSION['user_id']), bukan id dari input luar.
        $stmt = $conn->prepare('UPDATE users SET email = ? WHERE id = ?');
        $stmt->bind_param('si', $email, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();
        $message = 'Email diperbarui.';
    }
}

// Perbaikan celah #8 (IDOR): parameter ?id= dari URL TIDAK dipakai sama sekali untuk
// menentukan data siapa yang ditampilkan. Profil yang ditampilkan SELALU milik user
// yang sedang login (dari session), bukan dari input yang bisa dimanipulasi pengguna.
$stmt = $conn->prepare('SELECT id, username, email, role FROM users WHERE id = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

include 'header.php';
?>
<h2>Profil</h2>
<?php if ($user): ?>
    <!-- Perbaikan celah #3 (stored XSS): htmlspecialchars() pada semua output -->
    <p>ID: <?php echo (int) $user['id']; ?></p>
    <p>Username: <?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p>Email: <?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p>Role: <?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?></p>
<?php else: ?>
    <p>Pengguna tidak ditemukan.</p>
<?php endif; ?>

<h3>Ubah email (akun kamu sendiri)</h3>
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
    <input type="email" name="email" placeholder="Email baru" required>
    <button type="submit">Simpan</button>
</form>
<p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
<?php include 'footer.php'; ?>
