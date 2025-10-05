<?php

add_action("init", function () {
  $url = mx_get_matomo_option("url");
  $host = parse_url($url, PHP_URL_HOST);
  if ($host) {
    wstg_csp_allow("connect-src", $host);
  }
});

add_action("init", function () {
  $matomo_url = mx_get_matomo_option("url");
  $matomo_container_id = mx_get_matomo_option("container_id");
  $matomo_site_id = mx_get_matomo_option("site_id");
  if ($matomo_url && ($matomo_container_id || $matomo_site_id)) {
    wstg_csp_allow("connect-src", parse_url($matomo_url, PHP_URL_HOST));
  }
});
