console.log('orders page script');
$(document).ready(function() {
  $('#orderHistory').DataTable({
    ajax: '/account/order_api.php'
  });
});