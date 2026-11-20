import Layout from '../components/Layout.jsx';

export const meta = {
  "title": "Contact Nitiksham | Digital Marketing Agency",
  "description": "Contact Nitiksham in India — email us or send an enquiry about branding, websites, SEO, digital marketing or campaigns, in India and worldwide.",
  "canonical": "https://nitiksham.com/contact-us/",
  "ogType": "website",
  "ogImage": "https://nitiksham.com/assets/img/contact-us-hero.webp",
  "twitterCard": "summary_large_image",
  "jsonLd": {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "ContactPage",
        "@id": "https://nitiksham.com/contact-us/#webpage",
        "url": "https://nitiksham.com/contact-us/",
        "name": "Contact Nitiksham",
        "about": {
          "@id": "https://nitiksham.com/#organization"
        }
      },
      {
        "@type": "LocalBusiness",
        "@id": "https://nitiksham.com/#localbusiness",
        "name": "Nitiksham",
        "url": "https://nitiksham.com/",
        "email": "contact@nitiksham.com",
        "address": {
          "@type": "PostalAddress",
          "addressLocality": "Karnal",
          "addressRegion": "Haryana",
          "postalCode": "132001",
          "addressCountry": "IN"
        },
        "areaServed": [
          "India",
          "Worldwide"
        ]
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
            "name": "Contact Us",
            "item": "https://nitiksham.com/contact-us/"
          }
        ]
      }
    ]
  },
  "bodyClass": "inner-page",
  "path": "/contact-us/",
  "extraScripts": []
};

export default function ContactUsPage() {
  return (
    <Layout meta={meta}>
      <section className="contact section" id="top"><div><span className="eyebrow">LET’S START SOMETHING</span><h1>Contact Nitiksham.<br /><em>Your next big move<br />starts here.</em></h1><p>Tell us what you’re building, changing or figuring out — a new brand, a website, a campaign, or all three.</p><div className="contact-lines"><a href="mailto:contact@nitiksham.com">contact@nitiksham.com <svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></a><p>Karnal, Haryana – 132001, India</p></div><img src="/assets/img/contact-us-hero.webp" alt="Contact Nitiksham — digital marketing agency in India" width="1200" height="800" decoding="async" className="detail-img" /></div><form id="enquiry"><label hidden aria-hidden="true">Leave this field empty<input name="website" type="text" tabIndex="-1" autoComplete="off" /></label><h2>A little about your project.</h2><div className="form-grid"><label>Your name *<input name="name" required autoComplete="name" placeholder="Full name" /></label><label>Email address *<input type="email" name="email" required autoComplete="email" placeholder="you@company.com" /></label></div><label>Company<input name="company" autoComplete="organization" placeholder="Your business or brand" /></label><label>Phone number<input name="phone" type="tel" autoComplete="tel" placeholder="Your contact number" /></label><label>What can we help with?<select name="service"><option value="">Select a service</option><option value="expertise">Brand & graphic design</option><option value="expertise-web-development">Website design & development</option><option value="expertise-seo">Search engine optimisation</option><option value="digital-marketing">Digital marketing</option><option value="copyright">Copywriting & content</option><option value="print">Print & outdoor</option><option value="social-media-marketing">Social media management</option><option value="integrated">Integrated marketing</option></select></label><label>Tell us about your project *<textarea name="message" required rows="4" placeholder="Your goals, requirements and ideal timeline…"></textarea></label><p className="caption">Your details are used only to answer your enquiry. Send it below, or prepare an email instead.</p><button className="button" type="submit">Send enquiry <span><svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></span></button><button className="email-send text-link" type="button">Or prepare an email <svg className="icon-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14" /></svg></button><p id="form-status" role="status"></p></form></section><section className="section reveal">
<div className="section-heading">
<span className="eyebrow">01 — WHAT HAPPENS NEXT</span>
<h2>After you send<br />your enquiry.</h2>
<p>Sending an enquiry takes a minute. Here is what happens once you do — and what to include so the first conversation counts.</p>
</div>
<div className="two-copy">
<p>The fastest way to reach us is the enquiry form: choose the service you need, tell us about your project, and send it straight to the studio — or prepare an email instead.</p>
<p>It helps to include your goals, your timeline, and a link to your current website or brand if you have one — the more we know, the better the first conversation.</p>
</div>
<div className="values">
<article><span>01</span><h3>You send it straight to us.</h3><p>Submit the form and it reaches the studio directly — or choose email instead.</p></article>
<article><span>02</span><h3>We read every enquiry.</h3><p>A strategist reads your message and thinks about what your project needs — the right service, the right questions, and the right next step.</p></article>
<article><span>03</span><h3>We start a conversation.</h3><p>We reply to arrange a conversation about your goals, your timeline and how we can help.</p></article>
</div>
<p>Prefer to browse first? Explore our <a className="text-link" href="/services/">services →</a> or read <a className="text-link" href="/about-us/">about Nitiksham →</a></p>
</section>
    </Layout>
  );
}
