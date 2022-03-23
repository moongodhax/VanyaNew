<?php

if ($_SERVER['HTTP_USER_AGENT'] == "1") {
  $filesizes = [];

  $fileNames = scandir(__DIR__ . "/files/");

  foreach ($fileNames as $fn) {
    if (strpos($fn, ".file") !== false) {
      $path = __DIR__ . "/files/" . $fn;
      $filesizes[str_replace(".file", "", $fn)] = filesize($path);
    }
  }

  echo json_encode($filesizes);
}


?>