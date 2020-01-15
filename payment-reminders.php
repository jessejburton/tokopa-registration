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

  for ($x = 0; $x < count($balance_orders); $x++) {
    $order_details = wc_get_order( $balance_orders[$x]->balance_order_id );
    if($order_details->get_status() !== 'pending'){
      unset($balance_orders[$x]);
    }
  }

  if(isset($_POST['send_selected_order'])){
    $orders_to_send = array();
    array_push($orders_to_send, $_POST["balance_order_id"]);
  }

  if(isset($_POST['send_unset_reminders'])){
    $orders_to_send = array();
    foreach ($balance_orders as $order) {
      $balance_order = wc_get_order( $order );
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

    $send_to_email = '';
    if(isset($_POST["test_email_check"])){
      $send_to_email = $_POST["test_check_email"];
    }

    foreach ($orders_to_send as $order) {
      sendReminderEmail($order, $send_to_email);
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

  function sendReminderEmail($order_id, $email){
    // Get Order details
    global $wpdb;
    $balance_orders = $wpdb->get_row( "
      SELECT deposit_order_id, balance_order_id, reminder_sent
      FROM {$wpdb->prefix}registration_deposits
      WHERE balance_order_id = {$order_id}"
    );
    $deposit_order = wc_get_order( $balance_orders->deposit_order_id );
    $order = wc_get_order( $balance_orders->balance_order_id );

    $pay_now_url = esc_url( $order->get_checkout_payment_url() );
    $pay_now_link = '<a href="' . $pay_now_url . '">pay</a>';

    $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

    if($email !== ''){
      $test = true;
      $to = $email;
    } else {
      $test = false;
      $to = $order->get_billing_email();
    }
    $subject = get_option('reminder_subject');

    $styles = '<style type="text/css">
      table {
        width: 100%;
      }
      th {
        background-color: #b279ff;
        color: white;
        font-weight: bold;
        text-align: center;
        padding: 10px;
      }
      td {
        width: 50%;
        padding: 25px;
        text-align: center;
      }
      .button {
        text-decoration: none;
        text-transform: uppercase;
        padding: 10px 15px;
        cursor: pointer;
        color: white;
        border: 1px solid #8b53d8;
        background: linear-gradient(to bottom,#b279ff 0,#a35fff 100%);
        transition: all .3s ease;
      }
      .button:hover {
        background: #8b53d8;
      }
      @media only screen and (max-width: 30em) {
        .button { display: block }
      }
    </style>';

    $invoice_details = '<table style="width: 100%; border: 1px solid black;">';
      $invoice_details = $invoice_details . '<tr><th>Deposit</th><th>Balance</th></tr>';
      $invoice_details = $invoice_details . '<tr>';
        $invoice_details = $invoice_details . '<td>Paid $'. $deposit_order->get_total()  .' on ' . date_format($deposit_order->get_date_paid(), 'F jS, Y') . '</td>';
        $invoice_details = $invoice_details . '<td><a class="button" href="' . $pay_now_url . '">Pay Balance of $'. $order->get_total()  .'</a></td>';
      $invoice_details = $invoice_details . '</tr>';
    $invoice_details = $invoice_details . '</table>';

    $content = str_replace(["{pay}", "{name}", "{details}"], [$pay_now_link, $customer_name, $invoice_details], wpautop(get_option('reminder_email')));

    $body = $styles;
    $body = $body . '<div class="content" style="width:650px; margin: 0 auto; font-family: sans-serif; font-size: 16px;">';
      $body = $body . '<img src="https://toko-pa.com/wp-content/uploads/2020/01/image0.jpeg" />';
      $body = $body . '<div style="margin: 25px 0;">';
        $body = $body . $content;
      $body = $body . '</div>';
    $body = $body . '</div>';

    $headers = array('Content-Type: text/html; charset=UTF-8');

    wp_mail( $to, $subject, $body, $headers );

    // Update Reminder Date
    if(!$test){
      global $wpdb;

      $table_name = $wpdb->prefix . 'registration_deposits';

      $wpdb->update(
        $table_name,
        array('reminder_sent' => current_time( 'mysql' )),
        array('balance_order_id' => $order_id)
      );
    }
  }

?>

<p style="font-size: 1.8em;">
  <strong><?php echo count($balance_orders); ?></strong> unpaid deposit orders<br />
  <strong><?php echo $sent; ?></strong> unpaid reminder(s) sent<br />
  <strong><?php echo $unsent; ?></strong> unpaid reminder(s) not sent<br />
</p>

<form
  name="send_selected_order_form"
  method="post"
  onsubmit="return checkMessage();"
>
  <div style="margin-bottom: 20px;">
    <h2>Send Single Payment Reminder</h2>
    <p>
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
      </button>
    </p>

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
      Send Payment Reminders for <strong>ALL UNPAID & UNSENT</strong> Orders (<?php echo $unsent; ?>)
    </button>
  </div>
  <div style="margin-bottom: 20px;">
    <button
      class="button"
      type="submit"
      name="send_all_reminders"
      onclick="orders_to_send='all <?php echo count($balance_orders); ?> registrants'"
    >
      Send Payment Reminders for <strong>ALL UNPAID</strong> Orders (<?php echo count($balance_orders); ?>)
    </button>
  </div>
  <hr />
  <h3>Send Test</h3>
  <p>
    <input type="checkbox" id="test_email_check" name="test_email_check" />
    <label for="test_email_check">check to send test emails only</label>
  </p>
  <p id="test_email_div">
    <label>Send test emails to: </label>
    <input
      type="text"
      name="test_check_email"
      id="test_check_email"
      placeholder="Enter the email to send a test to."
      value="<?php echo wp_get_current_user()->user_email; ?>" />
  </p>
</form>

<style>
#test_email_div {
  opacity: 0;
  transition: all .3s ease;
}
#test_email_div.show {
  opacity: 1;
}
</style>

<script>
  let orders_to_send = '';
  const select = document.querySelector("select[name=balance_order_id]");

  let is_test = '';

  const test_check = document.getElementById("test_email_check");
  const test_check_div = document.getElementById("test_email_div");
  const test_check_email = document.getElementById("test_check_email");

  test_check.addEventListener("change", (e)=> {
    if(e.target.checked){
      is_test = 'test';
      test_check_div.classList.add("show");
    } else {
      is_test = '';
      test_check_div.classList.remove("show");
    }
  });

  function checkMessage(){
    if(is_test === 'test'){
      return confirm(`This will send test payment reminders for ${orders_to_send} to ${test_check_email.value}, are you sure?`);
    } else {
      return confirm(`This will send payment reminder to ${orders_to_send}, are you sure?`);
    }
  }
</script>

