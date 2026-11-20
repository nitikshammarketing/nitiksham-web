import Layout from '../components/Layout.jsx';

export const meta = {
  "title": "Creative Work Gallery | Branding, Web & Campaigns",
  "description": "Explore the Nitiksham creative gallery — studio visuals and explorations in branding, web design, campaigns, print and social from our studio.",
  "canonical": "https://nitiksham.com/gallery/",
  "ogType": "website",
  "ogImage": "https://nitiksham.com/assets/img/services-hero.webp",
  "twitterCard": "summary_large_image",
  "jsonLd": {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "ImageGallery",
        "name": "Creative Work Gallery | Branding, Web & Campaigns",
        "url": "https://nitiksham.com/gallery/",
        "description": "Explore the Nitiksham creative gallery — studio visuals and explorations in branding, web design, campaigns, print and social from our studio.",
        "image": [
          "https://nitiksham.com/assets/img/home-hero.webp",
          "https://nitiksham.com/assets/img/about-us-hero.webp",
          "https://nitiksham.com/assets/img/services-hero.webp",
          "https://nitiksham.com/assets/img/expertise-hero.webp",
          "https://nitiksham.com/assets/img/expertise-web-development-hero.webp",
          "https://nitiksham.com/assets/img/expertise-seo-hero.webp",
          "https://nitiksham.com/assets/img/digital-marketing-hero.webp",
          "https://nitiksham.com/assets/img/social-media-marketing-hero.webp",
          "https://nitiksham.com/assets/img/copyright-hero.webp",
          "https://nitiksham.com/assets/img/print-hero.webp",
          "https://nitiksham.com/assets/img/software-hero.webp",
          "https://nitiksham.com/assets/img/team-hero.webp"
        ]
      },
      {
        "@type": "BreadcrumbList",
        "itemListElement": [
          {
            "@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": "https://nitiksham.com/"
          },
          {
            "@type": "ListItem",
            "position": 2,
            "name": "Gallery",
            "item": "https://nitiksham.com/gallery/"
          }
        ]
      }
    ]
  },
  "bodyClass": "inner-page",
  "path": "/gallery/",
  "extraScripts": [
    "library.js"
  ]
};

export default function GalleryPage() {
  return (
    <Layout meta={meta}>
      <section className="page-head" id="top"><span className="eyebrow">THE STUDIO JOURNAL</span><h1>Creative work gallery. <em>Work worth looking into.</em></h1><p>Designs, ideas and newsletters from Nitiksham.</p></section><section className="section"><div className="section-heading"><h2>A look inside the studio</h2></div><div className="two-copy"><p>A look inside the studio — visuals, explorations and experiments from across our work: brand identity, web design, campaigns, print and social.</p><p>Browse by discipline with the filters, or open any piece to see it larger.</p></div></section><section className="section"><div className="section-heading"><h2>From the studio</h2></div><div className="filter-bar"><button type="button" className="active" data-filter="all">All work</button><button type="button" data-filter="branding">Branding</button><button type="button" data-filter="web">Web</button><button type="button" data-filter="campaigns">Campaigns</button><button type="button" data-filter="print">Print</button><button type="button" data-filter="social">Social</button></div><div className="gallery-grid"><figure className="gallery-item reveal" data-category="branding"><button type="button" data-image="/assets/img/home-hero.webp" data-title="Brand identity explorations"><img src="/assets/img/home-hero.webp" alt="Brand identity explorations — logo studies and colour palettes" width="1200" height="800" loading="lazy" decoding="async" /></button><figcaption>Brand identity explorations</figcaption></figure><figure className="gallery-item reveal" data-category="branding"><button type="button" data-image="/assets/img/about-us-hero.webp" data-title="Studio storyboards & moodboards"><img src="/assets/img/about-us-hero.webp" alt="Studio storyboards and moodboards — the thinking behind a brand" width="1200" height="800" loading="lazy" decoding="async" /></button><figcaption>Studio storyboards & moodboards</figcaption></figure><figure className="gallery-item reveal" data-category="campaigns"><button type="button" data-image="/assets/img/services-hero.webp" data-title="Campaign planning boards"><img src="/assets/img/services-hero.webp" alt="Campaign planning boards — ideas mapped before production" width="1200" height="800" loading="lazy" decoding="async" /></button><figcaption>Campaign planning boards</figcaption></figure><figure className="gallery-item reveal" data-category="branding"><button type="button" data-image="/assets/img/expertise-hero.webp" data-title="Identity & layout studies"><img src="/assets/img/expertise-hero.webp" alt="Identity and layout studies — type, colour and composition" width="1200" height="800" loading="lazy" decoding="async" /></button><figcaption>Identity & layout studies</figcaption></figure><figure className="gallery-item reveal" data-category="web"><button type="button" data-image="/assets/img/expertise-web-development-hero.webp" data-title="Website design explorations"><img src="/assets/img/expertise-web-development-hero.webp" alt="Website design explorations — layouts and interface sketches" width="1200" height="800" loading="lazy" decoding="async" /></button><figcaption>Website design explorations</figcaption></figure><figure className="gallery-item reveal" data-category="web"><button type="button" data-image="/assets/img/expertise-seo-hero.webp" data-title="Search & content planning"><img src="/assets/img/expertise-seo-hero.webp" alt="Search and content planning — pages mapped around real questions" width="1200" height="800" loading="lazy" decoding="async" /></button><figcaption>Search & content planning</figcaption></figure><figure className="gallery-item reveal" data-category="campaigns"><button type="button" data-image="/assets/img/digital-marketing-hero.webp" data-title="Campaign concept boards"><img src="/assets/img/digital-marketing-hero.webp" alt="Campaign concept boards — messages shaped for every channel" width="1200" height="800" loading="lazy" decoding="async" /></button><figcaption>Campaign concept boards</figcaption></figure><figure className="gallery-item reveal" data-category="social"><button type="button" data-image="/assets/img/social-media-marketing-hero.webp" data-title="Social content planning"><img src="/assets/img/social-media-marketing-hero.webp" alt="Social content planning — calendars, formats and graphics" width="1200" height="800" loading="lazy" decoding="async" /></button><figcaption>Social content planning</figcaption></figure><figure className="gallery-item reveal" data-category="campaigns"><button type="button" data-image="/assets/img/copyright-hero.webp" data-title="Editorial & messaging drafts"><img src="/assets/img/copyright-hero.webp" alt="Editorial and messaging drafts — words taking shape" width="1200" height="800" loading="lazy" decoding="async" /></button><figcaption>Editorial & messaging drafts</figcaption></figure><figure className="gallery-item reveal" data-category="print"><button type="button" data-image="/assets/img/print-hero.webp" data-title="Print & layout explorations"><img src="/assets/img/print-hero.webp" alt="Print and layout explorations — paper, grids and proofs" width="1200" height="800" loading="lazy" decoding="async" /></button><figcaption>Print & layout explorations</figcaption></figure><figure className="gallery-item reveal" data-category="web"><button type="button" data-image="/assets/img/software-hero.webp" data-title="Web application interface studies"><img src="/assets/img/software-hero.webp" alt="Web application interface studies — screens built around real work" width="1200" height="800" loading="lazy" decoding="async" /></button><figcaption>Web application interface studies</figcaption></figure><figure className="gallery-item reveal" data-category="branding"><button type="button" data-image="/assets/img/team-hero.webp" data-title="The studio workspace"><img src="/assets/img/team-hero.webp" alt="The studio workspace — where the work happens" width="1200" height="800" loading="lazy" decoding="async" /></button><figcaption>The studio workspace</figcaption></figure></div><dialog id="lightbox"><img src="" alt="" /><p></p><button className="close" type="button">Close</button></dialog></section><section className="section"><div className="section-heading"><h2>Studio journal</h2><p>Published designs and newsletters from the studio — new pieces appear here as they are shared.</p></div><div className="filter-bar"><button type="button" className="active" data-kind="all">All pieces</button><button type="button" data-kind="design">Design</button><button type="button" data-kind="newsletter">Newsletter</button></div><p id="gallery-status" role="status"></p><div id="gallery-grid" className="library-grid"></div></section><section className="new-cta"><span className="eyebrow">THE NEXT PIECE</span><h2>Your story could<br /><em>be next.</em></h2><a className="button" href="/contact-us/">Let’s create it ↗</a></section>
    </Layout>
  );
}
