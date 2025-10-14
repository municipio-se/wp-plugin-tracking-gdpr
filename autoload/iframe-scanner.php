<?php

function wstg_get_iframe_report_data() {
  $data = [];

  // Find all iframes in post content. We use a SELECT query to avoid loading all posts into memory.
  global $wpdb;
  $results = $wpdb->get_results(
    "SELECT ID, post_title, post_type, post_content FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type IN ('post', 'page') AND post_content LIKE '%<iframe%'",
  );
  foreach ($results as $post) {
    // Use regex to find all iframes in the post content.
    if (
      preg_match_all(
        '/<iframe[^>]+src=["\']([^"\']+)["\'][^>]*>/i',
        $post->post_content,
        $matches,
      )
    ) {
      foreach ($matches[1] as $src) {
        $data[] = [
          "post_id" => $post->ID,
          "post_title" => $post->post_title,
          "post_type" => $post->post_type,
          "iframe_src" => esc_url($src),
        ];
      }
    }
  }

  // Find all iframes in ACF fields. We use a SELECT query on meta_key="iframe_url" to avoid loading all meta into memory.
  $results = $wpdb->get_results(
    "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'iframe_url'",
  );
  $post_ids = array_map(function ($r) {
    return $r->post_id;
  }, $results);
  $posts = get_posts([
    "post__in" => $post_ids,
    "post_type" => ["mod-iframe"],
    "post_status" => "publish",
    "numberposts" => -1,
  ]);
  foreach ($results as $meta) {
    $post = null;
    foreach ($posts as $p) {
      if ($p->ID == $meta->post_id) {
        $post = $p;
        break;
      }
    }
    if ($post) {
      $data[] = [
        "post_id" => $post->ID,
        "post_title" => $post->post_title,
        "post_type" => $post->post_type,
        "iframe_src" => $meta->meta_value,
      ];
    }
  }

  foreach ($data as &$item) {
    $iframe = wstg_parse_input($item["iframe_src"] ?? "");
    $item["service"] = $iframe["service"] ?? null;
  }

  return $data;
}

add_action("admin_menu", function () {
  add_submenu_page(
    "wstg",
    __("Iframe report", "whitespace-tracking-gdpr"),
    __("Iframe report", "whitespace-tracking-gdpr"),
    "manage_options",
    "wstg-iframe-report",
    function () {
      $data = wstg_get_iframe_report_data();// $post_types_objects = get_post_types([], "objects");
      ?>
      <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <div id="wstg-iframe-report">
          <table class="wp-list-table widefat fixed striped table-view-list" cellspacing="0">
            <thead>
              <tr>
                <th style="width: 30ch">Post</th>
                <th style="width: 15ch">Service</th>
                <th>Iframe URL</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($data as $item): ?>
                <tr>
                  <td class="title column-title has-row-actions column-primary page-title">
                    <a href="<?php echo get_edit_post_link(
                      $item["post_id"],
                    ); ?>">
                      <strong>
                        <?php echo $item["post_title"]
                          ? esc_html($item["post_title"])
                          : "(Untitled post)"; ?>
                      </strong>
                    </a>
                  </td>
                  <td>
                    <?php if ($item["service"]["title"] ?? null) {
                      echo esc_html($item["service"]["title"]);
                    } else {
                      echo "(Unknown)";
                    } ?>
                  </td>
                  <td><?php echo esc_html($item["iframe_src"]); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php
    },
  );
});
