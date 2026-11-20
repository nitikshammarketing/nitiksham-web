# Nitiksham Studio — standalone cPanel rebuild (development v1)

**Target:** GoDaddy Deluxe Web Hosting (cPanel); no WordPress required.

This is a source-based rebuild of the user's existing Nitiksham Studio design, not a new visual concept. The public 18 routes preserve the original HTML/CSS/JS navigation, layout, copy and animation with the newer official Nitiksham PNG branding. Static pages stay static for fast rendering and SEO. A new PHP + MySQL backend replaces the WordPress/Cloudflare/Sites-specific login, gallery and Studio API. Optional React Studio source is under `react-studio/`, **not enabled in production yet**.

## Directory layout

```
standalone/
├── public_html/           <- place CONTENTS in cPanel domain document root
│   ├── index.html         <- public homepage
│   ├── {route}/index.html <- SEO-friendly public pages
│   ├── login/index.php    <- server-owned password login
│   ├── studio/index.php   <- owner-only dashboard, enforced in PHP
│   ├── api/index.php      <- PHP/MySQL JSON and media API
│   ├── .htaccess          <- Apache rewrite rules and security headers
│   └── assets/            <- original hero and official Nitiksham logo
├── private/               <- place OUTSIDE public_html as a sibling folder
│   ├── config.example.php <- copy to config.php and set credentials
│   ├── bootstrap.php
│   ├── data.php
│   ├── schema.sql         <- phpMyAdmin migration
│   └── storage/           <- owner uploads; no public direct URLs
├── react-studio/          <- React 19 Studio UI source, optional / not yet shipped
├── tools/                 <- local and cPanel maintenance tools
└── docs/
```

## What's implemented

- 18 page routes including homepage, service pages, gallery, login and Studio; the two private routes are PHP.
- Official full Nitiksham PNG in spacious header/footer, compact square `n.` icon on small layouts, favicon and touch icon.
- Existing service navigation, CSS animations, gallery frontend and WhatsApp/email enquiry behaviour.
- PHP owner login with password hashing, throttling, session renewal and CSRF protection.
- MySQL table for gallery designs/newsletters and software records.
- Private uploaded image/PDF files stored **outside** `public_html`, accessible only through the checked PHP API.
- Public JSON APIs (`/api/gallery`, `/api/software`) and owner CRUD endpoints, preserving prior frontend response shapes.
- Stockdesk default software description seeded by `private/schema.sql`. Any live/demo URL must be configured explicitly.
- React Studio component prototype for future progressive migration, not shipped in the current static release.

## Initial setup on new GoDaddy cPanel hosting

1. **Do NOT point nitiksham.com at the new hosting yet.** Keep current Managed WordPress site running as a fallback.
2. Open the new hosting's cPanel dashboard and find the destination document root (often `public_html`). Create a staging domain/subdomain or use a validated preview mechanism.
3. In **cPanel > MySQL Databases**, create a database and a user; grant the user the minimum database permissions needed for this application. In phpMyAdmin, import `private/schema.sql` to the new DB. No WordPress DB is required.
4. Copy `private/` into the cPanel account home directory as a sibling of the public domain root. **Do not put `private/` below `public_html`**. Ensure the `private/storage` folder is writable by PHP but not web-exposed.
5. Copy `private/config.example.php` to `private/config.php`; enter the cPanel DB host, name, user, password, chosen administrator email, and a generated `password_hash()` value (use `php tools/hash-admin-password.php` in Terminal, not a plaintext password or a browser). Keep `config.php` out of Git.
6. Upload the **contents** of `public_html/` to the staging site's document root. The `public_html` folder itself is not meant to become a nested URL prefix.
7. Verify PHP 8.1+ with PDO MySQL, `fileinfo`, sessions, TLS/SSL. Set PHP `upload_max_filesize` to at least `16M` and `post_max_size` to at least `17M` to enable 15 MB uploads. Enable production PHP error logging, not display.
8. Test `/`, `/services/`, `/contact-us/`, `/gallery/`, `/software/`, `/login/`; sign in to `/studio/`, upload a private image, confirm it is denied to a logged-out browser, then publish and archive the test item.
9. Verify domain/HTTPS, redirects, mobile design, OG/SEO and cache. **Only then** switch the domain's `@` A record to the **new cPanel server IP** and validate `www` CNAME. Do not reuse the former Managed WordPress server's IP.
10. Keep the old WordPress hosting and backup until the replacement passes all acceptance tests. Configure GitHub-to-cPanel deployment separately, after server credentials and staging are available.

### Important limitations / non-goals

- **Not deployed** to the new cPanel account. New cPanel credentials and destination have not been supplied or verified.
- Site data (uploaded gallery files, private records, user credentials) **does not magically migrate** from WordPress; original source export included zero published gallery records and one software record. A separate consented data export from the current WordPress DB is needed to preserve any newly created items.
- Existing Stockdesk application is a separate product, not part of this codebase. This site lists software only.
- The React source is a prototype: npm registry is unavailable in the current authoring environment; do not describe its bundle as built or its React UI as activated.
- Existing enquiry form deliberately opens WhatsApp or the visitor's mail app; it does not silently store contact details on the server. Add a consented server-side lead form if required.
- This initial build does not include an automated production pipeline or rollback system.

## Local test (public pages, no database required)

```bash
php -S 127.0.0.1:8090 -t public_html tools/dev-router.php
```

This serves public pages. Owner Studio API cannot complete database operations until `private/config.php` and MySQL are configured. Run:

```bash
python3 tools/smoke_test.py
```

Production publish is a separate, explicit operation. Do not copy secrets, `.env` or database dumps into the public document root.
