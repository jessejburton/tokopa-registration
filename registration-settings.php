<h1>Annual Retreat Settings</h1>

<style>
  table {
    min-width: 50%;
  }

  input[type=text] {
    width: 100%;
  }
</style>

<form method="post" action="options.php">
  <?php settings_fields( 'tokopa_registration_options_group' ); ?>
  <h2>Reminder Email</h2>

  <table>
    <tr valign="top">
      <th scope="row">
        <label for="retreat_product_id">Reminder Email Subject</label>
      </th>
      <td>
        <input type="text" id="reminder_subject" name="reminder_subject" value="<?php echo get_option('reminder_subject'); ?>" />
      </td>
    </tr>
    <tr valign="top">
      <th scope="row">
        <label for="retreat_product_id">Reminder Email Content</label>
      </th>
      <td>
        <?php
          $content = get_option('reminder_email');
          wp_editor( $content, 'reminder_email', $settings = array('textarea_rows' => 10));
        ?>
      </td>
    </tr>
  </table>

  <h2>Advanced</h2>
  <table>
    <tr valign="top">
      <th scope="row">
        <label for="retreat_product_id">Retreat Product ID</label>
      </th>
      <td>
        <input type="text" id="retreat_product_id" name="retreat_product_id" value="<?php echo get_option('retreat_product_id'); ?>" />
      </td>
    </tr>
    <tr valign="top">
      <th scope="row">
        <label for="deposit_product_id">Deposit Product ID</label>
      </th>
      <td>
        <input type="text" id="deposit_product_id" name="deposit_product_id" value="<?php echo get_option('deposit_product_id'); ?>" />
      </td>
    </tr>
    <tr valign="top">
      <th scope="row">
        <label for="balance_product_id">Balance Product ID</label>
      </th>
      <td>
        <input type="text" id="balance_product_id" name="balance_product_id" value="<?php echo get_option('balance_product_id'); ?>" />
      </td>
    </tr>
    <tr valign="top">
      <th scope="row">
        <label for="woocommerce_consumer_key">Consumer Key</label>
      </th>
      <td>
        <input type="text" id="woocommerce_consumer_key" name="woocommerce_consumer_key" value="<?php echo get_option('woocommerce_consumer_key'); ?>" />
      </td>
    </tr>
    <tr valign="top">
      <th scope="row">
        <label for="woocommerce_consumer_secret">Consumer Secret</label>
      </th>
      <td>
        <input type="text" id="woocommerce_consumer_secret" name="woocommerce_consumer_secret" value="<?php echo get_option('woocommerce_consumer_secret'); ?>" />
      </td>
    </tr>
  </table>
  <?php submit_button(); ?>
</form>