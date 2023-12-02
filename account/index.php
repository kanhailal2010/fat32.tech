<?php 
require_once(__DIR__.'/ac_util.php');
require_once(__DIR__.'/db.php');

// echo str_repeat('<br/>', 10);
// print_r($_REQUEST);

requireLogin();

$defaultPage = 'dashboard';
$page = (isset($_GET['page']) && !empty($_GET['page']) && $_GET['page'] != 'index.php') ? $_GET['page'] : $defaultPage;

// add pages which do not require to load header.php file
$noHeaderPages = ['logout'];

if(!in_array($page,$noHeaderPages)) {
    require_once(__DIR__.'/header.php');
}

require_once(__DIR__.'/'.$page.'.php');

include_once('footer.php');?>