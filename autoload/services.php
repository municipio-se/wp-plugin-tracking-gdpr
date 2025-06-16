<?php

global $wstg_services;
$wstg_services = [];

function wstg_register_service($key, $service) {
  global $wstg_services;
  $service["key"] = $key;
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
  $settings ??= get_field("wstg_service_settings", "option");
  $service_key = $service["key"] ?? null;
  return $settings[$service_key]["enabled"] ?? false;
}

function wstg_get_service($key) {
  global $wstg_services;
  return $wstg_services[$key] ?? null;
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
  wstg_register_service("wstg", [
    "category" => "necessary",
  ]);
  wstg_register_service("youtube", [
    "title" => "YouTube",
    "category" => "embedded",
    "termsUrl" => "https://www.youtube.com/t/terms",
    "iframe" => [
      "parseInput" => function (string $input) {
        if (
          preg_match(
            "/^https:\/\/www\.youtube\.com\/embed\/(?<id>[a-zA-Z0-9_-]+)$/",
            $input,
            $matches,
          ) ||
          preg_match(
            "/^https:\/\/www\.youtube\.com\/watch\?v=(?<id>[a-zA-Z0-9_-]+)$/",
            $input,
            $matches,
          ) ||
          preg_match(
            "/^https:\/\/youtu\.be\/(?<id>[a-zA-Z0-9_-]+)$/",
            $input,
            $matches,
          )
        ) {
          return [
            // "id" => $matches["id"],
            "embedUrl" => "https://www.youtube.com/embed/{$matches["id"]}",
            // "embedUrl" => "https://www.youtube-nocookie.com/embed/{$matches["id"]}",
            "thumbnailUrl" => "https://i3.ytimg.com/vi/{$matches["id"]}/hqdefault.jpg",
            "standaloneUrl" => "https://www.youtube.com/watch?v={$matches["id"]}",
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
  ]);
  wstg_register_service("vimeo", [
    "title" => "Vimeo",
    "category" => "embedded",
    "termsUrl" => "https://vimeo.com/cookie_policy",
    "iframe" => [
      "parseInput" => function (string $input) {
        if (
          preg_match(
            "/^https:\/\/player\.vimeo\.com\/video\/(?<id>[0-9]+)/",
            $input,
            $matches,
          ) ||
          preg_match("/^https:\/\/vimeo\.com\/(?<id>[0-9]+)/", $input, $matches)
        ) {
          extract($matches);
          return [
            // "id" => $id,
            "embedUrl" => "https://player.vimeo.com/video/{$id}",
            // "embedUrl" => "https://player.vimeo.com/video/{$id}?dnt=1",
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
  ]);
  wstg_register_service("mediaflow", [
    "title" => "Mediaflow",
    "category" => "embedded",
    "termsUrl" => "https://www.mediaflow.com/integritetsinformation/",
    "iframe" => [
      "parseInput" => function (string $input) {
        if (
          preg_match(
            "/https:\/\/play\.(?<domain>mediaflow(?:pro))\.com\/ovp\/(?<server>\d+)\/(?<id>[a-zA-Z0-9]+)/",
            $input,
            $matches,
          )
        ) {
          extract($matches);
          return [
            // "id" => $id,
            // "server" => $server,
            "embedUrl" => "https://play.{$domain}.com/ovp/{$server}/{$id}?dnt=1",
            "thumbnailUrl" => "https://im{$server}.inviewer.se/skiss/{$id}.jpg",
            "aspectRatio" => "16/9",
          ];
        }
      },
      // "match" => "/^https:\/\/play\.mediaflow\.com\/ovp\/{$mediaflow_server_id}\/([a-zA-Z0-9]+)$/",
      // "embedUrl" => "//play.mediaflow.com/ovp/{$mediaflow_server_id}/{data-id}",
      // "thumbnailUrl" => "https://im{$mediaflow_server_id}.inviewer.se/skiss/{data-id}.jpg",
      "attributes" => [
        "allow" =>
          "accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen;",
      ],
    ],
  ]);
});

add_action(
  "acf/init",
  function () {
    $categories = wstg_get_cookie_categories();
    // $category_choices = array_combine(
    //   array_keys($categories),
    //   array_map(function ($category) {
    //     return $category["title"];
    //   }, $categories),
    // );
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
       * Filter the sub fields for a service.
       *
       * @hook wstg_service_settings_sub_fields
       * @since 0.0.0
       *
       * @param array $sub_field The service field array.
       * @param string $service_key The service key.
       * @param array $service The service array.
       * @return array The modified sub field array.
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
            "Select which services your site are using. These will be displayed in the cookie consent dialog and acceptance dialog for embedded content.",
            "whitespace-tracking-gdpr",
          ),
          "type" => "group",
          "layout" => "grid",
          "sub_fields" => $services_sub_fields,
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
