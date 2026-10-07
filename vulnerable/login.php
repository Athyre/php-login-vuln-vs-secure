<?php
require 'config.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Celah #1 (SQL Injection): input digabung langsung ke query
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        // Celah: pesan error + query ditampilkan (membantu error-based SQLi)
        $message = 'Error: ' . mysqli_error($conn) . '<br>Query: ' . $sql;
    } elseif (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Celah #4: tidak ada session_regenerate_id() -> session fixation
        $_SESSION['user_id']  = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role']     = $row['role'];

        header('Location: profile.php?id=' . $row['id']);
        exit;
    } else {
        // Celah #7 (user enumeration): pesan berbeda untuk username salah / password salah
        $cek = mysqli_query($conn, "SELECT id FROM users WHERE username='$username'");
        if ($cek && mysqli_num_rows($cek) > 0) {
            $message = 'Password salah.';
        } else {
            $message = 'Username tidak ditemukan.';
        }
    }
    // Celah #5: tidak ada batas percobaan login (brute force)
}
include 'header.php';
?>
<h2>Login</h2>
<form method="POST">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>
<p><?php echo $message; ?></p>
<?php include 'footer.php'; ?>
