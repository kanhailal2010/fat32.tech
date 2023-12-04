<?php 
$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$database = $_ENV['DB_NAME'];

try {
  $db = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
  // set the PDO error mode to exception
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  // echo "PDO Connected successfully";
} catch(PDOException $e) {
  echo "DB Connection failed: " . $e->getMessage();
}

function addUnverifiedUser($user){
  $user->active = 0;
  $user->email_verified = 0;
  $res = createUser($user);
  if(!$res && getUserByEmail($user->email)) {
    return [false, 'User already registered'];
  }
  else if(!$res) {
    return [false, 'Could not add user'];
  }
  return [true, 'User added successfully'];
}

function addVerifiedUser($user){
  $user->active = 1;
  $user->email_verified = 1;
  return createUser($user);
}

function createVerifiedUserIfDoesNotExist($user){
  $userOld = getUserByEmail($user->email);
  if(!$userOld) {
    $res = addVerifiedUser($user);
    if(!$res) { return [false, 'Could not add user']; }    
    exit();
  }
  return [true, 'User already exist'];
}

function createUser($user){
  global $db,$debug;
  // echo str_repeat('<br/>', 15);
  // echo '<pre> db '.print_r($user,true).'</pre>';
  try {
    $query = $db->prepare("INSERT INTO users (id, active, fullname, email, email_verified, verification_code, user_pass, phone, photo, google_id) VALUES (:id, :active, :fullname, :email, :email_verified, :verification_code, :pass, :phone, :photo, :gid)");
    return $query->execute([
      'id' => null,
      'active' => $user->active,
      'fullname' => $user->name,
      'email' => $user->email,
      'email_verified' => $user->email_verified,
      'verification_code' => $user->verification_code,
      'pass' => password_hash($user->password, PASSWORD_DEFAULT),
      'phone' => $user->phone,
      'photo' => isset($user->photo) ? $user->photo : '',
      'gid' => isset($user->google_id) ? $user->google_id : '',
    ]);
  }
  //catch exception
  catch(Exception $e) {
    error_log($e->getMessage());
    if($debug) { echo 'Message: ' .$e->getMessage(); }
    else {
      return false;
    }
  }
}

function getUserByEmail($email){
  global $db, $debug;

  // Prepare and execute the SQL query
  $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
  $stmt->bindParam(':email', $email);
  if($stmt->execute()) {
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
  else {
    return false;
  }
}

function passwordResetLinkValid($email, $code){
  global $db, $debug;
  $user = getUserByEmail($email);
  // return false when user is not found or when codes do not match
  if(!$user || $user['verification_code'] != $code){ 
    return [false, 'Something failed. Please Try again'];
  }
  elseif ($user && $user['verification_code'] == $code) {
    return [true, 'reset codes matching'];
  }
}

function updateVerificationCode($email, $random) {
  global $db, $debug;
  // Prepare and execute the SQL query
  try {
    $stmt = $db->prepare("UPDATE users SET verification_code=:randomtxt WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':randomtxt', $random);
    if ($stmt->execute()) {
      return [true, 'Successfully updated verification code for '.$email];
    }
  }
  //catch exception
  catch(Exception $e) {
    error_log($e->getMessage());
    if($debug) { echo 'Message: ' .$e->getMessage(); }
    else {
      return [false, 'Could not update verification code for email '.$email];
    }
  }
}

function verifyLogin($email, $password) {
  global $db, $debug;

  // Fetch the user data
  $user = getUserByEmail($email);
  // var_dump($password, $user['password']);
  // password_hash('$ecret(@55',PASSWORD_DEFAULT);
  
  // password_hash('kanhai@341ET',PASSWORD_BCRYPT);
  if ($user) {
      // Verify the password
      if (password_verify($password, $user['user_pass'])) {
          // Password is correct, user is authenticated
          // echo "Login successful!";
          return $user;
          // Proceed with your logic for authenticated users
      } else {
          // Invalid password
          // echo 'wrong pass';
          return false;
      }
  } else {
      // Username not found
      // echo "User not found!";
      return false;
  }
}

function activateUser($email,$code){
  global $db, $debug;
  try {
    $user = getUserByEmail($email);
    if(!$user) { return [false, 'User not registered.'];}
// print_r($user);
    // user registered and code is same then activate user and set mail verified
    if($user['verification_code'] == $code) {
      $query = $db->prepare("UPDATE users SET active=:active, email_verified=:email_verified WHERE email=:email");
      $res = $query->execute([
        'active' => 1,
        'email_verified' => 1,
        'email' => $email
      ]);
      return [true, 'User Activated'];
    }
    else {
      return [false, 'Activation code does not match'];
    }
  }
  //catch exception
  catch(Exception $e) {
    error_log($e->getMessage());
    if($debug) { echo 'Message: ' .$e->getMessage(); }
    else {
      return [false, 'Could not activate user. Please try again'];
    }
  }
}

function changePassword($email, $password) {
  global $db, $debug;
  try {
    $query = $db->prepare("UPDATE users SET user_pass=:pass WHERE email=:email");
    return $query->execute([
      'email' => $email,
      'pass' => password_hash($password, PASSWORD_DEFAULT)
    ]);
  }
  //catch exception
  catch(Exception $e) {
    error_log($e->getMessage());
    if($debug) { echo 'Message: ' .$e->getMessage(); }
    else {
      return "DB Error:: Could not change password";
    }
  }
}

// Function to check for an active subscription
function getUserSubscriptionDetails($email) {
  global $db;
  $res = [];
  $result = getUserByEmail($email);
  if ($result) {
    $userId = $result['id'];
    // Then, check for an active subscription for the user
    $query = $db->prepare("SELECT * FROM subscriptions WHERE user_id = :userId AND subscription_status = 'active' ");// sub_end_date >= NOW()");
    $query->bindParam(':userId', $userId);
    $query->execute();
    $subscription = $query->fetch(PDO::FETCH_ASSOC);
    // var_dump($subscription);
    // return [true, $subscription];
    return ($subscription !== false) ? [true, $subscription, $result] : [false, 'Subscription details not found', $result];
  }
  else {
    return [false, 'User not found'];
  }
  return [false, 'No details found']; // User not found
}

// QUERY LOGGING
// SET global log_output = 'FILE';
// SET global general_log_file='/Users/kanhai/Documents/Projects/mysql_logs/mysql_general.log';

// INDEX 
// CREATE INDEX email_ids ON users(email);
// CREATE INDEX order_ids ON orders(pg_order_id);

// CREATE TABLE `users` (
//   `id` int NOT NULL AUTO_INCREMENT,
//   `active` boolean NOT NULL DEFAULT FALSE,
//   `name` varchar(255) NOT NULL,
//   `email` varchar(255) NOT NULL,
//   `email_verifed` boolean NOT NULL DEFAULT FALSE,
//   `verification_code` VARCHAR(30) NULL DEFAULT NULL,
//   `password` varchar(255) DEFAULT NULL,
//   `phone` varchar(20) NOT NULL,
//   `photo` varchar(255) DEFAULT NULL,
//   `google_id` varchar(255) DEFAULT NULL,
//   PRIMARY KEY (`id`),
//   UNIQUE KEY `email` (`email`)
// )
// insert into users VALUES (null, 1, 'Kanhai', 'kanhailal2010+test@gmail.com', 1, null, 'password_not_set', 9008654469, '', '');
// select * from users where email = 'kanhailal2010@gmail.com';

// CREATE TABLE `subscriptions` (
//   `id` int NOT NULL AUTO_INCREMENT,
//   `user_id` int NOT NULL,
//   `email` varchar(255) DEFAULT NULL,
//   `sub_plan_id` int DEFAULT '1',
//   `sub_plan_details` varchar(255) NOT NULL,
//   `sub_start_date` datetime DEFAULT CURRENT_TIMESTAMP,
//   `sub_end_date` datetime DEFAULT NULL,
//   `subscription_status` enum('active','inactive','cancelled') NOT NULL DEFAULT 'inactive',
//   PRIMARY KEY (`id`),
//   UNIQUE KEY `email` (`email`)
// )
// insert into subscriptions (id, user_id, name, email, sub_plan_id, sub_plan_details, sub_start_date, sub_end_date,subscription_status) VALUES (null,'00004', 'Kanhai', 'kanhailal2010@gmail.com', 1, 'plan details', null, null, 'inactive');
// select * from subscriptions where email='kanhailal2010@gmail.com' limit 5;
// update subscriptions set subscription_status = 'active' where user_id = 4;

// CREATE TABLE `prepaid_subscriptions` (
//   `id` int NOT NULL AUTO_INCREMENT,
//   `user_id` int NOT NULL,
//   `email` varchar(255) DEFAULT NULL,
//   `order_id` varchar(50) NOT NULL,
//   `payment_id` varchar(50) NOT NULL,
//   `sub_plan_id` int DEFAULT '1',
//   `sub_plan_details` varchar(255) NOT NULL,
//   `sub_start_date` date DEFAULT NULL,
//   `sub_end_date` date DEFAULT NULL,
//   `subscription_status` enum('queued','exhausted','cancelled') NOT NULL DEFAULT 'queued',
//   `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//   `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
//   PRIMARY KEY (`id`),
//   UNIQUE KEY `order_id` (`order_id`),
//   UNIQUE KEY `payment_id` (`payment_id`)
// )


// CREATE TABLE orders (
//   id INT PRIMARY KEY AUTO_INCREMENT,
//   receipt VARCHAR(50) NOT NULL,
//   pg_order_id VARCHAR(50) NOT NULL,
//   user_id INT NOT NULL,
//   order_date DATETIME NOT NULL,
//   order_status enum('created','attempted','paid') NOT NULL DEFAULT 'created',
//   order_notes JSON DEFAULT NULL,
//   total_amount INT(10) NOT NULL,
//   transaction_id VARCHAR(255) NOT NULL,
//   billing_address VARCHAR(255) NOT NULL
// );

// INSERT INTO orders VALUES (null,'fat_receipt', 'order_EKwxwAgItmmXdp', 4, '2023-11-21 15:20:21', 'created', '{"user_id": 4}', '118', 'csefo2e9Z19', 'Nagpur, 440001');


// CREATE TABLE order_transactions (
//   id INT PRIMARY KEY AUTO_INCREMENT,
//   order_id VARCHAR(50) NOT NULL,
//   payment_id VARCHAR(50),
//   signature VARCHAR(200),
//   method VARCHAR(50),
//   user_email VARCHAR(50),
//   user_phone VARCHAR(16),
//   payment_status VARCHAR(20),
//   payment_amount INT(10),
//   order_status enum('created','attempted','paid', 'order_not_created') NOT NULL DEFAULT 'created',
//   order_amount INT(10) NOT NULL,
//   transaction_data JSON DEFAULT NULL,
//   created_at DATETIME DEFAULT NULL
// );

// INSERT INTO order_transactions (id, order_id, payment_id, method, user_email, user_phone, payment_status, payment_amount, order_status, order_amount, transaction_data, created_at) VALUES (null, 'order_N4CvFaMWxOVtCF', 'pay_N4CvMnKey8Yv5R', 'upi', 'kanhailal2010@gmail.com', '9008654469', 'captured', 15354, 'paid', 15000, '{ "all" : { "transaction": "data"} }', '2023-11-24 16:10:45');