<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Celah #6 (CSRF) + #1 (SQLi) + #3 (stored XSS)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'];
    mysqli_query($conn, "INSERT INTO comments (user_id, content) VALUES (" . $_SESSION['user_id'] . ", '$content')");
}

$result = mysqli_query($conn,
    "SELECT c.content, c.created_at, u.username
     FROM comments c JOIN users u ON c.user_id = u.id
     ORDER BY c.id DESC");

include 'header.php';
?>
<h2>Komentar</h2>
<form method="POST">
    <textarea name="content" rows="3" placeholder="Tulis komentar..." required></textarea>
    <button type="submit">Kirim</button>
</form>

<?php while ($row = mysqli_fetch_assoc($result)): ?>
    <div class="comment">
        <b><?php echo $row['username']; ?></b> <small><?php echo $row['created_at']; ?></small><br>
        <?php echo $row['content']; ?>   <!-- tanpa escape -> stored XSS -->
    </div>
<?php endwhile; ?>
<?php include 'footer.php'; ?>
