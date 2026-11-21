// Render entry: imports every pre-rendered page so the build can bundle
// them once with esbuild and render them with ReactDOMServer.
import HomePage from './pages/home.jsx';
import AboutUsPage from './pages/about-us.jsx';
import ServicesPage from './pages/services.jsx';
import ExpertisePage from './pages/expertise.jsx';
import ExpertiseWebDevelopmentPage from './pages/expertise-web-development.jsx';
import ExpertiseSeoPage from './pages/expertise-seo.jsx';
import DigitalMarketingPage from './pages/digital-marketing.jsx';
import SocialMediaMarketingPage from './pages/social-media-marketing.jsx';
import CopyrightPage from './pages/copyright.jsx';
import PrintPage from './pages/print.jsx';
import SoftwarePage from './pages/software.jsx';
import NitikSoftwaresPage from './pages/nitik-softwares.jsx';
import NitikHrManagementPage from './pages/nitik-hr-management.jsx';
import NitikStockDeskPage from './pages/nitik-stock-desk.jsx';
import TeamPage from './pages/team.jsx';
import GalleryPage from './pages/gallery.jsx';
import ContactUsPage from './pages/contact-us.jsx';
import PrivacyPage from './pages/privacy.jsx';
import LoginPage from './pages/login.jsx';
import StudioPage from './pages/studio.jsx';
import NotFoundPage from './pages/not-found.jsx';

import { meta as homeMeta } from './pages/home.jsx';
import { meta as aboutUsMeta } from './pages/about-us.jsx';
import { meta as servicesMeta } from './pages/services.jsx';
import { meta as expertiseMeta } from './pages/expertise.jsx';
import { meta as expertiseWebDevelopmentMeta } from './pages/expertise-web-development.jsx';
import { meta as expertiseSeoMeta } from './pages/expertise-seo.jsx';
import { meta as digitalMarketingMeta } from './pages/digital-marketing.jsx';
import { meta as socialMediaMarketingMeta } from './pages/social-media-marketing.jsx';
import { meta as copyrightMeta } from './pages/copyright.jsx';
import { meta as printMeta } from './pages/print.jsx';
import { meta as softwareMeta } from './pages/software.jsx';
import { meta as nitikSoftwaresMeta } from './pages/nitik-softwares.jsx';
import { meta as nitikHrManagementMeta } from './pages/nitik-hr-management.jsx';
import { meta as nitikStockDeskMeta } from './pages/nitik-stock-desk.jsx';
import { meta as teamMeta } from './pages/team.jsx';
import { meta as galleryMeta } from './pages/gallery.jsx';
import { meta as contactUsMeta } from './pages/contact-us.jsx';
import { meta as privacyMeta } from './pages/privacy.jsx';
import { meta as loginMeta } from './pages/login.jsx';
import { meta as studioMeta } from './pages/studio.jsx';
import { meta as notFoundMeta } from './pages/not-found.jsx';

export const pages = [
  { meta: homeMeta, Component: HomePage },
  { meta: aboutUsMeta, Component: AboutUsPage },
  { meta: servicesMeta, Component: ServicesPage },
  { meta: expertiseMeta, Component: ExpertisePage },
  { meta: expertiseWebDevelopmentMeta, Component: ExpertiseWebDevelopmentPage },
  { meta: expertiseSeoMeta, Component: ExpertiseSeoPage },
  { meta: digitalMarketingMeta, Component: DigitalMarketingPage },
  { meta: socialMediaMarketingMeta, Component: SocialMediaMarketingPage },
  { meta: copyrightMeta, Component: CopyrightPage },
  { meta: printMeta, Component: PrintPage },
  { meta: softwareMeta, Component: SoftwarePage },
  { meta: nitikSoftwaresMeta, Component: NitikSoftwaresPage },
  { meta: nitikHrManagementMeta, Component: NitikHrManagementPage },
  { meta: nitikStockDeskMeta, Component: NitikStockDeskPage },
  { meta: teamMeta, Component: TeamPage },
  { meta: galleryMeta, Component: GalleryPage },
  { meta: contactUsMeta, Component: ContactUsPage },
  { meta: privacyMeta, Component: PrivacyPage },
  { meta: loginMeta, Component: LoginPage },
  { meta: studioMeta, Component: StudioPage },
  { meta: notFoundMeta, Component: NotFoundPage },
];
