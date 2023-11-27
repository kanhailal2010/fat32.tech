<section id="terms" class="features-area">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-10">
        <div class="section-title text-center pb-10">
          <h3 class="title">Login</h3>
          <p class="text">Login to manage your account</p>
        </div> <!-- row -->
      </div>
    </div> <!-- row -->
    <div class="row">
      <div class="col-lg-12">
        <div class="alert alert-<?php echo $alertType;?>" role="alert" style="display:<?php echo $alertDisplay;?>">
          <?php echo $alertMsg; ?>
        </div>
          <div class="contact-wrapper form-style-two pt-50">
              <h4 class="contact-title pb-10"><i class="lni lni-envelope"></i><span>Enter your</span> Login Credentials.</h4>
              <form id="login-form" class="js-form form-has-loaded" action="<?php echo SITE_URL; ?>login/" method="post">
                  <div class="row">
                      <div class="col-md-6">
                          <div class="form-input mt-25">
                              <label>Email</label>
                              <div class="input-items default">
                                <i class="lni lni-envelope"></i>
                                <input type="email" name="email" placeholder="Email">
                              </div>
                            </div> <!-- form input -->
                          </div>
                          <div class="col-md-6">
                            <div class="form-input mt-25">
                              <label>Password</label>
                              <div class="input-items default">
                                <i class="lni lni-lock-alt"></i>
                                <input name="password" type="password" placeholder="Password">
                              </div>
                          </div> <!-- form input -->
                      </div>
                      <p class="form-message"></p>
                      <div class="col-md-12">
                          <div class="form-input light-rounded-buttons mt-30">
                            <a href="/login/signup" class="main-btn float-left">Sign UP</a>
                            <a href="/login/forgot" class="login_forgot_link">Forgot Password?</a>

                            <input type="hidden" name="recaptcha_response" id="recaptchaResponse">
                            <button name="login" class="main-btn light-rounded-two float-right submit-btn">Login</button>
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
                      <div class="col-md-6 footer-links">
                        <?php echo googleLoginButton('Login with Google'); ?>
                        <button id="fb_login" class="btn btn-md btn-primary"><i class='lni lni-facebook'></i> &nbsp; Login with Facebook</button>
                      </div>
                    </div> <!-- row -->
                </div>
            </div><!-- row -->

            <div class="row">
                <div class="col-lg-12">
                  <div id="fb-root"></div>
                  <!-- script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v18.0&appId=320256154191505" nonce="zrRUa4D1"></script -->
                  <div class="fb-login-button" data-width="" data-size="" data-button-type="" data-layout="" data-auto-logout-link="true" data-use-continue-as="false"></div>
                  <div id="status">FB Status</div>
                </div>
            </div><!-- row -->

          </div> <!-- contact wrapper form -->
      </div>

  </div> <!-- row -->
  </div>  <!-- container -->
</section>