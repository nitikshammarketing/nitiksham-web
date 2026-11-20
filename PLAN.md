# Nitiksham website rebuild — WordPress → React (pre-rendered) + PHP + MySQL

> NOTE (2026-11-20 evening): the code now lives in
> `nitikshammarketing/nitiksham-web`, branch `react-rebuild` (the planned
> `nitiksham-site` repo was never created; `main` holds the older
> standalone static site — do not touch it).

Decision by Vijay, 2026-11-20 16:18 IST. Replaces the WordPress site entirely.
The merged WordPress SEO work (PR #1, squash 259a37d) is the content/design source
of truth — it is carried forward, NOT redone. The pending WordPress deploy
(workflow run + 18 images upload) is SUPERSEDED by this rebuild; the WP site
stays live until DNS cutover to the new cPanel hosting.

## Vijay's required stack

| Component | Technology |
|---|---|
| Frontend design | React + HTML/CSS |
| Public pages / SEO | Pre-rendered HTML |
| Studio dashboard | React |
| Backend APIs | PHP |
| Database | MySQL |
| Admin authentication | Secure PHP sessions |
| Gallery & image uploads | PHP + private file storage |
| Code management | GitHub |
| Hosting | GoDaddy Deluxe cPanel |

Target repo (to be created by Vijay, private, on nitikshammarketing):
`nitikshammarketing/nitiksham-site`. Muse cannot create repos (GitHub App 403).

## Source material (do not re-fetch what exists locally)

- Theme pages (round-2 trimmed content + full SEO): `~/workspace/nitiksham-upgrade/theme/pages/*/index.php`
  (15 public pages: home, about-us, services, expertise, expertise-web-development,
  expertise-seo, digital-marketing, social-media-marketing, copyright, print,
  software, team, gallery, contact-us, privacy). Full HTML docs with PHP wrappers;
  extract per-page `<head>` SEO + `<main>` content. Shared header/footer/mega-menu
  become React Layout components.
- Theme CSS/JS (round-3, sizes match repo exactly): `~/workspace/nitiksham-upgrade/theme-assets/`
  (style.css, refined.css, motion.css, cinematic.css, motion.js, cinematic.js,
  app.js, library.js, software-public.js, studio.js). Reuse CSS + motion.js +
  cinematic.js verbatim. Adapt app.js (contact → PHP API), library.js +
  software-public.js (apiBase `/api`, file endpoint `/api/file`).
- 18 editorial WebP images: `~/workspace/nitiksham-upgrade/assets/img/*.webp`
- Logos + brand assets: download byte-exact from repo at commit 259a37d via
  `curl https://raw.githubusercontent.com/nitikshammarketing/nitiksham-web/259a37d.../wordpress/wp-content/themes/nitiksham-original-v2/assets/<file>`
  (raw.githubusercontent.com works from this VM; github.com TLS does not).
  Files: nitiksham-brand.png, nitiksham-mark.png, nitiksham-favicon.png,
  nitiksham-touch.png, logo.png, favicon.svg, brand-editorial.webp.
  (Live-site copies in /tmp/logos differ in bytes — prefer repo versions.)
- Studio plugin (API contract + DB shape): `/tmp/studio-plugin.php`
  (also in repo at wordpress/wp-content/plugins/nitiksham-studio/).
  Login + studio page templates fetched to /tmp/page-login.php, /tmp/page-studio.php
  (NOTE: those two templates still carry the OLD footer with phone + street
  address — the rebuild uses the NEW shared footer everywhere).
- llms.txt text: `nko_llms_txt()` in `~/workspace/nitiksham-upgrade/theme/functions.php`.
  Sitemap paths: `nko_public_sitemap_paths()` in the same file (15 paths).

## Current site behaviour to replicate

- Routes (all trailing-slash): /, /about-us/, /services/, /expertise/,
  /expertise-web-development/, /expertise-seo/, /digital-marketing/,
  /social-media-marketing/, /copyright/, /print/, /software/, /team/,
  /gallery/, /contact-us/, /privacy/, plus /login/ and /studio/ (private).
- Studio plugin table `nitiksham_records`: id, kind (design|newsletter|software),
  title, description, mime, published, archived, live_url, demo_url, owner_id,
  created_at, file_bytes LONGBLOB. Seed record: id `stockdesk`, kind software,
  title `Stockdesk`, description `Plumbing inventory and stock management
  workspace.`, published 1.
- REST contract (WP: /wp-json/nitiksham/v1/* → new: /api/*):
  GET /session → {authenticated, owner, email}; GET /gallery (published
  design/newsletter, no file bytes); GET /software (published, id/title/
  description only); GET /manage/items (admin, all incl. archived + liveUrl/
  demoUrl for software); POST /manage/upload (multipart, kind design|newsletter,
  title ≤120, description ≤1000, file ≤15 MiB, magic-byte MIME check
  JPG/PNG/WebP/PDF only); POST /manage/software (JSON, HTTPS-only liveUrl/
  demoUrl, no user/pass in URL); PATCH /manage/items/:id (published/archived/
  title). File endpoint: public only if published + not archived; unpublished
  to admin only; PDF = attachment, images = inline; nosniff + CSP sandbox.
- Gallery page: fetches /gallery, kind filter (all/design/newsletter), cards
  with image or PDF cover, link to file endpoint.
- Software page: fetches /software, featured `stockdesk` card + other cards
  with "Request a walkthrough" → /contact-us/?interest=<title> demo.
- Contact form: name/email/company/phone/service/message. OLD behaviour:
  WhatsApp submit (wa.me number) + mailto fallback. NEW: POST to /api/contact
  (store in DB + email contact@nitiksham.com); WhatsApp REMOVED per Vijay's
  "number hata do" instruction; keep mailto as secondary option.
- Design tokens: ivory #f6f4ee, ink #09090b/#242421, burnt-orange accent,
  Newsreader serif display + Manrope (round-3 elevated typography + motion).

## Hard constraints (Vijay's rules)

- NO phone number and NO street address anywhere (code, copy, links, meta).
  Only address line allowed: "Karnal, Haryana – 132001, India" (footer +
  contact + schema addressLocality only). No city highlight; worldwide positioning.
- NO invented facts: no client names, testimonials, stats, founding year, awards.
  Only verified facts: 7 services, 5-step process (Discover, Plan, Build,
  Launch, Grow), industries (real estate, e-commerce, healthcare, education,
  B2B & industrial, FMCG & retail), name meaning (Niti = strategy,
  Ksham = capability), email contact@nitiksham.com.
- Keep the CURRENT design — improve, don't replace. Same URLs (slugs unchanged).
- Never commit secrets. DB credentials + install token via env / config file
  outside web root. No credentials in memory or chat.
- GitHub rule: work only in nitikshammarketing account (account_id
  ba725d47673544feb258c58874e08d23). Never touch purever-dev for this.

## Architecture (cPanel: no Node at runtime, PHP 8.x + MySQL)

```
nitiksham-site/
├── README.md, package.json (react, react-dom, esbuild)
├── src/
│   ├── site.config.js          # site URL, nav, footer, email, address line
│   ├── components/             # Layout, Header, Footer, MegaMenu, Seo
│   ├── pages/*.jsx             # 15 public page components (content from theme)
│   ├── studio/                 # Studio SPA (React, esbuild bundle)
│   └── build.mjs               # ReactDOMServer → dist/ + sitemap.xml, robots.txt, llms.txt
├── public-assets/              # css, js, img, logos → copied to dist/assets/
├── api/                        # PHP → deployed to public_html/api/
│   ├── config.php db.php auth.php csrf.php json.php mailer.php
│   ├── session.php login.php logout.php
│   ├── gallery.php software.php file.php contact.php
│   ├── manage/items.php manage/upload.php manage/software.php manage/item.php
│   └── install.php             # one-time setup, INSTALL_TOKEN-guarded
├── db/schema.sql               # records, admins, enquiries (+ stockdesk seed)
├── storage/.gitkeep            # on server: ~/nitiksham-storage (OUTSIDE web root)
├── .htaccess                   # pretty URLs, https/www canonical, /api/* rewrites,
│                               # security headers, ErrorDocument 404
├── .github/workflows/deploy.yml# npm build + rsync over SSH to cPanel
├── .gitignore                  # node_modules, dist, config.local.php, storage/*
└── docs/DEPLOY.md              # cPanel steps: MySQL DB, installer, secrets, DNS cutover
```

- Build output `dist/` = public_html content: index.html, `<slug>/index.html`,
  login/index.html, studio/index.html + studio/app.js, assets/, api/,
  .htaccess, sitemap.xml, robots.txt, llms.txt, 404.html.
- .htaccess rewrites /api/gallery → /api/gallery.php etc. (keep the frontend
  contract identical: apiBase `/api`, file endpoint `/api/file?id=`).
- File storage: disk under STORAGE_PATH (outside web root), files named
  `<uuid>.<ext>`; metadata in `records` (same shape as WP table minus
  file_bytes, plus file_name/file_size). Same access rules as the plugin.
- Auth: PHP sessions (httponly, secure, samesite=Lax, strict mode, regenerate
  on login). `admins` table (email unique, password_hash). CSRF token in
  session, returned by /api/session.php, required as X-CSRF-Token on
  POST/PATCH. Login throttle: 5 attempts / 15 min per IP+email.
- DB layer: PDO with DB_DSN config so local tests can use sqlite; keep SQL
  portable (no MySQL-only syntax in queries). Production DSN = MySQL.
- Contact API: validate, honeypot, rate limit 5/hour/IP, store in `enquiries`,
  email via mail() to contact@nitiksham.com.
- Studio SPA scope (mirror studio.js + template): session gate; tabs Gallery &
  newsletters / Software & demos / Enquiries (new, read-only list + delete);
  upload form; software form (create/edit links); managed lists with
  publish/unpublish, archive/restore (with Undo), rename; archived section;
  presentation view toggle; sign out. Reuse theme CSS classes.
- Login page: pre-rendered /login/ with real email+password form → /api/login.php,
  redirect to /studio/ on success. /login/ + /studio/ get noindex.
- SEO preserved exactly: titles/descriptions/canonical/OG/Twitter/JSON-LD from
  theme pages (OG image paths change /wp-content/themes/.../assets/img/ →
  /assets/img/). robots.txt disallows /studio/ /login/ /api/. Legacy redirects:
  /wp-json/nitiksham/v1/* → /api/*, /sample-page /hello-world → /.

## Verification required before handoff

- `npm run build` clean; all 15 pages + login + studio + 404 render.
- Every PHP file passes `php -l` (install php-cli via sudo apt if needed).
- API logic tested against SQLite via DB_DSN (auth, CSRF, gallery/software
  filters, upload validation, file access rules, contact).
- Static checks: no phone number / street address anywhere in dist/ or src/
  (grep for 79885, 67198, Spartan, Ramdev); all internal links resolve;
  every page has title/description/canonical/OG/JSON-LD; sitemap lists 15 paths.
- Serve dist/ locally and screenshot-check home + one service page + gallery
  in headless Chromium if available.
