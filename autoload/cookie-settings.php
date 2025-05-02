<?php

function wstg_get_cookie_categories() {
  return [
    "necessary" => [
      "title" => _x(
        "Necessary",
        "Cookie Category Title",
        "whitespace-tracking-gdpr",
      ),
      // "description" => __(
      //   "Necessary cookies are used to collect information about how visitors use a website.",
      //   "whitespace-tracking-gdpr",
      // ),
      "required" => true,
      "supports_services" => false,
    ],
    "analytics" => [
      "title" => _x(
        "Analytics",
        "Cookie Category Title",
        "whitespace-tracking-gdpr",
      ),
      "description" => __(
        "Analytics cookies are used to collect information about how visitors use the website.",
        "whitespace-tracking-gdpr",
      ),
    ],
    "marketing" => [
      "title" => _x(
        "Marketing",
        "Cookie Category Title",
        "whitespace-tracking-gdpr",
      ),
      "description" => __(
        "Marketing cookies are used to collect information about how visitors use a website.",
        "whitespace-tracking-gdpr",
      ),
    ],
    "embedded" => [
      "title" => _x(
        "Embedded",
        "Cookie Category Title",
        "whitespace-tracking-gdpr",
      ),
      "description" => __(
        "Cookies that are set by third parties when we embed content from different services.",
        "whitespace-tracking-gdpr",
      ),
    ],
    "uncategorized" => [
      "title" => _x(
        "Uncategorized",
        "Cookie Category Title",
        "whitespace-tracking-gdpr",
      ),
      "description" => __(
        "These cookies have not been categorized yet.",
        "whitespace-tracking-gdpr",
      ),
      "required" => true,
    ],
  ];
}

add_action(
  "acf/init",
  function () {
    $category_fields = [];
    foreach (wstg_get_cookie_categories() as $category_key => $category) {
      if ($category["required"]) {
        continue;
      }
      $category_field = [
        "key" => "field_wstg_cookie_category_{$category_key}",
        "name" => "{$category_key}",
        "label" => $category["title"],
        "type" => "group",
        "sub_fields" => [],
        "layout" => "block",
      ];
      $category_field["sub_fields"][] = [
        "key" => "field_wstg_cookie_category_{$category_key}_enabled",
        "name" => "enabled",
        "label" => _x(
          "Enabled",
          "Category Sub Field Label",
          "whitespace-tracking-gdpr",
        ),
        "type" => "true_false",
        "ui" => 1,
      ];
      $services = wstg_get_services();
      $services = array_filter($services, function ($service) use (
        $category_key,
      ) {
        return $service["category"] === $category_key;
      });
      $service_fields = [];
      foreach ($services as $service_key => $service) {
        $service_field = [
          "key" => "field_wstg_cookie_service_{$service_key}",
          "name" => "{$service_key}",
          "label" => $service["title"],
          "type" => "group",
          "sub_fields" => [],
          "layout" => "block",
        ];
        $service_field["sub_fields"][] = [
          "key" => "field_wstg_cookie_service_{$service_key}_enabled",
          "name" => "enabled",
          "label" => _x(
            "Enabled",
            "Service Sub Field Label",
            "whitespace-tracking-gdpr",
          ),
          "type" => "true_false",
          "ui" => 1,
        ];
        $service_fields[] = $service_field;
      }
      if ($service_fields) {
        $category_field["sub_fields"][] = [
          "key" => "field_wstg_cookie_category_{$category_key}_services",
          "name" => "services",
          "label" => __("Services", "whitespace-tracking-gdpr"),
          "instructions" => __(
            "Are you missing some service that you are using? Contact a developer to have it added.",
            "whitespace-tracking-gdpr",
          ),
          "type" => "group",
          "layout" => "row",
          "sub_fields" => $service_fields,
          "conditional_logic" => [
            [
              [
                "field" => "field_wstg_cookie_category_{$category_key}_enabled",
                "operator" => "==",
                "value" => 1,
              ],
            ],
          ],
        ];
      }
      $category_fields[] = $category_field;
    }
    acf_add_local_field_group([
      "key" => "group_wstg_cookie_settings",
      "title" => __("Cookie Settings", "whitespace-tracking-gdpr"),
      "fields" => [
        [
          "key" => "field_wstg_cookie_categories",
          "name" => "wstg_cookie_categories",
          "label" => __("Categories and Services", "whitespace-tracking-gdpr"),
          "type" => "group",
          "sub_fields" => $category_fields,
          "layout" => "row",
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
