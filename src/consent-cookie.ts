const COOKIE_PATH = '/';
const COOKIE_SAME_SITE = 'Lax';
const EXPIRED_AT = 'Thu, 01 Jan 1970 00:00:01 GMT';

export function getConsentCookieName(hostname: string): string {
  return `cc_cookie_${hostname.replace(/\./g, '_')}`;
}

function getCookieValue(cookieHeader: string, name: string): string | null {
  const prefix = `${name}=`;
  const cookie = cookieHeader
    .split(/;\s*/)
    .find((candidate) => candidate.startsWith(prefix));

  return cookie?.slice(prefix.length) ?? null;
}

function getExpirationTime(encodedValue: string): number | null {
  try {
    const value = JSON.parse(decodeURIComponent(encodedValue)) as {
      expirationTime?: unknown;
    };
    return typeof value.expirationTime === 'number' &&
      Number.isFinite(value.expirationTime)
      ? value.expirationTime
      : null;
  } catch {
    return null;
  }
}

function cookieAttributes(protocol: string): string {
  const secure = protocol === 'https:' ? '; Secure' : '';
  return `Path=${COOKIE_PATH}; SameSite=${COOKIE_SAME_SITE}${secure}`;
}

/**
 * Moves the consent state written by CookieConsent's former default domain to
 * a host-only cookie before CookieConsent reads it. The expiry is copied from
 * the stored consent payload so migration never extends an existing choice.
 */
export function migrateConsentCookieToHostOnly({
  cookieHeader,
  hostname,
  name,
  protocol,
  writeCookie,
}: {
  cookieHeader: string;
  hostname: string;
  name: string;
  protocol: string;
  writeCookie: (cookie: string) => void;
}): void {
  const value = getCookieValue(cookieHeader, name);
  if (value === null) {
    return;
  }

  const attributes = cookieAttributes(protocol);
  writeCookie(
    `${name}=; expires=${EXPIRED_AT}; ${attributes}; Domain=${hostname}`,
  );

  const expirationTime = getExpirationTime(value);
  if (expirationTime === null || expirationTime <= Date.now()) {
    return;
  }

  writeCookie(
    `${name}=${value}; expires=${new Date(expirationTime).toUTCString()}; ${attributes}`,
  );
}
