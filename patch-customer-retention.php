<?php

/**
 * Plugin Name: Patch Customer Retention
 * Plugin URI: https://app.patchretention.com
 * Description: Patch provides eCommerce brands with EVERY tool they need to boost customer retention, maximize lifetime value, and keep customers coming back, for life!
 * Author: Patch Customer Retention, Ben Oman
 * Version: 1.0.2
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.0
 * Requires PHP: 7.0
 */

define('PATCH_ALLOWED_OPTIONS', 'patch_retention_account_id,patch_retention_secret,patch_retention_cdn_url,patch_retention_app_url,patch_retention_checkout_sms_on,patch_retention_checkout_sms_on_label,patch_retention_checkout_email_on,patch_retention_checkout_email_on_label,patch_retention_checkout_marketing_disclosure,patch_retention_checkout_marketing_disclosure_label');

if (get_option('patch_retention_cdn_url')) {
  define('PATCH_RETENTION_CDN_URL', get_option('patch_retention_cdn_url'));
} else {
  define('PATCH_RETENTION_CDN_URL', 'https://cdn.patchretention.com');
}

if (get_option('patch_retention_app_url')) {
  define('PATCH_RETENTION_APP_URL', get_option('patch_retention_app_url'));
} else {
  define('PATCH_RETENTION_APP_URL', 'https://app.patchretention.com');
}

define('PATCH_RETENTION__PLUGIN_DIR', plugin_dir_path( __FILE__ ));
define('PATCH_RETENTION__BASENAME', plugin_basename(__FILE__));

require_once( PATCH_RETENTION__PLUGIN_DIR . 'patch-retention-activation.php' );
require_once( PATCH_RETENTION__PLUGIN_DIR . 'class.patch-retention-admin.php' );
require_once( PATCH_RETENTION__PLUGIN_DIR . 'class.patch-retention-pixel.php' );
require_once( PATCH_RETENTION__PLUGIN_DIR . 'class.patch-retention-rest-api.php' );
require_once( PATCH_RETENTION__PLUGIN_DIR . 'class.patch-retention-checkout-fields.php' );
