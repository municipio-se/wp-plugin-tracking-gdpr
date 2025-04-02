<?php

// add_filter(
//   "script_loader_tag",
//   function ($tag, $handle, $src) {
//     error_log(var_export($tag, true));
//     return $tag;
//   },
//   10,
//   3,
// );

add_filter(
  "wp_script_attributes",
  function ($attributes) {
    // error_log(var_export(get_site_url(), true));
    // error_log(var_export($attributes["src"], true));
    if (strpos($attributes["src"], get_site_url() . "/") !== 0) {
      $attributes["data-category"] = "uncategorized";
      if (isset($attributes["type"])) {
        $attributes["data-type"] = $attributes["type"];
      }
      $attributes["type"] = "text/plain";
    }
    // if ($attributes["id"] != "whitespace-tracking-gdpr-js") {
    //   $attributes["data-category"] = "uncategorized";
    //   $attributes["type"] = "text/plain";
    // }
    // error_log(var_export($attributes, true));
    return $attributes;
  },
  10,
  2,
);
