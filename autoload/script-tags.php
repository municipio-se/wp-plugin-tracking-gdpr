<?php

use WhitespaceTrackingGdpr\Csp;

add_action("init", function () {
  wstg_csp_deny("script-src", Csp::SELF);
  wstg_csp_allow("script-src-attr", Csp::SELF);
  wstg_csp_allow("script-src-attr", Csp::UNSAFE_INLINE);
  wstg_csp_allow("script-src-elem", Csp::NONCE);
});

/**
 * Adds nonce attribute to all inline script tags
 */
add_filter("wp_inline_script_attributes", function ($attributes) {
  if (is_admin()) {
    return $attributes; // Do not apply CSP in admin area
  }
  $csp_nonce = wstg_csp_nonce();
  if ($csp_nonce) {
    $attributes["nonce"] = $csp_nonce;
  }
  return $attributes;
});

/**
 * Adds nonce attribute to all script tags
 */
add_filter("wp_script_attributes", function ($attributes) {
  if (is_admin()) {
    return $attributes; // Do not apply CSP in admin area
  }
  $csp_nonce = wstg_csp_nonce();
  if ($csp_nonce) {
    $attributes["nonce"] = $csp_nonce;
  }
  return $attributes;
});

add_filter(
  "wp_script_attributes",
  function ($attributes) {
    if (is_admin()) {
      return $attributes; // Consent rewriting is frontend-only; admin scripts may be editor dependencies.
    }

    $category =
      strpos($attributes["src"], get_site_url() . "/") === 0
        ? ""
        : "uncategorized";

    /**
     * Filters the consent category assigned to an enqueued external script.
     *
     * @param string $category Consent category slug.
     * @param array $attributes Script tag attributes.
     * @return string Filtered consent category slug.
     */
    $category = apply_filters("wstg_script_category", $category, $attributes);

    if ($category) {
      $attributes["data-category"] = $category;

      /**
       * Filters the service key assigned to an enqueued external script.
       *
       * @param string $service Service key.
       * @param array $attributes Script tag attributes.
       * @return string Filtered service key.
       */
      $service = apply_filters("wstg_script_service", "", $attributes);
      if ($service) {
        $attributes["data-service"] = $service;
      }

      if (isset($attributes["type"])) {
        $attributes["data-type"] = $attributes["type"];
      }
      $attributes["type"] = "text/plain";
    }
    return $attributes;
  },
  10,
  2,
);

add_filter(
  "wstg_script_category",
  function ($category, $attributes) {
    $services = wstg_get_services();
    foreach ($services as $service) {
      if ($service["scripts"] ?? null) {
        $matching_scripts = array_filter(
          $service["scripts"],
          fn($script) => $script["match"]($attributes),
        );
        $matching_script = !empty($matching_scripts)
          ? array_values($matching_scripts)[0]
          : null;
        if ($matching_script) {
          $category =
            $matching_script["category"] ??
            ($service["category"] ?? "uncategorized");
          break;
        }
      }
    }
    return $category;
  },
  10,
  2,
);

add_filter(
  "wstg_script_service",
  function ($service, $attributes) {
    $services = wstg_get_services();
    foreach ($services as $service_name => $service_data) {
      if ($service_data["scripts"] ?? null) {
        $matching_scripts = array_filter(
          $service_data["scripts"],
          fn($script) => $script["match"]($attributes),
        );
        $matching_script = !empty($matching_scripts)
          ? array_values($matching_scripts)[0]
          : null;
        if ($matching_script) {
          $service = $matching_script["service"] ?? $service_name;
          break;
        }
      }
    }
    return $service;
  },
  10,
  2,
);
