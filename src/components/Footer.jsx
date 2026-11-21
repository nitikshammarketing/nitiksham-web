import { SOFTWARE_PRODUCTS } from '../site.config.js';

export default function Footer() {
  return (
    <>
      <footer className="new-footer"><div className="footer-grid"><div><a className="wordmark brand-wordmark" href="/" aria-label="Nitiksham home"><img src="/assets/nitiksham-brand.png" alt="Nitiksham — an idea driven agency" loading="lazy" decoding="async" width="1240" height="308" /></a><p>Strategy, creativity and digital.<br />Different skills. One clear direction.</p></div><div><h3>Explore</h3><a href="/about-us/">About</a><a href="/services/">Expertise</a><a href="/team/">The collective</a><a href="/gallery/">Gallery & newsletters</a>{SOFTWARE_PRODUCTS.map((p) => (<a key={p.slug} href={p.path}>{p.name}</a>))}</div><div><h3>Client login</h3>{SOFTWARE_PRODUCTS.map((p) => (<a key={p.slug} href={p.loginUrl} target="_blank" rel="noopener noreferrer">{p.name} ↗</a>))}</div><div><h3>Say hello</h3><a href="mailto:contact@nitiksham.com">contact@nitiksham.com</a><p>Karnal, Haryana – 132001, India</p></div></div><div className="foot-bottom"><span>© 2026 Nitiksham. All rights reserved.</span><a href="/privacy/">Privacy & enquiries</a><a href="#top">Back to top ↑</a></div></footer><button className="motion-toggle" aria-pressed="false">Pause motion</button>
    </>
  );
}
