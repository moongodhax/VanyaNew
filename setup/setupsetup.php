<?php

function sendFile($filename) {
  header("Pragma: public"); 
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Cache-Control: private", false);
  header("Content-Type: application/octet-stream");
  header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\";" );
  header("Content-Transfer-Encoding: binary");
  header("Content-Length: " . filesize($filename));

  readfile($filename);
}

if ($_SERVER['HTTP_USER_AGENT'] == "1") {
  sendFile("./setup/dll");
} else if ($_SERVER['HTTP_USER_AGENT'] == "2") {
  sendFile("./setup/soft");
} else {
  header("HTTP/1.0 404 Not Found");
  die();
}

?>