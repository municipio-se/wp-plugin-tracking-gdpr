import 'vanilla-cookieconsent/dist/cookieconsent.css';
import * as CookieConsent from 'vanilla-cookieconsent';

import './wstg-iframe';

import './custom.css';

const settings = window.whitespaceTrackingGdpr;

// console.log(settings);

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
    // enabled: key === 'necessary',
    // readOnly: key === 'necessary',
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
