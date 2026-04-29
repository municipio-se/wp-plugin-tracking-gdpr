<?php

define("WSTG_CONSENT_REVISION_OPTION", "wstg_consent_revision");

function wstg_get_consent_revision(): int {
  $stored_revision = get_option(WSTG_CONSENT_REVISION_OPTION, null);

  if ($stored_revision === null) {
    add_option(WSTG_CONSENT_REVISION_OPTION, 1, "", true);
    return 1;
  }

  $revision = max(1, (int) $stored_revision);
  if ((string) $stored_revision !== (string) $revision) {
    update_option(WSTG_CONSENT_REVISION_OPTION, $revision, true);
  }

  return $revision;
}

function wstg_set_consent_revision(int $revision): int {
  $revision = max(1, $revision);

  if (get_option(WSTG_CONSENT_REVISION_OPTION, null) === null) {
    add_option(WSTG_CONSENT_REVISION_OPTION, $revision, "", true);
  } else {
    update_option(WSTG_CONSENT_REVISION_OPTION, $revision, true);
  }

  return $revision;
}

function wstg_bump_consent_revision(): int {
  $previous_revision = wstg_get_consent_revision();
  $revision = wstg_set_consent_revision($previous_revision + 1);

  do_action("wstg_consent_revision_bumped", $revision, $previous_revision);

  return $revision;
}

function wstg_is_tracking_settings_page_request(): bool {
  $page = sanitize_key(wp_unslash($_REQUEST["page"] ?? ""));
  if ($page === "acf-options-mx-tracking") {
    return true;
  }

  $referer = wp_get_referer();
  if (!$referer) {
    $referer = wp_unslash($_REQUEST["_wp_http_referer"] ?? "");
  }

  if (!$referer) {
    return false;
  }

  $referer_query = wp_parse_url($referer, PHP_URL_QUERY);
  if (!is_string($referer_query)) {
    return false;
  }

  parse_str($referer_query, $query_args);
  return ($query_args["page"] ?? null) === "acf-options-mx-tracking";
}

function wstg_normalize_consent_revision_value($value) {
  if (!is_array($value)) {
    return $value;
  }

  $normalized = [];
  foreach ($value as $key => $item) {
    $normalized[$key] = wstg_normalize_consent_revision_value($item);
  }

  if (!array_is_list($normalized)) {
    ksort($normalized);
  }

  return $normalized;
}

function wstg_get_tracked_consent_settings(): array {
  return wstg_normalize_consent_revision_value([
    "serviceSettings" => get_field("wstg_service_settings", "option") ?? [],
    "allowAnyEmbeddedContent" => (bool) get_field(
      "wstg_allow_any_embedded_content",
      "option",
    ),
    "translation" => [
      "consentModal" => get_field("wstg_string_consent_modal", "option") ?? [],
      "preferencesModal" =>
        get_field("wstg_string_preferences_modal", "option") ?? [],
    ],
    "matomo" => [
      "url" => mx_get_matomo_option("url"),
      "containerId" => mx_get_matomo_option("container_id"),
      "siteId" => mx_get_matomo_option("site_id"),
    ],
  ]);
}

function wstg_get_tracked_consent_settings_hash(): string {
  return wp_json_encode(wstg_get_tracked_consent_settings()) ?: "{}";
}

add_action("admin_init", function () {
  wstg_get_consent_revision();
});

add_action("admin_post_wstg_bump_consent_revision", function () {
  if (!current_user_can("manage_options")) {
    wp_die(
      esc_html__(
        "You are not allowed to update the consent revision.",
        "whitespace-tracking-gdpr",
      ),
      403,
    );
  }

  check_admin_referer("wstg_bump_consent_revision");

  $revision = wstg_bump_consent_revision();

  wp_safe_redirect(
    add_query_arg(
      [
        "page" => "wstg",
        "wstg-consent-revision" => $revision,
      ],
      admin_url("admin.php"),
    ),
  );
  exit();
});

add_action(
  "acf/save_post",
  function ($post_id) {
    if ($post_id !== "options" || !wstg_is_tracking_settings_page_request()) {
      return;
    }

    $GLOBALS[
      "wstg_tracked_consent_settings_hash"
    ] = wstg_get_tracked_consent_settings_hash();
  },
  5,
);

add_action(
  "acf/save_post",
  function ($post_id) {
    if ($post_id !== "options" || !wstg_is_tracking_settings_page_request()) {
      return;
    }

    $previous_hash = $GLOBALS["wstg_tracked_consent_settings_hash"] ?? null;
    $GLOBALS["wstg_tracked_consent_settings_hash"] = null;

    if (!is_string($previous_hash)) {
      return;
    }

    $current_hash = wstg_get_tracked_consent_settings_hash();
    if ($previous_hash === $current_hash) {
      return;
    }

    wstg_bump_consent_revision();
  },
  15,
);
