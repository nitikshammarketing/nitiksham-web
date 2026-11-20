import Layout from '../components/Layout.jsx';
import { SOFTWARE_PRODUCTS } from '../site.config.js';

export const meta = {
  "title": "Custom Software Development Services | Nitiksham",
  "description": "Custom software development services from Nitiksham, India — websites, web applications and business tools built around the way your team works.",
  "canonical": "https://nitiksham.com/software/",
  "ogType": "website",
  "ogImage": "https://nitiksham.com/assets/img/software-hero.webp",
  "twitterCard": "summary_large_image",
  "jsonLd": {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "Service",
        "name": "Custom Software Development Services",
        "description": "Custom software development services from Nitiksham, India — websites, web applications and business tools built around the way your team works.",
        "url": "https://nitiksham.com/software/",
        "areaServed": [
          "India",
          "Worldwide"
        ],
        "provider": {
          "@type": "Organization",
          "name": "Nitiksham",
          "url": "https://nitiksham.com/",
          "email": "contact@nitiksham.com",
          "address": {
            "@type": "PostalAddress",
            "addressLocality": "Karnal",
            "addressRegion": "Haryana",
            "postalCode": "132001",
            "addressCountry": "IN"
          }
        }
      },
      {
        "@type": "FAQPage",
        "mainEntity": [
          {
            "@type": "Question",
            "name": "What kind of software do you build?",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "Websites, web applications and business tools — dashboards, inventory and operations tools, and custom workflows built around how your team works."
            }
          },
          {
            "@type": "Question",
            "name": "Do you build software for small businesses?",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "Yes. Many of the tools we build are for small and growing businesses — a single workflow made simpler, or one workspace that replaces several spreadsheets. A project does not need to be large to be worth doing well."
            }
          },
          {
            "@type": "Question",
            "name": "How does a software project start?",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "With a conversation about the work you want to improve."
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
            "name": "Services",
            "item": "https://nitiksham.com/services/"
          },
          {
            "@type": "ListItem",
            "position": 3,
            "name": "Software",
            "item": "https://nitiksham.com/software/"
          }
        ]
      }
    ]
  },
  "bodyClass": "inner-page",
  "path": "/software/",
  "extraScripts": [
    "software-public.js"
  ]
};

export default function SoftwarePage() {
  return (
    <Layout meta={meta}>
      <section className="page-head reveal" id="top"><span className="eyebrow">PRACTICAL TOOLS. PURPOSEFUL DESIGN.</span><h1>Custom software <em>built for the way you work.</em></h1><p>Business software created around everyday operational challenges.</p></section><section className="section"><div className="section-heading"><h2>Software that fits the business, not the other way round</h2></div><div className="two-copy"><p>Every business has its own way of working — its own customers, its own stock, its own daily routines.</p><p>We work with businesses across real estate, e-commerce, healthcare, education, B2B &amp; industrial, and FMCG &amp; retail.</p></div></section><section className="section"><div className="section-heading"><h2>What we build</h2><p>Practical tools, purposeful design — software your team will actually use.</p></div><div className="values"><article className="reveal"><span>01</span><h3>Websites &amp; web applications</h3><p>Fast, clear websites and web apps your customers can use with ease — designed and built in-house, from the first sketch to launch.</p></article><article className="reveal"><span>02</span><h3>Business tools &amp; dashboards</h3><p>One place to see the numbers that matter — orders, stock, enquiries or bookings — instead of hunting through spreadsheets.</p></article><article className="reveal"><span>03</span><h3>Inventory &amp; operations tools</h3><p>Tools that keep stock, orders and everyday operations in order — like Stockdesk, our inventory workspace for plumbing businesses, below.</p></article><article className="reveal"><span>04</span><h3>Custom workflows</h3><p>The small, repeated jobs that eat your day — quotes, follow-ups, records — turned into simple steps inside a tool built for them.</p></article></div></section><section className="section"><div className="section-heading"><h2>Our software products</h2><p>Software products from Nitiksham — built, hosted and supported by us.</p></div><div className="values">{SOFTWARE_PRODUCTS.map((p, i) => (<article className="reveal" key={p.slug}><span>{String(i + 1).padStart(2, '0')}</span><h3>{p.name}</h3><p>{p.description}</p><a href={p.path}>See the product →</a></article>))}</div></section><section className="section"><div className="software-feature"><div className="software-art"><span>STOCKDESK</span><h2>Everything<br />in its place.</h2><div className="inventory-lines"><i></i><i></i><i></i></div><small>PLUMBING INVENTORY / SOFTWARE</small></div><div><span className="eyebrow">01 / INVENTORY MANAGEMENT</span><h2>Stockdesk</h2><p>A dedicated workspace for plumbing inventory and stock workflows.</p><p>See how it fits your operation in a guided walkthrough.</p><a className="button" href="/contact-us/?interest=Stockdesk%20demo">Request a walkthrough ↗</a><a className="hero-secondary" href="/login/">Owner access →</a></div></div><div id="software-public" className="library-grid"></div></section><section className="section"><div className="section-heading"><h2>How a project moves</h2><p>From first conversation to launch — and beyond. Every project follows the same five steps.</p></div><div className="values"><article className="reveal"><span>01</span><h3>Discover</h3><p>We learn how your business works today — the people, the process and the problem worth solving.</p></article><article className="reveal"><span>02</span><h3>Plan</h3><p>We agree what the tool will do, what it will not do, and what success looks like — before anything is built.</p></article><article className="reveal"><span>03</span><h3>Build</h3><p>Design and development happen together, so the tool is clear to use as well as sound underneath.</p></article><article className="reveal"><span>04</span><h3>Launch</h3><p>We walk your team through the finished tool and stay close while it settles into daily work.</p></article><article className="reveal"><span>05</span><h3>Grow</h3><p>Your business changes, and the tool can change with it — improved in small steps, as you need them.</p></article></div></section><section className="section"><div className="section-heading"><h2>Custom software, answered</h2><p>The questions businesses ask us most before starting a software project.</p></div><div className="values"><article className="reveal"><span>01</span><h3>What kind of software do you build?</h3><p>Websites, web applications and business tools — dashboards, inventory and operations tools, and custom workflows built around how your team works.</p></article><article className="reveal"><span>02</span><h3>Do you build software for small businesses?</h3><p>Yes. Many of the tools we build are for small and growing businesses — a single workflow made simpler, or one workspace that replaces several spreadsheets. A project does not need to be large to be worth doing well.</p></article><article className="reveal"><span>03</span><h3>How does a software project start?</h3><p>With a conversation about the work you want to improve.</p></article></div></section><section className="new-cta"><span className="eyebrow">MAKE WORK WORK BETTER</span><h2>Your process.<br /><em>A better possibility.</em></h2><p>Have a workflow that needs a smarter tool?</p><a className="button" href="/contact-us/">Start a conversation ↗</a></section>
    </Layout>
  );
}
