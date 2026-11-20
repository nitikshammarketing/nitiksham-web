import Layout from '../components/Layout.jsx';

// 404 page, served by .htaccess ErrorDocument. Noindex.
export const meta = {
  path: '/404.html',
  title: 'Page not found | Nitiksham',
  description: 'This page is not here. Find your way back to the Nitiksham home page.',
  bodyClass: 'inner-page',
  noindex: true,
};

export default function NotFoundPage() {
  return (
    <Layout meta={meta}>
      <section className="page-head" id="top">
        <span className="eyebrow">NOTHING HERE</span>
        <h1>
          This page is not here. <em>Let’s find your way back.</em>
        </h1>
        <p>
          The link may have moved, or the address may be mistyped. The home
          page is a good place to start again.
        </p>
        <a className="button" href="/">
          Back to the home page ↗
        </a>
      </section>
    </Layout>
  );
}
