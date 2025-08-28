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
    $service_settings = get_field("wstg_service_settings", "option");
    // $categories_settings = get_field("wstg_cookie_categories", "option");
    $services = wstg_get_enabled_services();
    $settings = [];
    // foreach ($categories as $category_key => $category) {
    //   $settings["categories"][$category_key] = $category;
    // }
    foreach ($services as $service_key => $service) {
      $category_key = $service["category"];
      if (!isset($settings["categories"][$category_key])) {
        if (!isset($categories[$category_key])) {
          continue;
        }
        $settings["categories"][$category_key] = $categories[$category_key];
      }
      $settings["categories"][$category_key]["services"][$service_key] =
        $service + ($service_settings[$service_key] ?? []);
    }

    $matomo_url = mx_get_matomo_option("url");
    $matomo_container_id = mx_get_matomo_option("container_id");
    $matomo_site_id = mx_get_matomo_option("site_id");
    if ($matomo_url && ($matomo_container_id || $matomo_site_id)) {
      $settings["categories"]["analytics"] = $categories["analytics"];
    }

    $translations = get_field("wstg_translations", "option");
    // Find the translation that matches the current locale
    $translation = null;
    $locale = get_locale();
    foreach ($translations as $translation_item) {
      if ($translation_item["locale"] === $locale) {
        $translation = $translation_item;
        break;
      }
    }
    if ($translation === null) {
      // Fallback to the first translation
      $translation = [
        "locale" => $locale,
        "description" => get_field_object(
          "field_wstg_translations_consent_modal_description",
          "option",
        )["default_value"],
      ];
    }

    $settings["language"] = $translation["locale"];
    $settings["translation"] = [
      "consentModal" => [
        "title" =>
          $translation["consentModal"]["title"] ?:
          get_field_object(
            "field_wstg_translations_consent_modal_title",
            "option",
          )["placeholder"],

        "description" =>
          $translation["consentModal"]["description"] ??
          get_field_object(
            "field_wstg_translations_consent_modal_description",
            "option",
          )["default_value"],

        "acceptAllBtn" => _x(
          "Accept all",
          "Cookie Modal Button Label",
          "whitespace-tracking-gdpr",
        ),

        "acceptNecessaryBtn" => _x(
          "Accept necessary",
          "Cookie Modal Button Label",
          "whitespace-tracking-gdpr",
        ),

        "showPreferencesBtn" => _x(
          "Manage cookie preferences",
          "Cookie Modal Button Label",
          "whitespace-tracking-gdpr",
        ),
      ],
      "preferencesModal" => [
        "title" =>
          $translation["preferencesModal"]["title"] ?:
          get_field_object(
            "field_wstg_translations_preferences_modal_title",
            "option",
          )["placeholder"],

        "description" =>
          $translation["preferencesModal"]["description"] ??
          get_field_object(
            "field_wstg_translations_preferences_modal_description",
            "option",
          )["default_value"],

        "acceptAllBtn" => _x(
          "Accept all",
          "Cookie Modal Button Label",
          "whitespace-tracking-gdpr",
        ),

        "acceptNecessaryBtn" => _x(
          "Accept necessary",
          "Cookie Modal Button Label",
          "whitespace-tracking-gdpr",
        ),

        "savePreferencesBtn" => _x(
          "Save current choices",
          "Cookie Modal Button Label",
          "whitespace-tracking-gdpr",
        ),

        "closeIconLabel" => _x(
          "Close cookie consent dialog",
          "Cookie Modal Button Label",
          "whitespace-tracking-gdpr",
        ),
      ],
    ];

    wp_localize_script(
      "whitespace-tracking-gdpr",
      "whitespaceTrackingGdpr",
      $settings,
    );
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
    if ($attributes["id"] == "whitespace-tracking-gdpr-js") {
      // $attributes["data-category"] = "uncategorized";
      $attributes["type"] = "module";
    }
    return $attributes;
  },
  10,
  2,
);

/**
 * Adds ACF fields for translations
 */
add_action(
  "acf/init",
  function () {
    // if (!function_exists("acf_add_local_field_group")) {
    //   return;
    // }
    /*
      interface ConsentModalOptions {

        label?: string

        title?: string
        description?: string
        acceptAllBtn?: string
        acceptNecessaryBtn?: string
        showPreferencesBtn?: string

        closeIconLabel?: string

        revisionMessage?: string

        footer?: string
      }
      interface PreferencesModalOptions {
        title?: string
        acceptAllBtn?: string
        acceptNecessaryBtn?: string
        savePreferencesBtn?: string

        closeIconLabel?: string

        serviceCounterLabel?: string

        sections: Section[]
      }
    */
    acf_add_local_field_group([
      "key" => "group_wstg_translations",
      "title" => __("Translations", "whitespace-tracking-gdpr"),
      "fields" => [
        [
          "key" => "field_wstg_translations",
          "label" => __("Translations", "whitespace-tracking-gdpr"),
          "name" => "translations",
          "type" => "repeater",
          "layout" => "row",
          "sub_fields" => [
            [
              "key" => "field_wstg_translations_locale",
              "label" => __("Language", "whitespace-tracking-gdpr"),
              "name" => "locale",
              "type" => "select",
              "choices" => get_available_languages(),
            ],
            [
              "key" => "field_wstg_translations_consent_modal",
              "label" => __("Consent dialog", "whitespace-tracking-gdpr"),
              "name" => "consent_modal",
              "type" => "group",
              "sub_fields" => [
                [
                  "key" => "field_wstg_translations_consent_modal_title",
                  "label" => __("Title", "whitespace-tracking-gdpr"),
                  "name" => "title",
                  "type" => "text",
                  "placeholder" => _x(
                    "Cookie consent",
                    "Cookie Modal Title",
                    "whitespace-tracking-gdpr",
                  ),
                ],
                [
                  "key" => "field_wstg_translations_consent_modal_description",
                  "label" => __("Description", "whitespace-tracking-gdpr"),
                  "name" => "description",
                  "type" => "wysiwyg",
                  "toolbar" => "basic",
                  "media_upload" => 0,
                  "default_value" => _x(
                    "We use cookies to improve your experience on our site.",
                    "Cookie Modal Description",
                    "whitespace-tracking-gdpr",
                  ),
                ],
                // [
                //   "key" =>
                //     "field_wstg_translations_consent_modal_accept_all_btn",
                //   "label" => __(
                //     "Accept all button",
                //     "whitespace-tracking-gdpr",
                //   ),
                //   "name" => "accept_all_btn",
                //   "type" => "text",
                // ],
                // [
                //   "key" =>
                //     "field_wstg_translations_consent_modal_accept_necessary_btn",
                //   "label" => __(
                //     "Accept necessary button",
                //     "whitespace-tracking-gdpr",
                //   ),
                //   "name" => "accept_necessary_btn",
                //   "type" => "text",
                // ],
                // [
                //   "key" =>
                //     "field_wstg_translations_consent_modal_show_preferences_btn",
                //   "label" => __(
                //     "Show preferences button",
                //     "whitespace-tracking-gdpr",
                //   ),
                //   "name" => "show_preferences_btn",
                //   "type" => "text",
                // ],
              ],
            ],
            [
              "key" => "field_wstg_translations_preferences_modal",
              "label" => __("Preferences dialog", "whitespace-tracking-gdpr"),
              "name" => "preferences_modal",
              "type" => "group",
              "sub_fields" => [
                [
                  "key" => "field_wstg_translations_preferences_modal_title",
                  "label" => __("Title", "whitespace-tracking-gdpr"),
                  "name" => "title",
                  "type" => "text",
                  "placeholder" => _x(
                    "Manage cookie preferences",
                    "Cookie Modal Title",
                    "whitespace-tracking-gdpr",
                  ),
                ],
                [
                  "key" =>
                    "field_wstg_translations_preferences_modal_description",
                  "label" => __("Description", "whitespace-tracking-gdpr"),
                  "name" => "description",
                  "type" => "wysiwyg",
                  "toolbar" => "basic",
                  "media_upload" => 0,
                  "default_value" => _x(
                    "You can manage your cookie preferences here.",
                    "Cookie Modal Description",
                    "whitespace-tracking-gdpr",
                  ),
                ],
              ],
            ],
          ],
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
      "menu_order" => 12,
    ]);
  },
  12,
);

// Add "Tracking and GDPR" chooser in Appearance → Menus
add_action("admin_head-nav-menus.php", function () {
  add_meta_box(
    "wstg-menu-items",
    __("Tracking and GDPR"),
    function () {
      ?>
      <div id="posttype-cookie" class="posttypediv">
        <div id="tabs-panel-cookie" class="tabs-panel tabs-panel-active">
          <ul id="cookie-checklist" class="categorychecklist form-no-clear">
            <li>
              <label class="menu-item-title">
                <input
                  type="checkbox"
                  class="menu-item-checkbox"
                  name="menu-item[-1][menu-item-object-id]"
                  value="-1"
                >
                <?php _e(
                  "Change cookie preferences",
                  "whitespace-tracking-gdpr",
                ); ?>
              </label>

              <input type="hidden" name="menu-item[-1][menu-item-type]" value="custom">
              <input type="hidden" name="menu-item[-1][menu-item-title]" value="<?php esc_attr_e(
                "Change cookie preferences",
                "whitespace-tracking-gdpr",
              ); ?>">
              <input type="hidden" name="menu-item[-1][menu-item-url]" value="#">
              <input type="hidden" name="menu-item[-1][menu-item-classes]" value="wstg-trigger-cookie-dialog">
            </li>
          </ul>
        </div>

        <?php wp_nonce_field("add-menu_item", "menu-settings-column-nonce"); ?>
        <p class="button-controls wp-clearfix">
          <span class="list-controls">
            <a href="#" class="select-all"><?php _e("Select All"); ?></a>
          </span>
          <span class="add-to-menu">
            <input
              type="submit"
              class="button submit-add-to-menu right"
              id="submit-posttype-cookie"
              name="add-post-type-menu-item"
              value="<?php esc_attr_e("Add to Menu"); ?>"
            >
            <span class="spinner"></span>
          </span>
        </p>
      </div>
      <?php
    },
    "nav-menus",
    "side",
    "default",
  );
});

// Show a nicer “type” label inside the menu editor
add_filter("wp_setup_nav_menu_item", function ($item) {
  if (
    is_array($item->classes) &&
    in_array("wstg-trigger-cookie-dialog", $item->classes, true)
  ) {
    $item->type_label = __("Tracking and GDPR");
  }
  return $item;
});

add_filter(
  "walker_nav_menu_start_el",
  function ($output, $item, $depth, $args) {
    $classes = is_array($item->classes) ? $item->classes : [];
    if (in_array("wstg-trigger-cookie-dialog", $classes, true)) {
      $output = apply_filters(
        "wstg_trigger_cookie_dialog_menu_item",
        $output,
        $item,
        $depth,
        $args,
      );
      // $label = esc_html($item->title ?: __("Cookie settings"));
      // // Return a real button instead of the default <a>
      // return '<button type="button" class="menu-item wstg-trigger-cookie-dialog" data-wstg-trigger-cookie-dialog>' .
      //   $label .
      //   "</button>";
    }
    return $output;
  },
  10,
  4,
);
