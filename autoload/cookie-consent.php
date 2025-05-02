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
    $categories = wstg_get_cookie_categories();
    $categories_settings = get_field("wstg_cookie_categories", "option");
    $services = wstg_get_services();
    $cookie_consent = [];
    foreach ($categories as $category_key => $category) {
      $cookie_consent["categories"][$category_key] =
        ($categories_settings[$category_key] ?? []) + $category;
    }
    foreach ($services as $service_name => $service) {
      $category = $service["category"];
      if (!isset($cookie_consent["categories"][$category])) {
        continue;
      }
      $cookie_consent["categories"][$category]["services"][
        $service_name
      ] += $service;
    }
    wp_localize_script("whitespace-tracking-gdpr", "whitespaceTrackingGdpr", [
      "cookieConsent" => $cookie_consent,
    ]);
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
