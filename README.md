# Secure Login Lab

**Web Application Security — Vulnerable vs. Secure PHP Login System**

## Project Overview

This project is a hands-on web application security exercise built around a single PHP login/registration application, implemented in two parallel versions:

- `vulnerable/` — intentionally insecure, containing eight distinct, well-known classes of web vulnerabilities.
- `secure/` — the same application, rebuilt with each vulnerability properly mitigated.

The goal is to document a complete security workflow: identify a vulnerability, exploit it with evidence, fix it, and prove the fix works by repeating the same attack against the secure version.

## ⚠ Warning

> The `vulnerable/` folder intentionally contains security flaws. Run it only on localhost or an isolated lab network (as was done here, using a Windows host running XAMPP and a Kali Linux VM over a VMware host-only/NAT adapter). Do not deploy it to any public server or network. All testing was performed exclusively against systems owned by the author.

## Technology Stack

| Component | Details |
|---|---|
| Language | PHP 8.2 (mysqli / MySQLi OOP, no framework) |
| Database | MariaDB / MySQL (via XAMPP) |
| Web Server | Apache (XAMPP) and PHP built-in server (`php -S`) |
| Testing Tools | Burp Suite (Proxy, Repeater, Intruder), manual browser testing, phpMyAdmin |
| Environment | Windows host (XAMPP, VS Code) + Kali Linux VM (VMware) for attack traffic |

## Project Structure

```
secure-login-lab/
├── README.md
├── database/
│   ├── schema_vulnerable.sql     # schema + seed data (vulnerable)
│   └── schema_secure.sql         # schema + login_attempts table (secure)
├── vulnerable/                   # intentionally insecure version
│   ├── config.php
│   ├── header.php / footer.php
│   ├── index.php                 # home page (?name= parameter)
│   ├── register.php
│   ├── login.php
│   ├── profile.php               # profile view + email update
│   ├── comments.php
│   └── logout.php
├── secure/                       # hardened version (same file structure)
├── report/                       # one Markdown file per vulnerability
│   ├── 01-sql-injection.md
│   ├── 02-password-plaintext.md
│   ├── 03-xss.md
│   ├── 04-session-fixation.md
│   ├── 05-brute-force.md
│   ├── 06-csrf.md
│   ├── 07-user-enumeration.md
│   └── 08-idor.md
└── screenshots/                  # exploitation and retest evidence, one folder per vulnerability
```

## Application Features

- User registration and login
- Profile page (view account data and update email)
- A comments section visible to every logged-in user
- Simple role model (`admin` and `user`)

## Running the Application

**Prerequisites:**
- PHP 8.x with the `mysqli` extension (bundled with XAMPP)
- MariaDB/MySQL running (via XAMPP)
- A browser, plus Burp Suite for intercepting and crafting attack traffic

### 1. Prepare the database

1. Open `database/schema_vulnerable.sql` (or `schema_secure.sql` for the hardened version) and set the application database user's password.
2. Run the file via phpMyAdmin (SQL tab) or:
   ```bash
   mysql -u root -p < database/schema_vulnerable.sql
   ```

The schema creates a dedicated database, the `users` / `comments` tables (and `login_attempts` for the secure version), seed accounts, and a least-privilege database user — never `root` — restricted to `SELECT`, `INSERT`, `UPDATE`, `DELETE` on that one database.

### 2. Configure the connection

In `config.php`, match the password used in step 1:
```php
$conn = mysqli_connect('localhost', 'lab_user', 'YOUR_PASSWORD', 'lab_login_vuln');
```

### 3. Start the server

```bash
php -S 127.0.0.1:8000 -t vulnerable
# or, for the secure version:
php -S 127.0.0.1:8000 -t secure
```

To reach the application from a virtual machine (e.g. Kali Linux, for Burp Suite testing), bind the server to the host's VM-facing adapter IP address instead of `127.0.0.1`, and restrict inbound firewall access to that VM's subnet only:
```bash
php -S <HOST_VM_ADAPTER_IP>:8000 -t vulnerable
```

### Test Accounts

| Username | Password | Role |
|---|---|---|
| `admin` | `admin123` | admin |
| `budi` | `budi123` | user |

## Vulnerability Summary

| # | Vulnerability | Location | OWASP 2021 | CWE |
|---|---|---|---|---|
| 1 | SQL Injection | login.php, register.php, profile.php, comments.php | A03:2021 | CWE-89 |
| 2 | Password Stored in Plaintext | register.php, users table | A02:2021 | CWE-256 |
| 3 | Cross-Site Scripting (Reflected & Stored) | index.php (?name= parameter), comments.php, profile.php | A03:2021 | CWE-79 |
| 4 | Weak Session Management (Session Fixation & Insecure Cookies) | config.php, login.php, logout.php | A07:2021 | CWE-384 |
| 5 | No Brute-Force Protection | login.php | A07:2021 | CWE-307 |
| 6 | Cross-Site Request Forgery (CSRF) | profile.php (email update), comments.php | A01:2021 | CWE-352 |
| 7 | User Enumeration & Verbose Error Messages | login.php, register.php | A07:2021 / A05:2021 | CWE-204 |
| 8 | Insecure Direct Object Reference (IDOR) | profile.php?id= | A01:2021 | CWE-639 |

## Project Workflow

1. Build the vulnerable version (`vulnerable/`).
2. Exploit each vulnerability and collect evidence: Burp Suite requests/responses, screenshots.
3. Fix each vulnerability in the secure version (`secure/`).
4. Retest every original attack against the secure version to confirm it fails.
5. Document each vulnerability in `report/` (this set of documents).

## Status

- [x] Vulnerable version built
- [x] Exploitation performed and evidence captured for all 8 vulnerabilities
- [x] Secure version built
- [x] Evidence collected and reports written for all 8 vulnerabilities
- [ ] Full retest pass across all 8 vulnerabilities on the secure version

## Report Format

Each file in `report/` follows the same structure:

1. Overview (location, OWASP/CWE reference, severity)
2. Description
3. Vulnerable code
4. Exploitation steps
5. Evidence (screenshots)
6. Secure code (fix)
7. Retest results
8. Lessons learned

## Troubleshooting

| Symptom | Likely Cause | Fix |
|---|---|---|
| `Access denied for user 'lab_user'` | Password in `config.php` does not match the database user | Re-run the `CREATE USER` / `ALTER USER` / `GRANT` statements and keep the passwords in sync |
| `Unknown database` | Schema not yet imported | Run the matching `schema_*.sql` file |
| `Connection refused` | MySQL not running, or bound only to `127.0.0.1` | Start MySQL; switch `'localhost'` to `'127.0.0.1'` in `config.php` if needed |
| Not reachable from the VM | Firewall blocking the port, or server bound to `127.0.0.1` | Bind to the host's VM-facing adapter IP and allow that port from the VM subnet only |

## References

- [OWASP Top 10 (2021)](https://owasp.org/Top10/)
- [OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/)
- [PHP: Prepared Statements](https://www.php.net/manual/en/mysqli.quickstart.prepared-statements.php)
- [PHP: Password Hashing](https://www.php.net/manual/en/book.password.php)
- [CWE — Common Weakness Enumeration](https://cwe.mitre.org/)

## Ethics & License Note

*This project was built strictly for educational purposes. The techniques documented here were only ever applied against systems owned by the author, within an isolated lab network. They must not be applied to any system without explicit, written authorization.*
