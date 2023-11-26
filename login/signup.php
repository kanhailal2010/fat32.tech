<?php 
// echo str_repeat('<br/>',15);
// echo '<pre>'.print_r($_REQUEST,true).'</pre>';

if(isset($_POST['signup']) && verifyCaptcha()) {
  $user                     = new StdClass();
  $user->email              = sanitizeInput($_POST['email'], 'email');
  $user->verification_code  = generateRandomAlphanumericText();
  $user->name               = sanitizeInput($_POST['fullname'], 'fullname');
  $user->phone              = sanitizeInput($_POST['phone'], 'phone');
  $user->password           = sanitizeInput($_POST['password'], 'password');

  if(!fieldsNotEmpty($_POST,['phone'])) :
    $alertDisplay = 'block';
    $alertType = 'danger';
    $alertMsg = 'Please fill all required Fields';
  else:

    // echo str_repeat('<br/>', 15);
    $created = addUnverifiedUser($user);
    // $created = createVerifiedUserIfDoesNotExist($user);
    // var_dump('user created? ', $created);
    // echo '<pre>'.print_r($user,true).'</pre>';
    // exit();
    if($created[0]) { 
      if(sendActivationMail($user->email, $user->verification_code)) {
        $alertDisplay = 'block';
        $alertType = 'success';
        $alertMsg = 'Mail sent!. Please click on the link sent to your mail';
      }
    }
    else {
      $alertDisplay = 'block';
      $alertType = 'danger';
      $alertMsg = $created[1];//'Error occurred. Please try again';
    }

  endif;
}
?>
<section id="terms" class="features-area">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-10">
        <div class="section-title text-center pb-10">
          <h3 class="title">SIGN UP</h3>
          <p class="text">Sign up to create your account</p>
        </div> <!-- row -->
      </div>
    </div> <!-- row -->
    <div class="row">
      <div class="col-lg-12">
        <div class="alert alert-<?php echo $alertType;?>" role="alert" style="display:<?php echo $alertDisplay;?>">
          <?php echo $alertMsg; ?>
        </div>
          <div class="contact-wrapper form-style-two pt-50">
              <h4 class="contact-title pb-10"><i class="lni lni-envelope"></i> Enter your <span> info.</span></h4>
              <form id="captcha-form" class="js-form form-has-loaded" action="<?php echo SITE_URL; ?>login/signup" method="post">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-input mt-25">
                      <label>Name</label>
                      <div class="input-items default">
                        <i class="lni lni-user"></i>
                        <input type="text" name="fullname" id="fullName" required="required" placeholder="Your name" data-input-validation='[{"message":"Name is required","regexp":"full_name"}]'>
                        <span class="form-error-lbl"></span>
                      </div>
                    </div> <!-- form input -->
                  </div>
                  <div class="col-md-6">
                    <div class="form-input mt-25">
                      <label>Phone</label>
                      <div class="input-items default">
                        <i class="lni lni-phone"></i>
                        <input type="number" name="phone" id="phone" placeholder="Phone (10 digits)" minlength="3" maxlength="5" data-input-validation='[{"message":"Not a valid Phone number","regexp":"phone_if_filled"}]'>
                        <span class="form-error-lbl"></span>
                      </div>
                    </div> <!-- form input -->
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                      <div class="form-input mt-25">
                        <label>Email</label>
                        <div class="input-items default">
                          <i class="lni lni-envelope"></i>
                          <input type="email" name="email" id="email" required="required" placeholder="Your e-mail address" data-input-validation='[{"message":"Not a valid e-mail address","regexp":"email"}]'>
                          <span class="form-error-lbl"></span>
                        </div>
                      </div> <!-- form input -->
                    </div>
                    <div class="col-md-6">
                      <div class="form-input mt-25">
                        <label>Password</label>
                        <div class="input-items default">
                          <i class="lni lni-lock-alt"></i>
                          <input name="password" type="password" id="password" required="required" placeholder="Password" data-input-validation='[{"message":"Min 8 Characters","regexp":"min8"}, {"message":"One special character is required","regexp":"atleast_one_special"},{"message":"One digit is required","regexp":"atleast_one_digit"}]'>
                          <span class="form-error-lbl"></span>
                        </div>
                      </div> <!-- form input -->
                    </div>
                    <p class="form-message"></p>
                    <div class="col-md-12">
                        <div class="form-input light-rounded-buttons mt-30">
                            <a href="/login/" class="main-btn login_forgot_link">Already have an Account? Sign In</a>
                            <a href="/login/forgot" class="login_forgot_link">Forgot Password?</a>
                            <button name="signup" class="main-btn light-rounded-two float-right button submit-btn disabled" disabled="disabled">Sign Up</button>
                            <input type="hidden" value="create user" name="signup" />
                            <input type="hidden" name="recaptcha_response" id="recaptchaResponse" >
                            <!-- <button class="g-recaptcha main-btn light-rounded-two float-right button submit-btn disabled" disabled="disabled" value="signup"
                            name="signup"
                            data-sitekey="<?php echo $_ENV['GOOGLE_CAPTCHA_SITE_KEY'];?>" 
                            data-callback='onSubmit' 
                            data-action='submit'>Sign Up</button> -->
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