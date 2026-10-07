<?php
require 'config.php';
include 'header.php';
?>
<h2>Beranda</h2>

<?php
// Celah #3 (Reflected XSS): parameter ?name= dicetak tanpa di-escape
if (isset($_GET['name'])) {
    echo "<p>Halo, " . $_GET['name'] . "!</p>";
}
?>

<p>Selamat datang di aplikasi lab login (versi rentan).</p>
<?php include 'footer.php'; ?>
