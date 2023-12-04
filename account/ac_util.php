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

