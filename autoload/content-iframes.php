<?php

use DiDom\Document;
use DiDom\Query;

function wstg_detect_video_service($url) {
  if (str_contains($url, "vimeo")) {
    return "vimeo";
  }
  if (str_contains($url, "youtu")) {
    //Matches youtu.be and full domain
    return "youtube";
  }
  if (str_contains($url, "mediaflow")) {
    return "mediaflow";
  }
  return false;
}

function wstg_parse_youtube_id($embedLink) {
  $hostname = parse_url($embedLink, PHP_URL_HOST);

  //https://youtu.be/ID
  if ($hostname == "youtu.be") {
    return trim(rtrim(parse_url($embedLink, PHP_URL_PATH), "/"), "/");
  }

  //https://www.youtube.com/watch?v=ID
  parse_str(parse_url($embedLink, PHP_URL_QUERY), $queryParameters);
  if (isset($queryParameters["v"]) && !empty($queryParameters["v"])) {
    return $queryParameters["v"];
  }

  //https://www.youtube.com/embed/ID
  $path = parse_url($embedLink, PHP_URL_PATH);
  if (preg_match("/\/embed\/([a-zA-Z0-9_-]+)/", $path, $matches)) {
    return $matches[1];
  }

  return false;
}

/**
 * Get vimeo id from embed url
 *
 * @param  string $embedLink    The embed link
 * @return string $id           The id in embed link
 */
function wstg_parse_vimeo_id($embedLink) {
  preg_match("/\/video\/(\d+)/", $embedLink, $matches);

  if ($matches) {
    return $matches[1];
  }
  return false;
}

function wstg_get_video_id($embedLink, $videoService) {
  if ($videoService == "youtube") {
    return wstg_parse_youtube_id($embedLink);
  }

  if ($videoService == "vimeo") {
    return wstg_parse_vimeo_id($embedLink);
  }

  if ($videoService == "mediaflow") {
    preg_match(
      '/src=["\'].*?mediaflow(pro)?\.com\/ovp\/\d+\/([a-zA-Z0-9]+)\?/',
      $embedLink,
      $matches,
    );
    return $matches[2];
  }

  return false;
}

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
      $url = $node->getAttribute("src");
      $video_service = wstg_detect_video_service($url);
      $video_id = $video_service
        ? wstg_get_video_id($url, $video_service)
        : false;

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
