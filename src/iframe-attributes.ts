export const IFRAME_ATTRIBUTE_NAMES = [
  'allow',
  'allowfullscreen',
  'height',
  'loading',
  'name',
  'referrerpolicy',
  'sandbox',
  'src',
  'srcdoc',
  'title',
  'width',
] as const;

const iframeAttributeNames = new Set<string>(IFRAME_ATTRIBUTE_NAMES);

export function isSupportedIframeAttribute(name: string): boolean {
  return iframeAttributeNames.has(name);
}

/**
 * Keep the browser-created iframe aligned with the original embed without
 * forwarding arbitrary attributes from the serialized server payload.
 */
export function getSupportedIframeAttributes(
  attributes: Record<string, unknown>,
): Record<string, string> {
  return Object.fromEntries(
    Object.entries(attributes)
      .filter(
        ([name, value]) => isSupportedIframeAttribute(name) && value != null,
      )
      .map(([name, value]) => [name, value === true ? '' : String(value)]),
  );
}
