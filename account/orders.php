

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Orders</h1>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Order History</h6>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered" id="orderHistory" width="100%" cellspacing="0">
            <thead>
              <tr>
                <th>Transaction ID</th>
                <th>Order Description</th>
                <th>Order Date/Time</th>
                <th>Order Id</th>
                <th>Amount</th>
                <th>Order Status</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>  <!-- card-body -->
    </div> <!-- card -->
    

</div>
<!-- /.container-fluid -->
<?php 
$globalJs = ['datatable' => 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js'];

