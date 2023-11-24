<?php
if(isset($_SERVER['HTTP_ORIGIN'])) {
  $http_origin = $_SERVER['HTTP_ORIGIN'];
  $allowedOrigins = ['https://kite.zerodha.com','https://fat32.tech'];
  if(in_array($http_origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $http_origin");
  }
}
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
header("Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE");
// header("Access-Control-Max-Age: 1000");

include_once(__DIR__.'/../partials/par_util.php');
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

function razorpayAmount($rupees) {
   // Multiply the rupees by 100 to convert to paise
   $paise = $rupees * 100;
   // Remove any decimal places
   $paise = intval($paise);
   return $paise;
}

function createRazorOrder($userId, $receipt, $amount){
  require_once(__DIR__.'/razorpay-php-2.8.7/Razorpay.php');
  
  $api = new Razorpay\Api\Api($_ENV['KEY_ID'], $_ENV['KEY_SECRET']);
  return $api->order->create([
    'receipt'         => $receipt,
    'amount'          => razorpayAmount($amount), // amount in the smallest currency unit
    'currency'        => 'INR',// <a href="/docs/payments/payments/international-payments/#supported-currencies" target="_blank">See the list of supported currencies</a>.)
    'notes'           => [
      'user_id'    => $userId
      ]
    ]);
  }

function verifySignature($requestBody,$signature){
  $calSign = hash_hmac('sha256', $requestBody, $_ENV['WEBHOOK_SECRET']);
  return $calSign == $signature ? true : false;
}

function createWebhookTransaction(){
  try {
  // Get the webhook body and signature
  $webhookBody      = file_get_contents('php://input'); // Request body sent by Razorpay
  $webhookSignature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE']; // Signature sent by Razorpay
  $keySecret        = $_ENV['WEBHOOK_SECRET'];

  saveWebhookTransaction(json_encode($_SERVER));
  saveWebhookTransaction($webhookBody);
  require_once(__DIR__.'/razorpay-php-2.8.7/Razorpay.php');
  /* PHP SDK: https://github.com/razorpay/razorpay-php */
  $api = new Razorpay\Api\Api($_ENV['KEY_ID'], $_ENV['KEY_SECRET']);

    // Verify the webhook signature
    $isValidSignature = $api->utility->verifyWebhookSignature($webhookBody, $webhookSignature, $keySecret);

    if ($isValidSignature) {
      // Signature is valid, proceed to save the webhook data to the database
      // Here, you can parse $webhookBody (which is in JSON format) and save it to your database
      $decodedData = json_decode($webhookBody, true);
      $bool = saveWebhookTransaction($decodedData);
    }
    else {
      // Signature is not valid, log the error
      error_log("Invalid signature received using Razorpay");
      error_log(verifySignature($webhookBody, $webhookSignature));
      error_log('the sign razorpay '.$_SERVER['HTTP_X_RAZORPAY_SIGNATURE']);
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

if(isset($_REQUEST['check_subscription']) && !empty($_REQUEST['check_subscription'])) {
  $res = [];
  $res['status'] = false;
  if($_ENV['DEBUG']) { $res['server'] = $_SERVER;}
  
  $valid = validateAjaxData($_REQUEST);
  if(!$valid['status']) {
    $res['status']  = false;
    $res['msg']     = 'Error validating data';
  }

  $res['status']              = true;
  $res['msg']                 = "User does not have an active subscription.";
  $res['subscription_status'] = 'inactive';
  $res['subscription']        = false;

  // Example usage
  $emailOrPhone = str_replace('"','',$_REQUEST['email']);
  $subscription = getSubscriptionDetails($emailOrPhone);
  if ($subscription !== false) {
    $res['msg']                 = "User has an active subscription.";
    $res['subscription_status'] = $subscription['subscription_status'];
    $res['subscription']        = $subscription;
  }
  echo json_encode($res);
  exit();
}

if(isset($_REQUEST['webhook'])) {
  echo createWebhookTransaction();
}

if(isset($_REQUEST['create_orderssssssssssssssssssss'])) {
  try {
    $userId   = $_REQUEST['user_id'];
    $amount   = $_REQUEST['amount'];
    $receipt  = generateReceiptId($userId);

    $res = createRazorOrder($userId, $receipt, $amount);
    // var_dump($res);
    $response = new StdClass();
    $response->status   = false;
    $response->error    = ['could not create order'];
    if(isset($res->error)) { 
      echo json_encode($response); 
      exit();
    }
    
    unset($response->error);
    $response->status           = true;
    $response->pg_order_id      = $res->id;
    $response->receipt          = $receipt;
    $response->user_id          = $userId;
    $response->order_date       = Date('Y-m-d H:i:s');
    $response->order_status     = $res->status;
    $response->order_notes      = $res;
    $response->total_amount     = $res->amount;
    $response->transaction_id   = '';
    $response->billing_address  = '';

    insertUserOrder($response);

    echo json_encode($response);
    exit();
  }
  //catch exception
  catch(Exception $e) {
    if($debug) { echo 'Message: ' .$e->getMessage(); }
    else {
      return "DB Error:: Could not create order";
    }
    error_log($e->getMessage());
  } 
}

