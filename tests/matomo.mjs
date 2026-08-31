import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';
import ts from 'typescript';

const source = await readFile(
  new URL('../src/matomo.ts', import.meta.url),
  'utf8',
);
const compiled = ts.transpileModule(source, {
  compilerOptions: {
    module: ts.ModuleKind.ESNext,
    target: ts.ScriptTarget.ES2022,
  },
}).outputText;

function createEnvironment(categories = []) {
  const listeners = new Map();
  const insertedScripts = [];
  const firstScript = {
    parentNode: { insertBefore: (script) => insertedScripts.push(script) },
  };

  globalThis.document = {
    createElement: () => ({}),
    getElementsByTagName: () => [firstScript],
  };
  globalThis.window = {
    _mtm: [],
    _paq: [],
    addEventListener: (name, callback) => listeners.set(name, callback),
    CookieConsent: {
      getCookie: () => ({ categories }),
    },
  };

  return { insertedScripts, listeners };
}

async function importManager() {
  return import(
    `data:text/javascript,${encodeURIComponent(compiled)}#${Math.random()}`
  );
}

test('a direct tracker starts once in cookieless mode', async () => {
  const { insertedScripts } = createEnvironment();
  const { MatomoManager } = await importManager();

  new MatomoManager('https://matomo.test/', { siteId: '71' })
    .connectToConsentDialog()
    .loadMTM()
    .loadMatomo();

  assert.deepEqual(window._paq.slice(0, 2), [
    ['forgetCookieConsentGiven'],
    ['requireCookieConsent'],
  ]);
  assert.equal(
    window._paq.filter(([command]) => command === 'trackPageView').length,
    1,
  );
  assert.deepEqual(
    insertedScripts.map(({ src }) => src),
    ['https://matomo.test/matomo.js'],
  );
});

test('a container owns page-view startup and receives cookie consent first', async () => {
  const { insertedScripts } = createEnvironment(['analytics']);
  const { MatomoManager } = await importManager();

  new MatomoManager('https://matomo.test/', {
    containerId: 'container',
    siteId: '74',
  })
    .connectToConsentDialog()
    .loadMTM()
    .loadMatomo();

  assert.deepEqual(window._paq, [
    ['rememberCookieConsentGiven'],
    ['requireCookieConsent'],
  ]);
  assert.deepEqual(
    insertedScripts.map(({ src }) => src),
    ['https://matomo.test/js/container_container.js'],
  );
});

test('consent changes toggle Matomo cookie state without a page view', async () => {
  const { listeners } = createEnvironment();
  const { MatomoManager } = await importManager();

  new MatomoManager('https://matomo.test/', {
    siteId: '71',
  }).connectToConsentDialog();
  listeners.get('cc:onConsent')({
    detail: { cookie: { categories: [] } },
  });
  assert.deepEqual(window._paq, [['forgetCookieConsentGiven']]);
  window._paq.length = 0;
  window._mtm.length = 0;

  listeners.get('cc:onChange')({
    detail: {
      changedCategories: ['analytics'],
      cookie: { categories: ['analytics'] },
    },
  });
  listeners.get('cc:onChange')({
    detail: {
      changedCategories: ['analytics'],
      cookie: { categories: [] },
    },
  });

  assert.deepEqual(window._paq, [
    ['rememberCookieConsentGiven'],
    ['forgetCookieConsentGiven'],
  ]);
  assert.deepEqual(window._mtm, [
    { event: 'mtm.ConsentGiven' },
    { event: 'mtm.ConsentRevoked' },
  ]);
});
