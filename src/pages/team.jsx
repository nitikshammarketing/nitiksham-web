import Layout from '../components/Layout.jsx';

export const meta = {
  "title": "Meet the Team | Nitiksham Creative Collective",
  "description": "Meet the Nitiksham creative collective — brand designers, developers, marketers and writers in India, working together around your next challenge.",
  "canonical": "https://nitiksham.com/team/",
  "ogType": "website",
  "ogImage": "https://nitiksham.com/assets/img/team-hero.webp",
  "twitterCard": "summary_large_image",
  "jsonLd": {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "AboutPage",
        "name": "Meet the Team | Nitiksham Creative Collective",
        "url": "https://nitiksham.com/team/",
        "description": "Meet the Nitiksham creative collective — brand designers, developers, marketers and writers in India, working together around your next challenge."
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
            "name": "The Collective",
            "item": "https://nitiksham.com/team/"
          }
        ]
      }
    ]
  },
  "bodyClass": "inner-page",
  "path": "/team/",
  "extraScripts": []
};

export default function TeamPage() {
  return (
    <Layout meta={meta}>
      <section className="page-head reveal" id="top"><span className="eyebrow">THE NITIKSHAM COLLECTIVE</span><h1>Meet the team. <em>Many perspectives. One creative pulse.</em></h1><p>Designers, strategists, developers and communicators. Different ways of thinking, brought together around your next challenge.</p></section><section className="section"><div className="section-heading"><h2>Meet the minds<br />behind the disciplines.</h2><p>A good idea gets stronger when the right skills work together.</p></div><div className="collective-grid"><article className="reveal"><span>01 / THE COLLECTIVE</span><h3>The strategists</h3><p>Find the question worth answering. Turn business goals into a considered plan — then keep the work honest to it.</p><a href="/digital-marketing/">Explore the discipline <svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></a></article><article className="reveal"><span>02 / THE COLLECTIVE</span><h3>The designers</h3><p>Give ideas a visual language. Make every brand touchpoint — from a logo to a hoarding — feel connected.</p><a href="/expertise/">Explore the discipline <svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></a></article><article className="reveal"><span>03 / THE COLLECTIVE</span><h3>The developers</h3><p>Bring the experience to life. Build websites and tools around the people who use them, not around the technology.</p><a href="/expertise-web-development/">Explore the discipline <svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></a></article><article className="reveal"><span>04 / THE COLLECTIVE</span><h3>The storytellers</h3><p>Find the words and conversations that make a brand feel like itself — on the page, on screen and on social.</p><a href="/copyright/">Explore the discipline <svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></a></article></div></section><section className="section"><div className="section-heading"><h2>How the collective works</h2></div><div className="two-copy"><p>Nitiksham is a collective, not a department store.</p><p>That is why the work feels joined up: the person writing your website copy sits beside the person designing it, and the person planning your campaign knows what your brand looks like on a hoarding.</p></div></section><section className="section"><div className="section-heading"><h2>What we hold to</h2><p>Four disciplines, shared standards — the things every piece of Nitiksham work is measured against.</p></div><div className="values"><article className="reveal"><span>01</span><h3>Strategy before decoration</h3><p>An idea has to be right before it can be beautiful. We ask the awkward questions first — then design the answer.</p></article><article className="reveal"><span>02</span><h3>Craft in the details</h3><p>The kerning, the comma, the loading time: small things are what people notice, even when they cannot name them.</p></article><article className="reveal"><span>03</span><h3>Honest communication</h3><p>Clear about scope, clear about timelines, clear when something is not working. Good work needs straight talking.</p></article></div></section><section className="new-cta"><span className="eyebrow">BETTER TOGETHER</span><h2>Your next idea.<br /><em>Our kind of challenge.</em></h2><p>Meet the people who can help bring it to life.</p><a className="button" href="/contact-us/">Start a conversation ↗</a></section>
    </Layout>
  );
}
