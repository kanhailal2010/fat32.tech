<?php 
require_once(__DIR__.'/../partials/par_util.php');
session_destroy();
redirectTo(SITE_URL);
