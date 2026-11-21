import Layout from '../components/Layout.jsx';
import { SOFTWARE_PRODUCTS } from '../site.config.js';

// Hub page for Nitik Softwares — the client login doorway.
// Kept short: one card per product, each with a login button that opens
// the live app. Login URLs come from site.config.js (SOFTWARE_PRODUCTS),
// so swapping hosting later updates this page, the footer and the
// intro pages in one place. No prices here — pricing is "coming soon"
// on the product intro pages.
export const meta = {
  title: 'Nitik Softwares | Client Login — HR & Stock Software',
  description:
    'Open your Nitiksham software — log in to Nitik HR Management or Nitik Stock-Desk, the workspaces your company uses every day.',
  canonical: 'https://nitiksham.com/nitik-softwares/',
  ogType: 'website',
  ogImage: 'https://nitiksham.com/assets/img/software-hero.webp',
  twitterCard: 'summary_large_image',
  jsonLd: {
    '@context': 'https://schema.org',
    '@graph': [
      {
        '@type': 'WebPage',
        name: 'Nitik Softwares | Client Login — HR & Stock Software',
        description:
          'Open your Nitiksham software — log in to Nitik HR Management or Nitik Stock-Desk, the workspaces your company uses every day.',
        url: 'https://nitiksham.com/nitik-softwares/',
      },
      {
        '@type': 'BreadcrumbList',
        itemListElement: [
          { '@type': 'ListItem', position: 1, name: 'Home', item: 'https://nitiksham.com/' },
          {
            '@type': 'ListItem',
            position: 2,
            name: 'Nitik Softwares',
            item: 'https://nitiksham.com/nitik-softwares/',
          },
        ],
      },
    ],
  },
  bodyClass: 'inner-page',
  path: '/nitik-softwares/',
};

export default function NitikSoftwaresPage() {
  return (
    <Layout meta={meta}>
      <section className="page-head reveal" id="top">
        <span className="eyebrow">NITIKSHAM / SOFTWARE</span>
        <h1>
          Nitik <em>Softwares</em>
        </h1>
        <p>Your company&apos;s workspace, one click away — log in to the software you use every day.</p>
      </section>

      <section className="section">
        <div className="section-heading">
          <h2>Open your workspace</h2>
          <p>Two workspaces, built and supported by Nitiksham. Pick yours and sign in.</p>
        </div>
        <div className="values">
          {SOFTWARE_PRODUCTS.map((p, i) => (
            <article className="reveal" key={p.slug}>
              <span>{String(i + 1).padStart(2, '0')}</span>
              <h3>{p.name}</h3>
              <p>{p.tagline}</p>
              <p>{p.description}</p>
              <a className="button" href={p.loginUrl} target="_blank" rel="noopener noreferrer">
                Client login ↗
              </a>
              <a className="hero-secondary" href={p.path}>
                See the product →
              </a>
            </article>
          ))}
        </div>
      </section>

      <section className="new-cta">
        <span className="eyebrow">NEED ACCESS FOR YOUR COMPANY?</span>
        <h2>
          Not set up <em>yet?</em>
        </h2>
        <p>Talk to us and we will set up your company&apos;s workspace.</p>
        <a className="button" href="/contact-us/">
          Start a conversation ↗
        </a>
      </section>
    </Layout>
  );
}
