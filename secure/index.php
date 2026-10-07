<?php
require 'config.php';
include 'header.php';
?>
<h2>Beranda</h2>

<?php
// Perbaikan celah #3 (Reflected XSS): escape output dengan htmlspecialchars()
// sebelum dicetak ke HTML. Tanda kutip (ENT_QUOTES) ikut di-escape juga.
if (isset($_GET['name'])) {
    $name = htmlspecialchars($_GET['name'], ENT_QUOTES, 'UTF-8');
    echo "<p>Halo, {$name}!</p>";
}
?>

<p>Selamat datang di aplikasi lab login (versi aman).</p>
<?php include 'footer.php'; ?>
