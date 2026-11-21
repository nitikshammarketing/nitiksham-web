// Shared site facts for the Nitiksham rebuild.
// Only verified facts live here: no client names, stats, founding year or awards.
// The only address line allowed anywhere is ADDRESS_LINE (footer + contact +
// schema locality only). No phone number exists in this codebase.
export const SITE_URL = 'https://nitiksham.com';
export const SITE_NAME = 'Nitiksham';
export const CONTACT_EMAIL = 'contact@nitiksham.com';
export const ADDRESS_LINE = 'Karnal, Haryana – 132001, India';

// Public pages in the sitemap (the 15 WordPress routes + software hub
// + 2 software product intro pages).
export const SITEMAP_PATHS = [
  '/',
  '/about-us/',
  '/services/',
  '/expertise/',
  '/expertise-web-development/',
  '/expertise-seo/',
  '/digital-marketing/',
  '/social-media-marketing/',
  '/copyright/',
  '/print/',
  '/software/',
  '/nitik-softwares/',
  '/nitik-hr-management/',
  '/nitik-stock-desk/',
  '/team/',
  '/gallery/',
  '/contact-us/',
  '/privacy/',
];

// Software products built by Nitiksham. This list drives the Software menu
// in the header, the products section on /software/, and the footer links —
// add a product here and it appears in all three. Each product also needs
// an intro page in src/pages/<slug>.jsx and a sitemap entry above.
// appUrl/demoUrl/loginUrl are the live production apps (Stock-Desk on its
// GoDaddy domain stock.nitiksham.com; HR still on Render until
// hrm.nitiksham.com goes live); no demo credentials are published
// anywhere on this site.
export const SOFTWARE_PRODUCTS = [
  {
    name: 'Nitik HR Management',
    slug: 'nitik-hr-management',
    path: '/nitik-hr-management/',
    tagline: 'HR and recruitment, in one workspace.',
    description:
      'A multi-company HR and recruitment workspace — candidates, jobs, interviews and documents in one place.',
    appUrl: 'https://nitik-hr-management-6tpr.onrender.com',
    demoUrl: 'https://nitik-hr-management-6tpr.onrender.com',
    demoNote: 'Demo access is arranged on request — talk to us.',
    loginUrl: 'https://nitik-hr-management-6tpr.onrender.com',
    features: [
      {
        title: 'Candidate invites',
        text: 'Send a candidate an invite link and they fill in their own biodata — no manual data entry.',
      },
      {
        title: 'Skills taxonomy',
        text: 'Thousands of skills and job titles with autocomplete, so candidate records stay consistent.',
      },
      {
        title: 'Documents vault',
        text: 'CVs, ID proofs and certificates stored per candidate, with verification.',
      },
      {
        title: 'Auto-matching',
        text: 'Jobs matched to candidates on skills, department and qualification.',
      },
      {
        title: 'Multi-company',
        text: 'Every recruitment company gets its own separate workspace, with its own admins and users.',
      },
    ],
  },
  {
    name: 'Nitik Stock-Desk',
    slug: 'nitik-stock-desk',
    path: '/nitik-stock-desk/',
    tagline: 'Stock, packets and labels — always in order.',
    description:
      'A multi-client inventory and stock workspace — packet-wise stock with barcode labels, built for teams working together.',
    appUrl: 'https://stock.nitiksham.com',
    demoUrl: 'https://stock.nitiksham.com/demo/',
    demoNote: 'Open the public demo — no login needed.',
    loginUrl: 'https://stock.nitiksham.com',
    features: [
      {
        title: 'Packet-wise stock',
        text: 'Every packet tracked individually, each with its own barcode label.',
      },
      {
        title: 'Labels & reprint',
        text: 'Generate, print and reprint packet labels; cancel a label when plans change.',
      },
      {
        title: 'Multi-system use',
        text: 'Several computers work on the same stock at once, with live refresh.',
      },
      {
        title: 'Locations',
        text: 'Rack and row locations per packet, changed any time from the inventory.',
      },
      {
        title: 'Movement history',
        text: 'Every stock-in and stock-out recorded per SKU, with a full ledger.',
      },
    ],
  },
];

// llms.txt (GEO): factual agency summary for AI assistants, carried over
// verbatim from the WordPress theme (nko_llms_txt()).
export const LLMS_TXT = `# Nitiksham

> Nitiksham is a strategy-led brand, design and digital marketing agency working across India and worldwide.

## About

- Name: Nitiksham. "Niti" means strategy and "Ksham" means capability.
- Location: India — working worldwide.
- Address: Karnal, Haryana – 132001, India.
- Website: https://nitiksham.com/
- Email: contact@nitiksham.com
- Service area: India and worldwide.

## Services

- Brand & Graphic Design: https://nitiksham.com/expertise/
- Website Development: https://nitiksham.com/expertise-web-development/
- SEO (Search Engine Optimisation): https://nitiksham.com/expertise-seo/
- Digital Marketing: https://nitiksham.com/digital-marketing/
- Social Media Marketing: https://nitiksham.com/social-media-marketing/
- Copywriting & Content Writing: https://nitiksham.com/copyright/
- Print Design & Outdoor Advertising: https://nitiksham.com/print/

## Software products

- Nitik HR Management — multi-company HR and recruitment software: https://nitiksham.com/nitik-hr-management/
- Nitik Stock-Desk — multi-client inventory and stock software: https://nitiksham.com/nitik-stock-desk/

## Process

1. Discover
2. Plan
3. Build
4. Launch
5. Grow

## Industries served

- Real estate
- E-commerce
- Healthcare
- Education
- B2B & industrial
- FMCG & retail

## Pages

- Home: https://nitiksham.com/
- About Us: https://nitiksham.com/about-us/
- Services: https://nitiksham.com/services/
- Brand Identity & Graphic Design: https://nitiksham.com/expertise/
- Website Development: https://nitiksham.com/expertise-web-development/
- SEO Services: https://nitiksham.com/expertise-seo/
- Digital Marketing: https://nitiksham.com/digital-marketing/
- Social Media Marketing: https://nitiksham.com/social-media-marketing/
- Copywriting & Content Writing: https://nitiksham.com/copyright/
- Print Design & Outdoor Advertising: https://nitiksham.com/print/
- Custom Software Development: https://nitiksham.com/software/
- Nitik Softwares (client login): https://nitiksham.com/nitik-softwares/
- Nitik HR Management: https://nitiksham.com/nitik-hr-management/
- Nitik Stock-Desk: https://nitiksham.com/nitik-stock-desk/
- Team: https://nitiksham.com/team/
- Gallery: https://nitiksham.com/gallery/
- Contact Us: https://nitiksham.com/contact-us/
- Privacy Policy: https://nitiksham.com/privacy/
`;
