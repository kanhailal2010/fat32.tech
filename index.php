<?php 
require_once(__DIR__.'/partials/par_util.php'); 

// load pages 
$defaultPage = 'home';
$page = (isset($_GET['page']) && !empty($_GET['page']) && $_GET['page'] != 'index.php') ? $_GET['page'] : $defaultPage;

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
    let google_captcha_site_key = '<?php echo $_ENV['GOOGLE_CAPTCHA_SITE_KEY']; ?>';
  </script>
  <?php
  $globalJs = [
    'Google Captcha JS' => 'https://www.google.com/recaptcha/api.js?render='.$_ENV['GOOGLE_CAPTCHA_SITE_KEY'],
    'Capcha Code' => siteUrl('/assets/js/captcha.js'),
  ];
  require_once(__DIR__.'/partials/footer.php');
endif;