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

/**
 * Return CSP hashes only for known, non-executable or validated Municipio
 * inline output.
 *
 * Unknown inline code, including custom-code fields, must remain blocked. A
 * blanket hash of every rendered script would make injected markup trusted.
 */
function wstg_csp_get_trusted_inline_script_hashes(string $markup): array {
  preg_match_all(
    "/<script\\b([^>]*)>(.*?)<\\/script\\s*>/is",
    $markup,
    $matches,
    PREG_SET_ORDER,
  );

  $hashes = [];
  foreach ($matches as $match) {
    $attributes = $match[1];
    $script = $match[2];

    if (
      !is_string($attributes) ||
      !is_string($script) ||
      trim($script) === "" ||
      preg_match("/\\bsrc\\s*=/i", $attributes)
    ) {
      continue;
    }

    $trusted = false;
    if (trim($attributes) === "") {
      $trusted =
        preg_match(
          "/^\\s*var ajaxurl = '([^']+)';\\s*$/",
          $script,
          $ajax_url,
        ) === 1 &&
        hash_equals(
          admin_url("admin-ajax.php"),
          html_entity_decode($ajax_url[1], ENT_QUOTES | ENT_HTML5, "UTF-8"),
        );
    }

    if (
      preg_match(
        '/\\btype\\s*=\\s*(["\'])application\\/ld\\+json\\1/i',
        $attributes,
      ) === 1
    ) {
      json_decode(trim($script), true);
      $trusted = json_last_error() === JSON_ERROR_NONE;
    }

    if (!$trusted) {
      continue;
    }

    $hash = base64_encode(hash("sha256", $script, true));
    $hashes["'sha256-{$hash}'"] = true;
  }

  return array_keys($hashes);
}

/**
 * Send Tracking GDPR's CSP once all applicable sources have been collected.
 */
function wstg_csp_send_header(): void {
  static $sent = false;

  if ($sent || is_admin() || headers_sent()) {
    return;
  }

  $csp = wstg_csp();
  $csp->allow("default-src", "data:");
  $csp->deny("script-src", "data:");
  $csp->deny("frame-src", "data:");
  $csp->allow("img-src", "https:");
  header("Content-Security-Policy: " . $csp, true);
  $sent = true;
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
$wstg_has_markup_csp_integration = has_filter("Website/HTML/output");

if ($wstg_has_markup_csp_integration) {
  add_filter(
    "Website/HTML/output",
    function ($markup) {
      if (is_string($markup)) {
        foreach (wstg_csp_get_trusted_inline_script_hashes($markup) as $hash) {
          wstg_csp_allow("script-src-elem", $hash);
        }
      }
      wstg_csp_send_header();

      return $markup;
    },
    5,
  );
} else {
  add_action("send_headers", "wstg_csp_send_header");
}

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
            "Allowed iframe hosts (for the CSP “frame-src” directive)",
            "whitespace-tracking-gdpr",
          ),
          "instructions" => __(
            "One per line. ⚠️ Warning: This will allow any iframe from the specified sources, including potentially harmful ones.",
            "whitespace-tracking-gdpr",
          ),
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
  $input = get_field("wstg_csp_settings_allowed_frame_src", "option") ?? "";
  preg_match_all("/^\s*\S+\s*$/im", $input, $allowed_frame_src);
  $allowed_frame_src = $allowed_frame_src[0];
  $allowed_frame_src = array_map("trim", $allowed_frame_src);
  $allowed_frame_src = array_filter($allowed_frame_src);
  $allowed_frame_src = array_unique($allowed_frame_src);
  foreach ($allowed_frame_src as $source) {
    wstg_csp_allow("frame-src", $source);
  }
});
