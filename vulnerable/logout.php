<?php
require 'config.php';

// Celah #4: hanya menghapus beberapa variabel, tanpa session_destroy()
// dan tanpa menghapus cookie sesi.
unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role']);

header('Location: login.php');
exit;
