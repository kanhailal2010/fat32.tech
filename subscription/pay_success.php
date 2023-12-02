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
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Fredoka+One&display=swap">

<div class="background">
  <section id="plans" class="features-area">

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

    <div class="row">
      <div class="col"></div>
      <div class="col-10 col-sm-10 text-center">

      <?php echo $message; ?>

      </div> <!-- col-10 -->

      <div class="col"></div>

    </div><!-- row -->
  </section>
</div><!-- background -->
