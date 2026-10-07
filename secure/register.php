<?php
require 'config.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Perbaikan celah #6 (CSRF): validasi token sebelum memproses apa pun.
    if (!csrf_verify()) {
        http_response_code(403);
        die('Request ditolak: token keamanan tidak valid. Muat ulang halaman dan coba lagi.');
    }

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi input dasar (tidak ada sama sekali di versi rentan)
    if ($username === '' || $email === '' || $password === '') {
        $message = 'Semua kolom wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Format email tidak valid.';
    } elseif (strlen($password) < 8) {
        $message = 'Password minimal 8 karakter.';
    } else {
        // Perbaikan celah #1 (SQL Injection): prepared statement, bukan string concat
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Catatan desain: pesan "username sudah dipakai" tetap ditampilkan karena ini
            // kebutuhan UX normal pada form registrasi (celah #7 user enumeration pada
            // register.php dianggap risiko rendah dan dapat diterima, berbeda dengan
            // login.php yang sudah dibuat seragam pesannya).
            $message = 'Username sudah dipakai.';
        } else {
            // Perbaikan celah #2 (password plaintext): hash password sebelum disimpan
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $insert = $conn->prepare(
                'INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)'
            );
            $role = 'user';
            $insert->bind_param('ssss', $username, $email, $hashed, $role);

            if ($insert->execute()) {
                $message = 'Registrasi berhasil. Silakan login.';
            } else {
                // Perbaikan celah #7 (verbose error): pesan generik, detail ke log server
                error_log('Register insert error: ' . $insert->error);
                $message = 'Registrasi gagal. Coba lagi nanti.';
            }
            $insert->close();
        }
        $stmt->close();
    }
}
include 'header.php';
?>
<h2>Register</h2>
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password (min. 8 karakter)" minlength="8" required>
    <button type="submit">Daftar</button>
</form>
<p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
<?php include 'footer.php'; ?>
