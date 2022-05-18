<?php
require_once("./geoip/geoip.php");
require_once("./php/mysqli.php");

$ip = getIP();

// проверка потока и подпотока
if (!isset($_GET["stream"])) {
  writeRecord($mysqli, '', 0, '', 0, "decline", 'No stream', $_SERVER['HTTP_USER_AGENT'], '', $ip, '', getCountryCode($ip), time());
  die("0");
}

$stream = "";
$streamid = "";
$substreamid = "";
if ($_GET["stream"] == "mix" && $_GET["substream"] == "mixtwo") {
  $_GET["stream"] = "mixone";
} else if ($_GET["stream"] == "r" || $_GET["stream"] == "n") {
  writeRecord($mysqli, 0, 0, "decline", 'R or N', $_SERVER['HTTP_USER_AGENT'], '', $ip, '', getCountryCode($ip), time());
  die("0");
} else {
  $streams = getStreams($mysqli);
  foreach ($streams as $s) {
    if ($s["stream"] == $_GET["stream"]) {
      $stream = $s["stream"];
      $streamid = $s["id"];

      if (isset($_GET["substream"])) {
        $search = false;
        foreach ($s["substreams"] as $ss) {
          if ($ss['name'] == $_GET["substream"]) {
            $substreamid = $ss['id'];
            $search = true;
            break;
          }
        }
        if (!$search) {
          writeRecord($mysqli, $streamid, 0, "decline", 'Incorrect substream', $_SERVER['HTTP_USER_AGENT'], '', $ip, '', getCountryCode($ip), time());
          die("0");
        }
      }

      break;
    }
  }
}

if ($stream == "") {
  writeRecord($mysqli, '', 0, '', 0, "decline", 'Empty stream', $_SERVER['HTTP_USER_AGENT'], '', $ip, '', getCountryCode($ip), time());
  die("0");
}

// проверка IP на блеклист
if (checkBlacklist($mysqli, $ip)) {
  writeRecord($mysqli, $streamid, $substreamid, "blacklist", 'Blacklist', $_SERVER['HTTP_USER_AGENT'], '', $ip, '', getCountryCode($ip), time());
  die("0");
}

// проверка за забаненность страны
$country = getCountryCode($ip);
$banned = getBannedCountries($mysqli);

if (array_search($country, $banned) !== false) {
  writeRecord($mysqli, $streamid, $substreamid, "banned", 'Banned country', $_SERVER['HTTP_USER_AGENT'], '', $ip, '', getCountryCode($ip), time());
  die("0");
}

// проверка юзер-агента
if ($_SERVER['HTTP_USER_AGENT'] != "1") {
  writeRecord($mysqli, $streamid, $substreamid, "decline", 'Incorrect user-agent', $_SERVER['HTTP_USER_AGENT'], '', $ip, '', getCountryCode($ip), time());
  header("HTTP/1.0 404 Not Found");
  die();
}

// проверка айпи на повторы
if (!checkIP($mysqli, $ip)) {
  writeRecord($mysqli, $streamid, $substreamid, "decline", 'Duplicate', $_SERVER['HTTP_USER_AGENT'], '', $ip, '', getCountryCode($ip), time());
  die("0");
}

$sub = $_GET["sub"];

writeRecord($mysqli, $streamid, $substreamid, "ok", "", $_SERVER['HTTP_USER_AGENT'], $sub, $ip, getDistributor(strtoupper($stream)), getCountryCode($ip), time());

echo "1";

/*
 * функции
 */

function getBannedCountries($mysqli) {
  $result = mysqli_query($mysqli, "SELECT `value` AS val FROM `settings` WHERE `name` = 'banned_countries'");
  $row = mysqli_fetch_assoc($result);
  return json_decode($row['val']);
}

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
  
  // try {
      // $data = json_decode(file_get_contents('http://ip-api.com/json/' . $ip), true);
      // if ($data["status"] == "success") $countryCode = $data["countryCode"];
      // else $countryCode = ip_code($ip);
  // } catch (Exception $e) {
      $countryCode = ip_code($ip);
  // }
  
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
  
  if ($ip == 'unknown') $ip = $_SERVER['REMOTE_ADDR'];

  return $ip; 
}

function checkIP($mysqli, $ip) {
  $result = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM `records` WHERE `ip` = '$ip' AND `type` != 'blacklist'");
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

function writeRecord($mysqli, $streamid, $substreamid, $type, $reason, $ua, $sub,  $ip,$distributor, $country, $timestamp) {
  $q = "INSERT INTO `records` (`streamid`, `substreamid`, `type`, `reason`, `ua`, `sub`, `ip`, `distributor`, `country`, `timestamp`) VALUES 
  ($streamid, $substreamid, '$type', '$reason', '$ua', '$sub', '$ip', '$distributor', '$country', $timestamp)";
  mysqli_query($mysqli, $q);
}

?>