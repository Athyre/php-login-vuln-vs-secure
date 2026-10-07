<?php
require 'config.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Celah: tidak ada validasi input sama sekali
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];

    // Celah #7 (user enumeration): pesan membocorkan username yang sudah ada
    $cek = mysqli_query($conn, "SELECT id FROM users WHERE username='$username'");
    if ($cek && mysqli_num_rows($cek) > 0) {
        $message = 'Username sudah dipakai.';
    } else {
        // Celah #1 (SQL Injection) + #2 (password disimpan plaintext)
        $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
        if (mysqli_query($conn, $sql)) {
            $message = 'Registrasi berhasil. Silakan login.';
        } else {
            // Celah: error + query ditampilkan ke pengguna
            $message = 'Error: ' . mysqli_error($conn) . '<br>Query: ' . $sql;
        }
    }
}
include 'header.php';
?>
<h2>Register</h2>
<form method="POST">
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Daftar</button>
</form>
<p><?php echo $message; ?></p>
<?php include 'footer.php'; ?>
