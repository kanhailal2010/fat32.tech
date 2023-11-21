<?php 
require_once(__DIR__.'/partials/header.php');
require_once(__DIR__.'/account/db.php');

// if logged in already then redirect to subscription 
if(isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
  metaRedirectTo(SITE_URL."account");
}

$alertDisplay = 'none';
$alertMsg     = '';
$alertType    = '';

 if(isset($_POST['login'])){ 
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
      $_SESSION['user_name']  = $verified['name'];
      $_SESSION['user_photo'] = $verified['photo'];
      metaRedirectTo(SITE_URL."account",0);
    }
    else {
      $alertMsg               = 'Check your login credentials';
      $alertType              = 'danger';
    }
  }
}

?>
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
          <div class="contact-wrapper form-style-two pt-115">
              <h4 class="contact-title pb-10"><i class="lni lni-envelope"></i> Enter your <span>Login Credentials.</span></h4>
              <form id="login-form" action="<?php echo SITE_URL; ?>login.php" method="post">
                  <div class="row">
                      <div class="col-md-6">
                          <div class="form-input mt-25">
                              <label>Email</label>
                              <div class="input-items default">
                                <i class="lni lni-user"></i>
                                <input type="email" name="email" placeholder="Email">
                              </div>
                            </div> <!-- form input -->
                          </div>
                          <div class="col-md-6">
                            <div class="form-input mt-25">
                              <label>Password</label>
                              <div class="input-items default">
                                <i class="lni lni-star"></i>
                                <input name="password" type="password" placeholder="Password">
                              </div>
                          </div> <!-- form input -->
                      </div>
                      <p class="form-message"></p>
                      <div class="col-md-12">
                          <div class="form-input light-rounded-buttons mt-30">
                              <button name="login" class="main-btn light-rounded-two float-right">Login</button>
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
  <style>
    .navbar-area { background: #0166F3; }
  </style>
  <?php include('partials/footer.php');?>