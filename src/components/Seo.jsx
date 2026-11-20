import { SITE_URL } from '../site.config.js';

// <head> SEO block. Public pages get the full set carried over from the
// WordPress theme (title, description, canonical, OG, Twitter, JSON-LD).
// Private pages (login, studio, 404) get noindex and no canonical/OG/JSON-LD.
export default function Seo({ meta }) {
  const noindex = !!meta.noindex;
  // Pages without their own social image (privacy) fall back to the brand
  // editorial image so every public page still has og:image / twitter:image.
  const ogImage = meta.ogImage || `${SITE_URL}/assets/brand-editorial.webp`;
  const jsonLd = meta.jsonLd
    ? JSON.stringify(meta.jsonLd).replace(/</g, '\\u003c')
    : null;
  return (
    <>
      <title>{meta.title}</title>
      <meta name="description" content={meta.description} />
      {noindex ? (
        <meta name="robots" content="noindex,nofollow" />
      ) : (
        <>
          <link rel="canonical" href={meta.canonical} />
          <meta property="og:type" content={meta.ogType || 'website'} />
          <meta property="og:site_name" content="Nitiksham" />
          <meta property="og:title" content={meta.title} />
          <meta property="og:description" content={meta.description} />
          <meta property="og:url" content={meta.canonical} />
          <meta property="og:image" content={ogImage} />
          <meta name="twitter:card" content={meta.twitterCard || 'summary_large_image'} />
          <meta name="twitter:title" content={meta.title} />
          <meta name="twitter:description" content={meta.description} />
          <meta name="twitter:image" content={ogImage} />
          {jsonLd ? (
            <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: jsonLd }} />
          ) : null}
        </>
      )}
      <meta name="theme-color" content="#f6f4ee" />
      <link rel="icon" type="image/svg+xml" href="/assets/favicon.svg" />
      <link rel="icon" type="image/png" sizes="512x512" href="/assets/nitiksham-favicon.png" />
      <link rel="apple-touch-icon" sizes="180x180" href="/assets/nitiksham-touch.png" />
      <link rel="stylesheet" href="/assets/css/style.css" />
      <link rel="stylesheet" href="/assets/css/motion.css" />
      <link rel="stylesheet" href="/assets/css/cinematic.css" />
      <link rel="stylesheet" href="/assets/css/refined.css" />
    </>
  );
}

export { SITE_URL };
