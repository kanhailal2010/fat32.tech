console.log('orders page script');
$(document).ready(function() {
  // new DataTable('#orderHistory', {

  // });
  $('#orderHistory').DataTable({
    ajax: '/api_v1/order_api',
    "order": [[ 1, 'desc' ]],
    columnDefs: [
      {
        render: (data, type, row) => { 
          console.log(`the Data `,data)
          return '&#8377;'+data+'/-';
        },
        target: 4
      },
      {
        visible:false,
        target:[0]
      }
    ]
  });
});