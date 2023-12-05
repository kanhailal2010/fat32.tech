<?php
require_once(__DIR__.'/../partials/par_util.php');

if(!isset($_SESSION)) {  session_start(); }

function requireLogin() {
  if(!$_SESSION['logged_in']) { redirectTo(SITE_URL.'login/');  }
}

function getSessionValue($key, $default){
  return isset($_SESSION[$key]) && !empty($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

function daysRemaining($endingDate) {
  $currentDateTime = new DateTime();
  $endingDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $endingDate);

  if ($endingDateTime < $currentDateTime) {
      return 0;
  } else {
      $interval = $endingDateTime->diff($currentDateTime);
      return $interval->format('%a');
  }
}

// function redirectHereAfterLogin() {
//   $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
//   print_r($uri_path);
//   $uri_segments = explode('/', $uri_path);
//   print_r($uri_segments);
//   echo $uri_segments[0];
// }

// get All Prepaid subscriptions of a user.
function getAllQueuedPrepaidSubscriptionsOfUser($user_id){
  global $db;
  $query = $db->prepare("SELECT * FROM prepaid_subscriptions WHERE user_id = :user_id AND subscription_status = 'queued' ");
  $query->bindParam(':user_id', $user_id);;
  $subscription = $query->execute() ? $query->fetchAll(PDO::FETCH_ASSOC) : false;
  if(!$subscription) { return [false, 'No Prepaid subscriptions found']; }
  return [true, $subscription];
}

function getQueuedSubscriptionDays($userId) {
  $totalQueued = 0;
  $queued = getAllQueuedPrepaidSubscriptionsOfUser($userId);
  if(!$queued[0]) { return 0; }

  foreach ($queued[1] as $key => $arr) {
    $totalQueued += $arr['sub_plan_duration'];
  }
  return $totalQueued;
}