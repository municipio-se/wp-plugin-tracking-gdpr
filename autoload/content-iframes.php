<?php

use DiDom\Document;
use DiDom\Query;

add_filter(
  "the_content",
  function ($content) {
    if (empty(trim($content))) {
      return $content; // Avoid processing empty content
    }

    $document = new Document($content);
    $nodes = $document->find("iframe", Query::TYPE_CSS, false);
    $replacements = [];

    foreach ($nodes as $node) {
      $url = (string) $node->getAttribute("src");
      $parsed = wstg_parse_input($url);
      $video_service = $parsed["serviceKey"] ?? false;
      $video_id = $parsed["id"] ?? false;

      $inner_html = apply_filters("wstg_content_iframe_replacement", "", [
        "video_service" => $video_service,
        "video_id" => $video_id,
        "url" => $url,
        "node" => $node,
      ]);

      // Collect replacements to do later
      if (!empty($inner_html)) {
        $iframe_html =
          $node->outerHtml ?? $document->getDocument()->saveHTML($node);
        $replacements[$iframe_html] = $inner_html;
      }
    }

    // Apply all replacements
    foreach ($replacements as $iframe_html => $replacement_html) {
      $content = str_replace($iframe_html, $replacement_html, $content);
    }

    return $content;
  },
  20,
);
