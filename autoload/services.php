<?php

global $wstg_services;
$wstg_services = [];

function wstg_register_service($slug, $service) {
  global $wstg_services;
  $service = apply_filters("wstg_register_service", $service, $slug);
  $wstg_services[$slug] = $service;
}

function wstg_get_services() {
  global $wstg_services;
  return $wstg_services;
}

function wstg_get_enabled_services() {
  $settings = get_field("wstg_cookie_categories", "option");
  $enabled_services = array_filter(
    wstg_get_services(),
    function ($service, $slug) use ($settings) {
      $category = $service["category"];
      return ($settings[$category]["enabled"] ?? false) &&
        ($settings[$category]["services"][$slug]["enabled"] ?? false);
    },
    ARRAY_FILTER_USE_BOTH,
  );
  return $enabled_services;
}

function wstg_category_is_enabled($key) {
  $settings = get_field("wstg_cookie_categories", "option");
  return $settings[$key]["enabled"] ?? false;
}

function wstg_service_is_enabled($slug) {
  $service = wstg_get_service($slug);
  if (!$service) {
    return false;
  }
  $category = $service["category"];
  return ($settings[$category]["enabled"] ?? false) &&
    ($settings[$category]["services"][$slug]["enabled"] ?? false);
}

function wstg_get_service($slug) {
  global $wstg_services;
  return $wstg_services[$slug] ?? null;
}

function wstg_get_service_by_name($name) {
  global $wstg_services;
  foreach ($wstg_services as $slug => $service) {
    if ($service["title"] === $name) {
      return $service;
    }
  }
  return null;
}

function wstg_parse_input($input) {
  $enabled_services = wstg_get_enabled_services();
  foreach ($enabled_services as $service_name => $service) {
    if (isset($service["iframe"]["parseInput"])) {
      $result = call_user_func($service["iframe"]["parseInput"], $input);
      if ($result) {
        return array_merge($result, [
          "serviceName" => $service_name,
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
            "id" => $matches["id"],
            "embedUrl" => "https://www.youtube.com/embed/{$matches["id"]}",
            // "embedUrl" => "https://www.youtube-nocookie.com/embed/{$matches["id"]}",
            "thumbnailUrl" => "https://i3.ytimg.com/vi/{$matches["id"]}/hqdefault.jpg",
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
            "id" => $id,
            "embedUrl" => "https://player.vimeo.com/video/{$id}",
            // "embedUrl" => "https://player.vimeo.com/video/{$id}?dnt=1",
            "thumbnailUrl" => "https://vumbnail.com/{$id}.jpg",
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
            "id" => $id,
            "server" => $server,
            "embedUrl" => "https://play.{$domain}.com/ovp/{$server}/{$id}?dnt=1",
            "thumbnailUrl" => "https://im{$server}.inviewer.se/skiss/{$id}.jpg",
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
