<?php
require_once("./geoip/geoip.php");
require_once("./php/mysqli.php");

// проверка IP на блеклист
$ip = getIP();
if (checkBlacklist($mysqli, $ip)) {
  writeRecord($mysqli, "blacklist", '', $_SERVER['HTTP_USER_AGENT'], '', $ip, '', getCountryCode($ip), time());
  die("0");
}

// проверка юзер-агента
if ($_SERVER['HTTP_USER_AGENT'] != "1") {
  writeRecord($mysqli, "decline", '', $_SERVER['HTTP_USER_AGENT'], '', $ip, '', getCountryCode($ip), time());
  header("HTTP/1.0 404 Not Found");
  die();
}

// проверка потока и подпотока
if (!isset($_GET["stream"])) die("0");

$stream = "";
$substream = "";
$streams = getStreams($mysqli);
foreach ($streams as $s) {
  if ($s["stream"] == $_GET["stream"]) {
    $stream = $_GET["stream"];

    if (isset($_GET["substream"])) {
      $search = false;
      foreach ($s["substreams"] as $ss) {
        if ($ss['name'] == $_GET["substream"]) {
          $substream = $_GET["substream"];
          $search = true;
          break;
        }
      }
      if (!$search) {
        writeRecord($mysqli, "decline", '', $_SERVER['HTTP_USER_AGENT'], '', $ip, '', getCountryCode($ip), time());
        die("0");
      }
    }

    break;
  }
}

if ($stream == "") {
  writeRecord($mysqli, "decline", '', $_SERVER['HTTP_USER_AGENT'], '', $ip, '', getCountryCode($ip), time());
  die("0");
}

// проверка айпи на повторы
if (!checkIP($mysqli, $ip)) {
  writeRecord($mysqli, "decline", '', $_SERVER['HTTP_USER_AGENT'], '', $ip, '', getCountryCode($ip), time());
  die("0");
}

$sub = $_GET["sub"];

writeRecord($mysqli, $stream, $substream, $_SERVER['HTTP_USER_AGENT'], $sub, $ip, getDistributor(strtoupper($stream)), getCountryCode($ip), time());

echo "1";

if ($stream == "mix") $trackid = "lltvuslp";
else if ($stream == "us") $trackid = "badlcf58";
else if ($stream == "eu") $trackid = "wnckxrxo";

if ($stream != "ru" && $stream != "shortcuts" && $stream != "doubles") {
  file_get_contents("http://adsymbol.com/track/$trackid?sub=$sub&ip=$ip");
}

/*
 * функции
 */

function getStreams($mysqli) {
  $streams = [];
  $result = mysqli_query($mysqli, "SELECT * FROM `streams`");
  while($row = mysqli_fetch_assoc($result)) {
    $streams[$row['id']] = [
      "id" => $row['id'],
      "stream" => $row['name'],
      "substreams" => []
    ];
  }

  $result = mysqli_query($mysqli, "SELECT * FROM `substreams`");
  while($row = mysqli_fetch_assoc($result)) {
    $streams[$row['streamid']]["substreams"][] = [
      "id" => $row['id'],
      "name" => $row['name']
    ];
  }

  $streams = array_values($streams);

  return $streams;
}

function getPayloads($mysqli) {
  $payloads = [];
  $result = mysqli_query($mysqli, "SELECT * FROM `payloads`");
  while($row = mysqli_fetch_assoc($result)) {
    $payloads[] = [
      "id" => $row['id'],
      "name" => $row['name']
    ];
  }

  return $payloads;
}

function getDistributor($stream) {
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, "http://203.159.80.49/getsizes.php");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: 1']);

  $resp = curl_exec($ch);
  curl_close($ch);

  $decoded = json_decode($resp, true);
  
  if ($decoded !== NULL && isset($decoded[$stream])) {
    if ($decoded[$stream] == 4608) return 'single';
    else return 'double';
  }
  else return '';
}

function getCountryCode($ip) {
  $countryCode = "";
  
  try {
      $data = json_decode(file_get_contents('http://ip-api.com/json/' . $ip), true);
      if ($data["status"] == "success") $countryCode = $data["countryCode"];
      else $countryCode = ip_code($ip);
  } catch (Exception $e) {
      $countryCode = ip_code($ip);
  }
  
  return $countryCode;
}

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

function checkIP($mysqli, $ip) {
  $result = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM `records` WHERE `ip` = '$ip' AND `stream` != 'blacklist'");
  $row = mysqli_fetch_assoc($result);
  if ($row['cnt'] > 0) {
    return false;
  } else {
    return true;
  }
}

function checkBlacklist($mysqli, $ip) {
  $ip = mysqli_real_escape_string($mysqli, $ip);

  $result = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM `blacklist` WHERE `ip` = '$ip'");
  $row = mysqli_fetch_assoc($result);
  return ($row['cnt'] != 0);
}

function writeRecord($mysqli, $stream, $substream, $ua, $sub,  $ip,$distributor, $country, $timestamp) {
  $q = "INSERT INTO `records` (`stream`, `substream`, `ua`, `sub`, `ip`, `distributor`, `country`, `timestamp`) VALUES ('$stream', '$substream', '$ua', '$sub', '$ip', '$distributor', '$country', $timestamp)";
  mysqli_query($mysqli, $q);
}

?>