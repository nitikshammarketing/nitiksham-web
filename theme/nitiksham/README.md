# Nitiksham Studio — WordPress theme (draft v0.1.0)

Custom classic WordPress theme for Nitiksham Marketing, compatible with the site's GoDaddy Managed WordPress installation.

## Status

**Development starter only — not yet deployed.** The current GoDaddy WordPress theme is Twenty Twenty-Five. This package is a fresh design starting point; copy and contact details require the business owner's review before going live. No fabricated claims or testimonials are included.

## Install for a safe visual preview

1. Zip the `nitiksham` theme folder (the ZIP must contain top-level `nitiksham/style.css`, `nitiksham/functions.php`, etc.).
2. WordPress dashboard → Appearance → Themes → Add New → Upload Theme.
3. Install the ZIP. **Do not activate until ready to preview the new design**; use a staging site where possible.
4. In Appearance → Customize → Nitiksham contact details, enter a verified public email and phone if desired.
5. In Settings → Reading, set the homepage to show latest posts? This theme uses `front-page.php`, so it will render the designed homepage on the front page whether the site uses a static front page or latest posts.
6. In Appearance → Menus (or the Classic Navigation screen), optionally assign a Primary menu. The theme has built-in section links as its fallback.

## GitHub Actions deployment — not configured yet

The existing `.github/workflows/deploy.yml` runs only when manually dispatched and currently checks out the entire repository. **Do not run it as-is**.

After the theme has been installed once so its target directory exists, GoDaddy's official deploy action can be scoped using:

```yaml
with:
  source_path: theme/nitiksham
  deployment_dest: wp-content/themes/nitiksham
  cleanup_deleted_files: 'no'
```

Keep the existing `remote_host`, `ssh_user` and `secrets.PRIVATE_KEY` settings untouched. Confirm the destination path is correct and test on staging first. Never deploy the repository root to the WordPress site root or overwrite `wp-content/uploads`, `wp-config.php`, or the database.

## Files

- `style.css`: WordPress theme metadata
- `functions.php`: theme registration, fonts/assets, customizer settings
- `header.php` / `footer.php`: layout, navigation, brand
- `front-page.php`: redesigned marketing agency homepage
- `page.php`, `single.php`, `index.php`, `404.php`: standard WordPress templates
- `assets/css/main.css`: responsive design
- `assets/js/main.js`: accessible mobile menu

## Final review before launch

- Confirm brand name/registered business entity and legal pages.
- Provide a real logo, brand colors, contact email, and social links.
- Verify services and claims with the business.
- Add an actual contact form, privacy notice, analytics and cookie consent if applicable.
- Run browser/mobile/keyboard/accessibility tests and PHP/theme checks in staging.
- Back up GoDaddy site before activation or any deployment.
