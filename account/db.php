<?php 

require_once(__DIR__.'/../partials/par_util.php');

$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$database = $_ENV['DB_NAME'];


// // Create connection
// $conn = mysqli_connect($servername, $username, $password, $database);

// // Check connection
// if (!$conn) {
//   die("Mysql Connection failed: " . mysqli_connect_error());
// }
// echo "Mysql Connected successfully";

try {
  $db = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
  // set the PDO error mode to exception
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  // echo "PDO Connected successfully";
} catch(PDOException $e) {
  echo "DB Connection failed: " . $e->getMessage();
}

function createUser($name, $email, $password, $phone, $photo, $google_id=''){
  global $db,$debug;
  try {
    $query = $db->prepare('INSERT INTO users (id, name, email, password, phone, photo, google_id) VALUES (:id, :name, :email, :pass, :phone, :photo, :gid)');
    return $query->execute([
      'id' => null,
      'name' => $name,
      'email' => $email,
      'pass' => password_hash($password, PASSWORD_DEFAULT),
      'phone' => $phone,
      'photo' => $photo,
      'gid' => $google_id,
    ]);
  }
  //catch exception
  catch(Exception $e) {
    if($debug) { echo 'Message: ' .$e->getMessage(); }
    else {
      return "DB Error:: Could not save user";
    }
    error_log($e->getMessage());
  }
}

function verifyLogin($email, $password) {
  global $db, $debug;

  // Prepare and execute the SQL query
  $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
  $stmt->bindParam(':email', $email);
  $stmt->execute();

  // Fetch the user data
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  // var_dump($password, $user['password']);
  // password_hash('$ecret(@55',PASSWORD_DEFAULT);
  
  // password_hash('kanhai@341ET',PASSWORD_BCRYPT);
  if ($user) {
      // Verify the password
      if (password_verify($password, $user['password'])) {
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

function changePassword($email, $password) {
  global $db, $debug;
  try {
    $query = $db->prepare("UPDATE users SET password=:pass WHERE email=:email");
    return $query->execute([
      'email' => $email,
      'pass' => password_hash($password, PASSWORD_DEFAULT)
    ]);
  }
  //catch exception
  catch(Exception $e) {
    if($debug) { echo 'Message: ' .$e->getMessage(); }
    else {
      return "DB Error:: Could not change password";
    }
    error_log($e->getMessage());
  }
}

// generate order_id for a user.
function generateReceiptId($userId) {
  global $db, $debug;
  // Generate a random order ID
  $receiptId = 'fat_'.$userId.uniqid();

  // Check if the generated order ID already exists in the database
  $query = "SELECT COUNT(*) FROM orders WHERE receipt = :receipt_id AND user_id=:user_id";
  $statement = $db->prepare($query);
  $statement->bindParam(':receipt_id', $receiptId);
  $statement->bindParam(':user_id', $userId);
  $statement->execute();
  $rowCount = $statement->fetchColumn();

  // If the order ID already exists, generate a new one until a unique one is found
  if ($rowCount > 0) {
      return generateReceiptId();
  }

  return $receiptId;
}

function insertUserOrder($data) {
  global $db, $debug;
  try {
    $query = $db->prepare("INSERT INTO orders (receipt, pg_order_id, user_id, order_date, order_status, order_notes, total_amount, transaction_id, billing_address) VALUES (:receipt, :pg_order_id, :user_id, :order_date, :order_status, :order_notes, :total_amount, :transaction_id, :billing_address) ");
    return $query->execute([
      "receipt" => $data->receipt,
      "pg_order_id" => $data->pg_order_id,
      "user_id" => $data->user_id,
      "order_date" => $data->order_date,
      "order_status" => $data->order_status,
      "order_notes" => isset($data->order_notes) ? json_encode($data->order_notes) : [] ,
      "total_amount" => $data->total_amount,
      "transaction_id" => $data->transaction_id,
      "billing_address" => $data->billing_address
    ]);
  }
  //catch exception
  catch(Exception $e) {
    if($debug) { echo 'Message: ' .$e->getMessage(); }
    else {
      return "DB Error:: Could not Insert Order";
    }
    error_log($e->getMessage());
  } 
}

function saveWebhookTransaction($data){
  global $db;
  // $tr_data = json_encode($data);
  $tr_data = $data;
  try {
    $sql = "INSERT INTO transactions (tr_data) VALUES (:tr_data)";
    return $db->prepare($sql)->execute([
      'tr_data' => $tr_data
    ]);
  }
  //catch exception
  catch(Exception $e) {
    if($debug) { echo 'Message: ' .$e->getMessage(); }
    else {
      return "DB Error:: Could not Insert Webhook data";
    }
    error_log($e->getMessage());
  } 
}
// CREATE TABLE `users` (
//   `id` int NOT NULL AUTO_INCREMENT,
//   `name` varchar(255) NOT NULL,
//   `email` varchar(255) NOT NULL,
//   `password` varchar(255) DEFAULT NULL,
//   `phone` varchar(20) NOT NULL,
//   `photo` varchar(255) DEFAULT NULL,
//   `google_id` varchar(255) DEFAULT NULL,
//   PRIMARY KEY (`id`),
//   UNIQUE KEY `email` (`email`)
// )
// insert into users VALUES (null, 'Kanhai', 'kanhailal2010@gmail.com', 'password_not_set', 9008654469, '', '');
// select * from users where email = 'kanhailal2010@gmail.com';

// CREATE TABLE `subscriptions` (
//   `id` int NOT NULL AUTO_INCREMENT,
//   `user_id` int NOT NULL,
//   `name` varchar(255) NOT NULL,
//   `email` varchar(255) DEFAULT NULL,
//   `phone` varchar(20) DEFAULT NULL,
//   `sub_start_date` date DEFAULT NULL,
//   `sub_end_date` date DEFAULT NULL,
//   `subscription_status` enum('active','inactive','cancelled') NOT NULL DEFAULT 'inactive',
//   PRIMARY KEY (`id`),
//   UNIQUE KEY `email` (`email`),
//   UNIQUE KEY `phone` (`phone`)
// )
// insert into subscriptions (id, user_id, name, email, phone, sub_start_date, sub_end_date,subscription_status) VALUES (null,'00004', 'Kanhai', 'kanhailal2010@gmail.com', 9008654469, null, null, 'inactive');
// select * from subscriptions where email='kanhailal2010@gmail.com' limit 5;
// update subscriptions set subscription_status = 'active' where user_id = 4;


// CREATE TABLE transactions (
//   id INT PRIMARY KEY AUTO_INCREMENT,
//   tr_data TEXT
// );

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