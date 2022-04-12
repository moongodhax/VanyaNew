<?php

function getIP() {
  if ((!empty($_SERVER['GEOIP_ADDR'])) && (($_SERVER['GEOIP_ADDR']) <> '127.0.0.1'))
      $ip = $_SERVER['GEOIP_ADDR'];
  
  else if ((!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) && (($_SERVER['HTTP_X_FORWARDED_FOR']) <> '127.0.0.1') && (($_SERVER['HTTP_X_FORWARDED_FOR']) <> ($_SERVER['SERVER_ADDR'])))
      $ip = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR'])[0];
  
  else if ((!empty($_SERVER['HTTP_CLIENT_IP'])) && (($_SERVER['HTTP_CLIENT_IP']) <> '127.0.0.1') && (($_SERVER['HTTP_CLIENT_IP']) <> ($_SERVER['SERVER_ADDR'])))
      $ip = $_SERVER['HTTP_CLIENT_IP'];
  
  else if ((!empty($_SERVER['HTTP_X_REAL_IP'])) && (($_SERVER['HTTP_X_REAL_IP']) <> '127.0.0.1') && (($_SERVER['HTTP_X_REAL_IP']) <> ($_SERVER['SERVER_ADDR'])))
      $ip = $_SERVER['HTTP_X_REAL_IP'];
  
  else $ip = $_SERVER['REMOTE_ADDR'];
  
  return $ip; 
}

$ip = getIP();

if ($ip != "109.234.35.187") {
  header("HTTP/1.0 404 Not Found");
  die();
}

if (!isset($_GET["stream"])){
  die("0");
}

$filename = './addons/' . strtoupper($_GET["stream"]) . ".file";

$links = json_decode(file_get_contents("./addons/_links.json"), true);
$links[$_GET["stream"]] = "0";

if (isset($_GET["url"])) {
  $url = $_GET["url"];
  if (strpos($url, "http") === false || strpos($url, "https") === false) {
    $url = "http://" . $url;
  }

  $links[$_GET["stream"]] = $url;
  file_put_contents("./addons/_links.json", json_encode($links));

  $contents = file_get_contents($url);
  if (strlen($contents) < 1024) {
    $out = date("\nd/m/Y H:i") . " up.php -> " . $_GET['stream'] . " -> $url -> downloaded < 1024";
    file_put_contents(__DIR__ . "/addons/_links.log", $out, FILE_APPEND);
    die("0");    
  }
  
  if (file_put_contents($filename, $contents) !== false) {
    $out = date("\nd/m/Y H:i") . " up.php -> " . $_GET['stream'] . " -> $url -> downloaded " . strlen($contents) . "b";
    file_put_contents(__DIR__ . "/addons/_links.log", $out, FILE_APPEND);
    die("done");
  } else {
    $out = date("\nd/m/Y H:i") . " up.php -> " . $_GET['stream'] . " -> $url -> download error";
    file_put_contents(__DIR__ . "/addons/_links.log", $out, FILE_APPEND);
    die("0");
  }
} else if (!empty($_FILES) && $_FILES['uploadfile']['size'] > 1024 && $_FILES['uploadfile']['tmp_name'] != "none") {
  file_put_contents("./addons/_links.json", json_encode($links));
  if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $filename)) {
    $out = date("\nd/m/Y H:i") . " up.php -> " . $_GET['stream'] . " -> uploaded " . $_FILES['uploadfile']['size'] . "b";
    file_put_contents(__DIR__ . "/addons/_links.log", $out, FILE_APPEND);
    die("done");
  } else {
    $out = date("\nd/m/Y H:i") . " up.php -> " . $_GET['stream'] . " -> $url -> upload error";
    file_put_contents(__DIR__ . "/addons/_links.log", $out, FILE_APPEND);
    die("0");
  }
} else {
  file_put_contents("./addons/_links.json", json_encode($links));
  die("0");
}

?>