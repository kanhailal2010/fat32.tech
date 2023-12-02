<?php 
/**
 * Subscription will be saved to subscriptions table for a user
 * Subscriptions entry in the table is done by the webhook after signature verification
 * ===> We are supposed to delete the subscription entry for a user after subscription ends
 * ===> But we have not done it yet
 * if a payment is made before subscription end date, then the subscription entry is made in prepaid_subscription table
 * ==> When subscription ends ==> We now have to check for an entry in prepaid_subscription if entry exist then update the subscription and delete from prepaid_subscription
 * ==> prepaid_subscription will be logged in the root folder /logs/custom_errors_YYYY_MM.log
 */



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