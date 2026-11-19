# Nitiksham Original Studio — WordPress theme

This is a *conversion of the original source ZIP*, **not** the unrelated Nitiksham Studio green theme created earlier.

- Exact 18 original `site-content` HTML page bodies and original CSS/assets/animations preserved, with minimal server-side asset URL rewriting.
- Original navigation and page URLs preserved.
- Site `/studio/` now requires a WordPress administrator login; WordPress authentication replaces the ChatGPT Sites identity layer.
- Requires separate **Nitiksham Studio Backend** plugin, which provides WordPress REST APIs and controlled storage.
- Do not run the old root-level GoDaddy rsync workflow. Upload the ZIP to WordPress Appearance → Themes. Activate the companion plugin first.
- For page routes, save WordPress Settings → Permalinks once after activating the theme if the route slugs return 404.
- The original private Cloudflare R2 data was not included in the export. Public export: 0 gallery items and 1 software record.
- WordPress PHP file upload limits and database quota must be sufficient for 15MB uploads.
- Test all routes, forms, media, Studio access and animations on preview/staging before DNS cutover.
