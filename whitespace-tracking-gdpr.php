<?php

/**
 * Plugin Name: Whitespace Tracking & GDPR
 * Description: Adds tracking for Matomo and cookie consent for GDPR compliance.
 * Version: 0.0.0
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
  "WHITESPACE_TRACKING_GDPR_LANGUAGES_PATH",
  plugin_basename(dirname(__FILE__)) . "/languages",
);

load_plugin_textdomain(
  "whitespace-tracking-gdpr",
  false,
  WHITESPACE_TRACKING_GDPR_LANGUAGES_PATH,
);

load_muplugin_textdomain(
  "whitespace-tracking-gdpr",
  WHITESPACE_TRACKING_GDPR_LANGUAGES_PATH,
);

array_map(static function () {
  include_once func_get_args()[0];
}, glob(WHITESPACE_TRACKING_GDPR_AUTOLOAD_PATH . "/*.php"));
