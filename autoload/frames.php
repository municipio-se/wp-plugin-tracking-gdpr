<?php

add_action("init", function () {
  $enabled_services = wstg_get_enabled_services();
  foreach ($enabled_services as $service) {
    if (!empty($service["csp"])) {
      foreach ($service["csp"] as $directive => $sources) {
        foreach ($sources as $source) {
          wstg_csp_allow($directive, $source);
        }
      }
    }
  }
});
