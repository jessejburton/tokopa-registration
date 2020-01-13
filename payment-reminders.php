<h1>Payment Reminders</h1>

<?php

  require_once('check-settings.php');

  // Get Balance Orders
  global $wpdb;
  global $woocommerce;

  $balance_orders = $wpdb->get_results( "
    SELECT deposit_order_id, balance_order_id, reminder_sent
    FROM {$wpdb->prefix}registration_deposits"
  );

  if(isset($_POST['send_selected_order'])){
    $orders_to_send = array();
    array_push($orders_to_send, $_POST["balance_order_id"]);
  }

  if(isset($_POST['send_unset_reminders'])){
    $orders_to_send = array();
    foreach ($balance_orders as $order) {
      if(!isset($reminder_sent) || $reminder_sent === '0000-00-00 00:00:00'){
        array_push($orders_to_send, $order->balance_order_id);
      }
    }
  }

  if(isset($_POST['send_all_reminders'])){
    $orders_to_send = array();
    foreach ($balance_orders as $order) {
      array_push($orders_to_send, $order->balance_order_id);
    }
  }

  if(isset($orders_to_send)){
    foreach ($orders_to_send as $order) {
      sendReminderEmail($order);
      echo '<div style="line-height: 50px; margin: 0 25px 25px 0;" class="updated notice notice-success is-dismissible">Payment reminder sent for order #' . $order . '</div>';
    }

    // Get new Balance Order Information
    $balance_orders = $wpdb->get_results( "
      SELECT deposit_order_id, balance_order_id, reminder_sent
      FROM {$wpdb->prefix}registration_deposits"
    );
  }

  // Add any new records
  require_once('check-new-deposits.php');

  $sent = 0;
  $unsent = 0;
  $orders = [];

  foreach ($balance_orders as $order) {
    $deposit_order_id = $order->deposit_order_id;
    $balance_order_id = $order->balance_order_id;
    $reminder_sent = $order->reminder_sent;

    if(!isset($balance_order_id) || $balance_order_id === ''){
      header("Location: /wp-admin/admin.php?page=tokopa-registration/registration-deposits.php&reminders=true");
      die();
    }

    if(!isset($reminder_sent) || $reminder_sent === '0000-00-00 00:00:00'){
      $unsent ++;
    } else {
      $sent ++;
    };

    $deposit_order = wc_get_order( $deposit_order_id );
    $balance_order = wc_get_order( $balance_order_id );

    $myObj = new stdClass();
    $myObj->deposit_order_id = $deposit_order_id;
    $myObj->balance_order_id = $balance_order_id;
    $myObj->customer = $deposit_order->get_billing_first_name() . ' ' . $deposit_order->get_billing_last_name();
    $myObj->email = $deposit_order->get_billing_email();

    array_push($orders, $myObj);

  };

  function sendReminderEmail($order_id){
    $order = wc_get_order( $order_id );

    $pay_now_url = esc_url( $order->get_checkout_payment_url() );
    $pay_now_link = '<a href="' . $pay_now_url . '">pay</a>';
    $to = $order->get_billing_email();
    $subject = get_option('reminder_subject');
    $body = str_replace("{pay}", $pay_now_link, get_option('reminder_email'));
    $headers = array('Content-Type: text/html; charset=UTF-8');

    wp_mail( $to, $subject, $body, $headers );

    // Update Reminder Date
    global $wpdb;

    $table_name = $wpdb->prefix . 'registration_deposits';

    $wpdb->update(
      $table_name,
      array('reminder_sent' => current_time( 'mysql' )),
      array('balance_order_id' => $order_id)
    );
  }

?>

<p style="font-size: 1.8em;">
  <strong><?php echo count($balance_orders); ?></strong> deposit orders<br />
  <strong><?php echo $sent; ?></strong> reminders sent<br />
  <strong><?php echo $unsent; ?></strong> reminders not sent<br />
</p>

<form
  name="send_selected_order_form"
  method="post"
  onsubmit="return confirm(`This will send payment reminder to ${orders_to_send}, are you sure?`);"
>
  <div style="margin-bottom: 20px;">
    <h2>Send Single Payment Reminder</h2>
    <select name="balance_order_id">
      <?php
        foreach ($orders as $order) {
          $output = '<option data-email="' . $order->email .'" value="' . $order->balance_order_id . '">';
          $output = $output . $order->customer . ' (' . $order->deposit_order_id . ' | ' . $order->balance_order_id . ')';
          $output = $output . '</option>';
          echo $output;
        }
      ?>
    </select>
    <button
      class="button button-primary"
      type="submit"
      name="send_selected_order"
      onclick="orders_to_send=select.options[select.selectedIndex].dataset.email"
    >
      Send
    </button><br />
    <p style="font-size: 1em; font-style: italic;">* the numbers after the name are the id's of the original order and the balance order respectively.</p>
  </div>
  <hr />
  <div style="margin-bottom: 20px;">
    <h2>Send Bulk Payment Reminders</h2>
    <button
      class="button"
      type="submit"
      name="send_unset_reminders"
      onclick="orders_to_send='<?php echo $unsent; ?> registrants'"
    >
      Send Payment Reminders for <strong>ALL UNSENT</strong> Orders (<?php echo $unsent; ?>)
    </button>
  </div>
  <div style="margin-bottom: 20px;">
    <button
      class="button"
      type="submit"
      name="send_all_reminders"
      onclick="orders_to_send='all <?php echo count($balance_orders); ?> registrants'"
    >
      Send Payment Reminders for <strong>ALL</strong> Orders (<?php echo count($balance_orders); ?>)
    </button>
  </div>
</form>

<script>
  let orders_to_send = '';
  const select = document.querySelector("select[name=balance_order_id]");
</script>

