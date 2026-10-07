<?php
// config.php - versi AMAN
// Perbaikan dari versi rentan:
//   - error database TIDAK ditampilkan ke pengguna (information disclosure dicegah)
//   - session dimulai dengan parameter cookie yang aman (HttpOnly, SameSite, Secure)
//   - memakai mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT) + try/catch,
//     supaya error tercatat di log server, bukan dicetak ke layar pengguna

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Celah #4 (session fixation & cookie tidak aman) - perbaikan:
// Atur parameter cookie SEBELUM session_start() dipanggil.
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,     // JS tidak bisa membaca cookie sesi -> mitigasi tambahan untuk XSS
    'samesite' => 'Lax',    // mitigasi tambahan untuk CSRF (browser modern)
    // 'secure' => true,    // aktifkan ini kalau sudah pakai HTTPS
]);
session_start();

// Celah #5 (brute force) - perbaikan dasar: batas waktu idle sesi,
// supaya sesi lama yang tidak dipakai otomatis tidak valid lagi.
$session_timeout = 1800; // 30 menit
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    $_SESSION = [];
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

try {
    $conn = mysqli_connect('localhost', 'lab_user_secure', 'PasswordLab123', 'lab_login_secure');
} catch (mysqli_sql_exception $e) {
    // Perbaikan celah #7 (verbose error): pesan generik ke pengguna,
    // detail asli dicatat ke log server saja.
    error_log('DB connection error: ' . $e->getMessage());
    http_response_code(500);
    die('Terjadi kesalahan pada server. Coba lagi nanti.');
}

/**
 * Helper CSRF token - perbaikan celah #6.
 * Dipanggil di setiap halaman yang punya form POST.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validasi token CSRF dari form yang di-submit.
 * Memakai hash_equals() supaya tahan terhadap timing attack.
 */
function csrf_verify(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}
