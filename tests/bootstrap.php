<?php

declare(strict_types=1);

$scenario = $argv[1] ?? null;
if (!in_array($scenario, ["active", "missing", "late-activation"], true)) {
  fwrite(STDERR, "Unknown bootstrap scenario.\n");
  exit(2);
}

$GLOBALS["wstg_test_actions"] = [];
$GLOBALS["wstg_test_accepted_args"] = [];
$GLOBALS["wstg_test_fields"] = [
  "wstg_service_settings" => ["test-video" => ["enabled" => true]],
];

function add_action(
  string $hook,
  callable $callback,
  int $priority = 10,
  int $accepted_args = 1,
): void {
  $GLOBALS["wstg_test_actions"][$hook][$priority][] = $callback;
  $GLOBALS["wstg_test_accepted_args"][$hook][$priority][] = $accepted_args;
}

function add_filter(
  string $hook,
  callable $callback,
  int $priority = 10,
  int $accepted_args = 1,
): void {
  add_action($hook, $callback, $priority, $accepted_args);
}

function has_filter(string $hook): bool {
  return isset($GLOBALS["wstg_test_actions"][$hook]);
}

function apply_filters(string $hook, $value, ...$args) {
  if (empty($GLOBALS["wstg_test_actions"][$hook])) {
    return $value;
  }

  ksort($GLOBALS["wstg_test_actions"][$hook]);
  foreach ($GLOBALS["wstg_test_actions"][$hook] as $priority => $callbacks) {
    foreach ($callbacks as $index => $callback) {
      $acceptedArgs =
        $GLOBALS["wstg_test_accepted_args"][$hook][$priority][$index] ?? 1;
      $value = $callback(...array_slice([$value, ...$args], 0, $acceptedArgs));
    }
  }

  return $value;
}

function did_action(string $hook): int {
  global $scenario;

  return $hook === "plugins_loaded" && $scenario === "late-activation" ? 1 : 0;
}

function plugin_dir_url(string $file): string {
  return "https://example.test/wp-content/plugins/whitespace-tracking-gdpr/";
}

function plugin_basename(string $file): string {
  return basename($file);
}

function load_muplugin_textdomain(): void {
}

function load_plugin_textdomain(): void {
}

function current_user_can(string $capability): bool {
  return true;
}

function is_network_admin(): bool {
  return false;
}

function is_admin(): bool {
  return false;
}

function home_url(string $path = ""): string {
  return "https://example.test" . $path;
}

function admin_url(string $path = ""): string {
  return "https://example.test/wp/wp-admin/" . $path;
}

function get_field(string $name, string $context = "") {
  return $GLOBALS["wstg_test_fields"][$name] ?? null;
}

function __(string $text): string {
  return $text;
}

function esc_attr(string $text): string {
  return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

function esc_html(string $text): string {
  return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

function esc_url(string $url): string {
  return filter_var($url, FILTER_SANITIZE_URL) ?: "";
}

function wp_json_encode($value): string|false {
  return json_encode(
    $value,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
  );
}

function esc_html_e(string $text): void {
  echo htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

function wstg_test_run_action(string $hook, int $priority): void {
  foreach ($GLOBALS["wstg_test_actions"][$hook][$priority] ?? [] as $callback) {
    $callback();
  }
}

function wstg_test_assert(bool $condition, string $message): void {
  if (!$condition) {
    fwrite(STDERR, $message . "\n");
    exit(1);
  }
}

// Simulate current Municipio's early markup hook, which WPMU Security also
// uses. Tracking GDPR detects this hook when it selects its CSP integration.
add_filter("Website/HTML/output", fn($markup) => $markup, 10);

define("WPMU_PLUGIN_DIR", __DIR__ . "/fixtures/mu-plugins");
require dirname(__DIR__) . "/vendor/autoload.php";

if ($scenario === "late-activation") {
  eval("function acf_add_options_sub_page(array \$settings): void {};");
}

require dirname(__DIR__) . "/whitespace-tracking-gdpr.php";

if ($scenario === "active") {
  wstg_test_assert(
    isset($GLOBALS["wstg_test_actions"]["plugins_loaded"][0]),
    "The normal bootstrap must wait for plugins_loaded priority 0.",
  );
  eval("function acf_add_options_sub_page(array \$settings): void {};");
  wstg_test_run_action("plugins_loaded", 0);
  wstg_test_assert(
    function_exists("wstg_get_services"),
    "The plugin did not load after ACF Pro became available.",
  );
  wstg_test_assert(
    wstg_is_first_party_script_url(
      "https://example.test/wp-content/plugins/example/app.js",
    ),
    "A same-origin wp-content URL was classified as external.",
  );
  wstg_test_assert(
    wstg_is_first_party_script_url("/wp-content/plugins/example/app.js"),
    "A root-relative script URL was classified as external.",
  );
  wstg_test_assert(
    wstg_is_first_party_script_url("//example.test/wp-content/app.js"),
    "A protocol-relative same-origin URL was classified as external.",
  );
  wstg_test_assert(
    wstg_is_first_party_script_url("https://example.test:443/wp/app.js"),
    "An explicit default port changed the script origin.",
  );
  wstg_test_assert(
    !wstg_is_first_party_script_url("https://cdn.example.test/app.js"),
    "A sibling subdomain was classified as first-party.",
  );
  wstg_test_assert(
    !wstg_is_first_party_script_url("data:text/javascript,alert(1)"),
    "A data URL was classified as first-party.",
  );
  $ajax_script =
    "\n        var ajaxurl = 'https://example.test/wp/wp-admin/admin-ajax.php';\n    ";
  $json_ld = '{"@context":"https://schema.org"}';
  $unknown_script = "window.untrustedCustomCode = true;";
  $hashes = wstg_csp_get_trusted_inline_script_hashes(
    "<script>{$ajax_script}</script>" .
      "<script type=\"application/ld+json\">{$json_ld}</script>" .
      "<script>{$unknown_script}</script>",
  );
  wstg_test_assert(
    in_array(
      "'sha256-" . base64_encode(hash("sha256", $ajax_script, true)) . "'",
      $hashes,
      true,
    ),
    "Municipio's validated ajaxurl script was not trusted.",
  );
  wstg_test_assert(
    in_array(
      "'sha256-" . base64_encode(hash("sha256", $json_ld, true)) . "'",
      $hashes,
      true,
    ),
    "Valid JSON-LD was not trusted.",
  );
  wstg_test_assert(
    !in_array(
      "'sha256-" . base64_encode(hash("sha256", $unknown_script, true)) . "'",
      $hashes,
      true,
    ),
    "Unknown inline code was added to the CSP allowlist.",
  );
  apply_filters(
    "Website/HTML/output",
    "<script>{$ajax_script}</script><script>{$unknown_script}</script>",
  );
  wstg_test_assert(
    !str_contains(
      (string) wstg_csp(),
      "'sha256-" . base64_encode(hash("sha256", $ajax_script, true)) . "'",
    ),
    "The pre-minification ajaxurl hash was added to the CSP.",
  );
  $final_ajax_script =
    "var ajaxurl = 'https://example.test/wp/wp-admin/admin-ajax.php';";
  apply_filters(
    "Municipio\\MarkupProcessor",
    "<script>{$final_ajax_script}</script>" .
      "<script>{$unknown_script}</script>",
  );
  wstg_test_assert(
    str_contains(
      (string) wstg_csp(),
      "'sha256-" .
        base64_encode(hash("sha256", $final_ajax_script, true)) .
        "'",
    ),
    "The final Municipio ajaxurl hash was not added to the CSP.",
  );
  wstg_test_assert(
    !str_contains(
      (string) wstg_csp(),
      "'sha256-" . base64_encode(hash("sha256", $unknown_script, true)) . "'",
    ),
    "Unknown final inline code was added to the CSP allowlist.",
  );
  wstg_register_service("test-video", [
    "title" => "Test video",
    "category" => "embedded",
    "requests" => [
      [
        "url" => "https://video.example/events",
        "types" => ["fetch", "beacon", "unsupported"],
      ],
    ],
    "iframe" => [
      "parseInput" => function (string $input): ?array {
        return $input === "https://video.example/embed/123"
          ? [
            "embedUrl" => $input,
            "standaloneUrl" => "https://video.example/watch/123",
            "aspectRatio" => "16/9",
          ]
          : null;
      },
      "attributes" => ["allowfullscreen" => true],
    ],
  ]);
  wstg_test_assert(
    wstg_get_network_request_rules() === [
      [
        "service" => "test-video",
        "category" => "embedded",
        "url" => "https://video.example/events",
        "types" => ["fetch", "beacon"],
      ],
    ],
    "Enabled service network rules were not serialized safely.",
  );
  $GLOBALS["wstg_test_fields"]["wstg_service_settings"]["youtube"] = [
    "enabled" => true,
  ];
  wstg_test_run_action("wstg_register_services", 9);
  wstg_test_assert(
    wstg_parse_input("https://youtu.be/dQw4w9WgXcQ")["embedUrl"] ===
      "https://www.youtube.com/embed/dQw4w9WgXcQ",
    "A missing YouTube domain setting did not preserve the historical host.",
  );
  $GLOBALS["wstg_test_fields"]["wstg_service_settings"]["youtube"][
    "embed_domain"
  ] = "www.youtube-nocookie.com";
  wstg_test_assert(
    wstg_parse_input("https://www.youtube.com/watch?v=dQw4w9WgXcQ")[
      "embedUrl"
    ] === "https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ",
    "The privacy-enhanced YouTube host was not applied per site.",
  );
  $placeholder = wstg_render_iframe_placeholder([
    "url" => "https://video.example/embed/123",
  ]);
  wstg_test_assert(
    str_contains($placeholder, 'class="wstg-iframe-placeholder"') &&
      str_contains($placeholder, "test-video") &&
      str_contains($placeholder, "video.example") &&
      str_contains($placeholder, "--wstg-iframe-aspect-ratio: 16 / 9") &&
      str_contains($placeholder, "https://video.example/watch/123") &&
      str_contains($placeholder, "c-button__filled--secondary") &&
      !str_contains($placeholder, 'slot="acceptButton"'),
    "A supported iframe did not get a plugin-owned consent placeholder.",
  );
  wstg_test_assert(
    wstg_normalize_iframe_aspect_ratio("4 / 3") === "4 / 3" &&
      wstg_normalize_iframe_aspect_ratio("0/3") === "" &&
      wstg_normalize_iframe_aspect_ratio("16/9; color: red") === "",
    "Iframe aspect ratio metadata was not normalized safely.",
  );
  $componentAttributes = apply_filters(
    "ComponentLibrary/Component/Iframe/Attribute",
    ["src" => "https://video.example/embed/123"],
  );
  wstg_test_assert(
    ($componentAttributes["data-wstg-service"] ?? null) === "test-video" &&
      ($componentAttributes["data-wstg-category"] ?? null) === "embedded",
    "A supported Municipio iframe was not annotated for the frontend adapter.",
  );
  $processedContent = apply_filters(
    "the_content",
    '<p>Before</p><iframe src="https://video.example/embed/123" title="Video"></iframe>',
  );
  wstg_test_assert(
    str_contains($processedContent, "wstg-iframe-placeholder") &&
      !str_contains($processedContent, "<iframe") &&
      str_contains($processedContent, "&quot;title&quot;:&quot;Video&quot;"),
    "A supported content iframe was not replaced with its accessible name preserved.",
  );
  $municipioContent = apply_filters(
    "the_content",
    '<div class="c-acceptance js-suppressed-content"><div class="js-suppressed-content-prompt">Municipio prompt</div><div><template><iframe src="https://video.example/embed/123"></iframe></template></div></div>',
  );
  wstg_test_assert(
    substr_count($municipioContent, "wstg-iframe-placeholder") === 1 &&
      !str_contains($municipioContent, "Municipio prompt"),
    "The Municipio per-embed wrapper was not replaced by the global consent control.",
  );
}

if ($scenario === "missing") {
  wstg_test_run_action("plugins_loaded", 0);
  wstg_test_assert(
    !function_exists("wstg_get_services"),
    "The plugin loaded without ACF Pro.",
  );
  wstg_test_assert(
    isset($GLOBALS["wstg_test_actions"]["admin_notices"][10]),
    "The missing ACF Pro notice was not registered.",
  );
}

if ($scenario === "late-activation") {
  wstg_test_assert(
    function_exists("wstg_get_services"),
    "Late activation did not bootstrap immediately after plugins_loaded.",
  );
}
