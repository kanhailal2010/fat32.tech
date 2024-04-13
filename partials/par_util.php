<?php 
session_start();

// Local
// require_once __DIR__.'/../vendor/autoload.php';
// Production
require_once __DIR__.'/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

define('SITE_URL', $_ENV['SITE_URL']);
define('ALLOWED_SPECIAL_CHARACTER','!#$%&()*+,_.:@<>?[]{}|');
define('ALLOWED_SPECIAL_CHARACTER_JS','/[!#$%&()*+,_.:@<>?[]{}|]/');

function applog($errorMessage){
  // Generate log file name with current month and year
  $logFileName = __DIR__.'/../logs/custom_errors_' . date('Y_m') . '.log';

  // Error message with timestamp
  $formattedErrorMessage = '[' . date('Y-m-d H:i:s') . '] ' . $errorMessage . PHP_EOL;

  // Append the error message to the log file
  file_put_contents($logFileName, $formattedErrorMessage, FILE_APPEND | LOCK_EX);
}

function handleFatalError() {
  if ($error = error_get_last()) {
    $errorMessage = 'Fatal error: ' . $error['type'] . ' - ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line'];

    // Log the error to a file
    // error_log($errorMessage, 3, 'error_log.txt');
    print_r($errorMessage);
  }
}

function ifDebugOn(){
  $debug = $_ENV['DEBUG'];
  $debug ? register_shutdown_function('handleFatalError') : '';
  return $debug;
}
$debug = ifDebugOn();

function debug($var){
  // enable error reporting for php
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  echo '<pre>'; print_r($var); echo '</pre>';
}

function siteUrl($url='') {
  return SITE_URL.$url;
}

function redirectTo($url) {
  header("Location: ".$url);
}

function metaRedirectTo($url, $delay = 0) {
  // echo str_repeat('<br/>', 10);
  // echo $url; echo $delay;
  echo '<meta http-equiv="refresh" content="'.$delay.'; url=' . $url . '" />';
}

function loginRedirectTo($url, $metaRedirect = false) {
  $url = isset($_SESSION["login_redirect_url"]) ? $_SESSION['login_redirect_url'] : $url;
  unset($_SESSION["login_redirect_url"]);
  $metaRedirect ? metaRedirectTo($url) : redirectTo($url);
  exit();
}

function setLoginRedirectUrl(){
  if(isset($_REQUEST['redirect_to']) && !empty($_REQUEST['redirect_to']) && !isset($_SESSION['login_redirect_url'])){
    $_SESSION['login_redirect_url'] = urldecode($_REQUEST['redirect_to']);
  }
}

function getOnlyFilename($filename) {
  return preg_replace('/\.\w+$/', '', $filename);
}

function getPrettyPage($defaultPage){
  $page =  (isset($_GET['page']) && !empty($_GET['page']) && $_GET['page'] != 'index.php') ? $_GET['page'] : $defaultPage;
  return getOnlyFilename($page);
}

function metaTitleDesc($meta) {
  $title  = isset($meta['title']) ? $meta['title'] : 'Website Development 2023';
  $desc   = isset($meta['desc']) ? $meta['desc'] : 'Build your E-commerce website, Product website using React, Nodejs or PHP Frameworks like Wordpress. We also optimize your website for higher search rankings and traffic.';
  return [$title, $desc ];
}

$globalCss = isset($globalCss) ? $globalCss : [];
function includeCSS(){
  global $globalCss;
  $cssArray = [
    'Magnific Popup CSS' => siteUrl('assets/css/magnific-popup.css'),
    'Slick CSS' => siteUrl('assets/css/slick.css'),
    'Line Icons CSS' => siteUrl('assets/css/LineIcons.css'),
    'Bootstrap CSS' => siteUrl('assets/css/bootstrap.min.css'),
    'Default CSS' => siteUrl('assets/css/default.css'),
    'Style CSS' => siteUrl('assets/css/style.css')
  ];

  if(count($globalCss) > 0) { $cssArray = array_merge($cssArray, $globalCss); }

  $html = '';
  foreach($cssArray as $label => $url) {
    $html .= '<!--====== '.$label.' ======-->'."\n";
    $html .= '<link rel="stylesheet" href="'.$url.'">'."\n";
  }
  return $html;
}

$globalJs = isset($globalJs) ? $globalJs : [];
function includeJS($includeGlobal = true){
  global $globalJs, $globalJsAttr;
  
  $jsArray = [
    'Jquery js' => siteUrl('assets/js/vendor/jquery-1.12.4.min.js'),
    'Jquery Modernizer' => siteUrl('assets/js/vendor/modernizr-3.7.1.min.js'),
    'Jquery easing' => siteUrl('assets/js/jquery.easing.min.js'),
    'Bootstrap Popper' => siteUrl('assets/js/popper.min.js'),
    'Bootstrap Js' => siteUrl('assets/js/bootstrap.min.js'),
    'Slick Js' => siteUrl('assets/js/slick.min.js'),
    'Magnific Popup js' => siteUrl('assets/js/jquery.magnific-popup.min.js'),
    'Ajax Contact js' => siteUrl('assets/js/ajax-contact.js'),
    'Isotope js images Loaded' => siteUrl('assets/js/imagesloaded.pkgd.min.js'),
    'Isotope js' => siteUrl('assets/js/isotope.pkgd.min.js'),
    'Scrolling Nav js' => siteUrl('assets/js/scrolling-nav.js'),
    'Main Js' => siteUrl('assets/js/main.js')
  ];

  if(count($globalJs) > 0 && $includeGlobal) { $jsArray = array_merge($jsArray, $globalJs); }
  if(count($globalJs) > 0 && !$includeGlobal) { $jsArray = $globalJs; }
  
  $html = '';
  foreach($jsArray as $label => $url) {
    $attr = isset($globalJsAttr[$label]) ? $globalJsAttr[$label] : '';
    $html .= '<!--====== '.$label.' ======-->'."\n";
    $html .= '<script '.$attr.' src="'.$url.'"></script>'."\n";   
  }
  return $html;
}

function generateRandomAlphanumericText() {
  $length = 20;
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $randomString = '';

  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, strlen($characters) - 1)];
  }

  return $randomString;
}

function googleLoginButton($buttonTitle=''){
  try {    
    // init configuration
    $clientID = $_ENV['GOOGLE_CLIENT_ID'];
    $clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'];
    $redirectUri = $_ENV['GOOGLE_REDIRECT_URI'];

    // create Client Request to access Google API
    $client = new Google_Client();
    $client->setClientId($clientID);
    $client->setClientSecret($clientSecret);
    $client->setRedirectUri($redirectUri);
    $client->addScope("email");
    $client->addScope("profile");


    // authenticate code from Google OAuth Flow
    if (isset($_GET['code'])) {
      
      $code = urldecode($_GET['code']);
      $client->authenticate($code);
      if(!$client->getAccessToken()){
        error_log("Google Access Token Expired");
      }

      // $token = $client->fetchAccessTokenWithAuthCode($code);
      // $client->setAccessToken($token['access_token']);

      // get profile info
      $google_oauth2 = new Google_Service_Oauth2($client);
      $google_user = $google_oauth2->userinfo->get();
      $_SESSION['logged_in'] = true;
      // $_SESSION['user_id'] = $google_user->id;
      $_SESSION['user_email'] = $google_user->email;
      $_SESSION['user_name'] = isset($google_user->name) ? $google_user->name : $google_user->email;
      $_SESSION['user_photo'] = $google_user->picture;

      $user = new StdClass();
      $user->google_id        = $google_user->id;
      $user->email            = $google_user->email;
      $user->name             = isset($google_user->name) ? $google_user->name : $google_user->email;
      $user->picture          = $google_user->picture;
      $user->pass             = generateRandomAlphanumericText();
      $user->phone            = '0000000000';
      $user->email_verified   = 1;
      $user->verification_code= null;
      $user->active           = 1;
      $res = createVerifiedUserIfDoesNotExist($user);
      // if user exist and correct credentials entered
      if($res[0]) { 
        $_SESSION['user_id'] = $res[1]['id'];
        loginRedirectTo(SITE_URL."account",true); 
      }
      // print "id:				".$user->id."\n";
      // //print '<img src="'.$user->picture.'" style="float: right;margin-top: 33px;" />'."\n\n";
      // print "email:			".$user->email."\n";
      // print "full-name:		".$user->name."\n";
      //print "verified-email:	".$user->verifiedEmail."\n";	// just interesting if != "1"

    } else {
      return "<a class='btn btn-md btn-primary' href='".$client->createAuthUrl()."'><i class='lni lni-google'></i> &nbsp; {$buttonTitle} </a>";
    }
  }
  catch(Exception $e) {
    if(ifDebugOn()) { echo 'Message: ' .$e->getMessage(); }
    else {
      return "Error:: Could not authenticate Google user";
    }
    error_log('Google_authentication_error:: '.$e->getMessage());
  }
}

function facebookLoginButton($buttonTitle = 'Login with Facebook'){
  try {
    include_once(__DIR__.'/../login/fblogin/fb-config.php');
    // if(isset($_SESSION['logged_in']) and $_SESSION['user_email']!=""){
    //   // echo 'logged in now redirect to account';
    //   redirectTo('account');
    //   exit;
    // }
    $permissions = array('email'); // Optional permissions
    $loginUrl = $helper->getLoginUrl(siteUrl('login/facebook.php'), $permissions);
    return "<a class='btn btn-md btn-primary' href='".$loginUrl."' ><i class='lni lni-facebook'></i> &nbsp; {$buttonTitle} </a>";
  }
  catch(Exception $e) {
    if(ifDebugOn()) { echo 'Message: ' .$e->getMessage(); }
    else {
      return "Error:: Could not authenticate Facebook user";
    }
    error_log('FACEBOOK_authentication_error:: '.$e->getMessage());
  }
}

function validateAjaxData($data) {
  $errors = [];

  if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email address';
  }

  if (isset($data['phone_number']) && !preg_match('/^\d{10}$/', $data['phone_number'])) {
    $errors['phone_number'] = 'Invalid phone number';
  }

  if (isset($data['username']) && !preg_match('/^[a-zA-Z0-9]+$/', $data['username'])) {
    $errors['username'] = 'Invalid username';
  }

  if (count($errors) > 0) {
    $response['status'] = false;
    $response['errors'] = $errors;
  } else {
    $response['status'] = true;
  }

  return $response;
  // echo json_encode($response);
}

function sanitizeInput($data, $type) {
  if($type == 'fullname') {
    // Trim whitespace from the input
    $fullname = trim($data);  
    // Sanitize the input using filter_var()
    $fullname = filter_var($fullname, FILTER_UNSAFE_RAW);
    return $fullname;
  }
  if($type == 'email'){
    if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
        return false; // Invalid email address
    }
    // Sanitize the email address using filter_var()
    $email = filter_var($data, FILTER_SANITIZE_EMAIL);
    return $email;
  }
  if($type == 'phone'){
    $phoneNumber = preg_replace('/[^0-9]/', '', $data);
    // Sanitize the phone number using filter_var()
    $phoneNumber = filter_var($phoneNumber, FILTER_SANITIZE_NUMBER_INT);
    return $phoneNumber;
  }
  if($type == 'password'){
    $password = filter_var($data, FILTER_UNSAFE_RAW);
    // Allow special characters in the password
    $allowedSpecialCharacters = ALLOWED_SPECIAL_CHARACTER;
    $password = str_replace(['\\', '/'], ['\\\\', '\\/'], $password);
    $password = preg_replace("/[^a-zA-Z0-9$allowedSpecialCharacters]/", "", $password);
    return $password;
  }
  if($type == 'username') {
    $allowedCharacters = 'a-zA-Z0-9_.-';
    // Remove any characters that are not allowed
    $username = preg_replace("/[^$allowedCharacters]/", "", $data);
    // Convert the username to lowercase
    $username = strtolower($username);
    return $username;
  }
  if($type == 'number') {
    $allowedCharacters = '0-9';
    // Remove any characters that are not allowed
    $username = preg_replace("/[^$allowedCharacters]/", "", $data);
    // Convert the username to lowercase
    $username = strtolower($username);
    return $username;
  }

}

function verifyCaptcha() {
      // Storing google recaptcha response 
    // in $recaptcha variable 
    // $recaptcha = $_POST['g-recaptcha-response']; 
    $recaptcha = $_POST['recaptcha_response']; 
  
    // Put secret key here, which we get 
    // from google console 
    $secret_key = $_ENV['GOOGLE_CAPTCHA_SECRET_KEY']; 
  
    // Hitting request to the URL, Google will 
    // respond with success or error scenario 
    $url = 'https://www.google.com/recaptcha/api/siteverify?secret='
          . $secret_key . '&response=' . $recaptcha; 
  
    // Making request to verify captcha 
    $response = file_get_contents($url); 
  
    // Response return by google is in 
    // JSON format, so we have to parse 
    // that json 
    $response = json_decode($response); 

    return $response->success;
    // // Checking, if response is true or not 
    // if ($response->success == true) { 
    //     echo 'alert("Google reCAPTACHA verified")'; 
    // } else { 
    //     echo 'alert("Error in Google reCAPTACHA")'; 
    // }
}

function sendActivationMail($email, $code){
  // Send email with unique ID
  $link = siteUrl('login/email_activation?email='.$email.'&activation_code='.$code);
  $subject = 'Please verify your email address';
  $message = '<html><body>';
  $message .= "<h2>Hello!</h2>";
  $message .= "Thank you for registering at our website.<br/>";
  $message .= "To activate your account, please click on the following link:<br/>";
  $message .= "<a href='".$link."'>Click Here</a
  <br/><br/>If clicking the above link doesn't work, copy and paste this into your browser's address bar: ".$link."
  <br/><br/>Regards,";
  $message .= "</body></html>";
  // Always set content-type when sending HTML email
  $headers = "MIME-Version: 1.0" . "\r\n";
  $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
  // More headers
  $headers .= 'From: <noreply@fat32.tech>' . "\r\n";
  // $headers .= 'Cc: myboss@example.com' . "\r\n";

  if(mail($email,$subject,$message,$headers)){
    return true;
  }
  else { return false; }
}

function sendPasswordRecoveryMail($email, $code){
  // Send email with unique ID
  $link = siteUrl('login/forgot?email='.$email.'&reset_code='.$code);
  $subject = 'Password reset request';
  $message = '<html><body>';
  $message .= "<h2>Change your password!</h2>";
  $message .= "We have received a password change request for your account<br/>";
  $message .= "If you did not ask to change your password, then you can ignore this email and your password will not be changed.<br/>";
  $message .= "Click on the link below to reset your password<br/>";
  $message .= "<a href='".$link."'>Click Here</a
  <br/><br/>If clicking the above link doesn't work, copy and paste this into your browser's address bar: ".$link."
  <br/><br/>Regards,";
  $message .= "</body></html>";
  // Always set content-type when sending HTML email
  $headers = "MIME-Version: 1.0" . "\r\n";
  $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
  // More headers
  $headers .= 'From: <noreply@fat32.tech>' . "\r\n";
  // $headers .= 'Cc: myboss@example.com' . "\r\n";

  if(mail($email,$subject,$message,$headers)){
    return true;
  }
  else { return false; }
}

function fieldsNotEmpty($fields, $exclude=[]) {
  $empty = false;
  foreach ($fields as $field => $value) {
    // check if field empty, if empty mark as empty
    if (trim($value) == '' && !in_array($field, $exclude)){
      $empty = true;
      return false;
    }
  }
  return true;
}

// =========================================================================================
// ========================= Subscription PLAN Methods =====================================
// =========================================================================================


function planCodes() {
  $plans = array_keys(getPlans());
  return $plans;
}

function getPlanDuration($planCode) {
  $plan = getPlans($planCode);
  return ($plan) ? $plan['duration'] : 0;
}

function getPlans($plan = null) {
  $subscriptionPlans = [
    "TR_TRIAL" => ["id"=> 1, "price"=>0, "duration" => 7, "title"=>"Trial", "name"=>"Trading Extension - 7 Day Trial","description"=>"Full access to features for a Trial period of 7 Working Days", "image"=>"plane.png"],
    "TR_EXT_M" => ["id"=> 2, "price"=>199,"duration" => 30, "title"=>"Monthly", "name"=>"Trading Extension - Monthly Plan","description"=>"Full access to features for a Month", "image"=>"paperp300.png"],
    "TR_EXT_HY" => ["id"=> 3, "price"=>999,"duration" => 182, "title"=>"Half-Yearly", "name"=>"Trading Extension - Half Yearly Plan","description"=>"Full access to features for a period of 6 Months", "image"=>"plane300.png"],
    "TR_EXT_Y" => ["id"=> 4, "price"=>1999,"duration" => 365, "title"=>"Yearly", "name"=>"Trading Extension - Yearly Plan","description"=>"Full access to features for a period of 1 Year", "image"=>"rocket300.png"],
  ];
  if (isset($subscriptionPlans[$plan])){    return $subscriptionPlans[$plan]; }

  return ($plan && isset($subscriptionPlans[$plan])) ? $subscriptionPlans[$plan] : $subscriptionPlans;
}

function getPaidPlans() {
  $paidPlans = [];
  foreach (getPlans() as $key => $plan) {
    if (!$plan['price']) { continue; }
    $paidPlans[$key] = $plan;
  }
  return $paidPlans;
}

function saveToCache($key, $value){
  $_SESSION[$key] = $value;
  return $value;
}

function getFromCache($key){
  return (!isset($_SESSION[$key])) ? "" : $_SESSION[$key];
}