// Run against the actual Apache container, with SKILLSWAP_PUBLIC_DEMO=1.
const assert = require('assert/strict');
const origin = process.env.SKILLSWAP_CONTAINER_URL || 'http://127.0.0.1:18080';
let checks = 0, cookie = '';
async function request(route, fields) {
  const response = await fetch(origin + route, {
    method: fields ? 'POST' : 'GET', redirect: 'manual',
    headers: { Cookie: cookie, 'X-Forwarded-Proto': 'https', ...(fields ? { 'Content-Type': 'application/x-www-form-urlencoded' } : {}) },
    body: fields ? new URLSearchParams(fields) : undefined
  });
  const setCookie = response.headers.get('set-cookie');
  if (setCookie) cookie = setCookie.split(';')[0];
  return { status: response.status, body: await response.text(), cookie: setCookie, location: response.headers.get('location') };
}
function check(condition, message) { assert.ok(condition, message); checks++; }
(async () => {
  let healthy = false;
  for (let attempt = 0; attempt < 40; attempt++) {
    try { const health = await request('/health.php'); healthy = health.status === 200 && JSON.parse(health.body).status === 'ok'; if (healthy) break; } catch {}
    await new Promise(resolve => setTimeout(resolve, 250));
  }
  check(healthy, 'Container ready and data directory writable');
  check((await request('/')).status === 200, 'Landing page');
  const form = await request('/login.php');
  check(form.body.includes('student@skillswap.test'), 'Hosted demo credentials displayed');
  check(form.cookie.includes('secure') && form.cookie.includes('HttpOnly'), 'HTTPS proxy cookie protection');
  const token = form.body.match(/name="csrf_token" value="([^"]+)"/)[1];
  check((await request('/login.php', { email: 'student@skillswap.test', password: 'password' })).status === 403, 'Missing CSRF rejected');
  const login = await request('/login.php', { email: 'student@skillswap.test', password: 'password', csrf_token: token });
  check(login.status === 302 && login.location === '/dashboard.php', 'Root login redirects');
  check((await request('/dashboard.php')).body.includes('Demo Student'), 'Dashboard renders');
  check((await request('/matches.php')).body.includes('100% Match'), 'Mutual match renders');
  for (const route of ['/data/seed/users.txt', '/data/runtime/users.txt', '/includes/functions.php', '/.git/config']) {
    check((await request(route)).status === 403, 'Private path blocked: ' + route);
  }
  for (const route of ['/tests/run.php', '/tools/reset-demo.php', '/Dockerfile', '/render.yaml']) {
    check([403, 404].includes((await request(route)).status), 'Source/tool path unavailable: ' + route);
  }
  check((await request('/css/style.css')).status === 200, 'Stylesheet loads');
  check((await request('/js/script.js')).status === 200, 'JavaScript loads');
  console.log(`PASS: ${checks} Apache container checks`);
})().catch(error => { console.error(error.message); process.exitCode = 1; });
