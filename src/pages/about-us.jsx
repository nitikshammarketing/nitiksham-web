import Layout from '../components/Layout.jsx';

export const meta = {
  "title": "About Nitiksham | Brand & Digital Agency",
  "description": "Meet Nitiksham, a strategy-led brand and digital agency in India — who we are, how we work, and the values behind our branding and marketing work.",
  "canonical": "https://nitiksham.com/about-us/",
  "ogType": "website",
  "ogImage": "https://nitiksham.com/assets/img/about-us-hero.webp",
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
        "@type": "FAQPage",
        "mainEntity": [
          {
            "@type": "Question",
            "name": "What does Nitiksham do?",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "Nitiksham is a strategy-led brand, design and digital marketing agency in India."
            }
          },
          {
            "@type": "Question",
            "name": "What does the name Nitiksham mean?",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "Nitiksham combines Niti, meaning strategy, and Ksham, meaning capability."
            }
          },
          {
            "@type": "Question",
            "name": "How does a project with Nitiksham work?",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "Every project follows five stages: Discover, Plan, Build, Launch and Grow."
            }
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
            "name": "About Us",
            "item": "https://nitiksham.com/about-us/"
          }
        ]
      }
    ]
  },
  "bodyClass": "inner-page",
  "path": "/about-us/",
  "extraScripts": []
};

export default function AboutUsPage() {
  return (
    <Layout meta={meta}>
      <section className="page-head reveal" id="top"><span className="eyebrow">MEET NITIKSHAM</span><h1>A brand &amp; digital agency<br />in India.<br /><em>Strategy at heart.<br />Creativity in motion.</em></h1><p>Nitiksham is a multidisciplinary marketing agency connecting brands with the people who matter to them — across India and worldwide.</p></section><section className="about-strip reveal"><div><span className="eyebrow">01 — THE NAME</span><h2>Niti. Strategy.<br />Ksham. Capability.</h2><p>Our name brings together strategic thinking and the ability to execute.</p></div><img src="/assets/img/about-us-hero.webp" alt="The Nitiksham studio in India — a strategy-led brand and digital agency" width="1200" height="800" decoding="async" /></section><section className="section reveal">
<div className="section-heading">
<span className="eyebrow">02 — WHO WE ARE</span>
<h2>One team.<br />Many disciplines.</h2>
<p>Graphic designers, brand thinkers, web developers, writers and marketers — working from our studio, with businesses across India and around the world.</p>
</div>
<div className="two-copy">
<p>Nitiksham brings the disciplines of a full marketing agency together under one roof.</p>
<p>What we do spans seven connected services: brand and graphic design, website development, search engine optimisation, digital marketing, copywriting and content, print and outdoor advertising, and social media management.</p>
</div>
<p>See everything we offer on the <a className="text-link" href="/services/">services page →</a></p>
</section><section className="section"><div className="section-heading"><h2>Different skills.<br />Shared standards.</h2><p>Graphic designers, brand thinkers, web developers and marketers working towards a connected brand experience.</p></div><div className="values"><article><span>01</span><h3>Stay curious.</h3><p>Listen carefully. Ask better questions. Understand the business, the audience and the market before proposing the solution.</p></article><article><span>02</span><h3>Be honest.</h3><p>Keep communication clear, expectations realistic and decisions grounded in the needs of the project — not in jargon.</p></article><article><span>03</span><h3>Create together.</h3><p>Combine perspectives inside the studio and work closely with you throughout the process, so nothing is a surprise.</p></article><article><span>04</span><h3>Think beyond launch.</h3><p>Create a foundation your brand can build on, across channels and over time — work that keeps working after launch day.</p></article></div></section><section className="section reveal">
<div className="section-heading">
<span className="eyebrow">04 — HOW WE WORK</span>
<h2>Five stages.<br />One shared direction.</h2>
<p>Every project follows the same clear process, shaped around your goals and your timeline.</p>
</div>
<div className="values">
<article><span>01</span><h3>Discover</h3><p>We listen first: your business, your audience, your ambitions and what is getting in the way.</p></article>
<article><span>02</span><h3>Plan</h3><p>We shape the strategy, scope and creative direction, with a clear route for the project.</p></article>
<article><span>03</span><h3>Build</h3><p>We bring the ideas to life through design, content and development, with your feedback along the way.</p></article>
<article><span>04</span><h3>Launch</h3><p>We prepare the details and put your new brand, website or campaign into the world.</p></article>
<article><span>05</span><h3>Grow</h3><p>We review the response, learn from the results and plan the next step.</p></article>
</div>
</section><section className="section reveal" id="faq">
<div className="section-heading">
<span className="eyebrow">05 — IN PLAIN WORDS</span>
<h2>About Nitiksham,<br />in plain words.</h2>
</div>
<div className="values">
<article><span>01</span><h3>What does Nitiksham do?</h3><p>Nitiksham is a strategy-led brand, design and digital marketing agency in India.</p></article><article><span>02</span><h3>What does the name Nitiksham mean?</h3><p>Nitiksham combines Niti, meaning strategy, and Ksham, meaning capability.</p></article><article><span>03</span><h3>How does a project with Nitiksham work?</h3><p>Every project follows five stages: Discover, Plan, Build, Launch and Grow.</p></article>
</div>
</section><section className="new-cta"><span className="eyebrow">MEET YOUR CREATIVE PARTNER</span><h2>Different minds.<br /><em>One shared ambition.</em></h2><p>Bring your perspective. We’ll bring the questions that take it further.</p><a className="button" href="/contact-us/">Start a conversation ↗</a></section>
    </Layout>
  );
}
