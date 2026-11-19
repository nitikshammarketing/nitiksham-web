# Nitiksham Studio Backend (WordPress plugin)

Install first at **Plugins → Add New Plugin → Upload Plugin**. Activate it before activating the companion **Nitiksham Original Studio** theme.

The plugin provides WordPress-backed versions of the original site's JSON APIs and Studio record management. It stores uploaded media in a dedicated MySQL `LONGBLOB` table to prevent direct public file access. Images/PDFs are delivered through a checked WordPress endpoint and unpublished files are not exposed publicly.

## Security and setup

- WordPress administrator role (`manage_options`) is required for `/studio/` and all write endpoints.
- JavaScript reads a WordPress REST nonce, added by the active theme only for authenticated administrators.
- Supported uploads: JPG, PNG, WebP and PDF up to 15 MiB; PHP/MySQL server limits may be lower.
- `/gallery/` lists only published, nonarchived records.
- `/software/` lists public descriptions; live/demo URLs are private to the Studio.
- The exported public Stockdesk record is initialized on plugin activation. The original ZIP had 0 publicly listed gallery files.
- Private Cloudflare R2 records/files and original ChatGPT logins were NOT included in the export and cannot be automatically transferred.
- Admin sessions are WordPress sessions; use HTTPS, strong passwords, and enable 2FA. The original owner ChatGPT login is not transferable.
- Studio plugin and frontend require WordPress pretty permalink routing; after activation visit **Settings → Permalinks → Save Changes**.

## Validation required on GoDaddy staging

1. Upload and activate plugin; inspect **Tools / phpMyAdmin** if activation reports a DB error.
2. Visit `/wp-json/nitiksham/v1/gallery` (should show `[]` initially).
3. Visit `/wp-json/nitiksham/v1/software` (should show Stockdesk).
4. Anonymous `/studio/` should redirect to WordPress login; non-admin must receive 403.
5. Test file upload, file access (both draft/published), PDF download, publish/archive/restore, and software links with administrator account.
6. Check database upload quota/`max_allowed_packet` for 15 MiB files before using large files.

No external production WordPress or GoDaddy environment has been verified by this ZIP build process.
