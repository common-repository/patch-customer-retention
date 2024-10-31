<?php

add_filter( 'woocommerce_checkout_fields', ['Patch_Retention_Checkout_Fields', 'render'] );
add_action( 'woocommerce_checkout_update_order_meta', ['Patch_Retention_Checkout_Fields', 'update_order'] );
add_action( 'woocommerce_after_checkout_billing_form', ['Patch_Retention_Checkout_Fields', 'after_billing'] );

class Patch_Retention_Checkout_Fields {
  public static $field_prefix = '__patch__';

	public static function render ($fields) {
    // Render SMS/Email fields depending on options
    $sms_on = get_option('patch_retention_checkout_sms_on');
    if ($sms_on) {
      $sms_on_label = get_option('patch_retention_checkout_sms_on_label');
      if (!$sms_on_label) {
        $sms_on_label = 'Subscribe to SMS marketing messages';
      }
      $fields['billing'][self::$field_prefix . 'sms_on'] = [
        'type' => 'checkbox',
        'class' => ['form-row patch-sms-on'],
        'label' => esc_html($sms_on_label),
        'value'  => true,
        'default' => 0,
        'required'  => false
      ];
    }
  
    $email_on = get_option('patch_retention_checkout_email_on');
    if ($email_on) {
      $email_on_label = get_option('patch_retention_checkout_email_on_label');
      if (!$email_on_label) {
        $email_on_label = 'Subscribe to Email marketing messages';
      }
      $fields['billing'][self::$field_prefix . 'email_on'] = [
        'type' => 'checkbox',
        'class' => ['form-row patch-email-on'],
        'label' => esc_html($email_on_label),
        'value'  => true,
        'default' => 0,
        'required'  => false
      ];
    }
  
    return $fields;
  }

  public static function after_billing () {
    $marketing_disclosure = get_option('patch_retention_checkout_marketing_disclosure');
    if ($marketing_disclosure) {
      $marketing_disclosure_label = get_option('patch_retention_checkout_marketing_disclosure_label');
      if (!$marketing_disclosure_label) {
        $marketing_disclosure_label = '** By checking this box and entering your phone number above, you consent/agree to receive marketing messages at the number provided from us, including messages sent by autodialer. Consent is not a condition of any purchase. Message &amp; data rates may apply. Message frequency varies. Reply HELP for help or STOP to cancel. View our <a href="">Privacy Policy</a> and <a href="">Terms of Service</a>.';
      }
      $allowed_tags = [
        'a' => [],
        'em' => [],
        'strong' => [],
        'u' => []
      ];
      echo '<div>' . wp_kses($marketing_disclosure_label, $allowed_tags) . '</div>';
    }
  }

  // Handle post data and any field that starts with $field_prefix should be added to order meta data
  public static function update_order ($order_id) {
    foreach ($_POST as $key => $value) {
      if (substr($key, 0, strlen(self::$field_prefix)) === self::$field_prefix) {
        update_post_meta( $order_id, $key, sanitize_text_field( $_POST[$key] ) );
      }
    }
  }
}
