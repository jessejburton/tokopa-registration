<?php require_once('check-new-deposits.php'); ?>

<?php

function get_orders_by_product( $product_id ) {
    global $wpdb;

    $orders_statuses = "'wc-completed', 'wc-processing', 'wc-on-hold', 'wc-pending'";

    return $wpdb->get_col( "
        SELECT DISTINCT woi.order_id
        FROM {$wpdb->prefix}woocommerce_order_itemmeta as woim,
             {$wpdb->prefix}woocommerce_order_items as woi,
             {$wpdb->prefix}posts as p
        WHERE  woi.order_item_id = woim.order_item_id
        AND woi.order_id = p.ID
        AND p.post_status IN ( $orders_statuses )
        AND woim.meta_key IN ( '_product_id', '_variation_id' )
        AND woim.meta_value LIKE '$product_id'
        ORDER BY woi.order_item_id DESC"
    );
}

$registrants = 0;
$deposit_registrants = 0;
$paid_registrants = 0;
$pending_registrants = 0;

// DEPOSIT ORDERS
$deposit_product_id = get_option('deposit_product_id');
$deposit_orders = get_orders_by_product($deposit_product_id);
$deposit_order_details = [];
foreach($deposit_orders as $order_id){
  $order = wc_get_order($order_id);
  $items = $order->get_items();

  $myObj = new stdClass();
  $myObj->id = $order_id;
  $myObj->url = $order->get_edit_order_url();
  $myObj->date = date_format($order->get_date_created(), "M d, Y");
  $myObj->status = $order->get_status();
  $myObj->name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
  $myObj->email = $order->get_billing_email();
  $myObj->amount = $order->get_total();
  $myObj->data = json_encode($order->get_data());

  foreach($items as $item){
    $myObj->accommodation_id = $item["variation_id"];
    $myObj->accommodation = $item["accommodation"];
  }
  array_push($deposit_order_details, $myObj);
  if($myObj->status === "completed"){
	  $registrants = $registrants + 1;
	  $deposit_registrants = $deposit_registrants + 1;
  }
  if($myObj->status === "pending"){
	$pending_registrants = $pending_registrants + 1;
  }
}

// PAID IN FULL ORDERS
$retreat_product_id = get_option('retreat_product_id');
$retreat_orders = get_orders_by_product($retreat_product_id);
$retreat_order_details = [];
foreach($retreat_orders as $order_id){
  $order = wc_get_order($order_id);
  $items = $order->get_items();

  $myObj = new stdClass();
  $myObj->id = $order_id;
  $myObj->url = $order->get_edit_order_url();
  $myObj->date = date_format($order->get_date_created(), "M d, Y");
  $myObj->status = $order->get_status();
  $myObj->name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
  $myObj->email = $order->get_billing_email();
  $myObj->amount = $order->get_total();
  $myObj->data = json_encode($order->get_data());

  foreach($items as $item){
    $myObj->accommodation_id = $item["variation_id"];
    $myObj->accommodation = $item["accommodation"];
  }
  array_push($retreat_order_details, $myObj);
  if($myObj->status === "completed"){
	  $registrants = $registrants + 1;
	  $paid_registrants = $paid_registrants + 1;
  }
  if($myObj->status === "pending"){
	$pending_registrants = $pending_registrants + 1;
  }
  var_dump($myObj->status);
}

?>

<h1>Annual Retreat Registration</h1>

<?php require_once('check-settings.php'); ?>

<h2>Registrants</h2>
<p>
	There are <strong><?php echo $registrants; ?> total registrants</strong>
</p>
<p>
	There are <strong><?php echo $deposit_registrants; ?> registrants who paid a deposit</strong>
</p>
<p>
	There are <strong><?php echo $paid_registrants; ?> registrants who paid in full</strong>
</p>
<p>
	There are <strong><?php echo $pending_registrants; ?> registrants who have a payment pending</strong>
</p>

<div class="registration-wrapper">
  <h2>Paid with Deposit</h2>
  <div class="registration-container" id="paid_deposit">
    <table class="registration-table">
      <thead>
        <tr>
          <th>Order</th>
          <th>Date</th>
          <th>Status</th>
          <th>Name</th>
          <th>Email</th>
          <th>Amount</th>
          <th>Accommodation</th>
          <th>Balance Order</th>
          <th>Balance Amount</th>
          <th>Reminders</th>
        </tr>
      </thead>
      <tbody>
        <?php
          foreach($deposit_order_details as $order){
            $str = "<tr data-orderid='". $order->id ."' data-accommodation='". $order->accommodation."' data-order='".$order->data."'>";
            $str = $str . '<td><a href="'.$order->url.'">'. $order->id .'</a></td>';
            $str = $str . '<td>'. $order->date .'</td>';
            $str = $str . '<td>'. $order->status .'</td>';
            $str = $str . '<td>'. $order->name .'</td>';
            $str = $str . '<td><a href="mailto:'.$order->email.'">'. $order->email .'</a></td>';
            $str = $str . '<td>$'. $order->amount .'</td>';
            $str = $str . '<td>'. $order->accommodation .'</td>';
            $str = $str . '<td class="balance-order"></td>';
            $str = $str . '<td class="balance-amount"></td>';
            $str = $str . '<td class="balance-email"></td>';
            $str = $str . '</tr>';

            echo $str;
          }
        ?>
      </tbody>
      <tfoot></tfoot>
    </table>
  </div>

  <h2>Paid in Full</h2>
  <div class="registration-container" id="paid_in_full">
    <table class="registration-table">
      <thead>
        <tr>
          <th>Order</th>
          <th>Date</th>
          <th>Status</th>
          <th>Name</th>
          <th>Email</th>
          <th>Amount</th>
          <th>Accommodation</th>
        </tr>
      </thead>
      <tbody>
        <tr><td colspan="7" class="loading">Loading...</td></tr>
      </tbody>
      <tfoot></tfoot>
    </table>
  </div>
</div>

<?php require_once('get-quantities.php'); ?>