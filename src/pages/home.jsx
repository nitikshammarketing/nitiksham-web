import Layout from '../components/Layout.jsx';

export const meta = {
  "title": "Digital Marketing Agency in India | Nitiksham",
  "description": "Nitiksham is a digital marketing agency in India — branding, websites, SEO, social media, print and content for businesses across India and worldwide.",
  "canonical": "https://nitiksham.com/",
  "ogType": "website",
  "ogImage": "https://nitiksham.com/assets/img/home-hero.webp",
  "twitterCard": "summary_large_image",
  "jsonLd": {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "Organization",
        "@id": "https://nitiksham.com/#organization",
        "name": "Nitiksham",
        "url": "https://nitiksham.com/",
        "email": "contact@nitiksham.com",
        "address": {
          "@type": "PostalAddress",
          "addressLocality": "Karnal",
          "addressRegion": "Haryana",
          "postalCode": "132001",
          "addressCountry": "IN"
        },
        "areaServed": [
          "India",
          "Worldwide"
        ]
      },
      {
        "@type": "LocalBusiness",
        "@id": "https://nitiksham.com/#localbusiness",
        "name": "Nitiksham",
        "url": "https://nitiksham.com/",
        "email": "contact@nitiksham.com",
        "address": {
          "@type": "PostalAddress",
          "addressLocality": "Karnal",
          "addressRegion": "Haryana",
          "postalCode": "132001",
          "addressCountry": "IN"
        },
        "areaServed": [
          "India",
          "Worldwide"
        ]
      },
      {
        "@type": "WebSite",
        "@id": "https://nitiksham.com/#website",
        "url": "https://nitiksham.com/",
        "name": "Nitiksham",
        "publisher": {
          "@id": "https://nitiksham.com/#organization"
        }
      },
      {
        "@type": "BreadcrumbList",
        "itemListElement": [
          {
            "@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": "https://nitiksham.com/"
          }
        ]
      }
    ]
  },
  "bodyClass": "refined-home",
  "path": "/",
  "extraScripts": []
};

export default function HomePage() {
  return (
    <Layout meta={meta}>
      <section className="editorial-hero" id="top"><div className="hero-copy"><span className="eyebrow">INDEPENDENT THINKING. CONNECTED EXPERTISE.</span><h1>Digital marketing agency<br />in India.<br /><em>Clarity for your brand.</em></h1><p>Nitiksham is a strategy-led brand, design and digital marketing agency working across India and worldwide.</p><a className="button" href="/services/">Explore our expertise ↗</a><a className="hero-secondary" href="/gallery/">View the studio gallery →</a></div><div className="hero-art"><img src="/assets/img/home-hero.webp" alt="Nitiksham studio — brand strategy, design and digital marketing" width="1200" height="800" fetchpriority="high" /><span>01 / FROM STRATEGY TO EXPRESSION</span></div></section><section className="section reveal" id="about-nitiksham">
<div className="section-heading">
<span className="eyebrow">01 — WHO WE ARE</span>
<h2>A marketing agency<br />working across India and worldwide.</h2>
<p>Nitiksham is a multidisciplinary marketing agency working across India and worldwide.</p>
</div>
<div className="values">
<article><span>01</span><h3>Who we are</h3><p>A strategy-led team of brand thinkers, designers, developers, writers and marketers, working together around your business.</p></article>
<article><span>02</span><h3>What we do</h3><p>Brand identity, websites, SEO, digital marketing, copywriting, print and social media — seven services, one shared direction.</p></article>
<article><span>03</span><h3>Where we work</h3><p>Based in India, serving clients across India and worldwide, from the first conversation to long-term growth.</p></article>
<article><span>04</span><h3>How we work</h3><p>Five stages — Discover, Plan, Build, Launch, Grow — with clear communication and your feedback along the way.</p></article>
</div>
<p className="concept-note">Learn more <a className="text-link" href="/about-us/">about Nitiksham →</a></p>
</section><section className="service-v2"><div className="service-v2-heading reveal"><span className="eyebrow pink">02 — WHAT WE DO</span><h2>Big-picture thinking.<br /><span className="outlined">Every little detail.</span></h2></div><a className="service-v2-row reveal" href="/expertise/"><h3>Brand & graphic design</h3><p>Logo design, brand identity, brochures and exhibition graphics.</p><span className="service-number">01</span><span className="service-direction"><svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></span></a><a className="service-v2-row reveal" href="/expertise-web-development/"><h3>Website development</h3><p>Responsive websites, landing pages and considered enquiry journeys.</p><span className="service-number">02</span><span className="service-direction"><svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></span></a><a className="service-v2-row reveal" href="/expertise-seo/"><h3>Search engine optimisation</h3><p>Search strategy, useful content and on-page optimisation.</p><span className="service-number">03</span><span className="service-direction"><svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></span></a><a className="service-v2-row reveal" href="/digital-marketing/"><h3>Digital marketing</h3><p>Campaign planning, audience strategy and advertising creative.</p><span className="service-number">04</span><span className="service-direction"><svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></span></a><a className="service-v2-row reveal" href="/copyright/"><h3>Copywriting & content</h3><p>Brand messaging, website copy and ideas with a clear voice.</p><span className="service-number">05</span><span className="service-direction"><svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></span></a><a className="service-v2-row reveal" href="/print/"><h3>Print & outdoor</h3><p>Brochures, corporate stationery, signage and larger-than-life displays.</p><span className="service-number">06</span><span className="service-direction"><svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></span></a><a className="service-v2-row reveal" href="/social-media-marketing/"><h3>Social media management</h3><p>Content planning, social graphics and a recognisable brand presence.</p><span className="service-number">07</span><span className="service-direction"><svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></span></a></section><section className="approach-v2" id="approach"><div className="reveal approach-title"><span className="eyebrow pink">03 — HOW WE WORK</span><h2>Your ambition.<br />Our shared <span className="cyan">direction.</span></h2><p>We bring the right disciplines together around your business.</p></div><div className="number-bubbles"><div className="bubble reveal"><b data-count="7">7</b><span>Connected services</span></div><div className="bubble reveal"><b data-count="5">5</b><span>Stages from idea to growth</span></div><div className="bubble reveal"><b data-count="1">1</b><span>Shared direction</span></div></div><div className="process-tabs"><details className="reveal" open><summary><span>01</span>Discover<b>+</b></summary><p>We listen first: your business, your audience, your ambitions and what is getting in the way.</p></details><details className="reveal" ><summary><span>02</span>Plan<b>+</b></summary><p>We shape the strategy, scope and creative direction, with a clear route for the project.</p></details><details className="reveal" ><summary><span>03</span>Build<b>+</b></summary><p>We bring the ideas to life through design, content and development, with your feedback along the way.</p></details><details className="reveal" ><summary><span>04</span>Launch<b>+</b></summary><p>We prepare the details and put your new brand, website or campaign into the world.</p></details><details className="reveal" ><summary><span>05</span>Grow<b>+</b></summary><p>We review the response, learn from the results and plan the next step.</p></details></div></section><section className="new-cta"><span className="eyebrow">YOUR NEXT CHAPTER</span><h2>Good ideas deserve<br /><em>a clear direction.</em></h2><p>Let’s turn your business challenge into work that moves you forward.</p><a className="button" href="/contact-us/">Start a conversation ↗</a></section>
    </Layout>
  );
}
