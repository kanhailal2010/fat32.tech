<?php 
include_once(__DIR__.'/../partials/par_util.php');
include_once(__DIR__.'/../account/ac_util.php');
require_once(__DIR__.'/../account/db.php');
require_once(__DIR__.'/payment_methods.php');

// print_r($_SESSION);
// session_destroy();


// load pages 
$defaultPage = 'plans';
$page = (isset($_GET['page']) && !empty($_GET['page']) && $_GET['page'] != 'index.php') ? $_GET['page'] : $defaultPage;

$dontRequireLogin = ['plans','api'];
if(!in_array($page,$dontRequireLogin)) :
  requireLogin();
endif;


// load requested page
require_once(__DIR__.'/'.$page.'.php');