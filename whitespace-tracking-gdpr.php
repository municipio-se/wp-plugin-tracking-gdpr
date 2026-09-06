<?php

/**
 * Plugin Name: Whitespace Tracking & GDPR
 * Description: Adds tracking for Matomo and a consent dialog for cookie law and GDPR compliance.
 * Version: 2026.9.0
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

/**
 * Display the missing ACF Pro dependency without breaking frontend requests.
 */
function whitespace_tracking_gdpr_missing_acf_notice(): void {
  if (!current_user_can("activate_plugins")) {
    return;
  } ?>
    <div class="notice notice-error">
        <p><?php esc_html_e(
          "Whitespace Tracking & GDPR requires Advanced Custom Fields PRO to be installed and active.",
          "whitespace-tracking-gdpr",
        ); ?></p>
    </div>
    <?php
}

/**
 * Load the plugin after ordinary and must-use plugins have initialized.
 *
 * ACF Pro may be installed as an ordinary plugin while this plugin is loaded as
 * a must-use plugin. Checking the ACF API when this file is first evaluated
 * would disable the plugin solely because of WordPress plugin load order.
 */
function whitespace_tracking_gdpr_bootstrap(): void {
  static $bootstrapped = false;

  if ($bootstrapped) {
    return;
  }
  $bootstrapped = true;

  if (!function_exists("acf_add_options_sub_page")) {
    add_action(
      is_network_admin() ? "network_admin_notices" : "admin_notices",
      "whitespace_tracking_gdpr_missing_acf_notice",
    );
    return;
  }

  foreach (
    glob(WHITESPACE_TRACKING_GDPR_AUTOLOAD_PATH . "/*.php") ?: []
    as $autoload_file
  ) {
    include_once $autoload_file;
  }
}

if (did_action("plugins_loaded")) {
  whitespace_tracking_gdpr_bootstrap();
} else {
  add_action("plugins_loaded", "whitespace_tracking_gdpr_bootstrap", 0);
}
