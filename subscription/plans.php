<?php 
require_once(__DIR__.'/../partials/par_util.php');
require_once(__DIR__.'/payment_methods.php');

// print_r($_SESSION);
// session_destroy();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fat32 Tech | Chrome Extension Plans</title>
  <link rel="stylesheet" href="./style.css">
</head>
<body>
<div class="background">
  <div class="container">
    <div class="panel pricing-table flex">
      

      <div class="pricing-plan">
        <img src="https://s22.postimg.cc/8mv5gn7w1/paper-plane.png" alt="" class="pricing-img">
        <h2 class="pricing-header">Personal</h2>
        <ul class="pricing-features">
          <li class="pricing-features-item">Custom domains</li>
          <li class="pricing-features-item">Sleeps after 30 mins of inactivity</li>
        </ul>
        <span class="pricing-price">&#8377;199/-</span>
        <?php if(!isset($_SESSION['logged_in'])): ?>
        <a href="/login?redirect_to=<?php echo urlencode(siteUrl('subscription/plans'));?>" class="button-30">Sign up</a>
        <?php else: ?>
        <form action="/subscription/checkout" method="post">
          <input type="hidden" value="monthly" id="subscription_plan" name="subscription_plan"/>
          <input type="hidden" value="" id="plan1" name="recaptcha_response" />
        <button type="submit" name="selected_plan" value="monthly" class="button-30">Buy Monthly</button>
        </form>
        <?php endif; ?>
      </div>
      
      <div class="pricing-plan">
        <img src="https://s28.postimg.cc/ju5bnc3x9/plane.png" alt="" class="pricing-img">
        <h2 class="pricing-header">Small team</h2>
        <ul class="pricing-features">
          <li class="pricing-features-item">Never sleeps</li>
          <li class="pricing-features-item">Multiple workers for more powerful apps</li>
        </ul>
        <span class="pricing-price">$150</span>
        <a href="#/" class="button-30 is-featured">Free trial</a>
      </div>
      
      <div class="pricing-plan">
        <img src="https://s21.postimg.cc/tpm0cge4n/space-ship.png" alt="" class="pricing-img">
        <h2 class="pricing-header">Enterprise</h2>
        <ul class="pricing-features">
          <li class="pricing-features-item">Dedicated</li>
          <li class="pricing-features-item">Simple horizontal scalability</li>
        </ul>
        <span class="pricing-price">$400</span>
        <a href="#/" class="button-30">Free trial</a>
      </div>
      
    </div>
  </div> <!-- .container -->
</div> <!-- .background -->
<script>
  let GOOGLE_CAPTCHA_SITE_KEY = '<?php echo $_ENV['GOOGLE_CAPTCHA_SITE_KEY']; ?>';
  let captchaInputIds = ['#plan1'];
</script>
<?php 
  $globalJs = [
    'Google Captcha JS' => 'https://www.google.com/recaptcha/api.js?render='.$_ENV['GOOGLE_CAPTCHA_SITE_KEY'],
    'Capcha Code'       => siteUrl('/assets/js/captcha.js'),
  ];

  echo includeJS(false); // false to EXCLUDE including default js files
?>
</body>
</html>