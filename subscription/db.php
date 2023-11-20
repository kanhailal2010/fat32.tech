<?php 
include('common.php');

$servername = "localhost";
$username = "root";
$password = "MSkrishna@14";
$database = "fat32";


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

// CREATE TABLE users (
//   id INT PRIMARY KEY AUTO_INCREMENT,
//   name VARCHAR(255) NOT NULL,
//   email VARCHAR(255) NOT NULL UNIQUE,
//   phone VARCHAR(20) NOT NULL
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