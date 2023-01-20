<?php
require_once("./geoip/geoip.php");
require_once("./php/mysqli.php");

$ip = getIP();

if ($_GET["str"] == "r" || $_GET["str"] == "n") {
  writeRecord($mysqli, 0, 0, "decline", 'R or N', $_SERVER['HTTP_USER_AGENT'], '', $ip, '', getCountryCode($ip), time());
  die("0");
} 

$streamid = null;
$substreamid = null;

if ($_GET["str"] == 'start' && $_GET["substr"] == 'mixinte') {
  $streams = getStreams($mysqli);
  foreach ($streams as $s) {
    if ($s['name'] == 'start') {
      $streamid = $s['id'];
      break;
    }
  }

  $substreams = getSubStreams($mysqli);
  foreach ($substreams as $ss) {
    if ($ss['streamid'] == $streamid && $ss['name'] == 'mixinte') {
      $substreamid = $ss['id'];
      break;
    }
  }
}
else if (isset($_GET["substr"])) {
  $substreams = getSubStreams($mysqli);
  foreach ($substreams as $ss) {
    if ($ss['name'] == $_GET["substr"]) {
      $streamid = $ss['streamid'];
      $substreamid = $ss['id'];
      break;
    }
  }

  if ($substreamid === null) {
    writeRecord($mysqli, $streamid, 0, "decline", 'Incorrect substream', $_SERVER['HTTP_USER_AGENT'], '', $ip, '', getCountryCode($ip), time());
    die("0");
  }
} else {
  writeRecord($mysqli, '', 0, '', 0, "decline", 'No stream', $_SERVER['HTTP_USER_AGENT'], '', $ip, '', getCountryCode($ip), time());
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
if ($_SERVER['HTTP_USER_AGENT'] != "OK") {
  writeRecord($mysqli, $streamid, $substreamid, "decline", 'Incorrect user-agent', $_SERVER['HTTP_USER_AGENT'], '', $ip, '', getCountryCode($ip), time());
  header("HTTP/1.0 404 Not Found");
  die();
}

// проверка айпи на повторы
if (!checkIP($mysqli, $streamid, $ip)) {
  writeRecord($mysqli, $streamid, $substreamid, "decline", 'Duplicate', $_SERVER['HTTP_USER_AGENT'], '', $ip, '', getCountryCode($ip), time());
  die("0");
}

$sub = $_GET["s"];
$stream = getStreamName($mysqli, $streamid);
writeRecord($mysqli, $streamid, $substreamid, "ok", "", $_SERVER['HTTP_USER_AGENT'], $sub, $ip, getDistributor(strtoupper($stream)), getCountryCode($ip), time());

echo "1";

/*
 * функции
 */

function getStreamName($mysqli, $streamid) {
  $result = mysqli_query($mysqli, "SELECT `name` FROM `streams` WHERE `id` = $streamid");
  $row = mysqli_fetch_assoc($result);
  return $row['name'];
}

function getBannedCountries($mysqli) {
  $result = mysqli_query($mysqli, "SELECT `value` AS val FROM `settings` WHERE `name` = 'banned_countries'");
  $row = mysqli_fetch_assoc($result);
  return json_decode($row['val']);
}

function getStreams($mysqli) {
  $out = [];

  $result = mysqli_query($mysqli, "SELECT * FROM `streams`");
  while($row = mysqli_fetch_assoc($result)) {
    $out[] = [
      "id" => $row['id'],
      "name" => $row['name']
    ];
  }

  return $out;
}

function getSubStreams($mysqli) {
  $out = [];

  $result = mysqli_query($mysqli, "SELECT * FROM `substreams`");
  while($row = mysqli_fetch_assoc($result)) {
    $out[] = [
      "id" => $row['id'],
      "streamid" => $row['streamid'],
      "name" => $row['name']
    ];
  }

  return $out;
}

function getDistributor($stream) {
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, "http://45.12.253.75/getsizes.php");
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

function checkIP($mysqli, $streamid, $ip) {
  $result = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM `records` WHERE `streamid` = $streamid AND `ip` = '$ip' AND `type` != 'blacklist'");
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

function writeRecord($mysqli, $streamid, $substreamid, $type, $reason, $ua, $sub,  $ip, $distributor, $country, $timestamp) {
  $result = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM `records` WHERE `streamid` = $streamid AND `ip` = '$ip' AND `type` == '$type'");
  $row = mysqli_fetch_assoc($result);
  if ($row['cnt'] == 0) {
    $q = "INSERT INTO `records` (`streamid`, `substreamid`, `type`, `reason`, `ua`, `sub`, `ip`, `distributor`, `country`, `timestamp`) VALUES 
    ($streamid, $substreamid, '$type', '$reason', '$ua', '$sub', '$ip', '$distributor', '$country', $timestamp)";
    mysqli_query($mysqli, $q);
  }
}

?>