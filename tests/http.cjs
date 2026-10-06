// Node 18+ and PHP 8+: checks real forms, sessions, authorization and redirects.
const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');
const assert = require('assert/strict');
const temporary = fs.mkdtempSync(path.join(os.tmpdir(), 'skillswap-http-'));
const port = 18000 + Math.floor(Math.random() * 10000);
const origin = `http://127.0.0.1:${port}`;
let checks = 0, logs = '';
const php = spawn(process.env.PHP_BINARY || 'php', ['-S', `127.0.0.1:${port}`, 'tools/router.php'], {
  cwd: path.resolve(__dirname, '..'), env: { ...process.env, SKILLSWAP_TEST_DATA_DIR: temporary + path.sep }, windowsHide: true
});
php.stdout.on('data', x => { logs += x; });
php.stderr.on('data', x => { logs += x; });
php.on('error', error => { logs += error.message; });
function check(ok, message) { assert.ok(ok, message); checks++; }
class Client {
  cookie = '';
  async request(route, fields) {
    const response = await fetch(origin + route, {
      method: fields ? 'POST' : 'GET', redirect: 'manual',
      headers: { Cookie: this.cookie, ...(fields ? { 'Content-Type': 'application/x-www-form-urlencoded' } : {}) },
      body: fields ? new URLSearchParams(fields) : undefined
    });
    const cookie = response.headers.get('set-cookie');
    if (cookie) this.cookie = cookie.split(';')[0];
    return { status: response.status, body: await response.text(), location: response.headers.get('location'), cookie };
  }
  async post(route, fields) {
    const form = await this.request(route);
    const token = form.body.match(/name="csrf_token" value="([^"]+)"/);
    assert.ok(token, 'Form has CSRF token: ' + route);
    return this.request(route, { ...fields, csrf_token: token[1] });
  }
  async login(email) {
    const response = await this.post('/SkillSwap/login.php', { email, password: 'password' });
    check(response.status === 302, 'Login redirects');
    check(response.cookie.includes('HttpOnly') && response.cookie.includes('SameSite=Lax'), 'Session cookie protections');
  }
}
(async () => {
  try {
    let ready = false;
    for (let i = 0; i < 50; i++) {
      try { await fetch(origin + '/'); ready = true; break; } catch { await new Promise(r => setTimeout(r, 100)); }
    }
    assert.ok(ready, 'PHP server startup: ' + logs);
    const guest = new Client(), vivek = new Client(), rahul = new Client(), admin = new Client();
    check((await guest.request('/SkillSwap/')).body.includes('/SkillSwap/js/script.js'), 'Subfolder JS path');
    check((await guest.request('/SkillSwap/js/script.js')).status === 200, 'JS asset loads');
    check((await guest.request('/SkillSwap/dashboard.php')).location === '/SkillSwap/login.php', 'Student page requires login');
    check((await guest.request('/SkillSwap/admin/users.php')).location === '/SkillSwap/login.php', 'Admin page requires login');
    for (const route of ['/data/seed/users.txt', '/data/runtime/users.txt', '/includes/functions.php', '/tests/run.php', '/tools/reset-demo.php', '/.git/config']) {
      check((await guest.request('/SkillSwap' + route)).status === 404, 'Private path blocked: ' + route);
    }
    check((await guest.request('/SkillSwap/login.php', { email: 'vivek@gmail.com', password: 'password' })).status === 403, 'Missing CSRF rejected');
    await vivek.login('vivek@gmail.com');
    await rahul.login('rahul@gmail.com');
    check((await vivek.request('/SkillSwap/admin/users.php')).location === '/SkillSwap/dashboard.php', 'Student cannot access admin');
    await rahul.post('/SkillSwap/skills.php', { action: 'remove', user_skill_id: '1' });
    check((await rahul.request('/SkillSwap/skills.php')).body.includes('Could not remove'), 'Other student skill protected');
    check(fs.readFileSync(path.join(temporary, 'user_skills.txt'), 'utf8').includes('1|2|1|teach'), 'Unauthorized delete leaves record');
    await vivek.post('/SkillSwap/matches.php', { action: 'send_request', receiver_id: 999, offered_skill_id: 1, requested_skill_id: 2 });
    check(fs.readFileSync(path.join(temporary, 'requests.txt'), 'utf8') === '', 'Unknown receiver writes nothing');
    const matches = await vivek.request('/SkillSwap/matches.php');
    check(matches.body.includes('100% Match'), 'Mutual match displayed');
    await vivek.post('/SkillSwap/matches.php', { action: 'send_request', receiver_id: 3, offered_skill_id: 1, requested_skill_id: 2 });
    check(fs.readFileSync(path.join(temporary, 'requests.txt'), 'utf8').includes('1|2|3|1|2|pending'), 'Exchange persisted');
    await vivek.post('/SkillSwap/requests.php', { action: 'accept', request_id: 1 });
    check(fs.readFileSync(path.join(temporary, 'requests.txt'), 'utf8').includes('|pending|'), 'Sender cannot answer');
    await rahul.post('/SkillSwap/requests.php', { action: 'accept', request_id: 1 });
    check((await rahul.request('/SkillSwap/requests.php')).body.includes('mailto:vivek@gmail.com'), 'Accepted partner contact shown');
    await rahul.post('/SkillSwap/requests.php', { action: 'reject', request_id: 1 });
    check(fs.readFileSync(path.join(temporary, 'requests.txt'), 'utf8').includes('|accepted|'), 'Answered status protected');
    await guest.post('/SkillSwap/register.php', { name: 'New Student', email: 'new@example.com', password: 'password', confirm_password: 'password', department: 'CSE', year: 2 });
    check((await guest.request('/SkillSwap/dashboard.php')).body.includes('New Student'), 'Registration logs in');
    await guest.post('/SkillSwap/profile.php', { name: 'Updated Student', department: 'ECE', year: 3 });
    check((await guest.request('/SkillSwap/profile.php')).body.includes('Updated Student'), 'Profile update displayed');
    await guest.post('/SkillSwap/skills.php', { action: 'add', skill_id: 4, type: 'teach' });
    check(fs.readFileSync(path.join(temporary, 'user_skills.txt'), 'utf8').includes('|4|4|teach'), 'New student skill saved');
    await admin.login('admin@skillswap.com');
    check((await admin.request('/SkillSwap/admin/dashboard.php')).status === 200, 'Admin dashboard');
    await admin.post('/SkillSwap/admin/users.php', { action: 'delete', user_id: 4 });
    check((await guest.request('/SkillSwap/dashboard.php')).location === '/SkillSwap/login.php', 'Deleted student session blocked');
    check(!fs.readFileSync(path.join(temporary, 'user_skills.txt'), 'utf8').includes('|4|4|teach'), 'Admin deletion cascades');
    check((await vivek.request('/SkillSwap/logout.php')).status === 302, 'GET logout does not mutate');
    check((await vivek.request('/SkillSwap/dashboard.php')).status === 200, 'GET logout leaves session active');
    const page = await vivek.request('/SkillSwap/dashboard.php');
    const token = page.body.match(/name="csrf_token" value="([^"]+)"/)[1];
    await vivek.request('/SkillSwap/logout.php', { csrf_token: token });
    check((await vivek.request('/SkillSwap/dashboard.php')).location === '/SkillSwap/login.php', 'POST logout clears session');
    check(!/Fatal error|Warning:|Uncaught/.test(logs), 'No PHP warnings during HTTP flow');
    console.log(`PASS: ${checks} HTTP checks`);
  } finally {
    php.kill();
    await new Promise(resolve => { if (php.exitCode !== null) resolve(); else php.once('exit', resolve); });
    // Only individual files inside the generated temporary directory are removed.
    for (const file of fs.readdirSync(temporary)) fs.unlinkSync(path.join(temporary, file));
    fs.rmdirSync(temporary);
  }
})().catch(error => { console.error(error.message); console.error(logs.slice(-2000)); process.exitCode = 1; });
