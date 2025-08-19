<?php

add_action("acf/init", function () {
  // Add subpage for Tracking options
  acf_add_options_sub_page([
    "page_title" => _x(
      "Tracking",
      "Options Page Title",
      "whitespace-tracking-gdpr",
    ),
    "menu_title" => _x(
      "Tracking",
      "Options Page Menu Title",
      "whitespace-tracking-gdpr",
    ),
    "parent_slug" => "options-general.php",
    "menu_slug" => "acf-options-mx-tracking",
    "capability" => "manage_options",
    "autoload" => true,
  ]);

  acf_add_local_field_group([
    "key" => "group_mx_matomo",
    "title" => __("Matomo", "whitespace-tracking-gdpr"),
    "fields" => [
      [
        "key" => "field_mx_matomo_url",
        "label" => __("URL", "whitespace-tracking-gdpr"),
        "name" => "mx_matomo_url",
        "type" => "text",
        "instructions" => __(
          "The URL for the Matomo Tag Manager.",
          "whitespace-tracking-gdpr",
        ),
        "constant" => "MATOMO_URL",
      ],
      [
        "key" => "field_mx_matomo_container_id",
        "label" => __("Container ID", "whitespace-tracking-gdpr"),
        "name" => "mx_matomo_container_id",
        "type" => "text",
        // "instructions" => __(
        //   "The container ID for the Matomo Tag Manager.",
        //   "whitespace-tracking-gdpr",
        // ),
        "constant" => "MATOMO_CONTAINER_ID",
      ],
      [
        "key" => "field_mx_matomo_site_id",
        "label" => __("Site ID", "whitespace-tracking-gdpr"),
        "name" => "mx_matomo_site_id",
        "type" => "text",
        "instructions" => __(
          "The site ID for the Matomo Tag Manager.",
          "whitespace-tracking-gdpr",
        ),
        "constant" => "MATOMO_SITE_ID",
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
  ]);
});

add_filter("acf/prepare_field", function ($field) {
  if (
    $field["constant"] &&
    defined($field["constant"]) &&
    constant($field["constant"])
  ) {
    $field["value"] = constant($field["constant"]);
    $field["disabled"] = true;
    $field["instructions"] =
      (empty($field["instructions"]) ? "" : $field["instructions"] . "\n") .
      "<i>" .
      sprintf(
        /*
        Translators: This message is shown when a field is disabled because its value is defined as a constant in the code.
        */
        __(
          "This field is disabled because the constant %s has been defined in code.",
          "whitespace-tracking-gdpr",
        ),
        "<code>{$field["constant"]}</code>",
      ) .
      "</i>";
  }
  return $field;
});

function mx_matomo_option_is_constant($option) {
  $constant = "MATOMO_" . strtoupper($option);
  return defined($constant) && constant($constant);
}

function mx_get_matomo_option($option) {
  $constant = "MATOMO_" . strtoupper($option);
  if (defined($constant) && constant($constant)) {
    return constant($constant);
  }
  return get_field("mx_matomo_" . $option, "option");
}
