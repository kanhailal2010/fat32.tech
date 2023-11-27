console.log('orders page script');
$(document).ready(function() {
  $('#orderHistory').DataTable({
    ajax: '/api_v1/order_api'
  });
});