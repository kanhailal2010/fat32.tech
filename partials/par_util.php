<?php 
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
session_start();

require_once __DIR__.'/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

define('SITE_URL', $_ENV['SITE_URL']);

function debug(){
  return $_ENV['DEBUG'];
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

function generateRandomAlphanumericText() {
  $length = 10;
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $randomString = '';

  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, strlen($characters) - 1)];
  }

  return $randomString;
}

function googleLoginButton(){
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
      $_SESSION['user_id'] = $google_user->id;
      $_SESSION['user_email'] = $google_user->email;
      $_SESSION['user_name'] = $google_user->name;
      $_SESSION['user_photo'] = $google_user->picture;

      $pass = generateRandomAlphanumericText();
      $pass = password_hash($pass, PASSWORD_DEFAULT);
      $res = createUser($google_user->name, $google_user->email, $pass,  '0000000000', $google_user->picture);
      if($res) { metaRedirectTo(SITE_URL."account"); }
      // print "id:				".$google_user->id."\n";
      // //print '<img src="'.$google_user->picture.'" style="float: right;margin-top: 33px;" />'."\n\n";
      // print "email:			".$google_user->email."\n";
      // print "full-name:		".$google_user->name."\n";
      //print "verified-email:	".$google_user->verifiedEmail."\n";	// just interesting if != "1"

    } else {
      return "<a class='btn btn-md btn-primary' href='".$client->createAuthUrl()."'><i class='lni lni-google'></i> Login with Google</a>";
    }
  }
  catch(Exception $e) {
    if(debug()) { echo 'Message: ' .$e->getMessage(); }
    else {
      return "Error:: Could not authenticate Google user";
    }
    error_log('Google_authentication_error:: '.$e->getMessage());
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
