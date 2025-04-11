<?php

add_action("wp_head", function () {
  $url = mx_get_matomo_option("url");
  $container_id = mx_get_matomo_option("container_id");
  $site_id = mx_get_matomo_option("site_id");
  if ($container_id && $url): ?>
      <!-- Matomo Tag Manager -->
      <script>
        var _mtm = window._mtm = window._mtm || [];
        _mtm.push({'mtm.startTime': (new Date().getTime()), 'event': 'mtm.Start'});
        (function() {
          var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
          g.async=true;
          g.src='<?php echo $url; ?>js/container_' + '<?php echo $container_id; ?>' + '.js';
          s.parentNode.insertBefore(g,s);
        })();
      </script>
      <!-- End Matomo Tag Manager -->
      <?php elseif (!$container_id && $url && $site_id): ?>
        <!-- Matomo -->
        <script>
          var _paq = window._paq = window._paq || [];
          _paq.push(['requireCookieConsent']);
          _paq.push(['trackPageView']);
          _paq.push(['enableLinkTracking']);
          (function() {
            var u="<?php echo $url; ?>";
            _paq.push(['setTrackerUrl', u+'matomo.php']);
            _paq.push(['setSiteId', '<?php echo $site_id; ?>']);
            var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
            g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
          })();
        </script>
        <script data-category="analytics" type="text/plain">
          _paq.push(['rememberCookieConsentGiven']);
          console.log('rememberCookieConsentGiven');
        </script>
        <script data-category="!analytics" type="text/plain">
          _paq.push(['forgetCookieConsentGiven']);
          console.log('forgetCookieConsentGiven');
        </script>
        <!-- End Matomo Code -->
        <?php endif;
});
