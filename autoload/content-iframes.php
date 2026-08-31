<?php

use DiDom\Document;
use DiDom\Element;
use DiDom\Query;

function wstg_content_document_to_html(Document $document) {
  $domDocument = $document->getDocument();
  $body = $domDocument->getElementsByTagName("body")->item(0);

  if (!$body) {
    return $document->html();
  }

  $html = "";
  foreach ($body->childNodes as $childNode) {
    $html .= $domDocument->saveHTML($childNode);
  }

  return $html;
}

/**
 * Render the plugin-owned consent control for a supported iframe service.
 *
 * The original iframe attributes are copied to the custom element, but the
 * browser does not create the iframe until the global consent state permits it.
 */
function wstg_render_iframe_placeholder(array $context): string {
  $parsed = $context["parsed"] ?? wstg_parse_input($context["url"] ?? "");
  $service = $parsed["service"] ?? null;
  $serviceKey = $parsed["serviceKey"] ?? null;

  if (!$service || !$serviceKey || empty($parsed["embedUrl"])) {
    return "";
  }

  $category = $service["category"] ?? "embedded";
  $serviceTitle = $service["title"] ?? $serviceKey;
  $attributes = array_merge($parsed["attributes"] ?? [], [
    "src" => $parsed["embedUrl"],
  ]);

  $sourceNode = $context["node"] ?? null;
  if ($sourceNode instanceof Element) {
    foreach (
      [
        "allow",
        "allowfullscreen",
        "height",
        "loading",
        "name",
        "referrerpolicy",
        "sandbox",
        "title",
        "width",
      ]
      as $attributeName
    ) {
      $value = $sourceNode->getAttribute($attributeName);
      if ($value !== null) {
        $attributes[$attributeName] = (string) $value;
      }
    }
  }

  $payload = wp_json_encode([
    "iframe" => $attributes,
    "service" => $serviceKey,
    "category" => $category,
  ]);
  if (!$payload) {
    return "";
  }

  $thumbnailHost = parse_url($parsed["thumbnailUrl"] ?? "", PHP_URL_HOST);
  $siteHost = parse_url(home_url("/"), PHP_URL_HOST);
  $thumbnail =
    $thumbnailHost && $siteHost && strtolower($thumbnailHost) === strtolower($siteHost)
    ? sprintf(
      '<img src="%s" alt="" loading="lazy" slot="thumbnail">',
      esc_url($parsed["thumbnailUrl"]),
    )
    : "";

  return sprintf(
    '<div class="wstg-iframe-placeholder" data-wstg-iframe="%s">%s<div class="wstg-iframe__dialog" slot="dialog"><p>%s</p><div class="wstg-iframe__actions"><button type="button" slot="acceptButton">%s</button><button type="button" slot="settingsButton">%s</button></div></div></div>',
    esc_attr($payload),
    $thumbnail,
    esc_html(
      sprintf(
        __(
          "This content is provided by %s. Allow embedded content to view it.",
          "whitespace-tracking-gdpr",
        ),
        $serviceTitle,
      ),
    ),
    esc_html(
      sprintf(__("Allow %s", "whitespace-tracking-gdpr"), $serviceTitle),
    ),
    esc_html(__("Cookie settings", "whitespace-tracking-gdpr")),
  );
}

function wstg_resolve_iframe_replacement_target(
  Element $iframeNode,
  ?array $parsed,
  string $url,
) {
  // The parser can return null for legacy iframe markup with missing or invalid
  // src attributes, while external replacement filters may still produce HTML.
  // In that case, keep replacement scoped to the iframe node itself.
  $replacementTarget =
    $parsed["service"]["iframe"]["replacementTarget"] ?? null;

  $resolvedTarget = $iframeNode;

  if (is_callable($replacementTarget)) {
    $context = [
      "parsed" => $parsed,
      "url" => $url,
      "video_service" => $parsed["serviceKey"] ?? false,
      "video_id" => $parsed["id"] ?? false,
    ];

    try {
      $serviceTarget = $replacementTarget($iframeNode, $context);
      if ($serviceTarget instanceof Element && $serviceTarget->parent()) {
        $resolvedTarget = $serviceTarget;
      }
    } catch (\Throwable $exception) {
      $resolvedTarget = $iframeNode;
    }
  }

  $ancestor = $resolvedTarget;
  while ($ancestor instanceof Element && $ancestor->parent()) {
    $classes = preg_split(
      "/\s+/",
      trim((string) $ancestor->getAttribute("class")),
    );
    if (in_array("js-suppressed-content", $classes ?: [], true)) {
      return $ancestor;
    }
    $ancestor = $ancestor->parent();
  }

  return $resolvedTarget;
}

add_filter(
  "the_content",
  function ($content) {
    if (empty(trim($content))) {
      return $content; // Avoid processing empty content
    }

    $document = new Document($content);
    $nodes = $document->find("iframe", Query::TYPE_CSS);
    $placeholderReplacements = [];
    $replacementIndex = 0;

    foreach ($nodes as $node) {
      if (!$node->parent()) {
        continue;
      }

      $url = (string) $node->getAttribute("src");
      $parsed = wstg_parse_input($url);
      $video_service = $parsed["serviceKey"] ?? false;
      $video_id = $parsed["id"] ?? false;

      /**
       * Filters the replacement HTML used for consent-aware iframe placeholders.
       *
       * @param string $replacement_html Replacement HTML.
       * @param array $context Iframe replacement context.
       * @return string Filtered replacement HTML.
       */
      $replacement_html = apply_filters("wstg_content_iframe_replacement", "", [
        "video_service" => $video_service,
        "video_id" => $video_id,
        "url" => $url,
        "node" => $node,
        "parsed" => $parsed,
      ]);

      if (empty($replacement_html)) {
        continue;
      }

      $targetNode = wstg_resolve_iframe_replacement_target(
        $node,
        $parsed,
        $url,
      );
      $placeholder = "%%WSTG_IFRAME_REPLACEMENT_{$replacementIndex}%%";
      $placeholderReplacements[$placeholder] = $replacement_html;
      $replacementIndex++;

      $targetNode->replace(
        $document->getDocument()->createTextNode($placeholder),
        false,
      );
    }

    $processedContent = wstg_content_document_to_html($document);

    if (empty($placeholderReplacements)) {
      return $processedContent;
    }

    return strtr($processedContent, $placeholderReplacements);
  },
  20,
);

add_filter(
  "wstg_content_iframe_replacement",
  function (string $replacementHtml, array $context): string {
    return $replacementHtml ?: wstg_render_iframe_placeholder($context);
  },
  10,
  2,
);

/**
 * Annotate Municipio's inactive Component Library iframe template so the
 * frontend adapter can hand it to the global consent control without loading
 * the third-party source first.
 */
add_filter("ComponentLibrary/Component/Iframe/Attribute", function (
  $attributes,
) {
  // Component Library applies this hook both to the attribute array and to
  // the already-rendered attribute string. Only the array is safe to amend.
  if (!is_array($attributes)) {
    return $attributes;
  }

  $parsed = wstg_parse_input((string) ($attributes["src"] ?? ""));
  if (empty($parsed["serviceKey"]) || empty($parsed["service"])) {
    return $attributes;
  }

  $attributes["data-wstg-service"] = $parsed["serviceKey"];
  $attributes["data-wstg-category"] =
    $parsed["service"]["category"] ?? "embedded";
  $attributes["src"] = $parsed["embedUrl"] ?? $attributes["src"];
  foreach ($parsed["attributes"] ?? [] as $name => $value) {
    $attributes[$name] = $value;
  }

  return $attributes;
});
