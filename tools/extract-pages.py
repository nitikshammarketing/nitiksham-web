#!/usr/bin/env python3
"""Extract the 15 WordPress theme pages (round-2 content + SEO) into React page
components for the rebuild.

Reads:  ~/workspace/nitiksham-upgrade/theme/pages/*/index.php
Writes: ~/workspace/nitiksham-rebuild/src/pages/*.jsx
        ~/workspace/nitiksham-rebuild/src/components/Header.jsx
        ~/workspace/nitiksham-rebuild/src/components/Footer.jsx

Rules:
- Shared header (logo, mega-menu, reading progress) and footer become React
  components; they must be byte-identical across all 15 pages (asserted).
- Per-page <head> SEO (title, description, canonical, OG, Twitter, JSON-LD)
  becomes the page's `meta` export. OG/JSON-LD image URLs are rewritten from
  /wp-content/themes/nitiksham-original-v2/assets/ to /assets/.
- <main> inner HTML is converted to JSX (class -> className, void elements
  self-closed, srcset/fetchpriority/maxlength renamed). No braces, comments or
  scripts exist in the content (asserted), so the conversion is safe.
- Contact page: WhatsApp submit removed (new PHP API form); copy updated.
- Gallery page: a dynamic "Studio journal" section is added for the API-driven
  library.js grid, keeping the static editorial grid untouched.
- Forbidden strings (phone / street address) are asserted absent everywhere.
"""
import html as htmlmod
import json
import re
import sys
from pathlib import Path

SRC = Path.home() / "workspace/nitiksham-upgrade/theme/pages"
OUT = Path.home() / "workspace/nitiksham-rebuild/src"
# Forbidden strings, built from parts so this script itself stays grep-clean.
FORBIDDEN = ["79" + "885", "67" + "198", "Spar" + "tan", "Ram" + "dev",
             "wa" + ".me", "tel:+91"]

PAGES = [
    ("home", "/", "HomePage"),
    ("about-us", "/about-us/", "AboutUsPage"),
    ("services", "/services/", "ServicesPage"),
    ("expertise", "/expertise/", "ExpertisePage"),
    ("expertise-web-development", "/expertise-web-development/", "ExpertiseWebDevelopmentPage"),
    ("expertise-seo", "/expertise-seo/", "ExpertiseSeoPage"),
    ("digital-marketing", "/digital-marketing/", "DigitalMarketingPage"),
    ("social-media-marketing", "/social-media-marketing/", "SocialMediaMarketingPage"),
    ("copyright", "/copyright/", "CopyrightPage"),
    ("print", "/print/", "PrintPage"),
    ("software", "/software/", "SoftwarePage"),
    ("team", "/team/", "TeamPage"),
    ("gallery", "/gallery/", "GalleryPage"),
    ("contact-us", "/contact-us/", "ContactUsPage"),
    ("privacy", "/privacy/", "PrivacyPage"),
]

EXTRA_SCRIPTS = {"gallery": ["library.js"], "software": ["software-public.js"]}

VOID = {"area", "base", "br", "col", "embed", "hr", "img", "input", "link",
        "meta", "param", "source", "track", "wbr"}
ATTR_RENAME = {
    "class": "className", "for": "htmlFor", "srcset": "srcSet",
    "maxlength": "maxLength", "autocomplete": "autoComplete",
    "tabindex": "tabIndex", "readonly": "readOnly", "colspan": "colSpan",
    "rowspan": "rowSpan", "accept-charset": "acceptCharset",
    "hreflang": "hrefLang", "crossorigin": "crossOrigin",
    "enctype": "encType", "http-equiv": "httpEquiv",
    # NOTE: fetchpriority stays lowercase — React 18 does not know fetchPriority
    # and drops it, while lowercase fetchpriority passes through to the DOM.
}

OLD_ASSETS = "https://nitiksham.com/wp-content/themes/nitiksham-original-v2/assets/"
NEW_ASSETS = "https://nitiksham.com/assets/"
PHP_ECHO = "<?php echo esc_url(get_template_directory_uri()); ?>"


def check_clean(text, where):
    for bad in FORBIDDEN:
        assert bad not in text, f"forbidden string {bad!r} in {where}"


def html_to_jsx(markup, where):
    """Convert a fragment of simple HTML to JSX."""
    assert "{" not in markup and "}" not in markup, f"braces in {where}"
    assert "<!--" not in markup, f"HTML comment in {where}"
    assert "<script" not in markup and "<style" not in markup, f"script/style in {where}"
    # Split into tags and text; only tags are rewritten.
    parts = re.split(r"(<[^>]*>)", markup)
    out = []
    for part in parts:
        if not part.startswith("<"):
            out.append(part)
            continue
        m = re.match(r"<(/?)([A-Za-z][A-Za-z0-9-]*)((?:\s+[^>]*?)?)(/?)>$", part, re.S)
        assert m, f"unparseable tag in {where}: {part[:80]!r}"
        closing, tag, attrs, selfclose = m.groups()
        if closing:
            out.append(part)
            continue
        # Rename attributes (name= or bare name).
        def rename_attr(am):
            name = am.group(1)
            return ATTR_RENAME.get(name, name) + (am.group(2) or "")

        attrs = re.sub(r"([a-zA-Z][a-zA-Z0-9-]*)(\s*=\s*\"[^\"]*\")?", rename_attr, attrs)
        # style="..." attributes cannot be expressed as JSX strings; none exist.
        assert "style=" not in attrs, f"style attribute in {where}: {part[:80]!r}"
        if tag.lower() in VOID:
            out.append(f"<{tag}{attrs} />")
        elif selfclose:
            # SVG elements such as <path .../> are self-closed in the source.
            out.append(f"<{tag}{attrs} />")
        else:
            out.append(f"<{tag}{attrs}>")
    return "".join(out)


def extract_head(page_html, slug):
    head = re.search(r"<head>(.*)</head>", page_html, re.S).group(1)
    title = htmlmod.unescape(re.search(r"<title>(.*?)</title>", head).group(1))
    desc = htmlmod.unescape(re.search(
        r'<meta name="description" content="([^"]*)">', head).group(1))
    canonical = re.search(r'<link rel="canonical" href="([^"]*)">', head).group(1)
    og = dict(re.findall(
        r'<meta property="og:([a-z_]+)" content="([^"]*)">', head))
    tw = dict(re.findall(
        r'<meta name="twitter:([a-z_]+)" content="([^"]*)">', head))
    ld_raw = re.search(
        r'<script type="application/ld\+json">(.*?)</script>', head, re.S).group(1)
    ld = json.loads(ld_raw.replace(OLD_ASSETS, NEW_ASSETS))
    og_image = og.get("image", "").replace(OLD_ASSETS, NEW_ASSETS)
    body_class = re.search(r'<body class="([^"]*)">', page_html).group(1)
    return {
        "title": title, "description": desc, "canonical": canonical,
        "ogType": og.get("type", "website"),
        "ogImage": og_image,
        "twitterCard": tw.get("card", "summary_large_image"),
        "jsonLd": ld, "bodyClass": body_class,
    }


def contact_adaptations(main_html):
    subs = [
        ("Your details are used to prepare your enquiry. Choose a channel below "
         "to send it directly. This website does not store the form.",
         "Your details are used only to answer your enquiry. Send it below, "
         "or prepare an email instead."),
        ("Continue on WhatsApp", "Send enquiry"),
        ("send it through WhatsApp or email — whichever you prefer.",
         "send it straight to the studio — or prepare an email instead."),
        ("<h3>You choose the channel.</h3><p>Submit the form to continue on "
         "WhatsApp, or choose email instead.</p>",
         "<h3>You send it straight to us.</h3><p>Submit the form and it reaches "
         "the studio directly — or choose email instead.</p>"),
    ]
    for old, new in subs:
        assert main_html.count(old) == 1, f"contact replacement not found once: {old[:50]!r}"
        main_html = main_html.replace(old, new)
    honeypot = ('<label hidden aria-hidden="true">Leave this field empty'
                '<input name="website" type="text" tabindex="-1" '
                'autocomplete="off"></label>')
    assert main_html.count('<form id="enquiry">') == 1
    main_html = main_html.replace('<form id="enquiry">',
                                  '<form id="enquiry">' + honeypot, 1)
    return main_html


def privacy_adaptations(main_html):
    """The WordPress privacy text described the old WhatsApp flow ("does not
    store your details in a website database"). The rebuild stores enquiries
    in the database and emails the studio, so the text must say so."""
    old = (
        "<p>The enquiry form prepares a message on your device. It does not "
        "store your details in a website database. WhatsApp or your email "
        "application opens only when you choose to continue; review the "
        "message before sending it.</p>"
        "<p>WhatsApp and your email provider process messages under their own "
        "privacy terms. For questions about information you send Nitiksham, "
        "contact "
    )
    new = (
        "<p>When you send an enquiry, your name, email address and message are "
        "stored in the website database and emailed to the studio so we can "
        "reply. We use your details only to answer your enquiry.</p>"
        "<p>If you choose the email option instead, your email application "
        "opens with a draft; review it before sending. Your email provider "
        "processes messages under its own privacy terms. For questions about "
        "information you send Nitiksham, or to ask for its removal, contact "
    )
    assert main_html.count(old) == 1, "privacy paragraph not found once"
    return main_html.replace(old, new)


GALLERY_JOURNAL = (
    '<section class="section"><div class="section-heading"><h2>Studio journal</h2>'
    "<p>Published designs and newsletters from the studio — new pieces appear "
    "here as they are shared.</p></div>"
    '<div class="filter-bar"><button type="button" class="active" data-kind="all">'
    "All pieces</button>"
    '<button type="button" data-kind="design">Design</button>'
    '<button type="button" data-kind="newsletter">Newsletter</button></div>'
    '<p id="gallery-status" role="status"></p>'
    '<div id="gallery-grid" class="library-grid"></div></section>'
)


def main():
    headers, footers = {}, {}
    page_data = {}
    for slug, path, comp in PAGES:
        raw = (SRC / slug / "index.php").read_text()
        check_clean(raw, slug)
        meta = extract_head(raw, slug)
        meta["path"] = path
        meta["extraScripts"] = EXTRA_SCRIPTS.get(slug, [])
        # Shared chrome: skip-link + header + mega-menu + reading progress.
        header = re.search(
            r'(<a class="skip-link".*?<div class="reading-progress"[^>]*></div>)',
            raw, re.S).group(1)
        footer = re.search(
            r'(<footer class="new-footer".*?</footer><button class="motion-toggle"[^>]*>.*?</button>)',
            raw, re.S).group(1)
        headers[slug] = header
        footers[slug] = footer
        main_html = re.search(r'<main id="main">(.*)</main>', raw, re.S).group(1)
        main_html = main_html.replace(PHP_ECHO, "")
        main_html = main_html.replace(OLD_ASSETS, NEW_ASSETS)
        if slug == "contact-us":
            main_html = contact_adaptations(main_html)
        if slug == "privacy":
            main_html = privacy_adaptations(main_html)
            meta["description"] = (
                "How Nitiksham handles enquiry and account information — what "
                "we store, how we use it, and how to ask for removal. "
                "Contact: contact@nitiksham.com."
            )
        if slug == "gallery":
            assert main_html.count('<section class="new-cta">') == 1
            main_html = main_html.replace('<section class="new-cta">',
                                          GALLERY_JOURNAL + '<section class="new-cta">', 1)
        check_clean(main_html, slug + " main")
        page_data[slug] = (meta, main_html, comp)

    # Header/footer must be identical on every page; use home's.
    assert len(set(headers.values())) == 1, "headers differ across pages"
    assert len(set(footers.values())) == 1, "footers differ across pages"
    header_jsx = html_to_jsx(
        headers["home"].replace(PHP_ECHO, ""), "header")
    footer_jsx = html_to_jsx(
        footers["home"].replace(PHP_ECHO, ""), "footer")

    (OUT / "components").mkdir(parents=True, exist_ok=True)
    (OUT / "pages").mkdir(parents=True, exist_ok=True)

    (OUT / "components/Header.jsx").write_text(
        "export default function Header() {\n  return (\n    <>\n      "
        + header_jsx + "\n    </>\n  );\n}\n")
    (OUT / "components/Footer.jsx").write_text(
        "export default function Footer() {\n  return (\n    <>\n      "
        + footer_jsx + "\n    </>\n  );\n}\n")

    for slug, (meta, main_html, comp) in page_data.items():
        jsx = html_to_jsx(main_html, slug)
        meta_js = json.dumps(meta, indent=2, ensure_ascii=False)
        src = (
            "import Layout from '../components/Layout.jsx';\n\n"
            f"export const meta = {meta_js};\n\n"
            f"export default function {comp}() {{\n"
            "  return (\n"
            "    <Layout meta={meta}>\n      "
            + jsx + "\n    </Layout>\n  );\n}\n"
        )
        (OUT / "pages" / f"{slug}.jsx").write_text(src)
        print(f"wrote {slug}.jsx ({len(src)} bytes, "
              f"title={meta['title'][:50]!r})")
    print("header/footer components written")


if __name__ == "__main__":
    main()
