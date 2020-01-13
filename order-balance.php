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

$sql = "
  SELECT id, deposit_order_id, balance_order_id, reminder_sent
  FROM wp_registration_deposits";
$result = $conn->query($sql);
$balance = array();

if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    $myObj = new stdClass();
    $myObj->id = $row["id"];
    $myObj->deposit_order_id = $row["deposit_order_id"];
    $myObj->balance_order_id = $row["balance_order_id"];
    $myObj->reminder_sent = $row["reminder_sent"];

    array_push($balance, $myObj);
  }
}
$conn->close();

echo json_encode($balance);

?>