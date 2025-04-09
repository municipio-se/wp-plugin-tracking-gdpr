<?php

function wstg_get_cookie_categories() {
  return [
    "necessary" => [
      "title" => __("Necessary", "whitespace-tracking-gdpr"),
      "description" => __(
        "Necessary cookies are used to collect information about how visitors use a website.",
        "whitespace-tracking-gdpr",
      ),
      "required" => true,
    ],
    "analytics" => [
      "title" => __("Analytics", "whitespace-tracking-gdpr"),
      "description" => __(
        "Analytics cookies are used to collect information about how visitors use a website.",
        "whitespace-tracking-gdpr",
      ),
    ],
    "marketing" => [
      "title" => __("Marketing", "whitespace-tracking-gdpr"),
      "description" => __(
        "Marketing cookies are used to collect information about how visitors use a website.",
        "whitespace-tracking-gdpr",
      ),
    ],
    "embedded" => [
      "title" => __("Embedded", "whitespace-tracking-gdpr"),
      "description" => __(
        "Embedded cookies are used to collect information about how visitors use a website.",
        "whitespace-tracking-gdpr",
      ),
    ],
    "uncategorized" => [
      "title" => __("Uncategorized", "whitespace-tracking-gdpr"),
      "description" => __(
        "Uncategorized cookies are used to collect information about how visitors use a website.",
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
      $enabled = $category["required"]
        ? []
        : [
          [
            "key" => "field_wstg_cookie_category_{$category_key}_enabled",
            "name" => "enabled",
            "label" => __("Enabled", "whitespace-tracking-gdpr"),
            "type" => "true_false",
            "ui" => 1,
          ],
        ];
      $category_fields[] = [
        "key" => "field_wstg_cookie_category_{$category_key}",
        "name" => "wstg_cookie_category_{$category_key}",
        "label" => $category["title"],
        "type" => "group",
        "sub_fields" => [
          ...$enabled,
          [
            "key" => "field_wstg_cookie_category_{$category_key}_title",
            "name" => "title",
            "label" => __("Title", "whitespace-tracking-gdpr"),
            "type" => "text",
            "default_value" => $category["title"],
          ],
          [
            "key" => "field_wstg_cookie_category_{$category_key}_description",
            "name" => "description",
            "label" => __("Description", "whitespace-tracking-gdpr"),
            "type" => "wysiwyg",
            "toolbar" => "basic",
            "media_upload" => 0,
            "default_value" => $category["description"],
          ],
          [
            "key" => "field_wstg_cookie_category_{$category_key}_services",
            "name" => "services",
            "label" => __("Services", "whitespace-tracking-gdpr"),
            "type" => "repeater",
            "sub_fields" => [
              [
                "key" => "field_wstg_cookie_service_title",
                "name" => "title",
                "label" => __("Title", "whitespace-tracking-gdpr"),
                "type" => "text",
              ],
            ],
          ],
        ],
      ];
    }
    acf_add_local_field_group([
      "key" => "group_wstg_cookie_settings",
      "title" => __("Cookie Settings", "whitespace-tracking-gdpr"),
      "fields" => $category_fields,
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
