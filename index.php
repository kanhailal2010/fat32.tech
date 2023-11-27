<?php 
require_once(__DIR__.'/partials/par_util.php'); 
require_once(__DIR__.'/partials/header.php'); 


// load pages 
$defaultPage = 'home';
$page = (isset($_GET['page']) && !empty($_GET['page']) && $_GET['page'] != 'index.php') ? $_GET['page'] : $defaultPage;
// echo 'load the page now '.$page;
require_once(__DIR__.'/'.$page.'.php');

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