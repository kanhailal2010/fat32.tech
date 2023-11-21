<?php
$debug = 0;
if($debug) {
  register_shutdown_function('handleFatalError');
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}

require_once(__DIR__.'/../partials/par_util.php');

if(!isset($_SESSION)) {  session_start(); }

if(!$_SESSION['logged_in']) { redirectTo(SITE_URL.'login/');  }

function getSessionValue($key, $default){
  return isset($_SESSION[$key]) && !empty($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

function handleFatalError() {
  if ($error = error_get_last()) {
    $errorMessage = 'Fatal error: ' . $error['type'] . ' - ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line'];

    // Log the error to a file
    // error_log($errorMessage, 3, 'error_log.txt');
    print_r($errorMessage);
  }
}


