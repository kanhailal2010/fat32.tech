<?php 
include_once(__DIR__.'/../partials/par_util.php');
require_once(__DIR__.'/payment_methods.php');

$paymentSuccess = null;
$message = '';
if(isset($_POST['razorpay_payment_id']) && !empty($_POST['razorpay_payment_id'])) :
  $razorpayOrderId = $_POST['razorpay_order_id'];
  $razorpayPaymentId = $_POST['razorpay_payment_id'];
  $razorpaySignature = $_POST['razorpay_signature'];
  $paymentSuccess = verifyPaymentSignature($razorpayPaymentId, $razorpaySignature) ? true : false;
  if($paymentSuccess){
    $paymentSuccess = true;
    $message = '<h3>Your Payment was successful!</h3>';
    $message .= '<p>Check your email for the payment receipt.</p>';
    $message .= '<p>You can close this window!.</p>';
    $transaction = new StdClass();
    $transaction->order_id    = $razorpayOrderId;
    $transaction->payment_id  = $razorpayPaymentId;
    $transaction->signature   = $razorpaySignature;
    insertOrderCompleteTransaction($transaction);
  }
  else {
    $message = '<h3>Could not process Payment!</h3>'; 
    $message .= '<p>Sorry we could not process your payment Information. Don\'t worry, we are looking at this!</p>';
  }
endif;

// var_dump($paymentSuccess);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FAT32 Tech | Payment Complete</title>
  <?php if($paymentSuccess !== null) :?>
  <link rel="stylesheet" href="<?php echo siteUrl('subscription/style.css');?>">
  <link href="https://fonts.googleapis.com/css?family=Fredoka+One&display=swap" rel="stylesheet">
  <?php endif; ?>
</head>
<body>
<div class="background">
  <div class="container center">
    <div class="text-wrapper">
      <?php if($paymentSuccess === true) :?>
      <div class="heyy">
        <h1>GREAT !!</h1>
        <h1>GREAT !!</h1>
        <h1>GREAT !!</h1>
        <h1>GREAT !!</h1>
        <h1>GREAT !!</h1>
        <h1>GREAT !!</h1>
        <h1>GREAT !!</h1>
        <h1>GREAT !!</h1>
        <h1>GREAT !!</h1>
      </div>
      <?php elseif($paymentSuccess === false): ?>
        <div class="heyy">
          <h1>ERROR !!</h1>
          <h1>ERROR !!</h1>
          <h1>ERROR !!</h1>
          <h1>ERROR !!</h1>
          <h1>ERROR !!</h1>
          <h1>ERROR !!</h1>
        </div>
      <?php elseif($paymentSuccess === null): ?>

      <?php endif; ?>
    </div>
    <?php echo $message; ?>
  </div> <!-- container -->
</div>  <!-- background -->
</body>
</html>
