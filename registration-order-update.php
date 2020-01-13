<?php

// This is used to get the database details from wp-config.php
function find_require($file,$folder=null) {
  if ($folder === null) {$folder = dirname(__FILE__);}
  $path = $folder.'/'.$file;
  if (file_exists($path)) {require($path); return $folder;}
  else {
      $upfolder = find_require($file,dirname($folder));
      if ($upfolder != '') {return $upfolder;}
  }
}
$configpath = find_require('wp-config.php');

$servername = DB_HOST;
$username = DB_USER;
$password = DB_PASSWORD;
$dbname = DB_NAME;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Takes raw data from the request
$json = json_decode(file_get_contents('php://input'));
$deposit_order_id = $json->deposit_order_id;
$balance_order_id = $json->balance_order_id;
$reminder_sent = $json->reminder_sent;

$sql = "
  UPDATE wp_registration_deposits
  SET balance_order_id = $balance_order_id ";

if($reminder_sent !== null) {
  $sql = $sql . "reminder_sent = $reminder_sent ";
}

$sql = $sql . "WHERE deposit_order_id = $deposit_order_id";

if ($conn->query($sql) === TRUE) {
  $myObj = new stdClass();
  $myObj->deposit_order_id = $deposit_order_id;
  $myObj->balance_order_id = $balance_order_id;
  $myObj->reminder_sent = $reminder_sent;
  echo json_encode($myObj);
} else {
    echo json_encode("Error updating record: " . $conn->error);
}

$conn->close();

?>