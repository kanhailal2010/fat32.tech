<?php 
// better template 
// https://codepen.io/FlorinPop17/pen/vPKWjd

require_once(__DIR__.'/../partials/par_util.php');
require_once(__DIR__.'/../account/db.php');

//set login redirect url (to redirect after login to a particular url);
setLoginRedirectUrl();
// if logged in already then redirect to subscription 
if(isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
  metaRedirectTo(SITE_URL."account");
}

$alertDisplay = 'none';
$alertMsg     = '';
$alertType    = '';

 if(isset($_POST['login']) && verifyCaptcha()) {
  $valid = validateAjaxData($_POST);
  // var_dump($valid);
  if($valid['status']) {
    $email = $_POST['email'];
    $pass = $_POST['password'];
    // echo str_repeat('<br/>', 15);
    $verified = verifyLogin($email, $pass);
    // var_dump('cred ', $email, $pass);
    // var_dump('After verify ',$verified);
    $alertDisplay = 'block';
    if($verified) {
      $alertMsg               = 'Login Successful.. Redirecting to your dashboard';
      $alertType              = 'success';
      $_SESSION['logged_in']  = true;
      $_SESSION['user_id']    = $verified['id'];
      $_SESSION['user_email'] = $verified['email'];
      $_SESSION['user_name']  = $verified['fullname'];
      $_SESSION['user_photo'] = $verified['photo'];
      loginRedirectTo(SITE_URL."account",true);
    }
    else {
      $alertMsg               = 'Check your login credentials';
      $alertType              = 'danger';
    }
  }
}

// echo str_repeat('<br/>', 10);
// print_r($_REQUEST);

$defaultPage = 'login';
$page = (isset($_GET['page']) && !empty($_GET['page']) && $_GET['page'] != 'index.php') ? $_GET['page'] : $defaultPage;

// add pages which do not require to load header.php and footer file
$noHeaderFooterPages = ['google','facebook'];

if(!in_array($page,$noHeaderFooterPages)) {
  $globalCss = ['login CSS' => '/assets/css/login.css' ];
  require_once(__DIR__.'/../partials/header.php');
}


require_once(__DIR__.'/'.$page.'.php');

if(!in_array($page,$noHeaderFooterPages)) {
?>
  <script>
    let GOOGLE_CAPTCHA_SITE_KEY = '<?php echo $_ENV['GOOGLE_CAPTCHA_SITE_KEY']; ?>';
    // let FB_APP_ID = '<?php echo $_ENV['FB_APP_ID'];?>';
    let captchaInputIds = ['#recaptchaResponse'];
  </script>
  <?php 
  $globalJs = [
    'Login JS'          => '/assets/js/login.js',
    'Google Captcha JS' => 'https://www.google.com/recaptcha/api.js?render='.$_ENV['GOOGLE_CAPTCHA_SITE_KEY'],
    'Capcha Code'       => siteUrl('/assets/js/captcha.js'),
    // 'FB_auth'           => 'https://connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v18.0&appId='.$_ENV['FB_APP_ID']
  ];
  //add attributes to js
  $globalJsAttr = [
    'Login JS'          => '/assets/js/login.js',
    // 'FB_auth'           => 'async defer crossorigin="anonymous"'
  ];

  require_once(__DIR__.'/../partials/footer.php');
}
?>