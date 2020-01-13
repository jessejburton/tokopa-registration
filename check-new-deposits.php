<?php

function get_orders_by_product_id( $product_id ) {
  global $wpdb;

  # Get All defined statuses Orders IDs for a defined product ID (or variation ID)
  $orders = $wpdb->get_col( "
      SELECT DISTINCT woi.order_id
      FROM {$wpdb->prefix}woocommerce_order_itemmeta as woim,
           {$wpdb->prefix}woocommerce_order_items as woi,
           {$wpdb->prefix}posts as p
      WHERE  woi.order_item_id = woim.order_item_id
      AND woi.order_id = p.ID
      AND woim.meta_key IN ( '_product_id', '_variation_id' )
      AND woim.meta_value LIKE '$product_id'
      ORDER BY woi.order_item_id DESC"
  );

  foreach ($orders as &$order) {
    add_order_record($order);
  }
}
get_orders_by_product_id(get_option('deposit_product_id'));

function add_order_record($order_id) {
	global $wpdb;

  $sql = "INSERT INTO {$wpdb->prefix}registration_deposits (deposit_order_id) VALUES (%d) ON DUPLICATE KEY UPDATE deposit_order_id = %d";
  $sql = $wpdb->prepare($sql,$order_id,$order_id);
  $wpdb->query($sql);
}