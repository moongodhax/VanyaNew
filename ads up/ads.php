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

function handleDB($ip, $ua) {
  $mysqli = mysqli_connect("localhost", "root", "зфыы", "ads");

  // проверка есть ли запись с такими данными
  $result = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM `records` WHERE `ip` = '$ip' AND `ua` = $ua");
  $row = mysqli_fetch_assoc($result);
  if ($row['cnt'] > 0) { // если есть то получаю, инкрементирую и возвращаю текущую очередь
    $result = mysqli_query($mysqli, "SELECT `queue` AS q FROM `records` WHERE `ip` = '$ip' AND `ua` = $ua");
    $row = mysqli_fetch_assoc($result);
    $new_q = intval($row["q"]) + 1;
    mysqli_query($mysqli, "UPDATE `records` SET `queue` = $new_q WHERE `ip` = '$ip' AND `ua` = $ua");
    return $row["q"];
  } else { // если нет, то создаю, ставлю очередь 1 и возвращаю 0
    mysqli_query($mysqli, "INSERT INTO `records` (`ip`, `ua`, `queue`) VALUES ('$ip', $ua, 1)");
    return 0;
  }
}

function sendFile($code) {
  $filename = "./files/$code";
  
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

$ip = getIP();

if ($_SERVER['HTTP_USER_AGENT'] == "1") {
  $pos = handleDB($ip, $_SERVER['HTTP_USER_AGENT']);
  switch ($pos) {
    case 0: sendFile("MIX"); break;
    case 1: sendFile("D1"); break;
    case 2: sendFile("D2"); break;
    default: die("0");
  }
} else if ($_SERVER['HTTP_USER_AGENT'] == "2") {
  $pos = handleDB($ip, $_SERVER['HTTP_USER_AGENT']);
  switch ($pos) {
    case 0: sendFile("EU"); break;
    case 1: sendFile("D3"); break;
    case 2: sendFile("D4"); break;
    default: die("0");
  }
} else if ($_SERVER['HTTP_USER_AGENT'] == "3") {
  $pos = handleDB($ip, $_SERVER['HTTP_USER_AGENT']);
  switch ($pos) {
    case 0: sendFile("US"); break;
    case 1: sendFile("D3"); break;
    case 2: sendFile("D4"); break;
    default: die("0");
  }
}

?>