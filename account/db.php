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

// CREATE TABLE users (
//   id INT PRIMARY KEY AUTO_INCREMENT,
//   name VARCHAR(255) NOT NULL,
//   password VARCHAR(255) NOT NULL,
//   email VARCHAR(255) NOT NULL UNIQUE,
//   phone VARCHAR(20) NOT NULL,
//   photo VARCHAR(255),
//   google_id VARCHAR(255) 
// );
// insert into users VALUES (null, 'Kanhai', 'kanhailal2010@gmail.com', 9008654469);
// select * from users where email = 'kanhailal2010@gmail.com';

// CREATE TABLE subscriptions (
//     id INT PRIMARY KEY AUTO_INCREMENT,
//     user_id INT NOT NULL,
//     name VARCHAR(255) NOT NULL,
//     email VARCHAR(255) UNIQUE,
//     phone VARCHAR(20) UNIQUE,
//     sub_start_date DATE,
//     sub_end_date DATE,
//     subscription_status ENUM('active', 'inactive', 'cancelled') NOT NULL DEFAULT 'inactive'
//   );
// insert into subscriptions (id, user_id, name, email, phone, sub_start_date, sub_end_date,subscription_status) VALUES (null,'00004', 'Kanhai', 'kanhailal2010@gmail.com', 9008654469, null, null, 'inactive');
// select * from subscriptions where email='kanhailal2010@gmail.com' limit 5;
// update subscriptions set subscription_status = 'active' where user_id = 4;


// CREATE TABLE transactions (
//   id INT PRIMARY KEY AUTO_INCREMENT,
//   tr_data TEXT
// );

// CREATE TABLE orders (
//   order_id INT PRIMARY KEY AUTO_INCREMENT,
//   user_id INT NOT NULL,
//   order_date DATETIME NOT NULL,
//   order_status VARCHAR(255) NOT NULL,
//   total_amount DECIMAL(10,2) NOT NULL,
//   transaction_id VARCHAR(255) NOT NULL,
//   billing_address VARCHAR(255) NOT NULL
// );