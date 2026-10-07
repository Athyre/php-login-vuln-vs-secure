<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Perbaikan celah #6 (CSRF)
    if (!csrf_verify()) {
        http_response_code(403);
        die('Request ditolak: token keamanan tidak valid. Muat ulang halaman dan coba lagi.');
    }

    $content = trim($_POST['content'] ?? '');

    if ($content !== '') {
        // Perbaikan celah #1 (SQL Injection): prepared statement
        $stmt = $conn->prepare('INSERT INTO comments (user_id, content) VALUES (?, ?)');
        $stmt->bind_param('is', $_SESSION['user_id'], $content);
        $stmt->execute();
        $stmt->close();
    }

    // Pola Post/Redirect/Get: mencegah komentar terkirim ulang saat halaman di-refresh
    header('Location: comments.php');
    exit;
}

$result = $conn->query(
    'SELECT c.content, c.created_at, u.username
     FROM comments c JOIN users u ON c.user_id = u.id
     ORDER BY c.id DESC'
);

include 'header.php';
?>
<h2>Komentar</h2>
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
    <textarea name="content" rows="3" placeholder="Tulis komentar..." required></textarea>
    <button type="submit">Kirim</button>
</form>

<?php while ($row = $result->fetch_assoc()): ?>
    <div class="comment">
        <!-- Perbaikan celah #3 (stored XSS): htmlspecialchars() pada semua output -->
        <b><?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></b>
        <small><?php echo htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8'); ?></small><br>
        <?php echo nl2br(htmlspecialchars($row['content'], ENT_QUOTES, 'UTF-8')); ?>
    </div>
<?php endwhile; ?>
<?php include 'footer.php'; ?>
