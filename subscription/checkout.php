<?php 
// TODO:Add Checkbox for accepting terms and conditions

if(!isset($_POST['selected_plan'])) {
  // redirectTo(SITE_URL.'subscription/plans');
}

// $order_id = 'order_N6JrZYBG5ULug9';
$order_id = '';
// $order_details = $_SESSION['current_order'];
// debug($order_details);
if(isset($_POST['selected_plan']) && verifyCaptcha()) {
  $response         = new StdClass();
  $response->status = false;
  $selectedPlan     = sanitizeInput($_POST['subscription_plan_code'], 'fullname');
  $selectedPlanName = sanitizeInput($_POST['subscription_plan_name'], 'fullname');
  $selectedPlanId   = sanitizeInput($_POST['subscription_plan_id'], 'number');
  if(!isset($_POST['subscription_plan_code']) || !in_array($selectedPlan, planCodes())) {
    $response->msg = "Invalid subscription plan";
    echo json_encode($response);
    exit();
  }
  
  try {
    $plan       = getPlans($selectedPlan);
    $user       = getUserByEmail($_SESSION['user_email']);
    $user_id    = $user['id'];
    $user_email = $user['email'];
    $amount     = $plan['price'];
    $receipt    = generateReceiptId($user['id']);
    $notes      = [
      'user_id'     => $user_id,
      'user_email'  => $user_email,
      'plan_code'   => $selectedPlan,
      'plan_desc'   => $selectedPlanName,
      'plan_id'     => $selectedPlanId
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
        "user_email" => $order->notes->user_email,
        "user_id"    => $order->notes->user_id,
        "plan_code"  => $order->notes->plan_code,
        "plan_desc"  => $order->notes->plan_desc,
        "plan_id"    => $order->notes->plan_id
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

<div class="background">
  <section id="plans" class="features-area">
    <div class="row">
      <div class="col"></div>
      <div class="col-10 col-sm-10 text-center">

      <?php if(!empty($order_id)): ?>
          <div class="panel pricing-table noflex">
            <h3 class="summary-header">Order Summary</h3>
            <hr/>
            <div class="summary-table mt-50 mb-20">
              <div class="pricing-feature-item">
                <?php echo $order_details['notes']['plan_desc'];?> Subscription
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
    "description": "<?php echo $order_details['notes']['plan_code'];?> Subscription",
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
        "payment_for": "<?php echo $order_details['notes']['plan_code'];?> Subscription",
        "user_email" : "<?=$order->notes->user_email?>",
        "user_id"    : "<?=$order->notes->user_id?>",
        "plan_code"  : "<?=$order->notes->plan_code?>",
        "plan_desc"  : "<?=$order->notes->plan_desc?>",
        "plan_id"    : "<?=$order->notes->plan_id?>"
    },
    "theme": {
        "color": "#3399cc"
    }
};
var rzp1 = new Razorpay(options);
rzp1.on('payment.failed', function (response){
        // alert(response.error.code);
        // alert(response.error.description);
        // alert(response.error.source);
        // alert(response.error.step);
        // alert(response.error.reason);
        // alert(response.error.metadata.order_id);
        // alert(response.error.metadata.payment_id);
});
// setTimeout(() => { rzp1.open(); }, 2000);

document.getElementById('rzp-button1').onclick = function(e){
    rzp1.open();
    e.preventDefault();
}
</script>
<?php endif; ?>
      </div> <!-- col-10 -->

      <div class="col"></div>

    </div><!-- row -->
  </section>
</div><!-- background -->