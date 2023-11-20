<?php 

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: OPTIONS');
header('Access-Control-Allow-Credentials: true');

$debug = 1;
if($debug) {
  register_shutdown_function('handleFatalError');
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}

function validateAjaxData($data) {
  $errors = [];

  if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email address';
  }

  if (isset($data['phone_number']) && !preg_match('/^\d{10}$/', $data['phone_number'])) {
    $errors['phone_number'] = 'Invalid phone number';
  }

  if (isset($data['username']) && !preg_match('/^[a-zA-Z0-9]+$/', $data['username'])) {
    $errors['username'] = 'Invalid username';
  }

  if (count($errors) > 0) {
    $response['status'] = false;
    $response['errors'] = $errors;
  } else {
    $response['status'] = true;
  }

  return $response;
  // echo json_encode($response);
}

function getSubscribedFileName() {
  // Generate a random alphanumerical filename
  $randomFilename = generateRandomAlphanumericFilename();
  deleteOldFilesFromDeleteDirectory();

  // Make a copy of the subscriber_code.js file
  $sourceFile = 'subscriber.js';
  $destinationFile = 'delete/' . $randomFilename . '.js';

  if (!copy($sourceFile, $destinationFile)) {
    throw new Exception('Failed to copy subscriber_code.js file');
  }

  return $randomFilename;
}

function generateRandomAlphanumericFilename() {
  $length = 10;
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $randomString = '';

  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, strlen($characters) - 1)];
  }

  return $randomString;
}

// delete all the files created before 3 seconds
function deleteOldFilesFromDeleteDirectory() {
  $secretDirectory = 'delete';

  if (!is_dir($secretDirectory)) {
    return;
  }

  $files = scandir($secretDirectory);

  foreach ($files as $file) {
    if ($file === '.' || $file === '..') {
      continue;
    }

    $filePath = $secretDirectory . '/' . $file;
    $fileCreationTime = filemtime($filePath);
    $currentTime = time();

    if ($currentTime - $fileCreationTime > 3) {
      unlink($filePath);
    }
  }
}















function handleFatalError() {
  if ($error = error_get_last()) {
    $errorMessage = 'Fatal error: ' . $error['type'] . ' - ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line'];

    // Log the error to a file
    // error_log($errorMessage, 3, 'error_log.txt');
    print_r($errorMessage);
  }
}


