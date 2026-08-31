<?php

use WhitespaceTrackingGdpr\Csp;

/**
 * Normalize a URL to the origin a browser uses for same-origin checks.
 */
function wstg_url_origin(string $url, ?string $default_scheme = null): ?string {
  $parts = parse_url($url);
  if ($parts === false || empty($parts["host"])) {
    return null;
  }

  $scheme = strtolower($parts["scheme"] ?? ($default_scheme ?? ""));
  if (!in_array($scheme, ["http", "https"], true)) {
    return null;
  }

  $host = strtolower($parts["host"]);
  $port = $parts["port"] ?? ($scheme === "https" ? 443 : 80);

  return "{$scheme}://{$host}:{$port}";
}

/**
 * Determine whether a script URL belongs to the current public site origin.
 *
 * Municipio serves WordPress from /wp while wp-content lives at the public
 * root. Paths must therefore not be compared with site_url().
 */
function wstg_is_first_party_script_url(string $url): bool {
  $url = trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, "UTF-8"));
  if ($url === "") {
    return true;
  }

  $parts = parse_url($url);
  if ($parts === false) {
    return false;
  }

  if (empty($parts["scheme"]) && empty($parts["host"])) {
    return true;
  }

  $home_origin = wstg_url_origin(home_url("/"));
  if ($home_origin === null) {
    return false;
  }

  $home_scheme = parse_url($home_origin, PHP_URL_SCHEME);
  $script_origin = wstg_url_origin($url, $home_scheme ?: null);

  return $script_origin !== null && hash_equals($home_origin, $script_origin);
}

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

    $src = $attributes["src"] ?? "";
    $category =
      is_string($src) && wstg_is_first_party_script_url($src)
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
