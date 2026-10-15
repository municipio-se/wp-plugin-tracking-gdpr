import { Translation } from 'vanilla-cookieconsent';

export {};

declare type ServiceCookie = {
  name: string;
  path?: string;
  domain?: string;
};

declare global {
  type MatomoTracker = {
    requireConsent: () => void;
    isConsentRequired: () => boolean;
    setConsentGiven: (setCookieConsent?: boolean) => void;
    forgetConsentGiven: () => void;
    disableCookies: () => void;
    disableBrowserFeatureDetection: () => void;
    enableBrowserFeatureDetection: () => void;
    forgetCookieConsentGiven: () => void;
    rememberCookieConsentGiven: () => void;
    requireCookieConsent: () => boolean;
    setCookieConsentGiven: () => void;
  };

  type MatomoRuntime = {
    getAsyncTrackers: () => MatomoTracker[];
    on: (
      event: 'TrackerSetup',
      callback: (tracker: MatomoTracker) => void,
    ) => void;
  };

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
    Matomo?: MatomoRuntime;
    matomoPluginAsyncInit: Array<() => void>;
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
