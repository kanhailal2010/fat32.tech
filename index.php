<?php 
require_once(__DIR__.'/partials/par_util.php'); 

// load pages 
$defaultPage = 'home';
$page = getPrettyPage($defaultPage);

// add pages which do not require to load header.php and footer file
$noHeaderFooterPages = ['pay','pay_success','logout'];

if(!in_array($page,$noHeaderFooterPages)) {
  require_once(__DIR__.'/partials/header.php'); 
}

// load requested page
require_once(__DIR__.'/'.$page.'.php');


if(!in_array($page,$noHeaderFooterPages)) :
  ?>
  <script>
    let GOOGLE_CAPTCHA_SITE_KEY = '<?php echo $_ENV['GOOGLE_CAPTCHA_SITE_KEY']; ?>';
    let captchaInputIds = ['#recaptchaResponse'];
  </script>
  <?php
  $globalJs = [
    'Google Captcha JS' => 'https://www.google.com/recaptcha/api.js?render='.$_ENV['GOOGLE_CAPTCHA_SITE_KEY'],
    'Capcha Code' => siteUrl('/assets/js/captcha.js'),
  ];
  require_once(__DIR__.'/partials/footer.php');
endif;