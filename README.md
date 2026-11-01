# SkillSwap

SkillSwap is a small PHP project where students can exchange skills. You add what you can teach and what you want to learn, find students with matching interests, and send an exchange request.

I worked on this project to practise the web development concepts covered in my NIELIT training. The training took place offline from 1 to 15 September 2026. SkillSwap was separate project work after the training.

## What it does

- Student registration, login and profile editing
- Separate lists for skills to teach and skills to learn
- Matches based on both students' interests
- Exchange requests that the receiver can accept or reject
- An email link to contact the partner after acceptance
- Admin pages to view students, requests and basic statistics

For example, if I can teach C++ and want to learn Python, someone who teaches Python and wants to learn C++ is a mutual match. The app labels this as 100%. A match in only one direction gets 50%. These scores describe matching interests, not skill level. Sending a request requires a match in both directions.

## My contribution

My work covered login and registration, skill management, testing and debugging, and project documentation. I used Codex to help with development, debugging and documentation.

Working on the project helped me understand how browser forms reach PHP, how sessions keep a user logged in, and how the application reads and updates stored records.

## Built with

- HTML and CSS for pages and layout
- JavaScript for search filters, request tabs, confirmation dialogs and flash messages
- PHP for authentication, matching, requests and file operations
- Apache through XAMPP for local development
- Git and GitHub for version control

The app currently stores records in pipe-delimited text files. It does not use MySQL.

## Run it locally

The latest fixes, tests and hosting setup are on [`codex/mvp-hardening`](https://github.com/0xViivek/SkillSwap/tree/codex/mvp-hardening). They are tracked in [pull request #4](https://github.com/0xViivek/SkillSwap/pull/4).

1. Install XAMPP with PHP 8 or newer and enable the `mbstring` extension.
2. Clone the current development version into XAMPP's web folder:

   ```powershell
   cd C:\xampp\htdocs
   git clone --branch codex/mvp-hardening https://github.com/0xViivek/SkillSwap.git
   ```

3. Start Apache in the XAMPP Control Panel.
4. Open `http://localhost/SkillSwap/` in your browser.

On that branch, the app creates local records in `data/runtime` from the sample files in `data/seed`. PHP needs write access to the runtime folder. Apache must honour the included `.htaccess` rules so data files cannot be opened from the browser.

For a fresh local sample dataset:

| Role | Email | Password |
|---|---|---|
| Student | vivek@gmail.com | password |
| Student | rahul@gmail.com | password |
| Admin | admin@skillswap.com | password |

These are sample credentials for local testing. Public demo mode uses separate sample accounts, listed in the hosting guide.

## Tests

From the project folder on the development branch:

```powershell
C:\xampp\php\php.exe tests/run.php
C:\xampp\php\php.exe tests/hosting.php

# Node.js is also needed for these checks
$env:PHP_BINARY = 'C:\xampp\php\php.exe'
node tests/http.cjs
node tests/concurrency.cjs
```

The tests cover core functions, HTTP flows and competing file writes. More setup details and a demo walkthrough are in the [demo guide](https://github.com/0xViivek/SkillSwap/blob/codex/mvp-hardening/docs/DEMO.md).

## Current limits and next steps

This is an academic demo. Text files keep the setup simple, but I would move to a database and add backups before using it for a larger group. An accepted request provides an email link. The app does not send emails automatically, and it does not yet have chat, scheduling or reviews.

The PHP hosting configuration is included in the development branch. See the [hosting guide](https://github.com/0xViivek/SkillSwap/blob/codex/mvp-hardening/docs/HOSTING.md) for setup and storage limitations. There is no permanent hosted demo linked here yet.

## About me

Vivek Kumar, CSE, GEC Sheikhpura.
