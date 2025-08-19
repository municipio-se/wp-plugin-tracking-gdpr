<?php

namespace WhitespaceTrackingGdpr;

// https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Content-Security-Policy#fetch_directives

class Csp {
  const SELF = "'self'";
  const UNSAFE_INLINE = "'unsafe-inline'";
  const NONCE = "'nonce-{{nonce}}'";
  const STRICT_DYNAMIC = "'strict-dynamic'";

  private string $nonce = ""; // Used for script-src-attr and style-src-attr
  private array $srcDirectives; // Includes both fetch and navigation directives

  public function __construct() {
    $this->nonce = bin2hex(random_bytes(16));
    $this->srcDirectives = [
      "child-src" => [], // Fallback for frame-src and worker-src.
      "connect-src" => ["data:" => 0],
      "default-src" => [self::SELF => 1], // Fallback for all other fetch directives.
      "font-src" => [],
      "img-src" => [],
      "manifest-src" => [],
      "media-src" => [],
      "object-src" => [],
      "prefetch-src" => [],
      "script-src" => [], // Fallback for script-src-elem and script-src-attr.
      "script-src-elem" => [
        self::STRICT_DYNAMIC => 1,
      ],
      "script-src-attr" => [
        self::UNSAFE_INLINE => 1,
      ],
      "style-src" => ["data:" => 0, self::UNSAFE_INLINE => 1], // Fallback for style-src-elem and style-src-attr.
      "style-src-elem" => [],
      "style-src-attr" => [],
      "worker-src" => [],
      "form-action" => [],
      // "form-action" => [self::SELF => 1],
      "frame-ancestors" => [self::SELF => 1],
    ];
  }
  public function allow($fetchDirective, $sources) {
    $this->srcDirectives[$fetchDirective][$sources] = 1;
  }
  public function deny($fetchDirective, $sources) {
    $this->srcDirectives[$fetchDirective][$sources] = 0;
  }
  public function inherit($fetchDirective, $sources) {
    unset($this->srcDirectives[$fetchDirective][$sources]);
  }
  public function __toString() {
    $string = "";
    foreach ($this->srcDirectives as $directive => $sources) {
      if (count($sources) === 0) {
        continue; // Skip empty directives
      }
      switch ($directive) {
        case "default-src":
        case "form-action":
        case "frame-ancestors":
          // These do not inherit
          break;
        case "frame-src":
        case "worker-src":
          $sources = array_merge(
            $this->srcDirectives["default-src"],
            $this->srcDirectives["child-src"],
            $sources,
          );
          break;
        case "script-src-elem":
        case "script-src-attr":
          $sources = array_merge(
            $this->srcDirectives["default-src"],
            $this->srcDirectives["script-src"],
            $sources,
          );
          break;
        case "style-src-elem":
        case "style-src-attr":
          $sources = array_merge(
            $this->srcDirectives["default-src"],
            $this->srcDirectives["style-src"],
            $sources,
          );
        default:
          $sources = array_merge($this->srcDirectives["default-src"], $sources);
      }
      $sources = array_keys($sources, 1);
      if (count($sources) === 0) {
        continue; // Skip empty directives
      }
      $string .= "$directive ";
      $string .= implode(" ", $sources);
      $string = str_replace("{{nonce}}", $this->nonce, $string);
      // if (
      //   $this->nonce &&
      //   in_array($directive, [
      //     "default-src",
      //     "script-src-elem",
      //     "style-src-elem",
      //     "script-src",
      //     "style-src",
      //   ])
      // ) {
      //   $string .= " 'nonce-{$this->nonce}'";
      // }
      $string .= "; ";
    }
    return substr($string, 0, -2); // Remove the last "; "
  }
  function getNonce() {
    return $this->nonce;
  }
}
