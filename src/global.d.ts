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
      translation: Translation & {
        preferencesModal: {
          description?: string;
        };
      };
    };
    ccDebug: () => void;
  }
}
