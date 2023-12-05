<?php 
require_once(__DIR__.'/ac_util.php');
require_once(__DIR__.'/db.php');

// debug($_REQUEST);
// debug($_SESSION);

requireLogin();

$defaultPage = 'dashboard';
$page = getPrettyPage($defaultPage);

// add pages which do not require to load header.php file
$noHeaderPages = ['logout'];

if(!in_array($page,$noHeaderPages)) {
    require_once(__DIR__.'/header.php');
}

require_once(__DIR__.'/'.$page.'.php');

include_once('footer.php');?>