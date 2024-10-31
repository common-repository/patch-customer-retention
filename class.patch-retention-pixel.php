<?php

if (!is_admin()) {
  add_filter('script_loader_tag', ['Patch_Retention_Pixel', 'add_async_attribute'], 10, 2);
}
add_action('wp_footer', ['Patch_Retention_Pixel', 'load_scripts']);

class Patch_Retention_Pixel {
  // SILLY HACK
  // This is what I had to do to get the async attribute to show up for the pixel's script tag
  // https://stackoverflow.com/questions/18944027/how-do-i-defer-or-async-this-wordpress-javascript-snippet-to-load-lastly-for-fas
  public static function add_async_attribute($tag, $handle) {
    // if the unique handle/name of the registered script has 'async' in it
    if ($handle === 'patch_pixel') {
      // return the tag with the async attribute
      return str_replace( '<script ', '<script async ', $tag );
    } else {
      return $tag;
    }
  }

  // SCRIPT TAG RENDERER
  // This only runs if there is an account id set.
  public static function load_scripts () {
    $account_id = get_option('patch_retention_account_id');
    if ($account_id) {
      wp_enqueue_script('patch_pixel', PATCH_RETENTION_CDN_URL . '/pixel/' . $account_id . '/pixel.js');
  
      $current_user = wp_get_current_user();
      $patch_wp = [
        'nonce' => wp_create_nonce('wp_rest'), // This needs to be passed into ajax request to the wp api
        'user' => null
      ];
      if (($current_user instanceof WP_User) && $current_user->ID !== 0 && $current_user->ID !== null) {
        // This tells the pixel that the user is authenticated and to make a GET request to /wp-json/patch-retention/v1/contact_token
        $patch_wp['user'] = [
          'id' => $current_user->ID,
          'first_name' => $current_user->first_name
        ];
      }
      wp_add_inline_script('patch_pixel', 'window.__PATCH_WP = ' . json_encode($patch_wp), 'before');
    }
  }
}

