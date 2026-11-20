# Rebuild rationale

## Why not move the previous WordPress ZIP?

The requested target is new **GoDaddy cPanel Deluxe web hosting**, not Managed WordPress. The current WordPress theme used WordPress functions and plugin APIs, so uploading its PHP templates unchanged would not work without WordPress. The correct source of truth is `Nitiksham-Studio-Complete-Source(1).zip`, which already contains the original rendered HTML, source assets, animations and route map. Recreating those pages in an unnecessary client-only SPA would add dependency and search indexing risk without improving the design.

## Architecture

- **Public marketing site:** static HTML pages and CSS/JS, first-paint content available without JavaScript; no React runtime needed for public SEO.
- **Backend:** PHP 8.1+ behind Apache, PDO MySQL, session auth, CSRF-protected mutating REST endpoints; local file storage outside web root.
- **React:** source-only Studio dashboard prototype, for a gradual upgrade once package builds and auth integration are verified.
- **GitHub:** version-controlled source in separate `feature/standalone-cpanel-v1` branch, manual cPanel migration after staging tests.

## Migration parity checklist

Public: brand logo, font/layout, landing sections, mobile nav, menu drawer, service URLs, gallery display, software display, contact enquiry, animations, SEO canonical links.

Private: admin login, dashboard, owner permissions, file type and size validation, publish/unpublish/archive, software editing, no private direct asset URLs, CSRF, failed-login throttle, logout.

Operational: SSL, cPanel DB schema, PHP limits, staging, backup/restore, mail and DNS effects, deployment scripts and remote application testing.

## React limitations

Full React/Next.js SSR requires a Node runtime and a deployable build, which is not needed to serve this site on cPanel. React remains appropriate for a richer Studio dashboard and may be built with npm once network/package installation is available. Backend sessions remain on PHP and never depend only on client-side route guards.
