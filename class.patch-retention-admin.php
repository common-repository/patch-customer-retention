<?php

add_action('admin_menu', ['Patch_Retention_Admin', 'add_settings_link']);
add_filter('plugin_action_links_' . PATCH_RETENTION__BASENAME, ['Patch_Retention_Admin', 'settings_link'] );

class Patch_Retention_Admin {
  // SETTINGS LINK IN LEFT NAV
  public static function add_settings_link () {
    add_menu_page( 'Patch Retention Settings', 'Patch Retention', 'manage_options', 'patch-retention-settings', ['Patch_Retention_Admin', 'render_plugin_settings_page']);
    add_action( 'admin_init', ['Patch_Retention_Admin', 'register_plugin_options']);
  }

  // SETTINGS OPTIONS
  public static function register_plugin_options () {
    $patch_allowed_options = explode(',', PATCH_ALLOWED_OPTIONS);
    foreach ($patch_allowed_options as $option) {
      register_setting('patch-general', $option);
    }
  }

  // SETTINGS LINK ON PLUGINS PAGE
  public static function settings_link ($actions) {
    $mylinks = [
      '<a href="' . admin_url( 'admin.php?page=patch-retention-settings' ) . '">Settings</a>'
    ];

    // Adds the link to the end of the array.
    $actions = array_merge(
      $mylinks,
      $actions
    );

    return $actions;
  }


  // SETTINGS PAGE
  public static function render_plugin_settings_page () {
    $patch_allowed_options = explode(',', PATCH_ALLOWED_OPTIONS);
    foreach ($patch_allowed_options as $option) {
      if (isset($_GET['patch_disconnect'])) {
        if (in_array($option, ['patch_retention_app_url', 'patch_retention_cdn_url'])) {
          continue;
        }
        update_option($option, '');
      } else if (isset($_GET[$option])) {
        $value = sanitize_text_field($_GET[$option]);
        // All values should be 80 characters or less
        $value = substr($value, 0, 80);
        update_option($option, $value);
      } else if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'update' && isset($_REQUEST[$option])) {
        $value = sanitize_text_field($_REQUEST[$option]);
        // All values should be 80 characters or less
        $value = substr($value, 0, 80);
        update_option($option, $value);
      }
    }

    if (isset($_GET['patch_disconnect'])) {
      wp_redirect( admin_url( 'admin.php?page=patch-retention-settings' ) );
      return;
    }


    $wooPlugin = null;
    $wooActive = false;
    $siteUrl = get_site_url();

    $plugins = get_plugins();
    if (isset($plugins['woocommerce/woocommerce.php'])) {
      $wooPlugin = $plugins['woocommerce/woocommerce.php'];
      $wooActive = is_plugin_active('woocommerce/woocommerce.php');
    }

    $state = [
      'plugin_url' => admin_url( 'admin.php?page=patch-retention-settings' ), // Tells patch that this is being initiated from the "connect" button on the plugin
      'woo' => $wooActive ? '1' : '0'
    ];
    $account_id = get_option('patch_retention_account_id');
    if ($account_id) {
      $state['aid'] = $account_id;
    }
    $connectUrl = PATCH_RETENTION_APP_URL . '/v2/addons/wordpress/auth_start?site_url=' . urlencode($siteUrl) . '&state=' . base64_encode(json_encode($state));
    echo '<h2>Welcome to Patch Retention!</h2>';
    if ($account_id) {
      echo '<p>This plugin is set up to render the Pixel for Patch Retention account #' . esc_html($account_id);
      echo '<p><a class="button-primary" target="_blank" href="' . esc_url(PATCH_RETENTION_APP_URL . '/' . $account_id . '/settings/wordpress') . '">Open Patch Portal</a>&nbsp;&nbsp;<a class="button-secondary" href="' . admin_url( 'admin.php?page=patch-retention-settings&patch_disconnect=true' ) . '">Disconnect Account</a></p>';
    } else {
      echo '<p>Click the button below to connect your account.</p>';
      echo '<p><a class="button-primary" href="' . esc_url($connectUrl) . '">Connect Account</a></p>';
    }
  ?>
  <br />
  <hr />
  <br />
<?php
if ($wooActive) {
?>
  <h2>WooCommerce Settings</h2>
  <form method="post">
    <?php settings_fields( 'patch-general' ); ?>
    <?php do_settings_sections( 'patch-general' ); ?>
    <?php
      $sms_on = get_option('patch_retention_checkout_sms_on');
      $email_on = get_option('patch_retention_checkout_email_on');
      $marketing_disclosure = get_option('patch_retention_checkout_marketing_disclosure');
    ?>
    <p>
      <input type="checkbox" id="patch-sms-on" name="patch_retention_checkout_sms_on" value="1" <?php echo $sms_on == 1 ? 'checked' : '' ?> />
      <label for="patch-sms-on">Add SMS opt-in field to checkout page.</label>
    </p>
    <?php
    if ($sms_on) {
      $sms_on_label = get_option('patch_retention_checkout_sms_on_label');
      if (!$sms_on_label) {
        $sms_on_label = 'Subscribe to SMS marketing messages';
      }
    ?>
    <table class="form-table">
      <tr valign="top">
        <th scope="row">SMS opt-in field label text</th>
        <td><input type="text" name="patch_retention_checkout_sms_on_label" value="<?php echo esc_attr($sms_on_label); ?>" style="width: 100%; max-width: 400px;" /></td>
      </tr>
    </table>
    <?php
    }
    ?>
    <p>
      <input type="checkbox" id="patch-email-on" name="patch_retention_checkout_email_on" value="1" <?php echo $email_on == 1 ? 'checked' : '' ?> />
      <label for="patch-email-on">Add Email opt-in field to checkout page.</label>
    </p>
    <?php
    if ($email_on) {
      $email_on_label = get_option('patch_retention_checkout_email_on_label');
      if (!$email_on_label) {
        $email_on_label = 'Subscribe to Email marketing messages';
      }
    ?>
    <table class="form-table">
      <tr valign="top">
        <th scope="row">Email opt-in field label text</th>
        <td><input type="text" name="patch_retention_checkout_email_on_label" value="<?php echo esc_attr($email_on_label); ?>" style="width: 100%; max-width: 400px;" /></td>
      </tr>
    </table>
    <?php
    }
    ?>
    <p>
      <input type="checkbox" id="patch-marketing-disclosure" name="patch_retention_checkout_marketing_disclosure" value="1" <?php echo $marketing_disclosure == 1 ? 'checked' : '' ?> />
      <label for="patch-marketing-disclosure">Add marketing disclosure to checkout page.</label>
    </p>
    <?php
    if ($marketing_disclosure) {
      $marketing_disclosure_label = get_option('patch_retention_checkout_marketing_disclosure_label');
      if (!$marketing_disclosure_label) {
        $marketing_disclosure_label = '** By checking this box and entering your phone number above, you consent/agree to receive marketing messages at the number provided from us, including messages sent by autodialer. Consent is not a condition of any purchase. Message &amp; data rates may apply. Message frequency varies. Reply HELP for help or STOP to cancel. View our <a href="">Privacy Policy</a> and <a href="">Terms of Service</a>.';
      }
    ?>
    <table class="form-table">
      <tr valign="top">
        <th scope="row">Marketing disclosure text</th>
        <td><textarea name="patch_retention_checkout_marketing_disclosure_label" style="width: 100%; max-width: 400px; height: 200px;"><?php echo esc_textarea($marketing_disclosure_label); ?></textarea></td>
      </tr>
    </table>
    <?php
    }
    ?>
    <?php echo submit_button(); ?>
  </form>
  <br />
  <hr />
  <br />
<?php
}
?>
  <div
    style="opacity: 0.5"
    >
    <h2>Advanced Settings</h2>
    <p>If you follow the connect process using the above button these fields will be automatically populated.</p>
    <form method="post">
      <?php settings_fields( 'patch-general' ); ?>
      <?php do_settings_sections( 'patch-general' ); ?>
      <table class="form-table">
        <tr valign="top">
          <th scope="row">Account ID</th>
          <td><input type="text" name="patch_retention_account_id" value="<?php echo esc_attr(get_option('patch_retention_account_id')); ?>" style="width: 100%; max-width: 400px;" /></td>
        </tr>
        <tr valign="top">
          <th scope="row">Shared Secret</th>
          <td><input type="text" name="patch_retention_secret" value="<?php echo esc_attr(get_option('patch_retention_secret')); ?>" style="width: 100%; max-width: 400px;" /></td>
        </tr>
      <?php
      if (isset($_GET['patch_dev']) || get_option('patch_retention_app_url') || get_option('patch_retention_cdn_url')) {
      ?>
        <tr valign="top">
          <th scope="row">[DEV] APP URL</th>
          <td><input type="text" name="patch_retention_app_url" value="<?php echo esc_attr(get_option('patch_retention_app_url')); ?>" style="width: 100%; max-width: 400px;" /></td>
        </tr>
        <tr valign="top">
          <th scope="row">[DEV] CDN URL</th>
          <td><input type="text" name="patch_retention_cdn_url" value="<?php echo esc_attr(get_option('patch_retention_cdn_url')); ?>" style="width: 100%; max-width: 400px;" /></td>
        </tr>
      <?php
      }
      ?>
      </table>
      <?php echo submit_button(); ?>
    </form>
  </div>
  <?php
  }
}
