<?php

add_action("admin_menu", function () {
  add_menu_page(
    _x("Data sharing", "Options Page Title", "whitespace-tracking-gdpr"),
    _x("Data sharing", "Options Page Menu Title", "whitespace-tracking-gdpr"),
    "manage_options",
    "wstg",
    function () {
      ?>
        <div class="privacy-settings-header">
          <div class="privacy-settings-title-section">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
          </div>
        </div>
        <hr class="wp-header-end">
        <style>
          #wstg-index .postbox .hndle {
            cursor: default !important;
            margin: 0 !important;
            padding: 8px 12px !important;
          }
          #wstg-index .postbox .hndle span {
            font-weight: 600;
          }
        </style>
        <div id="wstg-index" class="privacy-settings-body">
          <div class="card-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
            <div class="postbox">
              <div class="postbox-header">
                <h2 class="hndle">
                  <span><?php echo _x(
                    "Settings",
                    "Options Page Menu Title",
                    "whitespace-tracking-gdpr",
                  ); ?></span>
                </h2>
              </div>
              <div class="inside">
                <p><?php echo _x(
                  "Configure settings for third-party services, analytics, cookies and more",
                  "Settings Page Description",
                  "whitespace-tracking-gdpr",
                ); ?></p>
                <p>
                  <a href="<?php echo admin_url(
                    "admin.php?page=acf-options-mx-tracking",
                  ); ?>" class="button button-primary">
                    <?php echo _x(
                      "Open Settings",
                      "Button Text",
                      "whitespace-tracking-gdpr",
                    ); ?>
                  </a>
                </p>
              </div>
            </div>
            
            <div class="postbox">
              <div class="postbox-header">
                <h2 class="hndle">
                  <span><?php echo _x(
                    "Iframe report",
                    "Options Page Menu Title",
                    "whitespace-tracking-gdpr",
                  ); ?></span>
                </h2>
              </div>
              <div class="inside">
                <p><?php echo _x(
                  "View all iframes detected on the website",
                  "Iframe Report Page Description",
                  "whitespace-tracking-gdpr",
                ); ?></p>
                <p>
                  <a href="<?php echo admin_url(
                    "admin.php?page=wstg-iframe-report",
                  ); ?>" class="button button-primary">
                    <?php echo _x(
                      "View Report",
                      "Button Text",
                      "whitespace-tracking-gdpr",
                    ); ?>
                  </a>
                </p>
              </div>
            </div>
          </div>
        </div>
      <?php
    },
    "dashicons-shield",
  );
});

add_action("acf/init", function () {
  acf_add_options_sub_page([
    "page_title" => _x(
      "Data sharing settings",
      "Options Page Title",
      "whitespace-tracking-gdpr",
    ),
    "menu_title" => _x(
      "Settings",
      "Options Page Menu Title",
      "whitespace-tracking-gdpr",
    ),
    // "icon_url" => "dashicons-shield",
    "parent_slug" => "wstg",
    "menu_slug" => "acf-options-mx-tracking",
    "capability" => "manage_options",
    "autoload" => true,
  ]);
});

add_filter("admin_body_class", function ($classes) {
  $screen = get_current_screen();
  if ($screen && $screen->id === "toplevel_page_wstg") {
    $classes .= " privacy-settings";
  }
  return $classes;
});
