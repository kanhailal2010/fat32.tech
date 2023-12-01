<?php 
require_once(__DIR__.'/../partials/par_util.php');
require_once(__DIR__.'/../account/db.php');
require_once(__DIR__.'/payment_methods.php');


// if(!isset($_POST['selected_plan'])) {
//   redirectTo(SITE_URL.'subscription/plans');
// }

// $order_id = 'order_N6JrZYBG5ULug9';
$order_id = '';
// $order_details = $_SESSION['current_order'];
if(isset($_POST['selected_plan']) && verifyCaptcha()) {
  $response = new StdClass();
  $response->status   = false;
  $selectedPlan = sanitizeInput($_POST['subscription_plan'], 'username');
  if(!isset($_POST['subscription_plan']) || !in_array($selectedPlan, planTitles())) {
    $response->msg = "Invalid subscription plan";
    echo json_encode($response);
    exit();
  }
  
  try {
    $plan       = getPlan($selectedPlan);
    $user       = getUserByEmail($_SESSION['user_email']);
    $user_email = $user['email'];
    $amount     = $plan['price'];
    $receipt    = generateReceiptId($user['id']);
    $notes      = [
      'user_email'  => $user_email,
      'plan'        => $selectedPlan
    ];

    $order = createRazorOrder($receipt, $amount, $notes);
    // var_dump($order);
    $response->error    = ['could not create order'];
    if(isset($order->error)) { 
      echo json_encode($response); 
      exit();
    }
    $order_details = [
      'id'              => $order->id,
      'entity'          => $order->entity,
      'amount'          => $order->amount,
      'amount_paid'     => $order->amount_paid,
      'amount_due'      => $order->amount_due,
      'currency'        => $order->currency,
      'receipt'         => $order->receipt,
      'offer_id'        => $order->offer_id,
      'status'          => $order->status,
      'attempts'        => $order->attempts,
      'notes'           => [ 
        "user_email" =>$order->notes->user_email,
        "plan"       =>$order->notes->plan
      ],
      'created_at'      => $order->created_at
    ];
    $_SESSION['current_order'] = $order_details;

    unset($response->error);
    $response->status           = true;
    $response->pg_order_id      = $order->id;
    $response->receipt          = $receipt;
    $response->user_id          = $user['id'];
    $response->order_date       = Date('Y-m-d H:i:s');
    $response->order_status     = $order->status;
    $response->total_amount     = $order->amount;
    $response->transaction_id   = '';
    $response->billing_address  = '';
    $response->order_notes      = $order_details;

    insertUserOrder($response);
    insertOrderCreateTransaction($order);

    $order_id = $order->id;
    // echo json_encode($response);
    // exit();
  }
  //catch exception
  catch(Exception $e) {
    error_log($e->getMessage());
    if($debug) { echo 'Message: ' .$e->getMessage(); }
    else {
      $response->error = "DB Error:: Could not create order";
      echo json_encode($response);
      exit();
    }
  } 
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fat32 Tech | Chrome Extension Plan Checkout</title>
  <link rel="stylesheet" href="<?php echo siteUrl('subscription/style.css'); ?> ">
</head>
<body>
<div class="background">
  <div class="container">
      <?php if(!empty($order_id)): ?>
          <div class="panel pricing-table noflex">
            <h3 class="summary-header">Order Summary</h3>
            <div class="summary-table">
              <div class="pricing-feature-item">
                <?php echo $order_details['notes']['plan'];?> Subscription
              </div>
              <div class="pricing-feature-item">
                &#8377;<?php 
                $amt = $order_details['amount']/100;
                echo number_format((float)$amt, 2, '.', '');
                ?>*
              </div>
              <div>
                <button id="rzp-button1" class="button-30">Pay Now</button>
              </div>
            </div>
          </div>
      <!-- https://razorpay.com/docs/payments/server-integration/php/payment-gateway/build-integration/ -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var options = {
    "key": "<?php echo $_ENV['RP_KEY_ID']?>", // Enter the Key ID generated from the Dashboard
    "amount": "<?php echo $order_details['amount'];?>", // Amount is in currency subunits. Default currency is INR. Hence, 50000 refers to 50000 paise
    "currency": "<?php echo $order_details['currency'];?>",
    "name": "FAT32 TECH",
    "description": "<?php echo $order_details['notes']['plan'];?> Subscription",
    "image": "<?php echo siteUrl('/assets/images/fat32-logo.png'); ?>",
    "order_id": "<?php echo $order_id;?>", //This is a sample Order ID. Pass the `id` obtained in the response of Step 1
    // "handler": function (response){
    //   // TODO: javascript payment callback handler
    //     console.log('payment Id',response.razorpay_payment_id);
    //     console.log('order Id',response.razorpay_order_id);
    //     console.log('Payment signature',response.razorpay_signature);
    // },
    "callback_url": "<?php echo siteUrl('subscription/pay_success');?>",
    "prefill": {
        "name": "<?php echo $_SESSION['user_name']; ?>",
        "email": "<?php echo $_SESSION['user_email']; ?>"
        // "contact": ""
    },
    "notes": {
        "payment_for": "<?php echo $order_details['notes']['plan'];?> Subscription",
    },
    "theme": {
        "color": "#3399cc"
    }
};
var rzp1 = new Razorpay(options);
rzp1.on('payment.failed', function (response){
        alert(response.error.code);
        alert(response.error.description);
        alert(response.error.source);
        alert(response.error.step);
        alert(response.error.reason);
        alert(response.error.metadata.order_id);
        alert(response.error.metadata.payment_id);
});
// setTimeout(() => { rzp1.open(); }, 2000);

document.getElementById('rzp-button1').onclick = function(e){
    rzp1.open();
    e.preventDefault();
}
</script>
<?php endif; ?>
  </div>
</div>
</body>
</html>