# Deploying nitiksham.com to GoDaddy Deluxe cPanel

This deploys the rebuilt site (React pre-rendered pages + PHP API + MySQL)
to the new GoDaddy Deluxe cPanel hosting. The old Managed WordPress site
stays live until the DNS cutover in step 7.

> **Secrets rule:** never paste a real password, token or key into the repo,
> into chat, or into a GitHub issue. Secrets live only in cPanel, in
> `config.local.php` (outside the web root), and in GitHub Actions secrets.

## 0. Prerequisites

- The code lives in repo `nitikshammarketing/nitiksham-web`, branch
  `react-rebuild`. (`main` holds the older standalone static site — do not
  deploy from it, and do not merge this branch into `main` until the
  standalone site is retired.)
- GoDaddy Deluxe cPanel hosting is active, with SSH access enabled
  (cPanel → Security → SSH Access → Manage SSH Keys, or ask GoDaddy
  support to enable SSH for the account).

## 1. Create the MySQL database

In cPanel → **MySQL® Databases**:

1. Create a database, e.g. `nitiksham_site` (cPanel prefixes it with your
   account username, e.g. `vjsharma_nitiksham_site` — note the full name).
2. Create a MySQL user with a strong password (save it in a password
   manager, not in the repo).
3. Add the user to the database with **ALL PRIVILEGES**.
4. Note the MySQL host: on GoDaddy cPanel it is usually `localhost`.

## 2. Create the private storage folder

Uploaded gallery/newsletter files live on disk **outside the web root**,
so they can never be reached by a direct URL — only through `/api/file.php`.

- In cPanel → **File Manager** (or over SSH), create a folder next to
  `public_html`, e.g. `/home/<user>/nitiksham-storage`.
- Permissions `750` (owner read/write/execute, group read/execute).

## 3. Create config.local.php (outside the web root)

Create `/home/<user>/config.local.php` (next to `public_html`, NOT inside
it) with the real values. The API reads it automatically — see
`api/config.php` for the full key list.

```php
<?php
// Lives OUTSIDE the web root. Never committed to the repo.
return array(
    'db_dsn' => 'mysql:host=localhost;dbname=<cpanel-prefix>_nitiksham_site;charset=utf8mb4',
    'db_user' => '<cpanel-prefix>_nitiksham_user',
    'db_pass' => '<the MySQL user password from step 1>',
    'storage_path' => '/home/<user>/nitiksham-storage',
    // Long random string; used once by the installer, then keep it safe.
    'install_token' => '<generate a long random token>',
);
```

Generate the install token with, e.g. `openssl rand -hex 32` (or any
password manager's generator). Replace `<...>` placeholders with real
values; do not leave the angle brackets in.

## 4. GitHub Actions secrets

Repo → Settings → Secrets and variables → Actions → New repository secret:

| Secret | Value |
|---|---|
| `CPANEL_SSH_HOST` | cPanel server hostname or IP (cPanel home page → Server Information) |
| `CPANEL_SSH_PORT` | SSH port (usually `22`; GoDaddy sometimes uses `2222`) |
| `CPANEL_SSH_USER` | cPanel username |
| `CPANEL_SSH_KEY` | Private SSH key (see below) |
| `CPANEL_DEPLOY_PATH` | `public_html` |

SSH key setup (once):

1. On your computer: `ssh-keygen -t ed25519 -C "nitiksham-deploy" -f ~/.ssh/nitiksham-deploy`
   (no passphrase — GitHub Actions cannot type one).
2. In cPanel → **SSH Access** → Manage SSH Keys → Import Key: paste the
   **public** key (`nitiksham-deploy.pub`), then Authorize it.
3. Paste the **private** key (`nitiksham-deploy`, the whole file including
   the `BEGIN/END OPENSSH PRIVATE KEY` lines) as the `CPANEL_SSH_KEY`
   secret. Never share this key anywhere else.

## 5. Deploy

Run the workflow manually from the Actions tab (branch: `react-rebuild`,
workflow: "Deploy to GoDaddy cPanel"). It runs `npm ci`, `npm run build`,
`npm run verify`, then rsyncs `dist/` to `public_html/`.

What lands in `public_html/`: the pre-rendered pages, `assets/`, `api/`
(PHP), `studio/app.js`, `.htaccess`, `sitemap.xml`, `robots.txt`,
`llms.txt`, `404.html`. Nothing else from the repo is deployed.

## 6. Run the installer (once)

The installer creates the tables (from `api/schema.sql`) and the first
admin account. It refuses to run twice.

```bash
# Check status (GET):
curl "https://nitiksham.com/api/install.php?token=<install-token>"

# Install (POST) — replace the email/password with the real admin login:
curl -X POST "https://nitiksham.com/api/install.php" \
  -H "Content-Type: application/json" \
  -d '{"token":"<install-token>","email":"<admin-email>","password":"<12+ character password>"}'
```

Expected response: `{"ok":true,"email":"<admin-email>"}`.

Then sign in at `https://nitiksham.com/login/` with that email and
password, and open `/studio/` to confirm the dashboard loads.

> The installer only works before DNS cutover if you test against the
> server's temporary URL or IP (see step 7 note). If the domain still
> points at the old WordPress site, run the installer after cutover, or
> point your computer's `hosts` file at the new server IP temporarily.

**Re-installing:** the installer answers `409 Already installed` once an
admin exists. To wipe and start over, create the empty flag file
`nitiksham-storage/.allow-reinstall`, run the installer again (it drops
and recreates all tables), and it removes the flag itself.

## 7. DNS cutover

1. In cPanel → **Domains**, make sure `nitiksham.com` is the primary
   domain of the new hosting (add it if needed) and note the server's IP
   address (cPanel home page → Server Information → Shared IP Address).
2. At the domain registrar (where nitiksham.com's DNS lives), change the
   **A record** for `nitiksham.com` (and `www`) to the new server IP.
   **Do not touch the MX records** — email keeps working only if they
   stay as they are.
3. Wait for propagation (usually minutes, up to 48 hours). Check with
   `dig +short nitiksham.com` or an online DNS checker.
4. In cPanel → **SSL/TLS Status**, run AutoSSL so `https://nitiksham.com`
   gets a certificate on the new server. The site's `.htaccess` forces
   HTTPS + non-www, so the certificate must cover both `nitiksham.com`
   and `www.nitiksham.com`.

## 8. Post-cutover checks

- [ ] Home page + a service page load with the design intact.
- [ ] `/gallery/` shows the editorial grid; `/software/` shows Stockdesk.
- [ ] Contact form sends an enquiry (check the inbox + the Studio
      Enquiries tab).
- [ ] `/login/` signs in; `/studio/` lists items; upload a test image,
      publish it, check it appears in `/gallery/`, then archive it.
- [ ] `/sitemap.xml` lists 17 pages; `/robots.txt` and `/llms.txt` load.
- [ ] Old WordPress URLs redirect: `/wp-json/nitiksham/v1/gallery` →
      `/api/gallery`; `/sample-page` → `/`.
- [ ] Google Search Console: submit the sitemap (unchanged URL).
- [ ] After 2–3 days with no issues, cancel the old Managed WordPress
      hosting.

## Updating the site later

Edit, push to `react-rebuild`, and run the workflow manually from the
Actions tab to redeploy. The MySQL database and `nitiksham-storage` are
never touched by deploys, so gallery files, enquiries and accounts survive
every deploy.

## Troubleshooting

- **500 on `/api/*`:** check `config.local.php` exists at
  `/home/<user>/config.local.php` with the right DB credentials, and that
  the MySQL user has privileges on the database. cPanel → Metrics →
  Errors shows the PHP error log.
- **Login says "Invalid email or password" after a fresh install:** the
  installer may have run against the wrong database — check the `db_dsn`
  database name (with the cPanel prefix).
- **Uploads fail:** check `nitiksham-storage` exists and is writable by
  the web server user, and that PHP's `upload_max_filesize` is at least
  16M (cPanel → Software → Select PHP Version → Options).
- **Images 404 after deploy:** confirm the deploy workflow's rsync step
  succeeded and `public_html/assets/img/` contains the `.webp` files.
