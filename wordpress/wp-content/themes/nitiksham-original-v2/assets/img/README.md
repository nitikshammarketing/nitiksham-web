# Theme images (`assets/img/`)

Brand-consistent editorial images for the multi-page upgrade (Nov 2026).
All files are WebP, 1600×1066, generated for this upgrade in the site's
ivory/ink/burnt-orange style. See `IMAGES-MANIFEST.md` for the full list.

## Why this folder may look empty in the repo

The Muse GitHub connector can only push UTF-8 text files, so the binary
`.webp` files could not be committed from the build environment. They must
be added once, by either route:

1. **GitHub web UI (preferred — keeps the repo complete):** on the
   `seo-multipage-v1` branch (or `main` after merge), open
   `wordpress/wp-content/themes/nitiksham-original-v2/assets/img/` and
   drag-drop the 18 `.webp` files from `nitiksham-images.zip` (supplied
   with the preview). Commit directly to the branch.
2. **GoDaddy file manager:** upload the 18 files to
   `wp-content/themes/nitiksham-original-v2/assets/img/` on the live
   server. They survive theme deploys (the deploy workflow does not
   delete files), but they will not be in the repo until route 1 is done.

Until the images are in place, pages still render (alt text shows), but the
hero/section images and the gallery will be missing. The theme's CSS, JS,
and logo files are unaffected — they already exist in the repo.
