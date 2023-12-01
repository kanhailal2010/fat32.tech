<?php 

// load pages 
$defaultPage = 'plans';
$page = (isset($_GET['page']) && !empty($_GET['page']) && $_GET['page'] != 'index.php') ? $_GET['page'] : $defaultPage;

// load requested page
require_once(__DIR__.'/'.$page.'.php');