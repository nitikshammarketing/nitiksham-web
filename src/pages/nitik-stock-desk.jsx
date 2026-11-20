import Layout from '../components/Layout.jsx';
import { SOFTWARE_PRODUCTS } from '../site.config.js';

// Intro page for Nitik Stock-Desk, our inventory/stock SaaS.
// Kept short and structured — Vijay will design this page properly later.
// Pricing is deliberately "coming soon": no prices or plans are invented here.
const product = SOFTWARE_PRODUCTS.find((p) => p.slug === 'nitik-stock-desk');

export const meta = {
  title: 'Nitik Stock-Desk — Inventory & Stock Software | Nitiksham',
  description:
    'Nitik Stock-Desk is a multi-client inventory and stock workspace from Nitiksham — packet-wise stock with barcode labels, built for teams working together.',
  canonical: 'https://nitiksham.com/nitik-stock-desk/',
  ogType: 'website',
  twitterCard: 'summary_large_image',
  jsonLd: {
    '@context': 'https://schema.org',
    '@graph': [
      {
        '@type': 'SoftwareApplication',
        name: 'Nitik Stock-Desk',
        applicationCategory: 'BusinessApplication',
        operatingSystem: 'Web',
        description:
          'A multi-client inventory and stock workspace — packet-wise stock with barcode labels, built for teams working together.',
        url: 'https://nitiksham.com/nitik-stock-desk/',
        provider: {
          '@type': 'Organization',
          name: 'Nitiksham',
          url: 'https://nitiksham.com/',
          email: 'contact@nitiksham.com',
        },
      },
      {
        '@type': 'BreadcrumbList',
        itemListElement: [
          { '@type': 'ListItem', position: 1, name: 'Home', item: 'https://nitiksham.com/' },
          { '@type': 'ListItem', position: 2, name: 'Software', item: 'https://nitiksham.com/software/' },
          {
            '@type': 'ListItem',
            position: 3,
            name: 'Nitik Stock-Desk',
            item: 'https://nitiksham.com/nitik-stock-desk/',
          },
        ],
      },
    ],
  },
  bodyClass: 'inner-page',
  path: '/nitik-stock-desk/',
};

export default function NitikStockDeskPage() {
  return (
    <Layout meta={meta}>
      <section className="page-head reveal" id="top">
        <span className="eyebrow">SOFTWARE PRODUCT / INVENTORY &amp; STOCK</span>
        <h1>
          Nitik <em>Stock-Desk</em>
        </h1>
        <p>{product.tagline} Built for businesses that hold stock — every client gets its own separate workspace.</p>
        <a className="button" href={product.demoUrl} target="_blank" rel="noopener noreferrer">
          Try the live demo ↗
        </a>
        <a className="hero-secondary" href={product.loginUrl} target="_blank" rel="noopener noreferrer">
          Existing client login →
        </a>
        <p>{product.demoNote}</p>
      </section>

      <section className="section">
        <div className="section-heading">
          <h2>What it does</h2>
          <p>Stock you can see, packet by packet — from goods-in to the shelf to the customer.</p>
        </div>
        <div className="values">
          {product.features.map((f, i) => (
            <article className="reveal" key={f.title}>
              <span>{String(i + 1).padStart(2, '0')}</span>
              <h3>{f.title}</h3>
              <p>{f.text}</p>
            </article>
          ))}
        </div>
      </section>

      <section className="section">
        <div className="section-heading">
          <h2>Pricing</h2>
          <p>
            Pricing is coming soon. Every business holds stock differently, so pricing is
            worked out per company — talk to us and we will put together a plan.
          </p>
        </div>
      </section>

      <section className="new-cta">
        <span className="eyebrow">SEE IT IN ACTION</span>
        <h2>
          Want a <em>demo?</em>
        </h2>
        <p>Tell us how your stock works today, and we will walk you through the workspace.</p>
        <a className="button" href="/contact-us/?interest=Nitik%20Stock-Desk%20demo">
          Ask for a demo ↗
        </a>
      </section>
    </Layout>
  );
}
