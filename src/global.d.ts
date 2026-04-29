import { Translation } from 'vanilla-cookieconsent';

export {};

declare type ServiceCookie = {
  name: string;
  path?: string;
  domain?: string;
};

declare global {
  interface Window {
    whitespaceTrackingGdpr: {
      categories: {
        [key: string]: {
          enabled: boolean;
          title: string;
          description: string;
          services: {
            title: string;
            cookies?: ServiceCookie[];
            enabled: boolean;
          }[];
        };
      };
      language: string;
      revision: number | string;
      translation: Translation & {
        preferencesModal: {
          description?: string;
        };
      };
      matomo: {
        url: string;
        containerId: string;
        siteId: string;
      };
    };
    ccDebug: () => void;
    _mtm: Array<any>;
    _paq: Array<any>;
  }

  declare interface WindowEventMap {
    'cc:onFirstConsent': CustomEvent<
      Parameters<CookieConsent.CookieConsentConfig['onFirstConsent']>[0]
    >;
    'cc:onConsent': CustomEvent<
      Parameters<CookieConsent.CookieConsentConfig['onConsent']>[0]
    >;
    'cc:onChange': CustomEvent<
      Parameters<CookieConsent.CookieConsentConfig['onChange']>[0]
    >;
  }
}
