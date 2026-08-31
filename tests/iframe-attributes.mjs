import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';
import ts from 'typescript';

const source = await readFile(
  new URL('../src/iframe-attributes.ts', import.meta.url),
  'utf8',
);
const compiled = ts.transpileModule(source, {
  compilerOptions: {
    module: ts.ModuleKind.ESNext,
    target: ts.ScriptTarget.ES2022,
  },
}).outputText;

async function importIframeAttributes() {
  return import(
    `data:text/javascript,${encodeURIComponent(compiled)}#${Math.random()}`
  );
}

test('supported iframe attributes preserve the accessible name', async () => {
  const { getSupportedIframeAttributes } = await importIframeAttributes();

  assert.deepEqual(
    getSupportedIframeAttributes({
      src: 'https://video.example/embed/123',
      title: 'YouTube E2E',
      allowfullscreen: true,
      'data-untrusted': 'discarded',
    }),
    {
      src: 'https://video.example/embed/123',
      title: 'YouTube E2E',
      allowfullscreen: '',
    },
  );
});
