<?php
// * * * * * php /path/to/cron_to_delete_old_files.php

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

// Call the deleteOldFilesFromSecretDirectory() method every minute
while (true) {
  deleteOldFilesFromDeleteDirectory();
  sleep(60);
}