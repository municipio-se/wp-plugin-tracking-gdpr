import 'vanilla-cookieconsent/dist/cookieconsent.css';
import * as CookieConsent from 'vanilla-cookieconsent';

import '@orestbida/iframemanager/dist/iframemanager.css';
import '@orestbida/iframemanager';
import './custom.css';

const cookieConsent = window.whitespaceTrackingGdpr.cookieConsent;
// console.log(cookieConsent);

const categories = {} as CookieConsent.CookieConsentConfig['categories'];
Object.entries(cookieConsent.categories).forEach(([key, category]) => {
  if (category.enabled === false) return;
  categories[key] = {
    enabled: key === 'necessary',
    readOnly: key === 'necessary',
  };
});

// console.log(categories);

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
          // sections: [
          //   {
          //     title: 'Någon sa ... cookies?',
          //     description: 'Jag vill ha en!',
          //   },
          //   {
          //     title: 'Nödvändiga cookies',
          //     description:
          //       'Dessa cookies är nödvändiga för att webbplatsen ska fungera korrekt och kan inte inaktiveras.',

          //     //this field will generate a toggle linked to the 'necessary' category
          //     linkedCategory: 'necessary',
          //   },
          //   {
          //     title: 'Prestanda och analys',
          //     description:
          //       'Dessa cookies samlar in information om hur du använder vår webbplats. All data är anonymiserad och kan inte användas för att identifiera dig.',
          //     linkedCategory: 'analytics',
          //   },
          //   {
          //     title: 'Oanvändade cookies',
          //     linkedCategory: 'uncategorized',
          //   },
          //   {
          //     title: 'Mer information',
          //     description:
          //       'För alla frågor i samband med min policy för cookies och dina val, vänligen <a href="#contact-page">kontakta oss</a>',
          //   },
          // ],
        },
      },
    },
  },
});

window.CookieConsent = CookieConsent;

window.addEventListener('load', function () {
  const im = window.iframemanager();

  // Example with youtube embed
  im.run({
    currLang: 'en',
    services: {
      youtube: {
        embedUrl: 'https://www.youtube-nocookie.com/embed/{data-id}',
        thumbnailUrl: 'https://i3.ytimg.com/vi/{data-id}/hqdefault.jpg',
        iframe: {
          allow:
            'accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen;',
        },
        languages: {
          en: {
            notice:
              'This content is hosted by a third party. By showing the external content you accept the <a rel="noreferrer noopener" href="https://www.youtube.com/t/terms" target="_blank">terms and conditions</a> of youtube.com.',
            loadBtn: 'Load video',
            loadAllBtn: "Don't ask again",
          },
        },
      },
      vimeo: {
        embedUrl: 'https://player.vimeo.com/video/{data-id}?dnt=1',
        thumbnailUrl: 'https://vumbnail.com/{data-id}.jpg',
        iframe: {
          allow:
            'accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen;',
        },
        languages: {
          en: {
            notice:
              'This content is hosted by a third party. By showing the external content you accept the <a rel="noreferrer noopener" href="https://vimeo.com/cookie_policy" target="_blank">terms and conditions</a> of vimeo.com.',
            loadBtn: 'Load video',
            loadAllBtn: "Don't ask again",
          },
        },
      },
      mediaflow: {
        embedUrl: '//play.mediaflow.com/ovp/16/{data-id}',
        thumbnailUrl: 'https://im16.inviewer.se/skiss/44/{data-id}.jpg',
        iframe: {
          allow:
            'accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen;',
        },
        languages: {
          en: {
            notice:
              'This content is hosted by a third party. By showing the external content you accept the <a rel="noreferrer noopener" href="https://www.mediaflow.com/integritetsinformation/" target="_blank">terms and conditions</a> of mediaflow.com.',
            loadBtn: 'Load video',
            loadAllBtn: "Don't ask again",
          },
        },
      },
      uncategorized: {
        embedUrl: '{data-id}',
        // thumbnailUrl: 'https://vumbnail.com/{data-id}.jpg',
        // iframe: {
        //   allow:
        //     'accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen;',
        // },
        languages: {
          en: {
            notice: 'This content is hosted by a third party.',
            loadBtn: 'Load content',
            loadAllBtn: "Don't ask again",
          },
        },
      },
    },
  });
});
