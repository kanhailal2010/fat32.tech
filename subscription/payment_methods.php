<?php 
// ====================================================================================
// ========================= ORDER Methods ======================================
// ====================================================================================

include_once(__DIR__.'/../account/db.php');

// Function to check for an active subscription
function getSubscriptionDetails($emailOrPhone) {
  global $db;
  // First, retrieve the user's ID based on their email or phone
  $query = $db->prepare("SELECT id as user_id FROM users WHERE email = :emailOrPhone OR phone = :emailOrPhone");
  $query->bindParam(':emailOrPhone', $emailOrPhone);
  $query->execute();
  $result = $query->fetch(PDO::FETCH_ASSOC);

  // var_dump('user details for email::',$emailOrPhone, $result);

  if ($result) {
    $userId = $result['user_id'];
    
    // Then, check for an active subscription for the user
    $query = $db->prepare("SELECT * FROM subscriptions WHERE user_id = :userId AND subscription_status = 'active' ");// sub_end_date >= NOW()");
    $query->bindParam(':userId', $userId);
    $query->execute();
    $subscription = $query->fetch(PDO::FETCH_ASSOC);
    // var_dump($subscription);
    return $subscription;
    // return ($subscription !== false);
  }

  return false; // User not found
}

// For verfication of webhook
function verifyWebhookSignature($requestBody,$signature){
  $calSign = hash_hmac('sha256', $requestBody, $_ENV['RP_WEBHOOK_SECRET']);
  return $calSign == $signature ? true : false;
}

// For verification after payment on success page
// Retrieve the order_id from your server. Do not use the razorpay_order_id returned by Checkout.
// Refer: https://razorpay.com/docs/payments/payment-gateway/web-integration/standard/build-integration/#12-integrate-with-checkout-on-client-side
function verifyPaymentSignature($razorpayPaymentId, $razorpaySignature){
  $str      = $_SESSION['current_order']['id'].'|'.$razorpayPaymentId;
  $calSign  = hash_hmac('sha256', $str, $_ENV['RP_KEY_SECRET']);
  return $calSign == $razorpaySignature ? true : false;
  // var_dump($_SESSION,$_ENV);
  // require_once(__DIR__.'/../subscription/razorpay-php-2.8.7/Razorpay.php');
  // $api = new Razorpay\Api\Api($_ENV['RP_KEY_ID'], $_ENV['RP_KEY_SECRET']);
  // return $api->utility->verifyPaymentSignature(array('razorpay_order_id' => $_SESSION['current_order']['id'], 'razorpay_payment_id' => $razorpayPaymentId, 'razorpay_signature' => $razorpaySignature));
}

// TODO: We're not using the argument $status as of now
function createWebhookTransaction($status){
  try {
  // Get the webhook body and signature
  $webhookBody      = file_get_contents('php://input'); // Request body sent by Razorpay
  $webhookSignature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE']; // Signature sent by Razorpay

  $isValidSignature = verifyWebhookSignature($webhookBody, $webhookSignature);
    if ($isValidSignature) {
      // Signature is valid, proceed to save the webhook data to the database
      // Here, you can parse $webhookBody (which is in JSON format) and save it to your database
      $bool = saveWebhookTransaction(json_decode($webhookBody));
    }
    else {
      $bool = saveWebhookTransaction(json_decode($webhookBody));
      // Signature is not valid, log the error
      error_log("Invalid webhook signature received ");
    }
  }
  //catch exception
  catch(Exception $e) {
    if($debug) { echo 'Message: ' .$e->getMessage(); }
    else {
      return "DB Error:: Could not Process Webhook data";
    }
    error_log($e->getMessage());
  }
  
}

function planTitles() {
  $plans = array_keys(getPlan());
  return $plans;
}

function getPlan($plan = null) {
  $subscriptionPlans = [
    "monthly" => ["price"=>199,"name"=>"Monthly","description"=>"Full access to features for a Month"],
    "half-yearly" => ["price"=>999,"name"=>"Half Yearly","description"=>"Full access to features for a period of 6 Months"],
    "yearly" => ["price"=>1999,"name"=>"Yearly","description"=>"Full access to features for a period of 1 Year"],
  ];
  if (isset($subscriptionPlans[$plan])){    return $subscriptionPlans[$plan]; }

  return ($plan && isset($subscriptionPlans[$plan])) ? $subscriptionPlans[$plan] : $subscriptionPlans;
}

function razorpayAmount($rupees) {
  // Multiply the rupees by 100 to convert to paise
  $paise = $rupees * 100;
  // Remove any decimal places
  $paise = intval($paise);
  return $paise;
}

function createRazorOrder($receipt, $amount, $notes){ 
  require_once(__DIR__.'/../subscription/razorpay-php-2.8.7/Razorpay.php');
  $api = new Razorpay\Api\Api($_ENV['RP_KEY_ID'], $_ENV['RP_KEY_SECRET']);
  return $api->order->create([
    'receipt'         => $receipt,
    'amount'          => razorpayAmount($amount), // amount in the smallest currency unit
    'currency'        => 'INR',// <a href="/docs/payments/payments/international-payments/#supported-currencies" target="_blank">See the list of supported currencies</a>.)
    'notes'           => $notes
    ]);
 }

