<?php

  $reminder_subject = get_option('reminder_subject');
  $reminder_email = get_option('reminder_email');
  $deposit_product_id = get_option('deposit_product_id');
  $retreat_product_id = get_option('retreat_product_id');
  $balance_product_id = get_option('balance_product_id');
  $woocommerce_consumer_key = get_option('woocommerce_consumer_key');
  $woocommerce_consumer_secret = get_option('woocommerce_consumer_secret');

  if(
    !isset($reminder_subject) || $reminder_subject == '' ||
    !isset($reminder_email) || $reminder_email == '' ||
    !isset($deposit_product_id) || $deposit_product_id == '' ||
    !isset($retreat_product_id) || $retreat_product_id == '' ||
    !isset($balance_product_id) || $balance_product_id == '' ||
    !isset($woocommerce_consumer_key) || $woocommerce_consumer_key == '' ||
    !isset($woocommerce_consumer_secret) || $woocommerce_consumer_secret == ''
  ) {
    echo 'It looks like there is still some <a href="/wp-admin/admin.php?page=tokopa-registration/registration-settings.php">setup</a> to take care of.';
    die();
  }

?>