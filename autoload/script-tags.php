<?php

add_filter(
  "wp_script_attributes",
  function ($attributes) {
    $category =
      strpos($attributes["src"], get_site_url() . "/") === 0
        ? ""
        : "uncategorized";

    /**
     * Filter the category of the script
     *
     * @hook wstg_script_category
     * @since 0.0.0
     *
     * @param string $category The category of the script
     * @param array $attributes The attributes of the script tag
     * @return string The category of the script
     */
    $category = apply_filters("wstg_script_category", $category, $attributes);

    if ($category) {
      $attributes["data-category"] = $category;

      /**
       * Filter the service of the script
       *
       * @hook wstg_script_service
       * @since 0.0.0
       *
       * @param string $service The service of the script
       * @param array $attributes The attributes of the script tag
       * @return string The service of the script
       */
      $service = apply_filters("wstg_script_service", "", $attributes);
      if ($service) {
        $attributes["data-service"] = $service;
      }

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
