<?php

add_action('rest_api_init', ['Patch_Retention_REST_API', 'init']);

class Patch_Retention_REST_API {
  /**
	 * Register the REST API routes.
	 */
	public static function init() {
		if ( ! function_exists( 'register_rest_route' ) ) {
			// The REST API wasn't integrated into core until 4.4, just in case it's not available.
			return false;
		}

    // METHODS CALLED FROM PATCH PORTAL TO CONFIGURE PLUGIN
    register_rest_route( 'patch-retention/v1', '/options', array(
      'methods' => 'GET',
      'callback' => ['Patch_Retention_REST_API', 'get_options'],
      'permission_callback' => function () {
        return current_user_can( 'administrator' );
      }
    ));
    register_rest_route( 'patch-retention/v1', '/options', array(
      'methods' => 'POST',
      'callback' => ['Patch_Retention_REST_API', 'post_options'],
      'permission_callback' => function () {
        return current_user_can( 'administrator' );
      }
    ));
    // METHODS CALLED BY WOOCOMMERCE STOREFRONT
    register_rest_route( 'patch-retention/v1', '/contact_token', array(
      'methods' => 'GET',
      'callback' => ['Patch_Retention_REST_API', 'get_contact_token'],
      'permission_callback' => '__return_true'
    ));
  }

  public static function get_options ($data) {
    $patch_allowed_options = explode(',', PATCH_ALLOWED_OPTIONS);
    $response = [];
    foreach ($patch_allowed_options as $allowed_option) {
      $response[$allowed_option] = get_option($allowed_option);
    }
    return $response;
  }
  
  public static function post_options ($data) {
    $patch_allowed_options = explode(',', PATCH_ALLOWED_OPTIONS);
    $params = $data->get_params();
    foreach($patch_allowed_options as $option) {
      if (array_key_exists($option, $params)) {
        update_option($option, sanitize_text_field($params[$option]));
      }
    }
  
    return self::get_options($data);
  }
  
  public static function get_contact_token ($data) {
    $account_id = get_option('patch_retention_account_id');
    $secret = get_option('patch_retention_secret');
  
    $current_user = wp_get_current_user();
    $response = [ 'token' => null ];
  
    if (
      $account_id &&
      $secret &&
      $current_user instanceof WP_User &&
      $current_user->ID !== 0 &&
      $current_user->ID !== null
    ) {
      try {
        $result = wp_remote_post(
          PATCH_RETENTION_APP_URL . '/v2/addons/wordpress/proxy/contact_token',
          [
            'method' => 'POST',
            'timeout' => 30,
            'body' => [
              'account_id' => $account_id,
              'secret' => $secret,
              'user_id' => $current_user->ID
            ]
          ]
        );
  
        if ($result instanceof WP_Error) {
          $response = ['error' => $result->get_error_message()];
        } else if (isset($result['body'])) {
          try {
            $response = json_decode($result['body']);
          } catch (Exception $e) {
            if (isset($result) && isset($result['body'])) {
              $response = $result['body'];
            }
            $response = ['error' => $e->getMessage()];
          }
        }
      } catch (Exception $e) {
        if ($e instanceof WP_Error) {
          $response = ['error' => $e->get_error_message()];
        }
        $response = [ 'error' => $e->getMessage() ];
      }
    }
    return $response;
  }
}
