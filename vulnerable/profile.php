<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';

// Celah #6 (CSRF): form ubah email tidak punya token anti-CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email']; // juga rentan SQLi + stored XSS
    mysqli_query($conn, "UPDATE users SET email='$email' WHERE id=" . $_SESSION['user_id']);
    $message = 'Email diperbarui.';
}

// Celah #8 (IDOR): id diambil dari URL tanpa cek apakah milik user yang login
// Celah #1: $id digabung langsung ke query
$id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT id, username, email, role FROM users WHERE id=$id");
if (!$result) {
    die('Error: ' . mysqli_error($conn));
}
$user = mysqli_fetch_assoc($result);

include 'header.php';
?>
<h2>Profil</h2>
<?php if ($user): ?>
    <p>ID: <?php echo $user['id']; ?></p>
    <p>Username: <?php echo $user['username']; ?></p>
    <p>Email: <?php echo $user['email']; ?></p>   <!-- tanpa escape -> stored XSS -->
    <p>Role: <?php echo $user['role']; ?></p>
<?php else: ?>
    <p>Pengguna tidak ditemukan.</p>
<?php endif; ?>

<h3>Ubah email (akun kamu sendiri)</h3>
<form method="POST">
    <input type="text" name="email" placeholder="Email baru" required>
    <button type="submit">Simpan</button>
</form>
<p><?php echo $message; ?></p>
<?php include 'footer.php'; ?>
