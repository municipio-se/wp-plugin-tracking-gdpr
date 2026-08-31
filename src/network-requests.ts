type RequestType = 'fetch' | 'beacon';

export type NetworkRequestRule = {
  service: string;
  category: string;
  url: string;
  types: RequestType[];
};

type ConsentReader = {
  acceptedCategory(category: string): boolean;
  acceptedService(service: string, category: string): boolean;
};

function requestUrl(input: RequestInfo | URL): URL | null {
  const value = input instanceof Request ? input.url : String(input);
  try {
    return new URL(value, window.location.href);
  } catch {
    return null;
  }
}

function ruleMatches(
  input: RequestInfo | URL,
  type: RequestType,
  rule: NetworkRequestRule,
): boolean {
  if (!rule.types.includes(type)) {
    return false;
  }

  const requested = requestUrl(input);
  let prefix: URL;
  try {
    prefix = new URL(rule.url, window.location.href);
  } catch {
    return false;
  }

  return (
    requested !== null &&
    requested.origin === prefix.origin &&
    requested.pathname.startsWith(prefix.pathname) &&
    requested.search.startsWith(prefix.search)
  );
}

function isAllowed(rule: NetworkRequestRule, consent: ConsentReader): boolean {
  return (
    rule.category === 'necessary' ||
    consent.acceptedService(rule.service, rule.category)
  );
}

function matchingRule(
  input: RequestInfo | URL,
  type: RequestType,
  rules: NetworkRequestRule[],
): NetworkRequestRule | undefined {
  return rules.find((rule) => ruleMatches(input, type, rule));
}

/**
 * Gate only declared fetch/sendBeacon destinations. This is intentionally not
 * a browser firewall: unregistered calls, iframe traffic, calls made before
 * installation and already in-flight requests remain outside the contract.
 */
export function installNetworkRequestGate(
  rules: NetworkRequestRule[],
  consent: ConsentReader,
): () => void {
  if (rules.length === 0) {
    return () => undefined;
  }

  const nativeFetch = window.fetch.bind(window);
  const nativeSendBeacon = navigator.sendBeacon?.bind(navigator);

  window.fetch = (input, init) => {
    const rule = matchingRule(input, 'fetch', rules);
    if (rule && !isAllowed(rule, consent)) {
      return Promise.reject(
        new DOMException(
          `Consent is required for the ${rule.service} service.`,
          'NotAllowedError',
        ),
      );
    }
    return nativeFetch(input, init);
  };

  if (nativeSendBeacon) {
    navigator.sendBeacon = (url, data) => {
      const rule = matchingRule(url, 'beacon', rules);
      return rule && !isAllowed(rule, consent)
        ? false
        : nativeSendBeacon(url, data);
    };
  }

  return () => {
    window.fetch = nativeFetch;
    if (nativeSendBeacon) {
      navigator.sendBeacon = nativeSendBeacon;
    }
  };
}
