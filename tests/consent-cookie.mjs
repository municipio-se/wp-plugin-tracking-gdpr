import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test, { mock } from 'node:test';
import ts from 'typescript';

const source = await readFile(
  new URL('../src/consent-cookie.ts', import.meta.url),
  'utf8',
);
const compiled = ts.transpileModule(source, {
  compilerOptions: {
    module: ts.ModuleKind.ESNext,
    target: ts.ScriptTarget.ES2022,
  },
}).outputText;

async function importCookieHelpers() {
  return import(
    `data:text/javascript,${encodeURIComponent(compiled)}#${Math.random()}`
  );
}

test('consent cookie names stay unique per hostname', async () => {
  const { getConsentCookieName } = await importCookieHelpers();

  assert.equal(
    getConsentCookieName('energi.example.test'),
    'cc_cookie_energi_example_test',
  );
});

test('a legacy domain cookie is migrated without extending consent', async () => {
  const now = new Date('2026-08-31T17:00:00Z').getTime();
  mock.timers.enable({ apis: ['Date'], now });

  try {
    const { migrateConsentCookieToHostOnly } = await importCookieHelpers();
    const expiresAt = now + 86_400_000;
    const value = encodeURIComponent(
      JSON.stringify({ categories: ['necessary'], expirationTime: expiresAt }),
    );
    const writes = [];

    migrateConsentCookieToHostOnly({
      cookieHeader: `other=value; cc_cookie_example_test=${value}`,
      hostname: 'example.test',
      name: 'cc_cookie_example_test',
      protocol: 'https:',
      writeCookie: (cookie) => writes.push(cookie),
    });

    assert.equal(writes.length, 2);
    assert.match(writes[0], /Domain=example\.test$/);
    assert.match(writes[0], /Secure/);
    assert.doesNotMatch(writes[1], /Domain=/);
    assert.match(writes[1], /SameSite=Lax; Secure$/);
    assert.match(writes[1], /expires=Tue, 01 Sep 2026 17:00:00 GMT/);
  } finally {
    mock.timers.reset();
  }
});

test('invalid legacy state is deleted instead of renewed', async () => {
  const { migrateConsentCookieToHostOnly } = await importCookieHelpers();
  const writes = [];

  migrateConsentCookieToHostOnly({
    cookieHeader: 'cc_cookie_example_test=invalid',
    hostname: 'example.test',
    name: 'cc_cookie_example_test',
    protocol: 'http:',
    writeCookie: (cookie) => writes.push(cookie),
  });

  assert.equal(writes.length, 1);
  assert.match(writes[0], /Domain=example\.test$/);
  assert.doesNotMatch(writes[0], /Secure/);
});

test('missing consent state does not write a cookie', async () => {
  const { migrateConsentCookieToHostOnly } = await importCookieHelpers();
  const writes = [];

  migrateConsentCookieToHostOnly({
    cookieHeader: 'other=value',
    hostname: 'example.test',
    name: 'cc_cookie_example_test',
    protocol: 'https:',
    writeCookie: (cookie) => writes.push(cookie),
  });

  assert.deepEqual(writes, []);
});
