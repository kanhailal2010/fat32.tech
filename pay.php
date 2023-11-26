<?php 
// Axis Razorpay TEST Keys
// Key ID: rzp_test_RhPHqIhEd6JYqA
// Key Secret: ng4xi6irS9Xx02cCpEqe6RDx
require_once(__DIR__.'/partials/par_util.php');
echo generateRandomAlphanumericText();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script -->
  </head>
  <body>
    <!-- <button type="button" id="renderBtn">
      Pay Now
    </button> -->

    <form><script src="https://checkout.razorpay.com/v1/payment-button.js" data-payment_button_id="pl_N4AyRpW5V9hc15" async> </script> </form>

    <!-- script>
    const cashfree = Cashfree({
      mode: "sandbox" //or production,
    });
    document.getElementById("renderBtn").addEventListener("click", () => {
      cashfree.checkout({
        paymentSessionId: "session_v4h1Wtt9aRe9RaHMZSC6m9iTcrWeEF2qhurb3Ot64u17_mIVTMttvIMSADHU41kJvwQz6QlzBpvpwjpgURsvxRiPuIgaoqNQPhnOroRP5Omp",
        returnUrl: "http://fat32.com/pay_success.php",
        redirectTarget: '_blank'
        });
      });
    </script -->
  </body>
</html>