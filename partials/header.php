<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    
    <!--====== Title ======-->
    <title>Fat32 Tech </title>
    
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!--====== Favicon Icon ======-->
    <link rel="shortcut icon" href="<?php echo siteUrl('assets/images/favicon/favicon-32x32.png'); ?>" type="image/png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo siteUrl('/assets/images/favicon/apple-touch-icon.png'); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo siteUrl('/assets/images/favicon/favicon-32x32.png'); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo siteUrl('/assets/images/favicon/favicon-16x16.png'); ?>">
    <!-- <link rel="manifest" href="<?php echo siteUrl('/assets/images/favicon/site.webmanifest'); ?>"> -->
    <link rel="mask-icon" href="<?php echo siteUrl('/assets/images/favicon/safari-pinned-tab.svg'); ?>" color="#5bbad5">
    <link rel="shortcut icon" href="<?php echo siteUrl('/assets/images/favicon/favicon.ico'); ?>">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-config" content="<?php echo siteUrl('/assets/images/favicon/browserconfig.xml'); ?>">
    <meta name="theme-color" content="#ffffff">
        
    <?php echo includeCSS(); ?>
    
</head>

<body>
    <!--[if IE]>
    <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
  <![endif]-->
   

    
    <!--====== NAVBAR TWO PART START ======-->

    <section class="navbar-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <nav class="navbar navbar-expand-lg">
                       
                        <a class="navbar-brand" href="/#home">
                            <img src="<?php echo siteUrl('assets/images/fat32-logo.png');?>" alt="Fat32 Logo">
                        </a>
                        
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTwo" aria-controls="navbarTwo" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="toggler-icon"></span>
                            <span class="toggler-icon"></span>
                            <span class="toggler-icon"></span>
                        </button>

                        <div class="collapse navbar-collapse sub-menu-bar" id="navbarTwo">
                            <ul class="navbar-nav m-auto">
                                <li class="nav-item active"><a class="page-scroll" href="/#home">home</a></li>
                                <li class="nav-item"><a class="page-scroll" href="/#services">Services</a></li>
                                <!-- <li class="nav-item"><a class="page-scroll" href="/#portfolio">Portfolio</a></li> -->
                                <!-- <li class="nav-item"><a class="page-scroll" href="/#pricing">Pricing</a></li> -->
                                <li class="nav-item"><a class="page-scroll" href="/#about">About</a></li>
                                <!-- <li class="nav-item"><a class="page-scroll" href="/#team">Team</a></li> -->
                                <li class="nav-item"><a class="page-scroll" href="/#contact">Contact</a></li>
                                <li class="nav-item hidden_for_desktop"><a href="<?php echo SITE_URL?>login">Login</a></li>
                            </ul>
                        </div>
                        
                        <div class="navbar-btn d-none d-sm-inline-block">
                            <ul>
                                <li><a class="solid" href="<?php echo SITE_URL?>login">Login</a></li>
                            </ul>
                        </div>
                    </nav> <!-- navbar -->
                </div>
            </div> <!-- row -->
        </div> <!-- container -->
    </section>

    <!--====== NAVBAR TWO PART ENDS ======-->