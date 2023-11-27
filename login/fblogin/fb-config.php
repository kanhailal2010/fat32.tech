<?php
// session_start();
include_once(__DIR__.'/php-graph-sdk-5.x/src/Facebook/autoload.php');
$fb = new Facebook\Facebook(array(
	// prod
	'app_id' => '320256154191505', // Replace with your app id
	'app_secret' => 'f04cc3ea316680916bcd8535cabc1162',  // Replace with your app secret
	// test
	// 'app_id' => '1763974904119953', // Replace with your app id
	// 'app_secret' => '9f7d24fcfdc79536df9781c234977415',  // Replace with your app secret
	'default_graph_version' => 'v3.2',
));

$helper = $fb->getRedirectLoginHelper();
?>