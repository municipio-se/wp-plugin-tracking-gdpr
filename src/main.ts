import 'vanilla-cookieconsent/dist/cookieconsent.css';
import './global.css';
import * as CookieConsent from 'vanilla-cookieconsent';

import './wstg-iframe';

import matomo from './matomo';

const settings = window.whitespaceTrackingGdpr;

function maybeRegex(value: string): string | RegExp {
  if (value.startsWith('/') && value.endsWith('/')) {
    return new RegExp(value.substring(1, value.length - 1));
  }
  return value;
}

const categories = {} as CookieConsent.CookieConsentConfig['categories'];
Object.entries(settings.categories).forEach(([key, category]) => {
  if (key === 'necessary') {
    categories[key] = {
      enabled: true,
      readOnly: true,
      services: {},
    };
    return;
  }
  categories[key] = {
    services: Object.fromEntries(
      Object.entries(category.services ?? {}).map(([serviceKey, service]) => {
        return [
          serviceKey,
          {
            label: service.title,
            cookies: (service.cookies ?? []).map((cookie) => ({
              name: cookie.name ? maybeRegex(cookie.name) : /^/,
              path: cookie.path,
              domain: cookie.domain,
            })),
          },
        ];
      }),
    ),
  };
});

const sections =
  [] as CookieConsent.Translation['preferencesModal']['sections'];

if (settings.translation.preferencesModal.description) {
  sections.push({
    description: settings.translation.preferencesModal.description,
  });
}

Object.keys(categories).forEach((key) => {
  const category = settings.categories[key];
  sections.push({
    title: category.title,
    description: category.description,
    linkedCategory: key,
  });
});

CookieConsent.run({
  autoShow: true,
  categories,
  cookie: {
    name: `cc_cookie_${window.location.hostname.replace(/\./g, '_')}`,
  },
  language: {
    default: settings.language,
    translations: {
      [settings.language]: {
        ...settings.translation,
        preferencesModal: {
          ...settings.translation.preferencesModal,
          sections,
        },
      },
    },
  },
});

window.CookieConsent = CookieConsent;

window.ccDebug = function () {
  console.log({
    categories,
    sections,
    settings,
  });
};

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.wstg-trigger-cookie-dialog').forEach((el) => {
    el.addEventListener('click', (event) => {
      event.preventDefault();
      CookieConsent.show(true);
    });
  });
});

matomo(settings.matomo.url, {
  containerId: settings.matomo.containerId || undefined,
  siteId: settings.matomo.siteId || undefined,
})
  .connectToConsentDialog()
  .loadMTM()
  .loadMatomo();
