<?php

function updateRetreatStock(){

  $retreat_product_id = get_option('retreat_product_id');
  $deposit_product_id = get_option('deposit_product_id');

  $retreat_variations = wc_get_product( $retreat_product_id )->get_available_variations();
  $deposit_variations = wc_get_product( $deposit_product_id )->get_available_variations();

  // Get Retreat id's
  foreach($retreat_variations as $variation){
    $accommodation = $variation["attributes"]["attribute_accommodation"];
    $retreats[$accommodation] = new stdClass();
    $retreats[$accommodation]->quantity = $variation["max_qty"];
    $retreats[$accommodation]->id = $variation["variation_id"];
  }

  // Get Deposit quantities
  foreach($deposit_variations as $variation){
    $accommodation = $variation["attributes"]["attribute_accommodation"];
    $deposits[$accommodation] = new stdClass();
    $deposits[$accommodation]->quantity = $variation["max_qty"];
    $deposits[$accommodation]->id = $variation["variation_id"];
  }

  // Update Stock
  foreach($retreats as $accommodation => $retreat){
    $deposit = ($deposits[$accommodation]);
    if($retreat->quantity < $deposit->quantity){
      $product = wc_get_product( $deposit->id );
      wc_update_product_stock($product, $retreat->quantity);
    }
    if($deposit->quantity < $retreat->quantity){
      $product = wc_get_product( $retreat->id );
      wc_update_product_stock($product, $deposit->quantity);
    }
  }

  $data = [];
  $data[0] = $retreats;
  $data[1] = $deposits;
  return $data;
}
$data = updateRetreatStock();

?>

<div style="display: grid; grid-template-columns: 200px 200px;">
  <div>
    <h3>Retreat Stock</h3>
    <?php
      foreach($data[0] as $accommodation => $retreat){
        $str = $accommodation;
        $str = $str . ' (' . $retreat->quantity . ')';
        $str = $str . '<br />';
        echo $str;
      }
    ?>
  </div>
  <div>
    <h3>Deposit Stock</h3>
    <?php
      foreach($data[1] as $accommodation => $deposit){
        $str = $accommodation;
        $str = $str . ' (' . $deposit->quantity . ')';
        $str = $str . '<br />';
        echo $str;
      }
    ?>
  </div>
</div>