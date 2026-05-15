<?php

global $wstg_services;
$wstg_services = [];

function wstg_register_service($key, $service) {
  global $wstg_services;
  $service["key"] = $key;
  /**
   * Filters a service definition before it is stored in the registry.
   *
   * @param array $service Service definition.
   * @param string $key Service key.
   * @return array Filtered service definition.
   */
  $service = apply_filters("wstg_register_service", $service, $key);
  $wstg_services[$key] = $service;
}

function wstg_get_services() {
  global $wstg_services;
  return $wstg_services;
}

function wstg_get_enabled_services() {
  $settings = get_field("wstg_service_settings", "option");
  $available_services = wstg_get_services();
  $enabled_services = array_filter(
    $available_services,
    fn($service) => wstg_service_is_enabled($service, $settings),
  );
  return $enabled_services;
}

function wstg_service_is_enabled($service, $settings = null) {
  $service = is_string($service) ? wstg_get_service($service) : $service;
  if (!$service) {
    return false;
  }
  if ($service["category"] === "necessary") {
    return true; // Necessary services are always enabled
  }
  $settings ??= get_field("wstg_service_settings", "option");
  $service_key = $service["key"] ?? null;
  return $settings[$service_key]["enabled"] ?? false;
}

function wstg_get_service($key) {
  global $wstg_services;
  return $wstg_services[$key] ?? null;
}

function wstg_normalize_embed_url($input) {
  if (str_starts_with($input, "//")) {
    return "https:{$input}";
  }
  return $input;
}

function wstg_parse_url_components($input) {
  $parts = parse_url(wstg_normalize_embed_url($input));
  if (!is_array($parts)) {
    return null;
  }
  return [
    "host" => strtolower($parts["host"] ?? ""),
    "path" => $parts["path"] ?? "",
    "query" => $parts["query"] ?? "",
  ];
}

function wstg_host_matches($host, $domain) {
  return $host === $domain || str_ends_with($host, ".{$domain}");
}

function wstg_parse_youtube_embed_id($input) {
  $parts = wstg_parse_url_components($input);
  if (!$parts || !$parts["host"] || !$parts["path"]) {
    return null;
  }

  if (wstg_host_matches($parts["host"], "youtu.be")) {
    if (preg_match("#^/(?<id>[a-zA-Z0-9_-]+)(?:/|$)#", $parts["path"], $matches)) {
      return $matches["id"];
    }
    return null;
  }

  if (
    !wstg_host_matches($parts["host"], "youtube.com") &&
    !wstg_host_matches($parts["host"], "youtube-nocookie.com")
  ) {
    return null;
  }

  if (
    preg_match(
      "#^/(?:embed|shorts|live|v)/(?<id>[a-zA-Z0-9_-]+)(?:/|$)#",
      $parts["path"],
      $matches,
    )
  ) {
    return $matches["id"];
  }

  if ($parts["path"] !== "/watch" && $parts["path"] !== "/watch/") {
    return null;
  }

  parse_str($parts["query"], $queryParameters);
  $id = $queryParameters["v"] ?? null;
  if (!is_string($id)) {
    return null;
  }

  if (preg_match("/^[a-zA-Z0-9_-]+$/", $id)) {
    return $id;
  }

  return null;
}

function wstg_parse_vimeo_embed_id($input) {
  $parts = wstg_parse_url_components($input);
  if (!$parts || !$parts["host"] || !$parts["path"]) {
    return null;
  }

  if (wstg_host_matches($parts["host"], "player.vimeo.com")) {
    if (
      preg_match(
        "#^/video/(?<id>[0-9]+)(?:/|$)#",
        $parts["path"],
        $matches,
      )
    ) {
      return $matches["id"];
    }
    return null;
  }

  if (!wstg_host_matches($parts["host"], "vimeo.com")) {
    return null;
  }

  if (preg_match("#^/(?<id>[0-9]+)(?:/|$)#", $parts["path"], $matches)) {
    return $matches["id"];
  }

  return null;
}

function wstg_parse_mediaflow_embed($input) {
  $parts = wstg_parse_url_components($input);
  if (!$parts || !$parts["host"] || !$parts["path"]) {
    return null;
  }

  if (
    !preg_match(
      "/^play\.(?<domain>mediaflow(?:pro)?)\.com$/",
      $parts["host"],
      $hostMatches,
    )
  ) {
    return null;
  }

  if (
    !preg_match(
      "#^/ovp/(?<server>\d+)/(?<id>[a-zA-Z0-9]+)(?:/|$)#",
      $parts["path"],
      $pathMatches,
    )
  ) {
    return null;
  }

  return [
    "domain" => $hostMatches["domain"],
    "server" => $pathMatches["server"],
    "id" => $pathMatches["id"],
  ];
}

function wstg_is_mediaflow_embed_wrapper(\DiDom\Element $node) {
  if (strtolower($node->tagName()) !== "div") {
    return false;
  }

  $iframes = $node->find("iframe");
  if (count($iframes) !== 1) {
    return false;
  }

  $images = $node->find("img");
  if (count($images) < 1) {
    return false;
  }

  return trim($node->text()) === "";
}

function wstg_parse_input($input) {
  $enabled_services = wstg_get_enabled_services();
  foreach ($enabled_services as $service_key => $service) {
    if (isset($service["iframe"]["parseInput"])) {
      $result = call_user_func($service["iframe"]["parseInput"], $input);
      if ($result) {
        return array_merge($result, [
          "serviceKey" => $service_key,
          "service" => $service,
          "attributes" => $service["iframe"]["attributes"] ?? [],
        ]);
      }
    }
  }
  // If valid URL but no service found, return the URL as is
  if (filter_var($input, FILTER_VALIDATE_URL)) {
    return [
      "embedUrl" => $input,
    ];
  }
  return null;
}

add_action("plugins_loaded", function () {
  /**
   * Runs when services should register themselves with wstg_register_service().
   */
  do_action("wstg_register_services");
});

add_action(
  "wstg_register_services",
  function () {
    wstg_register_service("wstg", [
      "category" => "necessary",
    ]);
    wstg_register_service("youtube", [
      "title" => "YouTube",
      "category" => "embedded",
      "termsUrl" => "https://www.youtube.com/t/terms",
      "iframe" => [
        "parseInput" => function (string $input) {
          $id = wstg_parse_youtube_embed_id($input);
          if ($id) {
            return [
              "id" => $id,
              "embedUrl" => "https://www.youtube.com/embed/{$id}",
              "thumbnailUrl" => "https://i3.ytimg.com/vi/{$id}/hqdefault.jpg",
              "standaloneUrl" => "https://www.youtube.com/watch?v={$id}",
              "aspectRatio" => "16/9",
            ];
          }
        },
        "attributes" => [
          "allow" =>
            "accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen;",
        ],
      ],
      "cookies" => [
        [
          // "name" => "/^/",
          "domain" => "youtube.com",
        ],
      ],
      "csp" => [
        "frame-src" => [
          "youtube.com",
          "*.youtube.com",
          "youtube-nocookie.com",
          "*.youtube-nocookie.com",
          "youtu.be",
          "*.youtu.be",
        ],
      ],
    ]);
    wstg_register_service("vimeo", [
      "title" => "Vimeo",
      "category" => "embedded",
      "termsUrl" => "https://vimeo.com/cookie_policy",
      "iframe" => [
        "parseInput" => function (string $input) {
          $id = wstg_parse_vimeo_embed_id($input);
          if ($id) {
            return [
              "id" => $id,
              "embedUrl" => "https://player.vimeo.com/video/{$id}",
              "thumbnailUrl" => "https://vumbnail.com/{$id}.jpg",
              "standaloneUrl" => "https://vimeo.com/{$id}",
              "aspectRatio" => "16/9",
            ];
          }
        },
        "attributes" => [
          "allow" =>
            "accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen;",
        ],
      ],
      "cookies" => [
        [
          // "name" => "/^/",
          "domain" => "vimeo.com",
        ],
      ],
      "csp" => [
        "frame-src" => ["vimeo.com", "*.vimeo.com"],
      ],
    ]);
    wstg_register_service("mediaflow", [
      "title" => "Mediaflow",
      "category" => "embedded",
      "termsUrl" => "https://www.mediaflow.com/integritetsinformation/",
      "iframe" => [
        "parseInput" => function (string $input) {
          $mediaflow = wstg_parse_mediaflow_embed($input);
          if ($mediaflow) {
            $domain = $mediaflow["domain"];
            $server = $mediaflow["server"];
            $id = $mediaflow["id"];
            return [
              "id" => $id,
              "embedUrl" => "https://play.{$domain}.com/ovp/{$server}/{$id}?dnt=1",
              "thumbnailUrl" => "https://im{$server}.inviewer.se/skiss/{$id}.jpg",
              "aspectRatio" => "16/9",
            ];
          }
        },
        // Mediaflow often wraps iframe embeds in image preview containers. Replace
        // the highest matching wrapper so preview images do not remain in output.
        "replacementTarget" => function (
          \DiDom\Element $iframeNode,
          array $context,
        ): \DiDom\Element {
          $target = $iframeNode;
          $parent = $iframeNode->parent();

          while (
            $parent instanceof \DiDom\Element &&
            wstg_is_mediaflow_embed_wrapper($parent)
          ) {
            $target = $parent;
            $parent = $parent->parent();
          }

          return $target;
        },
        // "match" => "/^https:\/\/play\.mediaflow\.com\/ovp\/{$mediaflow_server_id}\/([a-zA-Z0-9]+)$/",
        // "embedUrl" => "//play.mediaflow.com/ovp/{$mediaflow_server_id}/{data-id}",
        // "thumbnailUrl" => "https://im{$mediaflow_server_id}.inviewer.se/skiss/{data-id}.jpg",
        "attributes" => [
          "allow" =>
            "accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen;",
        ],
      ],
      "csp" => [
        "frame-src" => ["play.mediaflow.com", "play.mediaflowpro.com"],
      ],
    ]);
    wstg_register_service("visma-recruit", [
      "title" => "Visma Recruit",
      "category" => "embedded",
      "iframe" => [
        "parseInput" => function (string $input) {
          if (strpos($input, "https://recruit.visma.com/External/") === 0) {
            return [
              "embedUrl" => $input,
            ];
          }
        },
      ],
      "csp" => [
        "frame-src" => ["recruit.visma.com"],
      ],
    ]);
  },
  9,
);

// add_action(
//   "plugins_loaded",
//   function () {
//     wstg_register_service("unknown", [
//       "title" => __("unknown services"),
//       "category" => "embedded",
//       "iframe" => [
//         "parseInput" => function (string $input) {
//           // Accepts any valid URL
//           if (filter_var($input, FILTER_VALIDATE_URL)) {
//             return [
//               "embedUrl" => $input,
//             ];
//           }
//         },
//         "attributes" => [
//           "allow" =>
//             "accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen;",
//         ],
//       ],
//     ]);
//   },
//   20,
// );

add_action(
  "acf/init",
  function () {
    $services_sub_fields = [];
    $services = wstg_get_services();
    foreach ($services as $service_key => $service) {
      if ($service["category"] === "necessary") {
        continue;
      }
      $service_field = [
        "key" => "field_wstg_service_{$service_key}",
        "name" => "{$service_key}",
        "label" => $service["title"],
        "instructions" => $service["category"],
        "type" => "group",
        "sub_fields" => [],
        "layout" => "block",
      ];
      $service_field["sub_fields"][] = [
        "key" => "field_wstg_service_{$service_key}_enabled",
        "name" => "enabled",
        "label" => _x(
          "Enabled",
          "Service Sub Field Label",
          "whitespace-tracking-gdpr",
        ),
        "type" => "true_false",
        "ui" => 1,
        "wrapper" => [
          "width" => "25",
        ],
      ];
      // $service_field["sub_fields"][] = [
      //   "key" => "field_wstg_service_{$service_key}_category",
      //   "name" => "category",
      //   "label" => _x(
      //     "Category",
      //     "Service Sub Field Label",
      //     "whitespace-tracking-gdpr",
      //   ),
      //   "type" => "message",
      //   "message" => $categories[$service["category"]]["title"],
      //   "wrapper" => [
      //     "width" => "25",
      //   ],
      // ];
      // $service_field["sub_fields"][] = [
      //   "key" => "field_wstg_service_{$service_key}_category",
      //   "name" => "category",
      //   "label" => _x(
      //     "Category",
      //     "Service Sub Field Label",
      //     "whitespace-tracking-gdpr",
      //   ),
      //   "type" => "select",
      //   "choices" => $category_choices,
      //   "wrapper" => [
      //     "width" => "25",
      //   ],
      //   "disabled" => 1,
      // ];

      /**
       * Filters the ACF settings sub fields generated for a registered service.
       *
       * @param array $sub_fields Service settings sub fields.
       * @param string $service_key Service key.
       * @param array $service Service definition.
       * @return array Filtered service settings sub fields.
       */
      $service_field["sub_fields"] = apply_filters(
        "wstg_service_settings_sub_fields",
        $service_field["sub_fields"],
        $service_key,
        $service,
      );
      $services_sub_fields[] = $service_field;
    }
    acf_add_local_field_group([
      "key" => "group_wstg_service_settings",
      "title" => __("Services", "whitespace-tracking-gdpr"),
      "fields" => [
        [
          "key" => "field_wstg_service_settings",
          "name" => "wstg_service_settings",
          "label" => __(
            "Enabled services and settings",
            "whitespace-tracking-gdpr",
          ),
          "instructions" => __(
            "Select which services your site is using. These will be displayed in the cookie and tracking consent dialog.",
            "whitespace-tracking-gdpr",
          ),
          "type" => "group",
          "layout" => "grid",
          "sub_fields" => $services_sub_fields,
        ],
        [
          "key" => "field_wstg_allow_any_embedded_content",
          "name" => "wstg_allow_any_embedded_content",
          "label" => __(
            "Allow any embedded content",
            "whitespace-tracking-gdpr",
          ),
          "instructions" => __(
            "⚠️ Warning: This could violate cookie law compliance. Only enable this if you know what you are doing. Please report any missing services to the plugin author.",
            "whitespace-tracking-gdpr",
          ),
          "type" => "true_false",
          "ui" => 1,
        ],
      ],
      "location" => [
        [
          [
            "param" => "options_page",
            "operator" => "==",
            "value" => "acf-options-mx-tracking",
          ],
        ],
      ],
      "menu_order" => 11,
    ]);
  },
  11,
);
