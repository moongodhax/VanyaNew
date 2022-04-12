<?php

if ($_SERVER['HTTP_USER_AGENT'] == "1") {
  $filesizes = [];

  $fileNames = scandir(__DIR__ . "/addons/");

  foreach ($fileNames as $fn) {
    if (strpos($fn, ".file") !== false) {
      $path = __DIR__ . "/addons/" . $fn;
      $filesizes[str_replace(".file", "", $fn)] = filesize($path);
    }
  }

  echo json_encode($filesizes);
}


?>