<?php

function wstg_get_cookie_categories() {
  return [
    "necessary" => [
      "title" => _x(
        "Necessary",
        "Cookie Category Title",
        "whitespace-tracking-gdpr",
      ),
      "description" => __(
        "Necessary cookies are always allowed.",
        "whitespace-tracking-gdpr",
      ),
      "required" => true,
    ],
    "personalization" => [
      "title" => _x(
        "Personalization",
        "Cookie Category Title",
        "whitespace-tracking-gdpr",
      ),
      "description" => __(
        "Personalization services use cookies to remember your activity and preferences to improve your experience.",
        "whitespace-tracking-gdpr",
      ),
    ],
    "analytics" => [
      "title" => _x(
        "Analytics",
        "Cookie Category Title",
        "whitespace-tracking-gdpr",
      ),
      "description" => __(
        "Analytics services use cookies to collect information about how visitors use the website.",
        "whitespace-tracking-gdpr",
      ),
    ],
    // "marketing" => [
    //   "title" => _x(
    //     "Marketing",
    //     "Cookie Category Title",
    //     "whitespace-tracking-gdpr",
    //   ),
    //   "description" => __(
    //     "Marketing cookies are used to collect information about how visitors use a website.",
    //     "whitespace-tracking-gdpr",
    //   ),
    // ],
    "embedded" => [
      "title" => _x(
        "Embedded",
        "Cookie Category Title",
        "whitespace-tracking-gdpr",
      ),
      "description" => __(
        "Cookies that are set by third parties when we embed content from different services.",
        "whitespace-tracking-gdpr",
      ),
    ],
    "uncategorized" => [
      "title" => _x(
        "Uncategorized",
        "Cookie Category Title",
        "whitespace-tracking-gdpr",
      ),
      "description" => __(
        "These cookies have not been categorized yet.",
        "whitespace-tracking-gdpr",
      ),
      "required" => true,
    ],
  ];
}
