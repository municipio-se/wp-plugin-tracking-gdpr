<?php

add_action("init", function () {
  $url = mx_get_matomo_option("url");
  $host = parse_url($url, PHP_URL_HOST);
  if ($host) {
    wstg_csp_allow("connect-src", $host);
  }
});

add_action("wp_head", function () {
  $url = mx_get_matomo_option("url");
  $container_id = mx_get_matomo_option("container_id");
  $site_id = mx_get_matomo_option("site_id");
  if ($container_id && $url): ?>
      <!-- Matomo Tag Manager -->
      <script<?php echo wp_sanitize_script_attributes(
        apply_filters("wp_inline_script_attributes", []),
      ); ?>>
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
        <script<?php echo wp_sanitize_script_attributes(
          apply_filters("wp_inline_script_attributes", []),
        ); ?>>
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
        <script<?php echo wp_sanitize_script_attributes(
          apply_filters("wp_inline_script_attributes", []),
        ); ?> data-category="analytics" type="text/plain">
          _paq.push(['rememberCookieConsentGiven']);
          console.log('rememberCookieConsentGiven');
        </script>
        <script<?php echo wp_sanitize_script_attributes(
          apply_filters("wp_inline_script_attributes", []),
        ); ?> data-category="!analytics" type="text/plain">
          _paq.push(['forgetCookieConsentGiven']);
          console.log('forgetCookieConsentGiven');
        </script>
        <!-- End Matomo Code -->
        <?php endif;
});

add_action("init", function () {
  $matomo_url = mx_get_matomo_option("url");
  $matomo_container_id = mx_get_matomo_option("container_id");
  $matomo_site_id = mx_get_matomo_option("site_id");
  if ($matomo_url && ($matomo_container_id || $matomo_site_id)) {
    wstg_csp_allow("connect-src", parse_url($matomo_url, PHP_URL_HOST));
  }
});
