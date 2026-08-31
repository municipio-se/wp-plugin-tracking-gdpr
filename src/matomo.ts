window._paq = window._paq || [];
window._mtm = window._mtm || [];

export class MatomoManager {
  public containerId?: string;
  public siteId?: string;
  constructor(
    public url: string,
    { containerId, siteId }: { containerId?: string; siteId?: string },
  ) {
    this.containerId = containerId;
    this.siteId = siteId;
  }
  loadMatomo() {
    // A Matomo Tag Manager container may include its own page-view tag. When a
    // container is configured it owns tracker startup, preventing a second
    // direct tracker from recording the same page view.
    if (!this.siteId || this.containerId) {
      return this;
    }
    window._paq.push(['requireCookieConsent']);
    window._paq.push(['trackPageView']);
    window._paq.push(['enableLinkTracking']);
    var u = this.url;
    window._paq.push(['setTrackerUrl', u + 'matomo.php']);
    window._paq.push(['setSiteId', this.siteId]);
    var d = document,
      g = d.createElement('script'),
      s = d.getElementsByTagName('script')[0];
    g.async = true;
    g.src = u + 'matomo.js';
    s.parentNode!.insertBefore(g, s);
    return this;
  }
  loadMTM() {
    if (!this.containerId) {
      return this;
    }
    // Enforce the plugin's cookieless baseline even when the remote container
    // tag has not enabled Matomo's cookie-consent option itself.
    window._paq.push(['requireCookieConsent']);
    window._mtm.push({
      'mtm.startTime': new Date().getTime(),
      event: 'mtm.Start',
    });
    var d = document,
      g = d.createElement('script'),
      s = d.getElementsByTagName('script')[0];
    g.async = true;
    g.src = `${this.url}js/container_${this.containerId}.js`;
    s.parentNode!.insertBefore(g, s);
    return this;
  }
  connectToConsentDialog() {
    let analyticsConsent: boolean | null = null;
    const applyConsent = (categories: string[]) => {
      const nextAnalyticsConsent = categories.includes('analytics');
      if (nextAnalyticsConsent === analyticsConsent) {
        return;
      }
      analyticsConsent = nextAnalyticsConsent;

      if (nextAnalyticsConsent) {
        window._paq.push(['rememberCookieConsentGiven']);
        window._mtm.push({ event: 'mtm.ConsentGiven' });
      } else {
        window._paq.push(['forgetCookieConsentGiven']);
        window._mtm.push({ event: 'mtm.ConsentRevoked' });
      }
    };

    window.addEventListener('cc:onConsent', ({ detail }) => {
      applyConsent(detail.cookie.categories);
    });
    window.addEventListener('cc:onChange', ({ detail }) => {
      if (detail.changedCategories.includes('analytics')) {
        applyConsent(detail.cookie.categories);
      }
    });
    applyConsent(window.CookieConsent.getCookie().categories || []);
    return this;
  }
}

export default function matomo(
  url: string,
  {
    containerId,
    siteId,
  }: {
    containerId?: string;
    siteId?: string;
  },
) {
  return new MatomoManager(url, { containerId, siteId });
}
