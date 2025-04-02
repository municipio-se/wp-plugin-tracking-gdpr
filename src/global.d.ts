export {};

interface IframeManager {
  run: (options: {
    currLang: string;
    services: {
      [key: string]: {
        embedUrl: string;
        thumbnailUrl: string;
        iframe: {
          [key: string]: string;
        };
        languages: {
          [lang: string]: {
            [key: string]: string;
          };
        };
      };
    };
  }) => void;
}

declare global {
  interface Window {
    iframemanager: () => IframeManager;
  }
}
