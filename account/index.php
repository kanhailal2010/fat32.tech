<?php 
require_once(__DIR__.'/ac_util.php');
require_once(__DIR__.'/db.php');
require_once(__DIR__.'/header.php');

// echo str_repeat('<br/>', 10);
// print_r($_REQUEST);

$defaultPage = 'dashboard';
$page = (isset($_GET['page']) && !empty($_GET['page']) && $_GET['page'] != 'index.php') ? $_GET['page'] : $defaultPage;
require_once(__DIR__.'/'.$page.'.php');

include_once('footer.php');?>