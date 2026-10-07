<?php
require 'config.php';
$message = '';

// Perbaikan celah #5 (brute force): batas percobaan gagal per username
// dalam jendela waktu tertentu, dicatat di tabel login_attempts.
const MAX_ATTEMPTS   = 5;
const LOCKOUT_WINDOW = 300; // 5 menit

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Perbaikan celah #6 (CSRF)
    if (!csrf_verify()) {
        http_response_code(403);
        die('Request ditolak: token keamanan tidak valid. Muat ulang halaman dan coba lagi.');
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Hitung percobaan gagal baru-baru ini untuk username ini
    $lockoutWindow = LOCKOUT_WINDOW;
    $stmt = $conn->prepare(
        'SELECT COUNT(*) AS jumlah FROM login_attempts WHERE username = ? AND attempted_at > (NOW() - INTERVAL ? SECOND)'
    );
    $stmt->bind_param('si', $username, $lockoutWindow);
    $stmt->execute();
    $jumlahGagal = $stmt->get_result()->fetch_assoc()['jumlah'];
    $stmt->close();

    if ($jumlahGagal >= MAX_ATTEMPTS) {
        // Pesan generik, tidak membedakan alasan lockout dari alasan lain
        $message = 'Terlalu banyak percobaan gagal. Coba lagi dalam beberapa menit.';
    } else {
        // Perbaikan celah #1 (SQL Injection): prepared statement
        $stmt = $conn->prepare('SELECT id, username, password, role FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        // Perbaikan celah #2: verifikasi hash, bukan bandingkan string plaintext
        if ($row && password_verify($password, $row['password'])) {
            // Perbaikan celah #4 (session fixation): buat session ID baru setelah login
            session_regenerate_id(true);

            $_SESSION['user_id']  = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role']     = $row['role'];

            // Login berhasil: bersihkan histori percobaan gagal untuk username ini
            $del = $conn->prepare('DELETE FROM login_attempts WHERE username = ?');
            $del->bind_param('s', $username);
            $del->execute();
            $del->close();

            header('Location: profile.php');
            exit;
        } else {
            // Perbaikan celah #7 (user enumeration): SATU pesan generik untuk semua kegagalan,
            // tidak lagi membedakan "username tidak ditemukan" vs "password salah".
            $message = 'Username atau password salah.';

            // Catat percobaan gagal untuk rate limiting
            $log = $conn->prepare('INSERT INTO login_attempts (username) VALUES (?)');
            $log->bind_param('s', $username);
            $log->execute();
            $log->close();
        }
    }
}
include 'header.php';
?>
<h2>Login</h2>
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>
<p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
<?php include 'footer.php'; ?>
