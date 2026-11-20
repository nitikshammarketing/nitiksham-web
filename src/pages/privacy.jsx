import Layout from '../components/Layout.jsx';

export const meta = {
  "title": "Privacy Policy | Nitiksham",
  "description": "How Nitiksham handles enquiry and account information — what we store, how we use it, and how to ask for removal. Contact: contact@nitiksham.com.",
  "canonical": "https://nitiksham.com/privacy/",
  "ogType": "website",
  "ogImage": "",
  "twitterCard": "summary_large_image",
  "jsonLd": {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "WebPage",
        "@id": "https://nitiksham.com/privacy/#webpage",
        "url": "https://nitiksham.com/privacy/",
        "name": "Privacy Policy"
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
            "name": "Privacy Policy",
            "item": "https://nitiksham.com/privacy/"
          }
        ]
      }
    ]
  },
  "bodyClass": "inner-page",
  "path": "/privacy/",
  "extraScripts": []
};

export default function PrivacyPage() {
  return (
    <Layout meta={meta}>
      <section className="page-head reveal" id="top"><span className="eyebrow">PRIVACY & ENQUIRIES</span><h1>Clear communication.<br /><em>From the start.</em></h1><p>When you send an enquiry, your name, email address and message are stored in the website database and emailed to the studio so we can reply. We use your details only to answer your enquiry.</p><p>If you choose the email option instead, your email application opens with a draft; review it before sending. Your email provider processes messages under its own privacy terms. For questions about information you send Nitiksham, or to ask for its removal, contact <a href="mailto:contact@nitiksham.com">contact@nitiksham.com</a>.</p></section><section className="section"><h2>Studio account, uploads and software links</h2><p>The private Studio uses your signed-in account identity to restrict management access to the website owner. Uploaded designs, newsletter files, titles, descriptions and software links are stored on the server so they remain available across devices.</p><p>Unpublished uploads and software access links stay within the owner workspace. Publishing a gallery item makes its file available to visitors who can access this website. Publishing a software listing displays its name and description; live and demo access links remain private.</p><p>Archiving removes an item from display but retains its stored file. For permanent removal requests, contact contact@nitiksham.com. External software and demo applications apply their own authentication and data policies.</p></section>
    </Layout>
  );
}
