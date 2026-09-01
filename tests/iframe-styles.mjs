import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';

const source = await readFile(
  new URL('../src/global.css', import.meta.url),
  'utf8',
);

test('external iframe actions keep the filled button color in every link state', () => {
  assert.match(
    source,
    /\.wstg-iframe__actions\s*>\s*a\.wstg-iframe__action:any-link\s*\{[^}]*--c-button-primary-color-contrasting/s,
  );
});
