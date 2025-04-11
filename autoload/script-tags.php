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
    $category =
      strpos($attributes["src"], get_site_url() . "/") === 0
        ? ""
        : "uncategorized";
    $category = apply_filters("wstg_script_category", $category);
    if ($category) {
      $attributes["data-category"] = $category;
      if (isset($attributes["type"])) {
        $attributes["data-type"] = $attributes["type"];
      }
      $attributes["type"] = "text/plain";
    }
    return $attributes;
  },
  10,
  2,
);
