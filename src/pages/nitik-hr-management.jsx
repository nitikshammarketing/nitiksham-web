import Layout from '../components/Layout.jsx';
import { SOFTWARE_PRODUCTS } from '../site.config.js';

// Intro page for Nitik HR Management, our HR/recruitment SaaS.
// Kept short and structured — Vijay will design this page properly later.
// Pricing is deliberately "coming soon": no prices or plans are invented here.
const product = SOFTWARE_PRODUCTS.find((p) => p.slug === 'nitik-hr-management');

export const meta = {
  title: 'Nitik HR Management — HR & Recruitment Software | Nitiksham',
  description:
    'Nitik HR Management is a multi-company HR and recruitment workspace from Nitiksham — candidates, jobs, interviews and documents in one place.',
  canonical: 'https://nitiksham.com/nitik-hr-management/',
  ogType: 'website',
  twitterCard: 'summary_large_image',
  jsonLd: {
    '@context': 'https://schema.org',
    '@graph': [
      {
        '@type': 'SoftwareApplication',
        name: 'Nitik HR Management',
        applicationCategory: 'BusinessApplication',
        operatingSystem: 'Web',
        description:
          'A multi-company HR and recruitment workspace — candidates, jobs, interviews and documents in one place.',
        url: 'https://nitiksham.com/nitik-hr-management/',
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
            name: 'Nitik HR Management',
            item: 'https://nitiksham.com/nitik-hr-management/',
          },
        ],
      },
    ],
  },
  bodyClass: 'inner-page',
  path: '/nitik-hr-management/',
};

export default function NitikHrManagementPage() {
  return (
    <Layout meta={meta}>
      <section className="page-head reveal" id="top">
        <span className="eyebrow">SOFTWARE PRODUCT / HR &amp; RECRUITMENT</span>
        <h1>
          Nitik HR <em>Management</em>
        </h1>
        <p>{product.tagline} Built for recruitment companies — every company gets its own separate workspace.</p>
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
          <p>Recruitment work, from first candidate to placed hire — without the spreadsheets.</p>
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
            Pricing is coming soon. Every recruitment company works differently, so pricing is
            worked out per company — talk to us and we will put together a plan.
          </p>
        </div>
      </section>

      <section className="new-cta">
        <span className="eyebrow">SEE IT IN ACTION</span>
        <h2>
          Want a <em>demo?</em>
        </h2>
        <p>Tell us how your team recruits today, and we will walk you through the workspace.</p>
        <a className="button" href="/contact-us/?interest=Nitik%20HR%20Management%20demo">
          Ask for a demo ↗
        </a>
      </section>
    </Layout>
  );
}
