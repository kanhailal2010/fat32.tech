<?php

  $obj = new StdClass();
  $obj->data = [
    [
      143, // order_id
      "2023-11-17 09:15:00",//order time
      "118.00", // amount
      "f32xl5u23h92", // transaction id
      "paid"
    ]
  ];

  echo json_encode($obj);