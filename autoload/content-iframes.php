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

function wstg_resolve_iframe_replacement_target(
  Element $iframeNode,
  array $parsed,
  string $url,
) {
  // Services can optionally override which DOM node gets replaced.
  $replacementTarget = $parsed["service"]["iframe"]["replacementTarget"] ?? null;

  if (!is_callable($replacementTarget)) {
    return $iframeNode;
  }

  $context = [
    "parsed" => $parsed,
    "url" => $url,
    "video_service" => $parsed["serviceKey"] ?? false,
    "video_id" => $parsed["id"] ?? false,
  ];

  try {
    $resolvedTarget = $replacementTarget($iframeNode, $context);
  } catch (\Throwable $exception) {
    return $iframeNode;
  }

  if ($resolvedTarget instanceof Element && $resolvedTarget->parent()) {
    return $resolvedTarget;
  }

  return $iframeNode;
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
      $replacement_html = apply_filters(
        "wstg_content_iframe_replacement",
        "",
        [
          "video_service" => $video_service,
          "video_id" => $video_id,
          "url" => $url,
          "node" => $node,
        ],
      );

      if (empty($replacement_html)) {
        continue;
      }

      $targetNode = wstg_resolve_iframe_replacement_target($node, $parsed, $url);
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
