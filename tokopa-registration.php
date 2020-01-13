<?php

/**
 * @package TokopaRegistration
 *
**/
/*
Plugin Name: Toko-pa ~ Registration Deposits
Plugin URI: https://www.burtonmediainc.com/plugins/tokopadeposits
Description: A plugin that handles sending reminder invoices to customers
Version: 1.0.0
Author: Jesse James Burton
Author URI: https://www.burtonmediainc.com
License: GPLv2 or Later
Text Domain: tokopa-regisration
GIT: https://github.com/jessejburton/Tokopa-Registration-Deposits
*/

/* Include Styles */
function add_tokopa_registration_plugin_styles($hook) {
  // Load only on ?page=tokopa-registration/registration-deposits.php
  if( $hook != 'tokopa-registration/registration-deposits.php' ) {
    return;
  }
  wp_enqueue_style( 'tokopa-registration-styles', plugins_url('tokopa-registration.css',__FILE__ ), array(), '1.1', 'all');
}
add_action( 'admin_enqueue_scripts', 'add_tokopa_registration_plugin_styles' );

/* Include Scripts */
function add_tokopa_registration_plugin_script($hook) {
  // Load only on ?page=tokopa-registration/registration-deposits.php
  if( $hook != 'tokopa-registration/registration-deposits.php' ) {
    return;
  }
  wp_enqueue_script( 'tokopa-registration-scripts', plugins_url('tokopa-registration.js',__FILE__ ), array(), '1.1', 'all', false);

  // Localize the plugin options to the script
  $consumer_key = get_option('woocommerce_consumer_key');
  $consumer_secret = get_option('woocommerce_consumer_secret');
  $key = $consumer_key . ':' . $consumer_secret;

  $translation_array = array(
    'deposit_id' => get_option('deposit_product_id'),
    'product_id' => get_option('retreat_product_id'),
    'balance_id' => get_option('balance_product_id'),
    'plugin_url' => plugins_url() . '/tokopa-registration',
    'key' => base64_encode($key)
  );
  wp_localize_script( 'tokopa-registration-scripts', 'options', $translation_array );
}
add_action( 'admin_enqueue_scripts', 'add_tokopa_registration_plugin_script' );

/**
 * Register Menu Page
 */
function register_tokopa_registration_menu_page() {
  add_menu_page(
    'Annual Retreat',
    'Annual Retreat',
    'manage_options',
    'tokopa-registration/registration-deposits.php',
    '',
    'dashicons-groups',
    5
  );

  add_submenu_page(
    'tokopa-registration/registration-deposits.php',
    'Payment Reminders',
    'Payment Reminders',
    'manage_options',
    'tokopa-registration/payment-reminders.php',
    '',
    1
  );

  add_submenu_page(
    'tokopa-registration/registration-deposits.php',
    'Settings',
    'Settings',
    'manage_options',
    'tokopa-registration/registration-settings.php',
    'tokopa_registration_settings_page',
    2
  );
}
add_action( 'admin_menu', 'register_tokopa_registration_menu_page' );

/**
 * Register Settings
 */
function register_tokopa_registration_settings() {
  add_option( 'reminder_subject', '');
  add_option( 'reminder_email', '');
  add_option( 'deposit_product_id', '');
  add_option( 'retreat_product_id', '');
  add_option( 'balance_product_id', '');
  add_option( 'woocommerce_consumer_key', '');
  add_option( 'woocommerce_consumer_secret', '');

  register_setting( 'tokopa_registration_options_group', 'reminder_subject', '' );
  register_setting( 'tokopa_registration_options_group', 'reminder_email', '' );
  register_setting( 'tokopa_registration_options_group', 'deposit_product_id', '' );
  register_setting( 'tokopa_registration_options_group', 'retreat_product_id', '' );
  register_setting( 'tokopa_registration_options_group', 'balance_product_id', '' );
  register_setting( 'tokopa_registration_options_group', 'woocommerce_consumer_key', '' );
  register_setting( 'tokopa_registration_options_group', 'woocommerce_consumer_secret', '' );
}
add_action( 'admin_init', 'register_tokopa_registration_settings' );

function tokopa_registration_settings_page(){
  require_once('registration-settings.php');
}

/**
 * Database
 */
global $deposits_db_version;
$deposits_db_version = '1.0';

function deposits_install() {
	global $wpdb;
	global $deposits_db_version;

	$table_name = $wpdb->prefix . 'registration_deposits';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
		deposit_order_id int NOT NULL UNIQUE,
		balance_order_id int NULL UNIQUE,
    reminder_sent datetime DEFAULT '0000-00-00 00:00:00' NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'deposits_db_version', $deposits_db_version );
}
register_activation_hook( __FILE__, 'deposits_install' );