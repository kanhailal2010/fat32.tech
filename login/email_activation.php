<?php 

require_once(__DIR__.'/../partials/par_util.php');
require_once(__DIR__.'/../account/db.php');

$alertDisplay = isset($_SESSION['flash_msg']) ? 'block;text-align:center;' : 'none';
$alertType    = isset($_SESSION['flash_msg']) ? 'danger' : 'info';
$alertMsg     = isset($_SESSION['flash_msg']) ? $_SESSION['flash_msg'] : 'Nothing to see here';
$submitTxt    = isset($_SESSION['flash_msg']) ? 'Resend Activation Mail' : 'Send Activation Mail';
if(isset($_SESSION['flash_msg'])) { unset($_SESSION['flash_msg']); }

$desc         = 'After submitting your email. Click on the link you received on your email to verify your email address.';
$actFailed = false;

// echo str_repeat('<br/>',15);
// echo '<pre>'.print_r($_REQUEST,true).'</pre>';

// if user has clicked on the link and came here for verification
//  check the code and active the account
if(isset($_GET['email']) && isset($_GET['activation_code'])) {
  $email = sanitizeInput($_GET['email'], 'email');
  $code = sanitizeInput($_GET['activation_code'], 'fullname');
  // echo 'after sanitize '.$code."\n\n";
  // Error condition
  if(empty($email) || empty($code)) :
    $actFailed = true;
    $alertDisplay = 'block';
    $alertType = 'danger';
    $alertMsg = 'Something went wrong. Please try again by clicking on the link we sent to your email.';
    error_log('USER ACTIVATION ERROR: Empty email['.$_GET['email'].'] or Empty code ['.$_GET['activation_code'].']');
  else:
    // success condition
    $res = activateUser($email, $code);
    if($res[0]) {
      // reset verification code after successfull user activation
      updateVerificationCode($email, '');
      $alertDisplay = 'block; text-align: center;';
      $alertType = 'success';
      $alertMsg = 'Email verification successfull !! Proceed to login';
    } else {
      $actFailed = true;
      $alertDisplay = 'block';
      $alertType = 'danger';
      $alertMsg = $res[1];
    }
  endif;
}
if(isset($_POST['resend_activation_code']) && verifyCaptcha()) {
  $email = sanitizeInput($_POST['email'], 'email');
  if(empty($email)) :
    $actFailed = true;
    $alertDisplay = 'block';
    $alertType = 'danger';
    $alertMsg = 'Something went wrong';
    error_log('WRONG_USER_ENTRY: user entered empty email for resend_activation_code: '.$_POST['email']);
  else:
    $user = getUserByEmail($email);
    // error on user not found 
    // or when user is already verified
    if(!$user || ($user && $user['email_verified']==1)) { 
      $alertDisplay = 'block';
      $alertType = 'danger';
      $alertMsg = 'Something went wrong';
      error_log('WRONG_USER_ENTRY: user entered wrong email for resend_activation_code: '.$_POST['email']);
    }
    else {
      $random = generateRandomAlphanumericText();
      $res = updateVerificationCode($email, $random);
      if($res[0] && sendActivationMail($email, $random)) {
        $alertDisplay = 'block';
        $alertType = 'success';
        $alertMsg = 'User Activation link sent to your email. Please click on the link to active your account!.';
      }

    }
  endif;
}
?>
<section id="terms" class="features-area">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-10">
        <div class="section-title text-center pb-10">
          <h3 class="title">Email Verification</h3>
          <p class="text"><?php echo $desc; ?> </p>
        </div> <!-- row -->
      </div>
    </div> <!-- row -->
    <div class="row">
      <div class="col-lg-12 col-md-12">
        <div class="alert alert-<?php echo $alertType;?>" role="alert" style="display:<?php echo $alertDisplay;?>">
          <?php echo $alertMsg; ?>
        </div>
        <div class="contact-wrapper form-style-two pt-50">
            <h4 class="contact-title pb-10 text-center"><i class="lni lni-envelope"></i><span>Enter Your</span> Email.</h4>
            <form id="captcha-form" class="js-form form-has-loaded" action="<?php echo siteUrl('login/email_activation'); ?>" method="post">
                <div class="row justify-content-center">
                  <div class="col-md-6">
                    <div class="form-input mt-25">
                      <label>Email</label>
                      <div class="input-items default">
                        <i class="lni lni-envelope"></i>

                        <input type="email" name="email" placeholder="Email" id="email" data-input-validation='[{"message":"Not a valid Email Address","regexp":"email"}]' />
                        <span class="form-error-lbl"></span>

                        <input type="hidden" name="resend_activation_code" placeholder="Hidden Activation code input">
                      </div>
                    </div> <!-- form input -->
                  </div>
                    <p class="form-message"></p>
                    <div class="col-md-12">
                        <div class="form-input light-rounded-buttons mt-30 text-center">
                        <input type="hidden" name="recaptcha_response" id="recaptchaResponse" >
                        <!-- <button class="g-recaptcha main-btn light-rounded-two" value="password"
                          name="resend_activation_code"
                          data-sitekey="<?php echo $_ENV['GOOGLE_CAPTCHA_SITE_KEY'];?>" 
                          data-callback='onSubmit' 
                          data-action='submit'><?php echo $submitTxt; ?></button> -->
                          <button name="signup" class="main-btn light-rounded-two submit-btn" ><?php echo $submitTxt; ?></button>
                          <br/>
                          <a href="/login/" class="main-btn login_forgot_link">Back to Login</a>
                        </div> <!-- form input -->
                    </div>
                </div> <!-- row -->
            </form>

        </div><!-- contact-wrapper -->
      </div>
    </div>  <!-- row -->
  </div>  <!-- container -->
