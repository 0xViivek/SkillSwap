# PHP hosting with Render

This deploys the existing PHP application, including the updated dashboard and
navigation, on a stable `onrender.com` URL. It does not depend on your laptop.

## Free demo deployment

1. Install/connect the Render integration and sign in to your Render account.
2. Create a **Web Service** from `https://github.com/0xViivek/SkillSwap`.
3. Select branch **codex/mvp-hardening** and language/runtime **Docker**.
4. Choose the **Free** instance plan. Leave the Docker command empty.
5. Set health check path `/health.php` and environment variables:
   - `SKILLSWAP_PUBLIC_DEMO=1`
   - `SKILLSWAP_TRUST_PROXY=1`
6. Deploy. Share the exact URL Render returns once its health check succeeds.

Alternatively, use the repository's `render.yaml` Blueprint on that branch.
It explicitly selects the free plan and Singapore region.

### Demo accounts

| Role | Email | Password |
|---|---|---|
| Student | student@skillswap.test | password |
| Exchange partner | partner@skillswap.test | password |
| Admin | admin@skillswap.test | password |

The public-demo flag changes initial fixture names/emails to synthetic demo
identities. It does not overwrite an existing runtime dataset. Only sample
fixtures enter the image; local runtime data, Git files, tests and tools are
excluded. A banner tells visitors to use sample details.

### Free-plan limits

The URL is stable, but free services sleep after 15 minutes without traffic.
The next visit may take around a minute to start. Filesystem changes are lost
when the service restarts, redeploys or spins down, so new registrations,
profile changes and requests are disposable demo records. Do not use this
configuration for records you need to keep.

Official sources:
- https://render.com/docs/free
- https://render.com/docs/docker
- https://render.com/docs/disks

## Lasting records

Keep one application instance because storage uses local file locks. Render
requires a paid service for an attached persistent disk. If you later approve
that expense, mount a disk at `/var/lib/skillswap`. The image stores records in
`/var/lib/skillswap/records` and PHP sessions in `/var/lib/skillswap/sessions`.
Keep backups, and plan a database migration before production use.

No paid service or disk is created by the default Blueprint.

## Local Docker verification

```sh
docker build -t skillswap-hosted .
docker run --rm -p 18080:8080 -e PORT=8080 -e SKILLSWAP_PUBLIC_DEMO=1 -e SKILLSWAP_TRUST_PROXY=1 skillswap-hosted
```

In another terminal:

```sh
node tests/container.cjs
```

These HTTP checks exercise Apache, PHP, the health endpoint, root routing,
sample login, matching, secure proxy cookies and private-path protection.
GitHub Actions runs this build/check sequence as well as the existing PHP suites.

`SKILLSWAP_TRUST_PROXY=1` is for a host whose trusted reverse proxy supplies
`X-Forwarded-Proto`. Leave it unset when running a direct local HTTP server.
