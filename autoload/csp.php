<?php

use WhitespaceTrackingGdpr\Csp;

global $wstg_csp;

function wstg_csp() {
  global $wstg_csp;
  if (!$wstg_csp) {
    $wstg_csp = new Csp();
  }
  return $wstg_csp;
}

function wstg_csp_allow($fetchDirective, $sources) {
  $csp = wstg_csp();
  $csp->allow($fetchDirective, $sources);
}

function wstg_csp_deny($fetchDirective, $sources) {
  $csp = wstg_csp();
  $csp->deny($fetchDirective, $sources);
}

function wstg_csp_inherit($fetchDirective, $sources) {
  $csp = wstg_csp();
  $csp->inherit($fetchDirective, $sources);
}

function wstg_csp_nonce() {
  $csp = wstg_csp();
  return $csp->getNonce();
}

/*
Should result in something like this:

connect-src 'self';
default-src 'self' data:;
img-src 'self' data: https:;
script-src-elem 'strict-dynamic' 'nonce-xxx';
script-src-attr 'self' 'unsafe-inline';
style-src 'self' 'unsafe-inline';
frame-ancestors 'self'
*/
add_action("send_headers", function () {
  if (is_admin()) {
    return; // Do not apply CSP in admin area
  }
  $csp = wstg_csp();
  $csp->allow("default-src", "data:");
  $csp->deny("script-src", "data:");
  $csp->deny("frame-src", "data:");
  $csp->allow("img-src", "https:");
  header("Content-Security-Policy: " . $csp);
});

/**
 * Adds fields for adding additional allowed frame-src sources to the CSP
 */
add_action(
  "acf/init",
  function () {
    acf_add_local_field_group([
      "key" => "group_wstg_csp_settings",
      "title" => __("Content Security Policy", "whitespace-tracking-gdpr"),
      "fields" => [
        [
          "key" => "field_wstg_csp_settings_allowed_frame_src",
          "name" => "wstg_csp_settings_allowed_frame_src",
          "label" => __(
            "Allowed iframe hosts (for the CSP <i>frame-src</i> directive)",
            "whitespace-tracking-gdpr",
          ),
          "instructions" => __("One per line.", "whitespace-tracking-gdpr"),
          "type" => "textarea",
          "rows" => 10,
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

add_action("init", function () {
  $input = get_field("wstg_csp_settings_allowed_frame_src", "option");
  preg_match_all("/^\s*\S+\s*$/im", $input, $allowed_frame_src);
  $allowed_frame_src = array_column($allowed_frame_src, 0);
  $allowed_frame_src = array_map("trim", $allowed_frame_src);
  $allowed_frame_src = array_filter($allowed_frame_src);
  $allowed_frame_src = array_unique($allowed_frame_src);
  foreach ($allowed_frame_src as $source) {
    wstg_csp_allow("frame-src", $source);
  }
});
