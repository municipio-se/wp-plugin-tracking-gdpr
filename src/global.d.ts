export {};

declare type ServiceCookie = {
  name: string;
  path?: string;
  domain?: string;
};

declare global {
  interface Window {
    whitespaceTrackingGdpr: {
      cookieConsent: {
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
      };
    };
    ccDebug: () => void;
  }
}
