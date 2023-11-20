<?php
include('db.php');
// Function to check for an active subscription
function getSubscriptionDetails($emailOrPhone) {
  global $db;
  // First, retrieve the user's ID based on their email or phone
  $query = $db->prepare("SELECT id as user_id FROM users WHERE email = :emailOrPhone OR phone = :emailOrPhone");
  $query->bindParam(':emailOrPhone', $emailOrPhone);
  $query->execute();
  $result = $query->fetch(PDO::FETCH_ASSOC);

  // var_dump($result);

  if ($result) {
    $userId = $result['user_id'];
    
    // Then, check for an active subscription for the user
    $query = $db->prepare("SELECT * FROM subscriptions WHERE user_id = :userId AND subscription_status = 'active' ");// sub_end_date >= NOW()");
    $query->bindParam(':userId', $userId);
    $query->execute();
    $subscription = $query->fetch(PDO::FETCH_ASSOC);
    // var_dump($subscription);
    return $subscription;
    // return ($subscription !== false);
  }

  return false; // User not found
}

if(isset($_REQUEST['check_subscription']) && !empty($_REQUEST['check_subscription'])) {
  $res = [];
  $res['status'] = false;
  
  $valid = validateAjaxData($_REQUEST);
  if(!$valid['status']) {
    $res['status']  = false;
    $res['msg']     = 'Error validating data';
  }

  $res['status']              = true;
  $res['msg']                 = "User does not have an active subscription.";
  $res['subscription_status'] = 'inactive';
  $res['subscription']        = false;

  // Example usage
  $emailOrPhone = $_REQUEST['email'];
  $subscription = getSubscriptionDetails($emailOrPhone);
  if ($subscription !== false) {
    $res['msg']                 = "User has an active subscription.";
    $res['subscription_status'] = $subscription['subscription_status'];
    $res['subscription']        = $subscription;
  }
  echo json_encode($res);
  exit();
}

if(isset($_REQUEST['webhook'])) {
  global $db;
  $tr_data = json_encode($_REQUEST);
  // save data to db
  $data = [
    'tr_data' => $tr_data
  ];
  $sql = "INSERT INTO transactions (tr_data) VALUES (:tr_data)";
  $db->prepare($sql)->execute($data);

}

echo "<pre>everyting else condition:".print_r($_REQUEST,true)."</pre>";
?>
