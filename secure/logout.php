<?php
require 'config.php';

// Perbaikan celah #4: logout sekarang benar-benar menghapus seluruh sesi,
// termasuk cookie sesi di sisi browser, bukan hanya unset beberapa variabel.
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

header('Location: login.php');
exit;
