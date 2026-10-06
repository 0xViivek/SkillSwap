// Optional Windows/XAMPP check against real Apache, without editing its configuration.
const { spawn } = require('child_process');
const fs = require('fs'), os = require('os'), path = require('path'), assert = require('assert/strict');
const apacheRoot = process.env.APACHE_ROOT || 'C:/xampp/apache';
const executable = path.join(apacheRoot, 'bin/httpd.exe');
if (!fs.existsSync(executable)) throw Error('Set APACHE_ROOT to your XAMPP Apache folder.');
const temporary = fs.mkdtempSync(path.join(os.tmpdir(), 'skillswap-apache-'));
const root = path.resolve(__dirname, '..').replaceAll('\\', '/');
const temp = temporary.replaceAll('\\', '/');
const port = 28000 + Math.floor(Math.random() * 10000);
fs.writeFileSync(path.join(temporary, 'httpd.conf'), `
ServerRoot "${apacheRoot.replaceAll('\\', '/')}"
Listen 127.0.0.1:${port}
ServerName localhost
LoadModule authz_core_module modules/mod_authz_core.so
LoadModule rewrite_module modules/mod_rewrite.so
DocumentRoot "${root}"
PidFile "${temp}/httpd.pid"
ErrorLog "${temp}/error.log"
<Directory "${root}">
    AllowOverride All
    Require all granted
</Directory>
`);
const server = spawn(executable, ['-f', temp + '/httpd.conf', '-X'], { windowsHide: true });
let logs = '', exited = false;
server.stderr.on('data', data => { logs += data; });
server.on('exit', () => { exited = true; });
server.on('error', error => { logs += error.message; });
(async () => {
  try {
    let ready = false;
    for (let i = 0; i < 50; i++) {
      try { const response = await fetch(`http://127.0.0.1:${port}/css/style.css`); ready = response.status === 200; if (ready) break; }
      catch {}
      if (exited) break;
      await new Promise(r => setTimeout(r, 100));
    }
    assert.ok(ready, 'Apache startup: ' + logs);
    for (const route of ['/data/seed/users.txt', '/data/runtime/users.txt', '/includes/functions.php', '/tests/run.php', '/tools/reset-demo.php', '/.git/config', '/README.md']) {
      assert.equal((await fetch(`http://127.0.0.1:${port}${route}`)).status, 403, 'Apache denies ' + route);
    }
    console.log('PASS: 8 Apache asset/access checks');
  } finally {
    server.kill();
    await new Promise(resolve => { if (server.exitCode !== null) resolve(); else server.once('exit', resolve); });
    for (const file of fs.readdirSync(temporary)) fs.unlinkSync(path.join(temporary, file));
    fs.rmdirSync(temporary);
  }
})().catch(error => { console.error(error.message); console.error(logs); process.exitCode = 1; });
