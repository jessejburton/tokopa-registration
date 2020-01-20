<?php

function updateRetreatStock(){

  $retreat_product_id = get_option('retreat_product_id');
  $deposit_product_id = get_option('deposit_product_id');

  $retreat_variations = wc_get_product( $retreat_product_id )->get_available_variations();
  $deposit_variations = wc_get_product( $deposit_product_id )->get_available_variations();

  // Get Retreat id's
  foreach($retreat_variations as $variation){
    $variation_product = wc_get_product($variation["variation_id"]);
    $accommodation = $variation["attributes"]["attribute_accommodation"];
    $retreats[$accommodation] = new stdClass();
    $retreats[$accommodation]->quantity = $variation_product->get_stock_quantity();
    $retreats[$accommodation]->id = $variation["variation_id"];

    $sold[$accommodation] = (count(get_orders_by_product($variation["variation_id"])));
  }

  // Get Deposit quantities
  foreach($deposit_variations as $variation){
	  $variation_product = wc_get_product($variation["variation_id"]);
    $accommodation = $variation["attributes"]["attribute_accommodation"];
    $deposits[$accommodation] = new stdClass();
    $deposits[$accommodation]->quantity = $variation_product->get_stock_quantity();
    $deposits[$accommodation]->id = $variation["variation_id"];

    $sold[$accommodation] = $sold[$accommodation] + (count(get_orders_by_product($variation["variation_id"])));
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
  $data[2] = $sold;
  return $data;
}
$data = updateRetreatStock();

?>

<h3>Retreat Stock</h3>

<div class="registration-container">
  <table class="registration-table">
    <thead>
      <tr>
        <th>Accommodation</th>
        <th>Available</th>
        <th>Sold</th>
        <th>Remaining</th>
        <th>Deposit Stock</th>
        <th>Paid Full Stock</th>
      </th>
    </thead>
    <tbody>
      <tr>
        <td>Commuter</td>
        <td><?php echo get_option('commuter_stock'); ?></td>
        <td><?php echo $data[2]['Commuter']; ?></td>
        <td><?php echo (intval(get_option('commuter_stock')) - $data[2]['Commuter']); ?></td>
        <td><?php echo $data[0]['Commuter']->quantity; ?></td>
        <td><?php echo $data[1]['Commuter']->quantity; ?></td>
      </tr>
      <tr>
        <td>Shared Yurt</td>
        <td><?php echo get_option('shared_yurt_stock'); ?></td>
        <td><?php echo $data[2]['Shared Yurt']; ?></td>
        <td><?php echo (intval(get_option('shared_yurt_stock')) - $data[2]['Shared Yurt']); ?></td>
        <td><?php echo $data[0]['Shared Yurt']->quantity; ?></td>
        <td><?php echo $data[1]['Shared Yurt']->quantity; ?></td>
      </tr>
      <tr>
        <td>Shared Regular</td>
        <td><?php echo get_option('shared_regular_stock'); ?></td>
        <td><?php echo $data[2]['Shared Regular']; ?></td>
        <td><?php echo (intval(get_option('shared_regular_stock')) - $data[2]['Shared Regular']); ?></td>
        <td><?php echo $data[0]['Shared Regular']->quantity; ?></td>
        <td><?php echo $data[1]['Shared Regular']->quantity; ?></td>
      </tr>
      <tr>
        <td>Shared Deluxe</td>
        <td><?php echo get_option('shared_deluxe_stock'); ?></td>
        <td><?php echo $data[2]['Shared Deluxe']; ?></td>
        <td><?php echo (intval(get_option('shared_deluxe_stock')) - $data[2]['Shared Deluxe']); ?></td>
        <td><?php echo $data[0]['Shared Deluxe']->quantity; ?></td>
        <td><?php echo $data[1]['Shared Deluxe']->quantity; ?></td>
      </tr>
      <tr>
        <td>Private</td>
        <td><?php echo get_option('private_stock'); ?></td>
        <td><?php echo $data[2]['Private']; ?></td>
        <td><?php echo (intval(get_option('private_stock')) - $data[2]['Private']); ?></td>
        <td><?php echo $data[0]['Private']->quantity; ?></td>
        <td><?php echo $data[1]['Private']->quantity; ?></td>
      </tr>
    </tbody>
  </table>
</div>