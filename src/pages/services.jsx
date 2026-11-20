import Layout from '../components/Layout.jsx';

export const meta = {
  "title": "Our Services | Branding, Web, SEO & Marketing",
  "description": "Nitiksham's services: brand identity, websites, SEO, digital marketing, copywriting, print and social media — one connected team in India.",
  "canonical": "https://nitiksham.com/services/",
  "ogType": "website",
  "ogImage": "https://nitiksham.com/assets/img/services-hero.webp",
  "twitterCard": "summary_large_image",
  "jsonLd": {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "ItemList",
        "name": "Nitiksham services",
        "itemListElement": [
          {
            "@type": "ListItem",
            "position": 1,
            "name": "Brand & Graphic Design",
            "url": "https://nitiksham.com/expertise/"
          },
          {
            "@type": "ListItem",
            "position": 2,
            "name": "Website Design & Development",
            "url": "https://nitiksham.com/expertise-web-development/"
          },
          {
            "@type": "ListItem",
            "position": 3,
            "name": "Search Engine Optimisation",
            "url": "https://nitiksham.com/expertise-seo/"
          },
          {
            "@type": "ListItem",
            "position": 4,
            "name": "Digital Marketing",
            "url": "https://nitiksham.com/digital-marketing/"
          },
          {
            "@type": "ListItem",
            "position": 5,
            "name": "Copywriting & Content",
            "url": "https://nitiksham.com/copyright/"
          },
          {
            "@type": "ListItem",
            "position": 6,
            "name": "Print & Outdoor",
            "url": "https://nitiksham.com/print/"
          },
          {
            "@type": "ListItem",
            "position": 7,
            "name": "Social Media Management",
            "url": "https://nitiksham.com/social-media-marketing/"
          }
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
            "name": "Services",
            "item": "https://nitiksham.com/services/"
          }
        ]
      }
    ]
  },
  "bodyClass": "inner-page",
  "path": "/services/",
  "extraScripts": []
};

export default function ServicesPage() {
  return (
    <Layout meta={meta}>
      <section className="page-head reveal" id="top"><span className="eyebrow">OUR EXPERTISE</span><h1>Digital marketing services<br />in India.<br /><em>Built to work.<br />Better together.</em></h1><p>Seven connected services — brand, digital and communication — from one team in India.</p></section><section className="section reveal">
<div className="section-heading">
<span className="eyebrow">SEVEN SERVICES. ONE TEAM.</span>
<h2>Everything your brand needs,<br />under one roof.</h2>
</div>
<div className="two-copy">
<p>Each service stands on its own — a new logo, a faster website, a campaign with a clear audience.</p>
<p>That is how Nitiksham works. One team, one process, and seven disciplines that share what they learn. Explore each service below, or <a href="/contact-us/">talk to a strategist</a> if you are not sure where to start.</p>
</div>
</section><section className="section"><a className="service-row reveal" href="/expertise/"><span className="num">01</span><h3>Brand & graphic design</h3><span className="arrow"><svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></span></a><a className="service-row reveal" href="/expertise-web-development/"><span className="num">02</span><h3>Website design & development</h3><span className="arrow"><svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></span></a><a className="service-row reveal" href="/expertise-seo/"><span className="num">03</span><h3>Search engine optimisation</h3><span className="arrow"><svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></span></a><a className="service-row reveal" href="/digital-marketing/"><span className="num">04</span><h3>Digital marketing</h3><span className="arrow"><svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></span></a><a className="service-row reveal" href="/copyright/"><span className="num">05</span><h3>Copywriting & content</h3><span className="arrow"><svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></span></a><a className="service-row reveal" href="/print/"><span className="num">06</span><h3>Print & outdoor</h3><span className="arrow"><svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></span></a><a className="service-row reveal" href="/social-media-marketing/"><span className="num">07</span><h3>Social media management</h3><span className="arrow"><svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></span></a></section><section className="section reveal">
<div className="section-heading">
<span className="eyebrow">02 — HOW IT WORKS</span>
<h2>Five stages,<br />from first idea to growth.</h2>
</div>
<div className="two-copy">
<p>Every service follows the same five-stage process: Discover, Plan, Build, Launch, Grow.</p>
<p>After launch, we review the response and plan the next step — whether that means growing a campaign, improving a website, or bringing another service into the mix.</p>
</div>
<p>We bring this process to real estate, e-commerce, healthcare, education, B2B and industrial, and FMCG and retail clients — across India and worldwide.</p>
<figure className="image-wrap reveal"><img src="/assets/img/services-section.webp" alt="The Nitiksham five-stage process — Discover, Plan, Build, Launch, Grow" width="1200" height="800" decoding="async" loading="lazy" /><figcaption className="caption">Every project follows the same five stages — Discover, Plan, Build, Launch, Grow.</figcaption></figure>
</section><section className="new-cta"><span className="eyebrow">CONNECT THE DOTS</span><h2>The right expertise.<br /><em>At the right moment.</em></h2><p>Let’s find what your business needs next.</p><a className="button" href="/contact-us/">Start a conversation ↗</a></section>
    </Layout>
  );
}
