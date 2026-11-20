import Layout from '../components/Layout.jsx';

// Studio login: pre-rendered page with a real email + password form.
// Submits to /api/login.php (see public-assets/js/login.js). Noindex.
export const meta = {
  path: '/login/',
  title: 'Studio login | Nitiksham',
  description:
    'Sign in to the Nitiksham studio to manage the gallery, newsletters and software.',
  bodyClass: 'inner-page',
  extraScripts: ['login.js'],
  noindex: true,
};

export default function LoginPage() {
  return (
    <Layout meta={meta}>
      <section className="login-layout" id="top">
        <div>
          <span className="eyebrow">NITIKSHAM / STUDIO ACCESS</span>
          <h1>
            Your work.
            <br />
            Your tools.
            <br />
            <em>One place.</em>
          </h1>
          <p>
            Manage designs and newsletters. Open your software. Prepare your
            next client presentation.
          </p>
        </div>
        <div className="login-card">
          <span className="eyebrow">PRIVATE OWNER WORKSPACE</span>
          <h2>Welcome back.</h2>
          <p>
            Sign in with your studio administrator account to manage this studio.
          </p>
          <form id="login-form">
            <label>
              Email address
              <input
                type="email"
                name="email"
                required
                autoComplete="email"
                placeholder="you@example.com"
              />
            </label>
            <label>
              Password
              <input
                type="password"
                name="password"
                required
                autoComplete="current-password"
                placeholder="Your password"
              />
            </label>
            <button className="button" type="submit">
              Sign in to your studio ↗
            </button>
            <p id="login-status" role="status"></p>
          </form>
          <p className="fine-print">
            Software may require its own login. Studio access does not change
            another application’s permissions.
          </p>
        </div>
      </section>
    </Layout>
  );
}
