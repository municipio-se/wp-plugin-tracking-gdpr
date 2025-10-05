// add_action("wp_head", function () {
//   $url = mx_get_matomo_option("url");
//   $container_id = mx_get_matomo_option("container_id");
//   $site_id = mx_get_matomo_option("site_id");
//   if ($url && ($container_id || $site_id)):
//     if ($container_id): ?>
//         <script<?php echo wp_sanitize_script_attributes(
//           apply_filters("wp_inline_script_attributes", []),
//         ); ?>>
//           var _paq = window._paq = window._paq || [];
//           var _mtm = window._mtm = window._mtm || [];
//           _mtm.push({'mtm.startTime': (new Date().getTime()), 'event': 'mtm.Start'});
//           (function() {
//             var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
//             g.async=true;
//             g.src='<?php echo $url; ?>js/container_' + '<?php echo $container_id; ?>' + '.js';
//             s.parentNode.insertBefore(g,s);
//           })();
//         </script>
//       <?php elseif ($site_id): ?>
//         <script<?php echo wp_sanitize_script_attributes(
//           apply_filters("wp_inline_script_attributes", []),
//         ); ?>>
//           var _paq = window._paq = window._paq || [];
//           var _mtm = window._mtm = window._mtm || [];
//           _paq.push(['requireCookieConsent']);
//           _paq.push(['trackPageView']);
//           _paq.push(['enableLinkTracking']);
//           (function() {
//             var u="<?php echo $url; ?>";
//             _paq.push(['setTrackerUrl', u+'matomo.php']);
//             _paq.push(['setSiteId', '<?php echo $site_id; ?>']);
//             var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
//             g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
//           })();
//         </script>
//       <?php endif; ?>
//       <script<?php echo wp_sanitize_script_attributes(
//         apply_filters("wp_inline_script_attributes", []),
//       ); ?> data-category="analytics" type="text/plain">
//         _paq.push(['rememberCookieConsentGiven']);
//         _mtm.push({'event': 'mtm.ConsentGiven'})
//       </script>
//       <script<?php echo wp_sanitize_script_attributes(
//         apply_filters("wp_inline_script_attributes", []),
//       ); ?> data-category="!analytics" type="text/plain">
//         _paq.push(['forgetCookieConsentGiven']);
//         _mtm.push({'event': 'mtm.ConsentRevoked'})
//       </script>
//     <?php
//   endif;
// });

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
    if (!this.siteId) {
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
    window.addEventListener('cc:onConsent', ({ detail }) => {
      if (detail.cookie.categories.includes('analytics')) {
        window._paq.push(['rememberCookieConsentGiven']);
        window._mtm.push({ event: 'mtm.ConsentGiven' });
      } else {
        window._paq.push(['forgetCookieConsentGiven']);
        window._mtm.push({ event: 'mtm.ConsentRevoked' });
      }
    });
    window.addEventListener('cc:onChange', ({ detail }) => {
      if (detail.changedCategories.includes('analytics')) {
        if (detail.cookie.categories.includes('analytics')) {
          window._paq.push(['rememberCookieConsentGiven']);
          window._mtm.push({ event: 'mtm.ConsentGiven' });
        } else {
          window._paq.push(['forgetCookieConsentGiven']);
          window._mtm.push({ event: 'mtm.ConsentRevoked' });
        }
      }
    });
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
