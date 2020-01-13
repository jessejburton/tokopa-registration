<?php require_once('check-new-deposits.php'); ?>

<h1>Annual Retreat Registration</h1>

<?php require_once('check-settings.php'); ?>

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
        <tr><td colspan="10" class="loading">Loading...</td></tr>
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