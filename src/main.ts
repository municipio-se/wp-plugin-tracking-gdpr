import 'vanilla-cookieconsent/dist/cookieconsent.css';
import * as CookieConsent from 'vanilla-cookieconsent';

import './wstg-iframe';

import './custom.css';

const cookieConsent = window.whitespaceTrackingGdpr.cookieConsent;

function maybeRegex(value: string): string | RegExp {
  if (value.startsWith('/') && value.endsWith('/')) {
    return new RegExp(value.substring(1, value.length - 1));
  }
  return value;
}

const categories = {} as CookieConsent.CookieConsentConfig['categories'];
Object.entries(cookieConsent.categories).forEach(([key, category]) => {
  if (category.enabled === false) return;
  categories[key] = {
    enabled: key === 'necessary',
    readOnly: key === 'necessary',
    services: Object.fromEntries(
      Object.entries(category.services ?? {})
        .filter(([, service]) => service.enabled)
        .map(([serviceKey, service]) => {
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

Object.entries(cookieConsent.categories).forEach(([key, category]) => {
  if (category.enabled === false) return;
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
    default: 'sv',
    translations: {
      sv: {
        consentModal: {
          title: 'Vi använder cookies',
          description: 'Cookie modal description',
          acceptAllBtn: 'Acceptera alla',
          acceptNecessaryBtn: 'Acceptera nödvändiga',
          showPreferencesBtn: 'Hantera individuella preferenser',
        },
        preferencesModal: {
          title: 'Hantera cookie-preferenser',
          acceptAllBtn: 'Acceptera alla',
          acceptNecessaryBtn: 'Acceptera nödvändiga',
          savePreferencesBtn: 'Acceptera nuvarande val',
          closeIconLabel: 'Stäng modal',
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
    cookieConsent,
  });
};
