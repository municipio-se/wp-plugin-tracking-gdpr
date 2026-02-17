<?php

/**
 * Plugin Name: Whitespace Tracking & GDPR
 * Description: Adds tracking for Matomo and a consent dialog for cookie law and GDPR compliance.
 * Version: 2025.12.6
 * Author: Whitespace Dev
 * Text Domain: whitespace-tracking-gdpr
 * Domain Path: /languages/
 */

define("WHITESPACE_TRACKING_GDPR_PLUGIN_FILE", __FILE__);
define("WHITESPACE_TRACKING_GDPR_PATH", dirname(__FILE__));
define("WHITESPACE_TRACKING_GDPR_URL", rtrim(plugin_dir_url(__FILE__), "/"));
define(
  "WHITESPACE_TRACKING_GDPR_AUTOLOAD_PATH",
  WHITESPACE_TRACKING_GDPR_PATH . "/autoload",
);
define(
  "WHITESPACE_TRACKING_GDPR_IS_MU",
  strpos(WHITESPACE_TRACKING_GDPR_PATH, WPMU_PLUGIN_DIR) === 0,
);
define(
  "WHITESPACE_TRACKING_GDPR_LANGUAGES_PATH",
  plugin_basename(dirname(__FILE__)) . "/languages",
);

add_action("plugins_loaded", function () {
  if (WHITESPACE_TRACKING_GDPR_IS_MU) {
    load_muplugin_textdomain(
      "whitespace-tracking-gdpr",
      WHITESPACE_TRACKING_GDPR_LANGUAGES_PATH,
    );
  } else {
    load_plugin_textdomain(
      "whitespace-tracking-gdpr",
      false,
      WHITESPACE_TRACKING_GDPR_LANGUAGES_PATH,
    );
  }
});

// Check for ACF Pro requirements
if (!function_exists("acf_add_options_sub_page")) {
  add_action("admin_notices", function () {
    ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e(
              "Whitespace Tracking & GDPR requires Advanced Custom Fields PRO to be installed and active.",
              "whitespace-tracking-gdpr",
            ); ?></p>
        </div>
        <?php
  });
  return;
}

array_map(static function () {
  include_once func_get_args()[0];
}, glob(WHITESPACE_TRACKING_GDPR_AUTOLOAD_PATH . "/*.php"));
