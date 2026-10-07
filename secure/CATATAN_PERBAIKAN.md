# Catatan Perbaikan — versi `secure/`

Ringkasan tiap celah dan di file mana perbaikannya ada. Dipakai sebagai acuan
saat menulis laporan di `report/`.

| # | Celah | Perbaikan | File |
|---|---|---|---|
| 1 | SQL Injection | Semua query memakai **prepared statement** (`bind_param`) | `login.php`, `register.php`, `profile.php`, `comments.php` |
| 2 | Password plaintext | `password_hash()` saat register, `password_verify()` saat login | `register.php`, `login.php` |
| 3 | XSS (reflected & stored) | `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` pada semua output dari input pengguna | `index.php`, `comments.php`, `profile.php`, `header.php` |
| 4 | Session fixation & cookie tidak aman | `session_regenerate_id(true)` setelah login; cookie `HttpOnly`+`SameSite`; `logout.php` menghapus sesi sepenuhnya (termasuk cookie) | `login.php`, `config.php`, `logout.php` |
| 5 | Brute force | Tabel `login_attempts` mencatat percobaan gagal; lockout 5 menit setelah 5 kali gagal | `login.php`, `schema_secure.sql` |
| 6 | CSRF | Token CSRF (`csrf_token()`/`csrf_verify()`) di semua form POST | `config.php`, `login.php`, `register.php`, `profile.php`, `comments.php` |
| 7 | User enumeration & verbose error | Pesan login digabung jadi satu ("Username atau password salah."); error database dicatat ke `error_log()`, bukan ditampilkan ke pengguna | `login.php`, `config.php`, `register.php` |
| 8 | IDOR | `profile.php` **tidak lagi membaca `?id=` dari URL**; data yang ditampilkan/diubah selalu berdasarkan `$_SESSION['user_id']` | `profile.php` |

## Yang perlu diisi manual sebelum dijalankan

1. Di `database/schema_secure.sql`: hapus/ganti dua baris `INSERT INTO users` (hash contoh di situ
   adalah placeholder, bukan hash asli), lalu daftar ulang akun `admin` dan `budi` lewat
   `register.php` supaya password ter-hash dengan benar. Ubah role akun admin lewat phpMyAdmin
   setelah daftar (`UPDATE users SET role='admin' WHERE username='admin'`).
2. Ganti `GANTI_PASSWORD_INI` di `schema_secure.sql` dan samakan di `secure/config.php`.
3. Jalankan server dari folder utama proyek:
   ```
   php -S <IP_ADAPTER_VM>:8000 -t secure
   ```

## Uji ulang (verifikasi perbaikan)

Untuk setiap celah, coba ulangi langkah eksploitasi yang sama seperti di `vulnerable/`,
lalu catat hasilnya di `report/`:

- Payload SQLi (`admin' -- `, `UNION SELECT`) → harus gagal total, prepared statement
  memperlakukan input sebagai data, bukan sebagai bagian dari query.
- `<script>alert(1)</script>` di `?name=` dan komentar → harus tampil sebagai teks
  biasa (ter-escape), bukan dieksekusi.
- Bandingkan `PHPSESSID` sebelum/sesudah login → harus **berbeda**.
- Login salah berkali-kali → setelah 5 kali, muncul pesan lockout.
- Form CSRF eksternal (tanpa token valid) → ditolak dengan HTTP 403.
- Username salah vs password salah → pesannya **sama persis**.
- Login sebagai `budi`, buka `profile.php` → selalu tampil data budi sendiri,
  parameter `?id=` di URL tidak lagi berpengaruh.
