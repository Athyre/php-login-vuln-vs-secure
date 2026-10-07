# Vulnerability #2: Password Stored in Plaintext

## 1. Overview

| Field | Value |
|---|---|
| Location | register.php, users table |
| OWASP Top 10 (2021) | A02:2021 - Cryptographic Failures |
| CWE Reference | CWE-256 (Plaintext Storage of a Password) |
| Severity | Critical |

## 2. Description

User passwords are written to the database exactly as submitted on the registration form, with no hashing or encryption applied. Anyone who can read the users table — whether through a database leak, an insider, a backup exposure, or (as demonstrated) a SQL Injection vulnerability — immediately obtains every user's real password in readable form, with no cracking required.

## 3. Vulnerable Code

*vulnerable/register.php (excerpt)*

```php
$username = $_POST['username'];
$email    = $_POST['email'];
$password = $_POST['password']; // stored exactly as typed, no hashing

$sql = "INSERT INTO users (username, email, password) VALUES
        ('$username', '$email', '$password')";
mysqli_query($conn, $sql);
```

## 4. Exploitation Steps

1. A new account was registered through register.php with a known password, to confirm how it is stored.
2. Rather than relying on direct database access, the SQL Injection vulnerability (Vulnerability #1) was reused as the extraction method: a UNION SELECT payload with GROUP_CONCAT(username, ':', password) was submitted through the login form.
3. The resulting profile.php page displayed every account's username and password concatenated together in plain, readable text — proving that the stored values were never hashed.

## 5. Evidence (Screenshots)

![profile.php page, reached via the SQL Injection UNION payload, showing every account's username and password concatenated together in plain, readable text](../screenshots/02-password-plaintext/02-password-plaintext-01.png)

*profile.php page, reached via the SQL Injection UNION payload, showing every account's username and password concatenated together in plain, readable text*

## 6. Secure Code (Fix)

*secure/register.php and secure/login.php (excerpts)*

```php
// register.php - hash the password before storing it
$hashed = password_hash($password, PASSWORD_DEFAULT);
$insert->bind_param('ssss', $username, $email, $hashed, $role);

// login.php - verify against the stored hash, never compare plaintext
if ($row && password_verify($password, $row['password'])) {
    // credentials are valid
}
```

## 7. Retest Results

- A new account was registered through secure/register.php. The password column in the users table now contains a bcrypt hash beginning with $2y$10$..., not the plaintext password.
- Login with the correct password still succeeds, confirming password_verify() correctly validates against the stored hash.
- Even in combination with the (now-fixed) SQL Injection vulnerability, no plaintext password could be recovered from the database.

## 8. Lessons Learned

Passwords must never be stored or compared as plaintext. PHP's built-in password_hash() (bcrypt by default) and password_verify() handle salting and hashing correctly out of the box and should always be used instead of custom or missing hashing logic. This mitigation also reduces the real-world impact of other vulnerabilities, such as SQL Injection, that might otherwise expose the users table.
