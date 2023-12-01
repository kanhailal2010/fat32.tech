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
require_once(__DIR__.'/payment_methods.php');

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