#!/usr/bin/env python3
"""Verify the built site in dist/ (run after `npm run build`).

Checks:
- all 18 public pages + login + studio + 404 exist
- every public page has title / description / canonical / OG / Twitter / JSON-LD
- login, studio and 404 carry noindex
- sitemap.xml lists the 18 public paths
- robots.txt disallows /studio/ /login/ /api/
- no forbidden strings (phone number / street address) anywhere in dist/ or src/
- every internal link and image in dist/ resolves to a file in dist/
- no wp-content / wp-json / admin-ajax references remain in dist/

Exit code 0 when every check passes.
"""
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
DIST = ROOT / "dist"

# Forbidden strings, built from parts so this checker itself stays grep-clean.
FORBIDDEN = ["79" + "885", "67" + "198", "Spar" + "tan", "Ram" + "dev",
             "wa" + ".me", "tel:+91"]

PUBLIC_PATHS = [
    "/", "/about-us/", "/services/", "/expertise/",
    "/expertise-web-development/", "/expertise-seo/", "/digital-marketing/",
    "/social-media-marketing/", "/copyright/", "/print/", "/software/",
    "/nitik-softwares/", "/nitik-hr-management/", "/nitik-stock-desk/",
    "/team/", "/gallery/", "/contact-us/", "/privacy/",
]
PRIVATE_PATHS = ["/login/", "/studio/"]

failures = []


def check(cond, msg):
    print(("PASS " if cond else "FAIL ") + msg)
    if not cond:
        failures.append(msg)


def page_file(path):
    if path == "/":
        return DIST / "index.html"
    return DIST / path.strip("/") / "index.html"


def main():
    # --- pages exist ---
    for p in PUBLIC_PATHS + PRIVATE_PATHS:
        check(page_file(p).is_file(), f"page exists: {p}")
    check((DIST / "404.html").is_file(), "page exists: /404.html")

    # --- SEO on public pages ---
    for p in PUBLIC_PATHS:
        f = page_file(p)
        if not f.is_file():
            continue
        html = f.read_text()
        for label, pat in [
            ("title", r"<title>[^<]{10,}</title>"),
            ("description", r'<meta name="description" content="[^"]{20,}"/?>'),
            ("canonical", r'<link rel="canonical" href="https://nitiksham\.com[^"]*"/?>'),
            ("og:title", r'<meta property="og:title" content="[^"]+"/?>'),
            ("og:description", r'<meta property="og:description" content="[^"]+"/?>'),
            ("og:url", r'<meta property="og:url" content="https://nitiksham\.com[^"]*"/?>'),
            ("og:image", r'<meta property="og:image" content="https://nitiksham\.com/assets/(img/[^"]+\.webp|brand-editorial\.webp)"/?>'),
            ("twitter:card", r'<meta name="twitter:card" content="[^"]+"/?>'),
            ("JSON-LD", r'<script type="application/ld\+json">'),
        ]:
            check(re.search(pat, html) is not None, f"{p} has {label}")
        check("noindex" not in html, f"{p} is indexable (no noindex)")

    # --- private pages noindexed ---
    for p in PRIVATE_PATHS + ["/404.html"]:
        f = page_file(p) if p != "/404.html" else DIST / "404.html"
        if f.is_file():
            check("noindex" in f.read_text(), f"{p} has noindex")

    # --- sitemap / robots / llms ---
    sitemap = (DIST / "sitemap.xml").read_text()
    for p in PUBLIC_PATHS:
        check(f"<loc>https://nitiksham.com{p}</loc>" in sitemap,
              f"sitemap lists {p}")
    check(len(re.findall(r"<url>", sitemap)) == 18, "sitemap has exactly 18 urls")
    robots = (DIST / "robots.txt").read_text()
    for d in ("/studio/", "/login/", "/api/"):
        check(f"Disallow: {d}" in robots, f"robots disallows {d}")
    check("Sitemap: https://nitiksham.com/sitemap.xml" in robots,
          "robots points at sitemap")
    check((DIST / "llms.txt").is_file(), "llms.txt exists")
    check((DIST / ".htaccess").is_file(), ".htaccess exists")
    check((DIST / "studio/app.js").is_file(), "studio/app.js exists")

    # --- forbidden strings in dist/ and src/ ---
    for base in (DIST, ROOT / "src"):
        bad = []
        for f in sorted(base.rglob("*")):
            if not f.is_file():
                continue
            try:
                text = f.read_text(errors="strict")
            except (UnicodeDecodeError, ValueError):
                continue  # binary asset (image/font): skip
            for s in FORBIDDEN:
                if s in text:
                    bad.append(f"{f.relative_to(ROOT)}: {s!r}")
        check(not bad, f"no forbidden strings under {base.relative_to(ROOT)}"
              + ("" if not bad else f" -> {bad[:5]}"))

    # --- no WordPress leftovers in dist/ ---
    leftovers = []
    for f in sorted(DIST.rglob("*")):
        if not f.is_file() or f.suffix in (".webp", ".png", ".svg"):
            continue
        if f.name == ".htaccess":
            continue  # legacy wp-json redirect lives here by design
        text = f.read_text(errors="ignore")
        for s in ("wp-content", "wp-json", "admin-ajax", "get_template_directory"):
            if s in text:
                leftovers.append(f"{f.relative_to(ROOT)}: {s}")
    check(not leftovers, "no WordPress references in dist/"
          + ("" if not leftovers else f" -> {leftovers[:5]}"))

    # --- internal links + images resolve ---
    def resolves(target):
        target = target.split("#")[0].split("?")[0]
        if not target or target.startswith(("http", "mailto:", "tel:")):
            return True
        if target.startswith("/api/"):
            return True  # served by PHP, checked separately
        if target == "/":
            return (DIST / "index.html").is_file()
        cand = DIST / target.lstrip("/")
        return cand.is_file() or (cand.is_dir() and (cand / "index.html").is_file())

    broken = []
    for f in sorted(DIST.rglob("*.html")):
        html = f.read_text()
        for attr in ("href", "src"):
            for target in re.findall(rf'{attr}="([^"]+)"', html):
                if not resolves(target):
                    broken.append(f"{f.relative_to(DIST)}: {attr}={target}")
    check(not broken, "all internal links/images resolve"
          + ("" if not broken else f" -> {broken[:10]}"))

    print(f"\n{len(failures)} failure(s)" if failures else "\nAll checks passed.")
    return 1 if failures else 0


if __name__ == "__main__":
    sys.exit(main())
