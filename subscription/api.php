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

// TODO: update subscription if prepaid
// FIXME: change request to post
/**
 * This method checks of the current status and returns 'active' if active subscription is found for a user
 * if no subscription record of inactive getUserSubscriptionDetails($email) returns [false, 'msg'] 
 *    Then maybe trial period ended Or subscription ended
 *    For confirmation we check if the user has made any prepaid subscription 
 *      If prepaid_subscription row found with status 'queued' 
 *        then we extend the users subscription from todays date + duration of prepaid_subscription
 *          if users sub_end_date is less than todays date
 *          else if users sub_end_date is in the future then we add sub_end_date + duration of prepaid_subscription 
 *    Then we re-fetch the subscription details to get the lastest subscription status 
 * If subscription record is active 
 *    Then we check if the sub_end_date is greater than todays date
 */
$checkedPrepaidSubscriptions = false;
if(isset($_REQUEST['check_subscription']) && !empty($_REQUEST['check_subscription'])) {
  $res = [];
  $res['status'] = false;
  // if($_ENV['DEBUG']) { $res['server'] = $_SERVER;}
  
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
  $email = str_replace('"','',$_REQUEST['email']);
  $subscription = getUserSubscriptionDetails($email);
  // if subscription details [not_found/inactive] but user details exist in users table
  // check prepaid_subscriptions (maybe trial subscription has ended)
  if($subscription[0] == false && isset($subscription[2])) {
  // if(isset($subscription[2])) {
    // check prepaid subscriptions
    $userId = $subscription[2]['id'];
    $prepaidSubscriptions = getPrepaidSubscriptions($userId);

    // if prepaid_subscription record exist
    if($prepaidSubscriptions[0]) {
      // get the duration of prepaid_subscription
      $duration     = $prepaidSubscriptions[1]['sub_plan_duration'];
      $prepaidSubId = $prepaidSubscriptions[1]['id'];

      // extend the duration
      $bool = extendUserSubscriptionFlow($userId, $duration, $prepaidSubId);

      // fetch the user subscription details again
      $subscription = getUserSubscriptionDetails($email);
    }
  }
  // extendUserSubscriptionFlow(1, 7, 1);
  // extendUserSubscription(1, -2);
  if ($subscription[0] !== false) {
    // check date of end subs else mark inactive
    $today = strtotime(Date('Y-m-d'));
    $sub_end = strtotime($subscription[1]['sub_end_date']);
    if($today < $sub_end){
      echo json_encode($res);
      exit();
    }
    $res['msg']                 = "User has an active subscription.";
    $res['subscription_status'] = $subscription[1]['subscription_status'];
    $res['subscription']        = $subscription;
  }
  echo json_encode($res);
  exit();
}

/**
 * This method will execute in a transaction 
 * It runs two methods 
 *    One method to extend the duration of subscription
 *    another method to mark the used prepaid_subscription row to exhausted
 */
function extendUserSubscriptionFlow($user_id, $duration, $prepaid_sub_id){
  global $db;
  $db->beginTransaction();
  try {
    
    $bool = extendUserSubscription($user_id, $duration);
    $dub = exhaustPrepaidSubscription($prepaid_sub_id);

    $db->commit();
    return true;
  }   //catch exception
  catch(Exception $e) {
    $db->rollBack();
    $log = "EXTEND_SUBSCRIPTION_FLOW_ERROR: subscription::user_id[$user_id] duration_to_extend[$duration] prepaid_subscription::id[$prepaid_sub_id] ";
      applog($log);
    error_log($e->getMessage());
    return false;
  }
}

// Razorpay webhook
if(isset($_REQUEST['webhook'])) {
  $status = sanitizeInput($_REQUEST['webhook'], 'fullname');
  $bool = createWebhookTransaction($status);

  // On Error:: send internal error (500) to razorpay
  if(!$bool) { http_response_code(500); }
}