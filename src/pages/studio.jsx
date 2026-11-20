import Layout from '../components/Layout.jsx';

// Studio dashboard shell: pre-rendered page; the React SPA bundle
// (src/studio/, built to /studio/app.js) mounts into #studio-root and
// handles the session gate, tabs and all studio behaviour. Noindex.
export const meta = {
  path: '/studio/',
  title: 'Studio dashboard | Nitiksham',
  description:
    'The Nitiksham studio dashboard — manage the gallery, newsletters, software and enquiries.',
  bodyClass: 'inner-page',
  extraScripts: ['/studio/app.js'],
  noindex: true,
};

export default function StudioPage() {
  return (
    <Layout meta={meta}>
      <section className="dashboard section" id="top">
        <div id="studio-root">
          <p id="account-status">Verifying access…</p>
        </div>
      </section>
    </Layout>
  );
}
