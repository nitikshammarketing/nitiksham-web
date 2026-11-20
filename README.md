# Nitiksham — WordPress-free cPanel website

The **current website source** is `standalone/`. Public site HTML, CSS, JS, PHP API endpoints, and PHP/MySQL Studio are developed without WordPress.

- **Public source:** `standalone/public_html/` → GoDaddy cPanel document root `public_html/preview.nitiksham.com` (shared by `nitiksham.com` and `preview.nitiksham.com`).
- **Private server code:** `standalone/private/` → deploy separately, outside `public_html`. Never commit `private/config.php`, database passwords, session data or user uploads.
- **GitHub FTPS:** `.github/workflows/cpanel-ftps.yml`. This is **manual only**. Run **check** first. Production deployment is a separate explicit decision and has not been fully tested. Do not run legacy WordPress workflows.
- **Validation:** `.github/workflows/validate-standalone.yml` runs code checks but never uploads files.

The former WordPress theme/plugin and Managed WordPress deployment workflows have been deleted from the current `main` tree. Old commits may retain them in Git history; this does not deploy them. The old hosting account is managed separately from GitHub.

For implementation and backend configuration details see [the standalone documentation](standalone/README.md).
