<?php

add_filter(
  "style_loader_tag",
  function ($html) {
    if (is_admin()) {
      return $html; // Do not apply CSP in admin area
    }
    $csp_nonce = wstg_csp_nonce();
    if ($csp_nonce) {
      // Add nonce attribute to the style tag
      $html = str_replace(
        "rel='stylesheet'",
        'rel="stylesheet" nonce="' . esc_attr($csp_nonce) . '"',
        $html,
      );
    }
    return $html;
  },
  10,
);
