import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';
import ts from 'typescript';

const source = await readFile(
  new URL('../src/network-requests.ts', import.meta.url),
  'utf8',
);
const compiled = ts.transpileModule(source, {
  compilerOptions: {
    module: ts.ModuleKind.ESNext,
    target: ts.ScriptTarget.ES2022,
  },
}).outputText;

async function importGate() {
  return import(
    `data:text/javascript,${encodeURIComponent(compiled)}#${Math.random()}`
  );
}

function createEnvironment() {
  let accepted = false;
  const fetchCalls = [];
  const beaconCalls = [];

  globalThis.Request = class Request {
    constructor(url) {
      this.url = url;
    }
  };
  globalThis.window = {
    location: { href: 'https://site.test/page' },
    fetch: async (...args) => {
      fetchCalls.push(args);
      return { ok: true };
    },
  };
  Object.defineProperty(globalThis, 'navigator', {
    configurable: true,
    value: {
      sendBeacon: (...args) => {
        beaconCalls.push(args);
        return true;
      },
    },
  });

  return {
    beaconCalls,
    consent: {
      acceptedCategory: () => accepted,
      acceptedService: () => accepted,
    },
    fetchCalls,
    setAccepted: (value) => {
      accepted = value;
    },
  };
}

const rules = [
  {
    service: 'example',
    category: 'analytics',
    url: 'https://collector.test/events',
    types: ['fetch', 'beacon'],
  },
];

test('registered fetch calls follow current service consent', async () => {
  const environment = createEnvironment();
  const { installNetworkRequestGate } = await importGate();
  installNetworkRequestGate(rules, environment.consent);

  await assert.rejects(
    window.fetch('https://collector.test/events/1'),
    (error) => error.name === 'NotAllowedError',
  );
  assert.equal(environment.fetchCalls.length, 0);

  environment.setAccepted(true);
  await window.fetch(new Request('https://collector.test/events/2'));
  assert.equal(environment.fetchCalls.length, 1);

  environment.setAccepted(false);
  await assert.rejects(window.fetch('https://collector.test/events/3'));
  assert.equal(environment.fetchCalls.length, 1);
});

test('registered beacons are refused without consent and allowed with it', async () => {
  const environment = createEnvironment();
  const { installNetworkRequestGate } = await importGate();
  installNetworkRequestGate(rules, environment.consent);

  assert.equal(
    navigator.sendBeacon('https://collector.test/events', 'rejected'),
    false,
  );
  assert.equal(environment.beaconCalls.length, 0);

  environment.setAccepted(true);
  assert.equal(
    navigator.sendBeacon('https://collector.test/events', 'accepted'),
    true,
  );
  assert.equal(environment.beaconCalls.length, 1);
});

test('unregistered calls and lookalike origins remain outside the gate', async () => {
  const environment = createEnvironment();
  const { installNetworkRequestGate } = await importGate();
  installNetworkRequestGate(rules, environment.consent);

  await window.fetch('https://unregistered.test/events');
  await window.fetch('https://collector.test.evil.example/events');
  assert.equal(environment.fetchCalls.length, 2);
});
