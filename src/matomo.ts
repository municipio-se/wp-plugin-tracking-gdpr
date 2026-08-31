window._paq = window._paq || [];
window._mtm = window._mtm || [];

export class MatomoManager {
  public containerId?: string;
  public siteId?: string;
  private analyticsConsent: boolean | null = null;
  private containerConsentRegistered = false;
  private directTrackerStarted = false;
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
    this.directTrackerStarted = true;
    this.applyConsentToDirectTracker();
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
    this.registerContainerConsent();
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
    const applyConsent = (categories: string[], emitEvent = true) => {
      const nextAnalyticsConsent = categories.includes('analytics');
      if (nextAnalyticsConsent === this.analyticsConsent) {
        return;
      }
      this.analyticsConsent = nextAnalyticsConsent;

      if (this.containerId && this.containerConsentRegistered) {
        window.Matomo?.getAsyncTrackers().forEach((tracker) => {
          this.applyConsentToTracker(tracker);
        });
      } else if (!this.containerId && this.directTrackerStarted) {
        this.applyConsentToDirectTracker();
      }

      if (emitEvent) {
        window._mtm.push({
          event: nextAnalyticsConsent
            ? 'mtm.ConsentGiven'
            : 'mtm.ConsentRevoked',
        });
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
    // Restore the persisted choice without replaying a Tag Manager consent
    // event on every page view. TrackerSetup applies this state before the
    // container's page-view tag runs.
    applyConsent(window.CookieConsent.getCookie().categories || [], false);
    return this;
  }

  private applyConsentToTracker(tracker: MatomoTracker) {
    if (this.analyticsConsent === null) {
      return;
    }
    if (this.analyticsConsent) {
      tracker.rememberCookieConsentGiven();
    } else {
      tracker.forgetCookieConsentGiven();
    }
  }

  private applyConsentToDirectTracker() {
    if (this.analyticsConsent === null) {
      return;
    }
    window._paq.push([
      this.analyticsConsent
        ? 'setCookieConsentGiven'
        : 'forgetCookieConsentGiven',
    ]);
  }

  private registerContainerConsent() {
    if (this.containerConsentRegistered) {
      return;
    }
    this.containerConsentRegistered = true;

    /**
     * Queuing commands in `_paq` before Matomo's bundled tracker loads makes
     * Matomo create an unconfigured default tracker before the container adds
     * its configured tracker. Registering at TrackerSetup keeps one tracker and
     * applies the requirement before its first page view. Tag Manager applies
     * the same requirement again after TrackerSetup, so container consent must
     * use Matomo's remembered cookie. If Tracking GDPR already has consent but
     * that cookie is missing, restore it in a microtask after the container has
     * configured Secure and SameSite.
     */
    const register = () => {
      if (!window.Matomo) {
        return;
      }
      const prepareTracker = (tracker: MatomoTracker) => {
        const needsRememberedConsent = tracker.requireCookieConsent();
        if (needsRememberedConsent && this.analyticsConsent) {
          queueMicrotask(() => this.applyConsentToTracker(tracker));
        }
      };
      window.Matomo.on('TrackerSetup', prepareTracker);
      window.Matomo.getAsyncTrackers().forEach(prepareTracker);
    };

    if (window.Matomo) {
      register();
      return;
    }
    window.matomoPluginAsyncInit = window.matomoPluginAsyncInit || [];
    window.matomoPluginAsyncInit.push(register);
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
