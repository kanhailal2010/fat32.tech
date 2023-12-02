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
  
  $webhookBodyObj   = json_decode($webhookBody);
  $isValidSignature = verifyWebhookSignature($webhookBody, $webhookSignature);
    if ($isValidSignature) {
      // Signature is valid, proceed to save the webhook data to the database
      // Here, you can parse $webhookBody (which is in JSON format) and save it to your database
      $bool = orderPaidWebhookTransaction($webhookBodyObj);

      // update the subscribed plan to the user
      $bool = setupUserSubscribedPlan($webhookBodyObj);
    }
    else {
      $bool = orderPaidWebhookTransaction($webhookBodyObj);
      // Signature is not valid, log the error
      error_log("Invalid webhook signature received ");
    }
    return true;
  }
  //catch exception
  catch(Exception $e) {
    error_log($e->getMessage());
    if($debug) { echo 'Message: ' .$e->getMessage(); }
    else {
      return false;
    }
  }
  
}

// after payment confirmation using webhook 
// update the user with active subscription plan
function setupUserSubscribedPlan($obj){
  $subs = new StdClass();
  $subs->user_id              = $obj->payload->payment->entity->notes->user_id;
  $subs->email                = $obj->payload->payment->entity->notes->user_email;
  $subs->sub_plan_id          = $obj->payload->payment->entity->notes->plan_id;
  $subs->sub_plan_details     = $obj->payload->payment->entity->notes->plan;
  $subs->sub_start_date       = Date('Y-m-d H:i:s');
  $time                       = strtotime($subs->sub_start_date);
  $subs->sub_end_date         = date("Y-m-d H:i:s", strtotime("+1 month", $time));
  $subs->subscription_status  = 'active';

  try {
    return insertSubscription($subs);
  }
  //catch exception
  catch(Exception $e) {
    error_log('ERROR::USER_SUBSCRIPTIONS_INSERT:: '.$e->getMessage());
    $isDuplicateRow = isDuplicateError($e->getMessage());
    $orderId = $obj->payload->payment->entity->order_id;
    $paymentId = $obj->payload->payment->entity->id;
    if($isDuplicateRow[0]) {
      $log = "PREPAID_subscription: ".$isDuplicateRow[1].' order_id:['.$orderId.'] payment_id['.$paymentId.']'.PHP_EOL.
      ' plan_id['.$subs->sub_plan_id.'] plan_details['.$subs->sub_plan_details.'] start_date['.$subs->sub_start_date.'] end_date['.$subs->sub_end_date.']'.PHP_EOL;
      applog($log);
      $subs->subscription_status = 'queued';
      $subs->order_id            = $orderId;
      $subs->payment_id          = $paymentId;
      $bool = insertPrepaidSubscription($subs);
      // if could not insert to prepaid_subsctiptions also then log the error but return true;
      if(!$bool) {
        applog('PREPAID_SUBSCRIPTION_INSERT_FAIL::'.json_encode($obj));
      }
      return true;
    }
    return false;
  } 
}

function planTitles() {
  $plans = array_keys(getPlan());
  return $plans;
}

function getPlan($plan = null) {
  $subscriptionPlans = [
    "trial" => ["id"=> 1, "price"=>0, "name"=>"7 Day Trial","description"=>"Full access to features for a Trial period of 7 Working Days"],
    "monthly" => ["id"=> 2, "price"=>199,"name"=>"Monthly","description"=>"Full access to features for a Month"],
    "half-yearly" => ["id"=> 3, "price"=>999,"name"=>"Half Yearly","description"=>"Full access to features for a period of 6 Months"],
    "yearly" => ["id"=> 4, "price"=>1999,"name"=>"Yearly","description"=>"Full access to features for a period of 1 Year"],
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

 //To check if the error is a duplicate error 
//  return [boolean, email_if_matched]
 //GTP: Using PHP write me a method, which on matching this error string "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'kanhailal2010@gmail.com' for key 'subscriptions.email'" will return an array. The email in the error string can change but rest of the string has to be compared. On matching the method should return an array with first element as true and second element as the email for which error occurred.
 function isDuplicateError($errorString) {
  $pattern = "/SQLSTATE\[23000\]: Integrity constraint violation: 1062 Duplicate entry '(.+)' for key 'subscriptions.email'/";
  
  // Perform regex match
  if (preg_match($pattern, $errorString, $matches)) {
      // Extract the email from the matched string
      $email = $matches[1];

      // Return an array indicating a match and the email
      return [true, $email];
  } else {
      // No match found
      return [false, null];
  }
}
// // Example usage:
// $errorString = "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'kanhailal2010@gmail.com' for key 'subscriptions.email'";
// $result = matchErrorString($errorString);
// echo '<pre>'.print_r($result, true).'</pre>';
// $errorString = "SQLSTATE[23005]: Integrity coint violation: 1062 Duplicate entry 'kanhailal2010@gmail.com' for key 'subscriptions.email'";
// $result = matchErrorString($errorString);
// echo '<pre>'.print_r($result, true).'</pre>';
// $errorString = "SQLSTATE[23002]: Integrity constraint violation: 1062 Duplicate entry 'kanhailal2010@gmail.com' for key 'subscriptions.email'";
// $result = matchErrorString($errorString);
// echo '<pre>'.print_r($result, true).'</pre>';
// $errorString = "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'kanhailal4488@gmail.com' for key 'subscriptions.email'";
// $result = matchErrorString($errorString);
// echo '<pre>'.print_r($result, true).'</pre>';
// $errorString = "SQLSTATE[23000]: Integrity constraint violation: 1062  entry 'kanhailal4488@gmail.com' for key 'subscriptions.email'";
// $result = matchErrorString($errorString);
// echo '<pre>'.print_r($result, true).'</pre>';