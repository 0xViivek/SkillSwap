const { spawn } = require('child_process');
const fs = require('fs'), os = require('os'), path = require('path'), assert = require('assert/strict');
const temporary = fs.mkdtempSync(path.join(os.tmpdir(), 'skillswap-concurrency-'));
function worker(email) {
  return new Promise((resolve, reject) => {
    const process = spawn(global.process.env.PHP_BINARY || 'php', ['tests/concurrency-worker.php', temporary + path.sep, email], { cwd: path.resolve(__dirname, '..'), windowsHide: true });
    let output = '', errors = '';
    process.stdout.on('data', data => { output += data; });
    process.stderr.on('data', data => { errors += data; });
    process.on('error', reject);
    process.on('exit', code => code || errors ? reject(Error(errors || 'Worker failed')) : resolve(output.trim()));
  });
}
(async () => {
  try {
    const duplicates = await Promise.all(Array.from({ length: 8 }, () => worker('same@example.com')));
    assert.equal(duplicates.filter(x => x !== 'duplicate').length, 1, 'One concurrent duplicate registration succeeds');
    const ids = await Promise.all(Array.from({ length: 8 }, (_, i) => worker(`unique${i}@example.com`)));
    assert.equal(new Set(ids).size, 8, 'Concurrent IDs stay unique');
    const users = fs.readFileSync(path.join(temporary, 'users.txt'), 'utf8').trim().split(/\r?\n/);
    assert.equal(users.length, 12, 'No records lost');
    assert.ok(users.every(row => row.split('|').length === 7), 'No partial records');
    console.log('PASS: 4 concurrent storage checks (16 competing registrations)');
  } finally {
    for (const file of fs.readdirSync(temporary)) fs.unlinkSync(path.join(temporary, file));
    fs.rmdirSync(temporary);
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
