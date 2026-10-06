# Demo and verification

## Start

Use Apache in XAMPP with the repository in `C:\xampp\htdocs\SkillSwap`.
Open `http://localhost/SkillSwap/`. PHP 8+, mbstring and a writable
`data/runtime` directory are required. Apache must honor the supplied
`.htaccess` files (`AllowOverride All`, `mod_authz_core` and `mod_rewrite`).

For a local development alternative, run from the repository root:

```powershell
& C:\xampp\php\php.exe -S 127.0.0.1:8080 tools/router.php
```

Open `http://127.0.0.1:8080/SkillSwap/`. Always use the router with PHP's
development server; it does not process Apache `.htaccess` files.

## Five-minute demo

1. Show the landing page and explain teach/learn skills.
2. Log in as `vivek@gmail.com` / `password`. Show the profile and skills.
3. Open Matches: Rahul should show **100%**. Explain the C++/Python exchange.
4. Send Rahul an exchange request: offer C++, request Python.
5. In a separate browser/private window, log in as `rahul@gmail.com` / `password`.
6. Accept the incoming request. Show the email link for arranging the exchange.
   The link opens an email client; the platform does not send an email.
7. Return to Vivek's Sent requests and show Accepted.
8. Log in as `admin@skillswap.com` / `password` and show statistics and requests.
9. Register a disposable student, edit their profile and add a skill. Delete that
   student in the admin screen and confirm their session is blocked.

## Checks before presenting

- Test at desktop width and 375px mobile width: navigation, forms, landing cards,
  matching cards and horizontally scrolling admin tables.
- Search matches and students; confirm destructive-action prompts appear.
- Try wrong passwords, duplicate email/skill entries and blank forms.
- Request `/SkillSwap/data/seed/users.txt` and `/SkillSwap/data/runtime/users.txt`
  directly: Apache must return **403** (development router returns **404**).
- Try admin pages as a student and after logout.
- Capture landing, mutual match, accepted request and admin dashboard screenshots
  for the presentation. Use only the synthetic demo accounts.

## Automated checks

```powershell
& C:\xampp\php\php.exe tests/run.php
$env:PHP_BINARY = 'C:\xampp\php\php.exe'
node tests/http.cjs
node tests/concurrency.cjs
# Optional Windows/XAMPP Apache access checks:
node tests/apache.cjs
```

Node 18+ is required for HTTP tests. Tests use temporary data/session directories
and leave your runtime records untouched. GitHub Actions runs syntax and all
three suites on PHP 8.0 and 8.3.

## Restore the demo

Back up `data/runtime` first if you need its records. This explicitly replaces
all local records with the versioned demo fixtures:

```powershell
& C:\xampp\php\php.exe tools/reset-demo.php --confirm
```

Log out and back in after resetting. Never put real users in `data/seed` or
commit `data/runtime`. The fixtures deliberately contain public demo passwords.

## Limits

The matching score describes matching directions (100: both, 50: one), not
skill proficiency or statistical compatibility. One-way matches are displayed
but exchange requests require both directions. Accepted exchanges provide an
email contact link; scheduling, chat, completion and ratings remain future work.

Flat files suit an academic demo. Locks serialize normal application writes;
temporary-file replacement prevents partial individual records, and failed
multi-file operations attempt rollback. A power loss during changes to several
files is not a database transaction: keep backups or migrate to a database
before broader deployment.
