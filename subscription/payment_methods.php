<?php 
include_once(__DIR__.'/../account/db.php');

/**
 * Changing subscription plans: 
 *    we just need to update the sub_end_date in subscription table
 *    plans will have a duration(days) column 
 * 
 * Trial plan gets activated on the day the user signs up
 */

// =========================================================================================
// ========================= Subscription PLAN Methods =====================================
// =========================================================================================


function planTitles() {
  $plans = array_keys(getPlan());
  return $plans;
}

function getPlanDuration($planCode) {
  $plan = getPlan($planCode);
  return ($plan) ? $plan['duration'] : 0;
}

function getPlan($plan = null) {
  $subscriptionPlans = [
    "trial" => ["id"=> 1, "price"=>0, "duration" => 7, "name"=>"7 Day Trial","description"=>"Full access to features for a Trial period of 7 Working Days"],
    "monthly" => ["id"=> 2, "price"=>199,"duration" => 30, "name"=>"Monthly","description"=>"Full access to features for a Month"],
    "half-yearly" => ["id"=> 3, "price"=>999,"duration" => 182, "name"=>"Half Yearly","description"=>"Full access to features for a period of 6 Months"],
    "yearly" => ["id"=> 4, "price"=>1999,"duration" => 365, "name"=>"Yearly","description"=>"Full access to features for a period of 1 Year"],
  ];
  if (isset($subscriptionPlans[$plan])){    return $subscriptionPlans[$plan]; }

  return ($plan && isset($subscriptionPlans[$plan])) ? $subscriptionPlans[$plan] : $subscriptionPlans;
}

// ====================================================================================
// ========================= Subscription Methods =====================================
// ====================================================================================

// insert subscription row for user
function insertSubscription($data){
  global $db,$debug;
  $sql = "INSERT INTO subscriptions (user_id, email, sub_plan_id, sub_plan_details, sub_start_date, sub_end_date, subscription_status) ";
  $sql .= " VALUES ";
  $sql .= "(:user_id, :email, :sub_plan_id, :sub_plan_details, :sub_start_date, :sub_end_date, :subscription_status) ";
  return $db->prepare($sql)->execute([
    'user_id'             => $data->user_id,
    'email'               => $data->email,
    'sub_plan_id'         => $data->sub_plan_id,
    'sub_plan_details'    => $data->sub_plan_details,
    'sub_start_date'      => $data->sub_start_date,
    'sub_end_date'        => $data->sub_end_date,
    'subscription_status' => $data->subscription_status,
  ]);
}

// insert subscription row for user
function insertPrepaidSubscription($data){
  global $db,$debug;
  $sql = "INSERT INTO prepaid_subscriptions (user_id, email, order_id, payment_id, sub_plan_id, sub_plan_duration, sub_plan_details, sub_start_date, sub_end_date, subscription_status) ";
  $sql .= " VALUES ";
  $sql .= "(:user_id, :email, :order_id, :payment_id, :sub_plan_id, :sub_plan_duration, :sub_plan_details, :sub_start_date, :sub_end_date, :subscription_status) ";
  try {

    return $db->prepare($sql)->execute([
      // 'table_name'          => $tableName,
      'user_id'             => $data->user_id,
      'email'               => $data->email,
      'order_id'            => $data->order_id,
      'payment_id'          => $data->payment_id,
      'sub_plan_id'         => $data->sub_plan_id,
      'sub_plan_duration'   => $data->sub_plan_duration,
      'sub_plan_details'    => $data->sub_plan_details,
      'sub_start_date'      => $data->sub_start_date,
      'sub_end_date'        => $data->sub_end_date,
      'subscription_status' => $data->subscription_status,
    ]);
  }
  //catch exception
  catch(Exception $e) {
    error_log('ERROR::PREPAID_USER_SUBSCRIPTIONS_INSERT:: Could not insert to db:: '.$e->getMessage());
    error_log(json_encode($data));
    if($debug) { echo 'DB Error:: Could not insert to prepaid_subscriptions ::' .$e->getMessage(); }
    else {
      return false;
    }
  }
}

// Function to check for an active subscription
function getUserSubscriptionDetails($email) {
  global $db;
  $res = [];
  $result = getUserByEmail($email);
  if ($result) {
    $userId = $result['id'];
    // Then, check for an active subscription for the user
    $query = $db->prepare("SELECT * FROM subscriptions WHERE user_id = :userId AND subscription_status = 'active' ");// sub_end_date >= NOW()");
    $query->bindParam(':userId', $userId);
    $query->execute();
    $subscription = $query->fetch(PDO::FETCH_ASSOC);
    // var_dump($subscription);
    // return [true, $subscription];
    return ($subscription !== false) ? [true, $subscription, $result] : [false, 'Subscription details not found', $result];
  }
  else {
    return [false, 'User not found'];
  }
  return [false, 'No details found']; // User not found
}

// get prepaid subscriptions row ONE by ONE.
function getPrepaidSubscriptions($user_id){
  global $db;
  $query = $db->prepare("SELECT * FROM prepaid_subscriptions WHERE user_id = :user_id AND subscription_status = 'queued' ");
  $query->bindParam(':user_id', $user_id);;
  $subscription = $query->execute() ? $query->fetch(PDO::FETCH_ASSOC) : false;
  if(!$subscription) { return [false, 'No Prepaid subscriptions found']; }
  return [true, $subscription];
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
  $duration                   = getPlanDuration($subs->sub_plan_details);
  $subs->sub_end_date         = date("Y-m-d H:i:s", strtotime("+$duration day", $time));
  $subs->subscription_status  = 'active';

  try {
    return insertSubscription($subs);
  }
  //catch exception
  catch(Exception $e) {
    error_log('ERROR::USER_SUBSCRIPTIONS_INSERT:: '.$e->getMessage());
    $isDuplicateRow = isDuplicateErrorOnSubscription($e->getMessage());
    $orderId = $obj->payload->payment->entity->order_id;
    $paymentId = $obj->payload->payment->entity->id;
    if($isDuplicateRow[0]) {
      $log = "PREPAID_SUBSCRIPTION_ADDED::user[".$subs->email."] ".$isDuplicateRow[1].' order_id:['.$orderId.'] payment_id['.$paymentId.']'.PHP_EOL.
      ' plan_id['.$subs->sub_plan_id.'] plan_details['.$subs->sub_plan_details.'] start_date['.$subs->sub_start_date.'] end_date['.$subs->sub_end_date.']'.PHP_EOL;
      applog($log);
      // get plan duration using sub_plan_details
      $subs->sub_plan_duration    = getPlanDuration($subs->sub_plan_details);
      $subs->subscription_status  = 'queued';
      $subs->order_id             = $orderId;
      $subs->payment_id           = $paymentId;
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


//To check if the error is a duplicate error 
//  return [boolean, email_if_matched]
//GTP: Using PHP write me a method, which on matching this error string "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'kanhailal2010@gmail.com' for key 'subscriptions.email'" will return an array. The email in the error string can change but rest of the string has to be compared. On matching the method should return an array with first element as true and second element as the email for which error occurred.
function isDuplicateErrorOnSubscription($errorString) {
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

/** 
 * 
 * ==========================================================================================
 * ============================================================ TRANSACTIONS START ==========
 * ==========================================================================================
 */

// This method should be handled under try catch block with transactions
// extend the subscription period by [days] duration
// update the status if sub_end_date is greater than current date
/**
 * If the 'status' is 'active', meaning the 'subscription_end_date' is in the future, it adds [duration] days to the existing 'subscription_end_date'.
 * If the 'status' is 'inactive', meaning the 'subscription_end_date' is less than or equal to the current date, it sets the 'subscription_end_date' to the current date plus [duration] days.
 */
/**
 * Test if auto status change is working
 * set end_date to a previous date and status = 'inactive'
 * update subscriptions set sub_end_date = NOW() + INTERVAL -20 DAY, subscription_status = 'inactive'  where id =1;
 * then run extendUserSubscription($user_id, -1) for inactive check 
 * then run extendUserSubscription($user_id, 1) for active check 
 */
function extendUserSubscription($user_id, $duration) {
  global $db;
  $sql = "UPDATE subscriptions SET ";
  $sql .= " sub_end_date = CASE WHEN sub_end_date < CURRENT_DATE() THEN CURRENT_DATE() + INTERVAL $duration DAY ELSE sub_end_date + INTERVAL $duration DAY END, ";
  $sql .= " subscription_status = CASE WHEN sub_end_date >= CURRENT_DATE() THEN 'active' ELSE 'inactive' END ";
  $sql .= " WHERE user_id=:user_id ";
  // echo $sql;
  $query = $db->prepare($sql);
  $query->bindParam(':user_id', $user_id);
  // $query->bindParam(':duration', $duration);
  return $query->execute();
}

// This method should be handled under try catch block with transactions
function exhaustPrepaidSubscription($subscription_id){
  global $db;
  $query = $db->prepare("UPDATE prepaid_subscriptions SET subscription_status='exhausted' WHERE id=:subscription_id ");
  $query->bindParam(':subscription_id', $subscription_id);
  return $query->execute();
}


/** 
  * =========================================================================================
 * ============================================================== TRANSACTIONS END ==========
 * ==========================================================================================
 */

// ====================================================================================
// ========================= ORDER Methods ======================================
// ====================================================================================

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