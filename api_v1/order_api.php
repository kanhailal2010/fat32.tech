<?php

require_once(__DIR__.'/../partials/par_util.php');
require_once(__DIR__.'/../account/db.php');

$user = getUserByEmail($_SESSION['user_email']);

$orders = $user ? getUsersPaidOrders($user['id']) : [];

$orderData = new StdClass();
$orderData->data = [];
foreach ($orders as $key => $order) {
  $notes = json_decode($order['order_notes']);
  $orderData->data[] = [
    $order['id'],
    isset($notes->notes) && isset($notes->notes->plan_code) ? $notes->notes->plan_desc .'('.$notes->notes->plan_code.')' : '',
    $order['order_date'],
    $order['pg_order_id'],
    ($order['total_amount']/100),
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