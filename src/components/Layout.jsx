import Header from './Header.jsx';
import Footer from './Footer.jsx';
import Seo from './Seo.jsx';
import { GA_MEASUREMENT_ID } from '../site.config.js';

// Full HTML document shell: shared header (logo, mega-menu, reading progress),
// <main>, shared footer, and the theme JS. Per-page extra scripts (library.js,
// software-public.js, login.js, studio app) load deferred after the theme JS.
const THEME_SCRIPTS = [
  '/assets/js/app.js',
  '/assets/js/motion.js',
  '/assets/js/cinematic.js',
];

export default function Layout({ meta, children }) {
  const extra = (meta.extraScripts || []).map((s) =>
    s.startsWith('/') ? s : `/assets/js/${s}`,
  );
  return (
    <html lang="en">
      <head>
        <meta charSet="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <Seo meta={meta} />
        {/* Google Analytics 4 */}
        <script async src={`https://www.googletagmanager.com/gtag/js?id=${GA_MEASUREMENT_ID}`} />
        <script
          dangerouslySetInnerHTML={{
            __html: `window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','${GA_MEASUREMENT_ID}');`,
          }}
        />
      </head>
      <body className={meta.bodyClass || 'inner-page'}>
        <Header />
        <main id="main">{children}</main>
        <Footer />
        <script
          dangerouslySetInnerHTML={{
            __html: 'window.NITIKSHAM={apiBase:"/api",fileEndpoint:"/api/file.php"};',
          }}
        />
        {THEME_SCRIPTS.map((src) => (
          <script key={src} src={src}></script>
        ))}
        {extra.map((src) => (
          <script key={src} src={src} defer></script>
        ))}
      </body>
    </html>
  );
}
