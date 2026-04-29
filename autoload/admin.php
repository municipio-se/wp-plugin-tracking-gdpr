<?php

add_action("admin_menu", function () {
  add_menu_page(
    _x("Data sharing", "Options Page Title", "whitespace-tracking-gdpr"),
    _x("Data sharing", "Options Page Menu Title", "whitespace-tracking-gdpr"),
    "manage_options",
    "wstg",
    function () {
      $current_revision = wstg_get_consent_revision();
      $updated_revision = isset($_GET["wstg-consent-revision"])
        ? max(1, (int) $_GET["wstg-consent-revision"])
        : null;
      ?>
        <div class="privacy-settings-header">
          <div class="privacy-settings-title-section">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
          </div>
        </div>
        <hr class="wp-header-end">
        <?php if ($updated_revision !== null) { ?>
          <div class="notice notice-success is-dismissible">
            <p>
              <?php echo esc_html(
                sprintf(
                  _x(
                    "Consent revision %d has been published.",
                    "Consent Revision Success Notice",
                    "whitespace-tracking-gdpr",
                  ),
                  $updated_revision,
                ),
              ); ?>
            </p>
          </div>
        <?php } ?>
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
                    "Consent revision",
                    "Options Page Menu Title",
                    "whitespace-tracking-gdpr",
                  ); ?></span>
                </h2>
              </div>
              <div class="inside">
                <p><?php echo _x(
                  "Current published revision used by the cookie consent dialog.",
                  "Consent Revision Description",
                  "whitespace-tracking-gdpr",
                ); ?></p>
                <p>
                  <strong><?php echo esc_html(
                    sprintf(
                      _x(
                        "Revision %d",
                        "Consent Revision Current Value",
                        "whitespace-tracking-gdpr",
                      ),
                      $current_revision,
                    ),
                  ); ?></strong>
                </p>
                <form method="post" action="<?php echo esc_url(
                  admin_url("admin-post.php"),
                ); ?>">
                  <input type="hidden" name="action" value="wstg_bump_consent_revision">
                  <?php wp_nonce_field("wstg_bump_consent_revision"); ?>
                  <p>
                    <button type="submit" class="button button-primary">
                      <?php echo esc_html_x(
                        "Publish new revision",
                        "Button Text",
                        "whitespace-tracking-gdpr",
                      ); ?>
                    </button>
                  </p>
                </form>
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
  if (function_exists("acf_add_options_sub_page")) {
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
  }
});

add_filter("admin_body_class", function ($classes) {
  $screen = get_current_screen();
  if ($screen && $screen->id === "toplevel_page_wstg") {
    $classes .= " privacy-settings";
  }
  return $classes;
});
