<?php

/**
 * Enqueue script from /dist/assets/index.js
 */
add_action(
  "wp_enqueue_scripts",
  function () {
    wp_enqueue_script(
      "whitespace-tracking-gdpr",
      WHITESPACE_TRACKING_GDPR_URL . "/dist/assets/index.js",
      [],
      filemtime(WHITESPACE_TRACKING_GDPR_PATH . "/dist/assets/index.js"),
      true,
    );
    // wp_localize_script(
    //   "whitespace-tracking-gdpr",
    //   "whitespaceTrackingGdpr",
    //   [
    //     "cookieConsent" => get_field("cookie_consent", "option"),
    //     "matomoUrl" => get_field("mx_matomo_url", "option"),
    //     "matomoContainerId" => get_field("mx_matomo_container_id", "option"),
    //     "matomoSiteId" => get_field("mx_matomo_site_id", "option"),
    //   ],
    // );
  },
  10,
);

/**
 * Enqueue styles
 */
add_action(
  "wp_enqueue_scripts",
  function () {
    wp_enqueue_style(
      "whitespace-tracking-gdpr",
      WHITESPACE_TRACKING_GDPR_URL . "/dist/assets/index.css",
      [],
      filemtime(WHITESPACE_TRACKING_GDPR_PATH . "/dist/assets/index.css"),
    );
  },
  10,
);

add_filter(
  "wp_script_attributes",
  function ($attributes) {
    // error_log(var_export(get_site_url(), true));
    // error_log(var_export($attributes["src"], true));
    // error_log(var_export($attributes["id"], true));
    if ($attributes["id"] == "whitespace-tracking-gdpr-js") {
      // $attributes["data-category"] = "uncategorized";
      $attributes["type"] = "module";
    }
    return $attributes;
  },
  10,
  2,
);
