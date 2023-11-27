<?php 
// TODO: 
// password strenght is not being checked on password reset flow

$showResetFields  = false;
$alertDisplay     = 'none';
$alertMsg         = '';
$alertType        = '';

// echo str_repeat('<br/>',15);
// echo '<pre>'.print_r($_REQUEST,true).'</pre>';

// reset password 
if(isset($_POST['password_reset']) && verifyCaptcha()) {
  $password   = sanitizeInput($_POST['password'], 'password');
  $password2  = sanitizeInput($_POST['password2'], 'password');
  
  $alertDisplay = 'block';
  $alertMsg     = 'Password change failed. Enter Same password on both fields and try again';
  $alertType    = 'danger';
  if($password==$password2 && changePassword($_POST['email'],$password)) {
    $alertDisplay = 'block';
    $alertMsg     = 'Password changed successfully';
    $alertType    = 'success';
  }

}

// verify reset password link 
if(isset($_GET['email']) && isset($_GET['reset_code'])) {
  $email = sanitizeInput($_GET['email'], 'email');
  $code = sanitizeInput($_GET['reset_code'], 'fullname');
  // echo 'after sanitize '.$code."\n\n";
  if(empty($email) || empty($code)) :
    $alertDisplay = 'block';
    $alertType = 'danger';
    $alertMsg = 'Something went wrong. Please try again by clicking on the link we sent to your email.';
    error_log('PASSWORD RESET ERROR: Empty email['.$_GET['email'].'] or Empty code ['.$_GET['reset_code'].']');
  else:
    $res = passwordResetLinkValid($email, $code);
    // var_dump($email, $code, $res);
    if($res[0]) {
      $showResetFields = true;
      // reset verification code after successfull user code verification
      updateVerificationCode($email, '');
    } else {
      $alertDisplay = 'block';
      $alertType = 'danger';
      $alertMsg = $res[1];
    }
  endif;
}

if(isset($_POST['recover_password']) && verifyCaptcha()) {
  $email = sanitizeInput($_POST['email'], 'email');
  if(empty($email)) :
    $alertDisplay = 'block';
    $alertType = 'danger';
    $alertMsg = 'Something went wrong';
    error_log('WRONG_USER_ENTRY: user entered empty email for forgot password: '.$_POST['email']);
  else:
    $user = getUserByEmail($email);
    if(!$user) { 
      $alertDisplay = 'block';
      $alertType = 'danger';
      $alertMsg = 'Something went wrong';
      error_log('WRONG_USER_ENTRY: user entered wrong email for forgot password: '.$_POST['email']);
    }
    else {

      $random = generateRandomAlphanumericText();
      $res = updateVerificationCode($email, $random);
      if($res[0] && sendPasswordRecoveryMail($email, $random)) {
        $alertDisplay = 'block';
        $alertType = 'success';
        $alertMsg = 'Password reset link sent to your email. Please click on the link to reset your password!.';
      }
    }
  endif;
}
$submitText = $showResetFields ? 'Reset Password' : 'Recover Password';
?>
<section id="terms" class="features-area">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-10">
        <div class="section-title text-center pb-10">
          <h3 class="title">Forgot Password</h3>
          <p class="text">Lets recover your account password!</p>
        </div> <!-- row -->
      </div>
    </div> <!-- row -->
    <div class="row">
      <div class="col-lg-12">
        <div class="alert alert-<?php echo $alertType;?>" role="alert" style="display:<?php echo $alertDisplay;?>">
          <?php echo $alertMsg; ?>
        </div>
          <div class="contact-wrapper form-style-two pt-50">
            <h4 class="contact-title pb-10 text-center"><i class="lni lni-lock-alt"></i> <span>Enter your</span> credentials.</h4>
            <form id="captcha-form" class="js-form" action="<?php echo SITE_URL; ?>login/forgot" method="post">
                <div class="row justify-content-center">
                  <div class="col-md-6">
                    <?php if($showResetFields) : ?>
                      <div class="form-input mt-25">
                      <label>Password</label>
                      <div class="input-items default">
                        <i class="lni lni-lock-alt"></i>
                        <input type="hidden" name="password_reset" placeholder="Hidden password reset">
                        <input type="hidden" name="email" value="<?php echo sanitizeInput($_GET['email'], 'email'); ?>">
                        <input type="password" name="password" placeholder="Change password">
                      </div>
                    </div> <!-- form input -->
                    <div class="form-input mt-25">
                      <label>Confirm Password</label>
                      <div class="input-items default">
                        <i class="lni lni-lock-alt"></i>
                        <input type="password" name="password2" placeholder="Confirm Password">
                      </div>
                    </div> <!-- form input -->
                    <?php else: ?>
                    <div class="form-input mt-25">
                      <label>Email</label>
                      <div class="input-items default">
                        <i class="lni lni-envelope"></i>
                        <input type="email" id="email_field" name="email" required="required" placeholder="Your e-mail address" data-input-validation='[{"message":"Not a valid e-mail address","regexp":"email"}]'>
                        <span class="form-error-lbl"></span>
                      </div>
                    </div> <!-- form input -->
                    <?php endif; ?>
                  </div>
                  <p class="form-message"></p>
                  <div class="col-md-12">
                    <div class="form-input light-rounded-buttons mt-30 text-center">
                          <input type="hidden" name="recover_password" placeholder="hidden recover password">
                          <input type="hidden" name="recaptcha_response" id="recaptchaResponse">
                          <button type="submit" name="recover" class="main-btn light-rounded-two submit-btn" value="password">
                          <?php echo $submitText; ?>
                          </button>
                          <br/>
                          <a href="/login/" class="main-btn login_forgot_link">Back to Login</a>
                        </div> <!-- form input -->
                    </div>
                </div> <!-- row -->
            </form>

            <br/>
            <hr/>
            <br/>

            <div class="row">
                <div class="col-lg-12">
                    <div class="row justify-content-center">
                      <div class="footer-links">
                        <?php echo googleLoginButton(); ?>
                      </div>
                    </div> <!-- row -->
                </div>
            </div>

          </div> <!-- contact wrapper form -->
      </div>

  </div> <!-- row -->
  </div>  <!-- container -->
</section>