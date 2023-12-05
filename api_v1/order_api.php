<?php

require_once(__DIR__.'/../partials/par_util.php');
require_once(__DIR__.'/../account/db.php');


$user = getUserByEmail($_SESSION['user_email']);

$orders = getUsersPaidOrders($user['id']);

$orderData = new StdClass();
$orderData->data = [];
foreach ($orders as $key => $order) {
  $orderData->data[] = [
    $order['id'],
    $order['order_date'],
    '&#8377;'.($order['total_amount']/100).'/-',
    $order['pg_order_id'],
    $order['order_status']
  ];
}

echo json_encode($orderData);
exit();
  // $obj = new StdClass();
  // $obj->data = [
  //   [
  //     143, // order_id
  //     "2023-11-17 09:15:00",//order time
  //     "118.00", // amount
  //     "f32xl5u23h92", // transaction id
  //     "paid"
  //   ]
  // ];

  // echo json_encode($obj);
  // exit();