import 'vanilla-cookieconsent/dist/cookieconsent.css';
import * as CookieConsent from 'vanilla-cookieconsent';

import '@orestbida/iframemanager/dist/iframemanager.css';
import '@orestbida/iframemanager';

CookieConsent.run({
  autoShow: true,
  categories: {
    necessary: {
      enabled: true,
      readOnly: true,
    },
    uncategorized: {
      enabled: false,
      readOnly: false,
    },
  },
  language: {
    default: 'en',
    translations: {
      en: {
        consentModal: {
          title: 'We use cookies',
          description: 'Cookie modal description',
          acceptAllBtn: 'Accept all',
          acceptNecessaryBtn: 'Reject all',
          showPreferencesBtn: 'Manage Individual preferences',
        },
        preferencesModal: {
          title: 'Manage cookie preferences',
          acceptAllBtn: 'Accept all',
          acceptNecessaryBtn: 'Reject all',
          savePreferencesBtn: 'Accept current selection',
          closeIconLabel: 'Close modal',
          sections: [
            {
              title: 'Somebody said ... cookies?',
              description: 'I want one!',
            },
            {
              title: 'Strictly Necessary cookies',
              description:
                'These cookies are essential for the proper functioning of the website and cannot be disabled.',

              //this field will generate a toggle linked to the 'necessary' category
              linkedCategory: 'necessary',
            },
            {
              title: 'Performance and Analytics',
              description:
                'These cookies collect information about how you use our website. All of the data is anonymized and cannot be used to identify you.',
              linkedCategory: 'analytics',
            },
            {
              title: 'Uncategorized cookies',
              linkedCategory: 'uncategorized',
            },
            {
              title: 'More information',
              description:
                'For any queries in relation to my policy on cookies and your choices, please <a href="#contact-page">contact us</a>',
            },
          ],
        },
      },
    },
  },
});

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
