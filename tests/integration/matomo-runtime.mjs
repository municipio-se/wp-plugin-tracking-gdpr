import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';
import { JSDOM } from 'jsdom';
import ts from 'typescript';

// Supply a published test container with a bundled tracker and one PageView tag.
// Execute its real templates and tracker, intercepting all outbound transports.
const fixture = process.env.MATOMO_CONTAINER_FIXTURE;
if (!fixture) {
  throw new Error(
    'Set MATOMO_CONTAINER_FIXTURE to a published MTM container with a bundled tracker and one PageView tag.',
  );
}
const source = await readFile(
  new URL('../../src/matomo.ts', import.meta.url),
  'utf8',
);
const compiled = ts
  .transpileModule(source, {
    compilerOptions: {
      module: ts.ModuleKind.ESNext,
      target: ts.ScriptTarget.ES2022,
    },
  })
  .outputText.replace(/export default function/g, 'function')
  .replace(/export class/g, 'class');

async function environment(options, categories, stale) {
  let script = await readFile(fixture, 'utf8');
  const start =
    script.lastIndexOf('window.MatomoTagManager.addContainer(') +
    'window.MatomoTagManager.addContainer('.length;
  const end = script.indexOf(', Templates)', start);
  const config = JSON.parse(script.slice(start, end));
  const settings = {
    matomoUrl: 'https://analytics.example.test/',
    idSite: '100',
    enableHeartBeatTimer: false,
    enableLinkTracking: false,
    enableFormAnalytics: false,
    enableMediaAnalytics: false,
    disablePerformanceTracking: true,
    disableBrowserFeatureDetection: true,
    cookiePath: '/',
    cookieNamePrefix: '_matrix_',
    setSecureCookie: true,
    ...options,
  };
  for (const tag of config.tags)
    Object.assign(tag.parameters.matomoConfig.parameters, settings);
  for (const variable of config.variables)
    if (variable.type === 'MatomoConfiguration')
      Object.assign(variable.parameters, settings);
  script = script.slice(0, start) + JSON.stringify(config) + script.slice(end);
  const dom = new JSDOM(
    '<!doctype html><title>Consent regression</title><script></script>',
    {
      url: 'https://site.example.test/',
      runScripts: 'outside-only',
      pretendToBeVisual: true,
    },
  );
  const w = dom.window;
  const requests = [];
  const capture = (url, body) =>
    requests.push({ url: String(url), body: body == null ? '' : String(body) });
  w.XMLHttpRequest = class {
    open(method, url) {
      this.url = url;
    }
    setRequestHeader() {}
    send(body) {
      capture(this.url, body);
      this.status = 204;
      this.readyState = 4;
      this.onreadystatechange?.();
      this.onload?.();
    }
  };
  Object.defineProperty(w.HTMLImageElement.prototype, 'src', {
    set(url) {
      capture(url);
      this.onload?.();
    },
    get() {
      return '';
    },
  });
  w.navigator.sendBeacon = (url, body) => {
    capture(url, body);
    return true;
  };
  w.fetch = async (url, options) => {
    capture(url, options?.body);
    return { ok: true, text: async () => '' };
  };
  w.CookieConsent = { getCookie: () => ({ categories }) };
  if (stale) {
    w.document.cookie = 'mtm_cookie_consent=123456; Path=/; Secure';
    w.document.cookie = 'mtm_consent=123456; Path=/; Secure';
  }
  w.eval(
    compiled +
      `\nwindow.manager = new MatomoManager('https://analytics.example.test/', {containerId:'test', siteId:'100'}).connectToConsentDialog().loadMTM().loadMatomo();`,
  );
  w.observedTrackers = [];
  w.matomoPluginAsyncInit.push(() =>
    w.Matomo.on('TrackerSetup', (tracker) => w.observedTrackers.push(tracker)),
  );
  w.eval(script);
  const settle = (ms = 100) =>
    new Promise((resolve) => setTimeout(resolve, ms));
  await settle();
  const change = async (allowed) => {
    categories = allowed ? ['analytics'] : [];
    w.dispatchEvent(
      new w.CustomEvent('cc:onChange', {
        detail: { changedCategories: ['analytics'], cookie: { categories } },
      }),
    );
    await settle();
  };
  const views = () =>
    requests.filter(
      ({ url, body }) =>
        /action_name=/.test(url + '?' + body) &&
        !/[?&]ping=1/.test(url + '?' + body),
    );
  return { w, dom, requests, change, views, settle };
}

test('MTM consent matrix', { concurrency: 8 }, async (t) => {
  const pending = [];
  for (const requireCookieConsent of [false, true]) {
    for (const requireConsent of [false, true]) {
      for (const disableCookies of [false, true]) {
        for (const saved of [false, true]) {
          for (const stale of [false, true]) {
            for (const registerAsDefaultTracker of [false, true]) {
              const mode = {
                requireCookieConsent,
                requireConsent,
                disableCookies,
                registerAsDefaultTracker,
              };
              pending.push(
                t.test(
                  `real MTM ${JSON.stringify(mode)} saved=${saved} stale=${stale}`,
                  async () => {
                    const env = await environment(
                      mode,
                      saved ? ['analytics'] : undefined,
                      stale,
                    );
                    try {
                      const tracker = env.w.observedTrackers[0];
                      assert.equal(env.w.observedTrackers.length, 1);
                      assert.equal(
                        env.w.Matomo.getAsyncTrackers().length,
                        registerAsDefaultTracker ? 1 : 0,
                      );
                      assert.equal(
                        tracker.areCookiesEnabled(),
                        saved && !disableCookies,
                        'initial cookie state',
                      );
                      assert.equal(
                        env.views().length,
                        requireConsent && !saved ? 0 : 1,
                        'initial requests',
                      );
                      await env.change(false);
                      assert.equal(
                        tracker.areCookiesEnabled(),
                        false,
                        'revoked cookie state',
                      );
                      const count = env.views().length;
                      await env.change(true);
                      assert.equal(
                        tracker.areCookiesEnabled(),
                        !disableCookies,
                        'granted cookie state',
                      );
                      assert.equal(
                        env.views().length,
                        count || 1,
                        'grant sends queued page view at most once',
                      );
                      await env.change(false);
                      assert.equal(tracker.areCookiesEnabled(), false);
                      assert.equal(
                        env.views().length,
                        1,
                        'no replay on consent changes',
                      );
                      assert.ok(
                        !env.w.document.cookie.includes('_matrix_'),
                        'analytics cookies removed',
                      );
                      tracker.trackPageView('After revoke');
                      await env.settle(1200);
                      assert.equal(
                        env.views().length,
                        requireConsent ? 1 : 2,
                        'tracking requirement survives revocation',
                      );
                      await env.change(true);
                      assert.equal(
                        env.views().length,
                        2,
                        'queued view released once',
                      );
                      await env.change(true);
                      assert.equal(
                        env.views().length,
                        2,
                        'repeated consent does not replay views',
                      );
                    } finally {
                      env.dom.window.close();
                    }
                  },
                ),
              );
            }
          }
        }
      }
    }
  }

  await Promise.all(pending);
});

for (const grant of ['setConsentGiven', 'rememberConsentGiven']) {
  test(
    `MTM ${grant} cannot override the dialog`,
    { skip: !fixture },
    async () => {
      const env = await environment(
        { requireConsent: true, [grant]: true },
        [],
        true,
      );
      try {
        assert.equal(env.views().length, 0);
        assert.equal(env.w.observedTrackers[0].areCookiesEnabled(), false);
        await env.change(true);
        assert.equal(env.views().length, 1);
        await env.change(false);
        assert.equal(env.w.observedTrackers[0].areCookiesEnabled(), false);
      } finally {
        env.dom.window.close();
      }
    },
  );
}
