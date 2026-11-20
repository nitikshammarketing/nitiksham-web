# Nitiksham — nitiksham.com

The Nitiksham marketing agency website, rebuilt from WordPress on Vijay's
chosen stack: **React pre-rendered public pages + React Studio dashboard +
PHP API + MySQL, hosted on GoDaddy Deluxe cPanel.**

Live site: https://nitiksham.com

## Stack

| Component | Technology |
|---|---|
| Frontend design | React + HTML/CSS |
| Public pages / SEO | Pre-rendered HTML (ReactDOMServer) |
| Studio dashboard | React SPA (esbuild bundle) |
| Backend APIs | PHP 8.x |
| Database | MySQL (SQLite for local tests) |
| Admin authentication | Secure PHP sessions + CSRF |
| Gallery & image uploads | PHP + private file storage (outside web root) |
| Code management | GitHub |
| Hosting | GoDaddy Deluxe cPanel |

## Layout

```
src/
  site.config.js        # site URL, email, address line, sitemap paths, llms.txt, software products
  components/           # Layout, Seo, Header, Footer (shared chrome)
  pages/*.jsx           # 17 public pages + login + studio shell + 404
  studio/               # Studio SPA (React): gallery, software, enquiries
  build.mjs             # ReactDOMServer -> dist/ + sitemap/robots/llms
  render-entry.jsx      # page registry for the build
public-assets/          # css, js, img, brand files -> copied to dist/assets/
api/                    # PHP endpoints -> deployed to public_html/api/
db/schema.sql           # records, admins, enquiries (+ stockdesk seed)
storage/.gitkeep        # on the server: ~/nitiksham-storage (outside web root)
tools/                  # extract-pages.py (WP theme -> JSX), verify.py
docs/DEPLOY.md          # exact cPanel deploy steps
.github/workflows/      # deploy.yml — npm build + rsync over SSH
```

`dist/` is the build output and the exact content of `public_html`:
pre-rendered pages, `assets/`, `api/`, `studio/app.js`, `.htaccess`,
`sitemap.xml`, `robots.txt`, `llms.txt`, `404.html`.

## Commands

```bash
npm install        # react, react-dom, esbuild
npm run build      # pre-render pages, bundle the Studio SPA, assemble dist/
npm run verify     # check dist/: SEO tags, sitemap, links, forbidden strings
```

Local preview (static pages only — the API needs PHP + a database):

```bash
npx serve dist -l 3000        # or: python3 -m http.server 3000 -d dist
```

Local API tests (SQLite, no MySQL needed):

```bash
php tests/api-test.php
```

## Rules that must not be broken

- **No phone number and no street address anywhere** — in code, copy, links
  or meta. The only address line allowed is
  "Karnal, Haryana – 132001, India", and only in the footer, the contact
  page and the schema locality. `npm run verify` greps for this.
- **No invented facts**: no client names, testimonials, stats, founding
  year or awards. Only verified facts (7 services, 5-step process,
  industries, the name's meaning, contact@nitiksham.com).
- **Never commit secrets.** DB credentials and the install token come from
  environment variables or `config.local.php` outside the web root.
  See `docs/DEPLOY.md`.

## Content source

The 15 WordPress pages were carried over from the WordPress theme
(nitiksham-original-v2, PR #1 content + round-3 typography/motion) by
`tools/extract-pages.py`, which converts each theme page's `<main>` content
and `<head>` SEO into a React page component. The script is kept for
reference; pages are edited as JSX from here on.

Two deliberate content changes from the WordPress version:

- **Contact form**: submits to the PHP API (`/api/contact.php`), which
  stores the enquiry and emails the studio. The old WhatsApp submit is gone.
- **Privacy page**: the paragraph that said enquiries "do not store your
  details in a website database" was rewritten to describe the new
  store-and-email behaviour truthfully.

## Deploy

See [docs/DEPLOY.md](docs/DEPLOY.md). In short: the code lives in
`nitikshammarketing/nitiksham-web`, branch `react-rebuild`; run the
"Deploy to GoDaddy cPanel" workflow manually from the Actions tab to build
and rsync `dist/` to cPanel. First-time setup needs the MySQL database,
the private storage folder, `config.local.php`, and one run of the
installer (`/api/install.php`).

**Note:** `main` of that repo holds the older standalone static site — this
React rebuild lives only on the `react-rebuild` branch.
