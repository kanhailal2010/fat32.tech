<?php
require_once(__DIR__.'/../partials/par_util.php');

if(!isset($_SESSION)) {  session_start(); }

function requireLogin() {
  if(!$_SESSION['logged_in']) { redirectTo(SITE_URL.'login/');  }
}

function getSessionValue($key, $default){
  return isset($_SESSION[$key]) && !empty($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

// function redirectHereAfterLogin(){
//   $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
//   print_r($uri_path);
// // $uri_segments = explode('/', $uri_path);
// // print_r($uri_segments);

// // echo $uri_segments[0];
// }

