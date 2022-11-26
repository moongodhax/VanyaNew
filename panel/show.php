<?php
$ip = getIP();

// if ($ip != '109.234.35.187' && $ip != '212.192.246.217' && $ip != '185.197.75.169'){
//   header("HTTP/1.0 404 Not Found");
//   die();
// }

require_once("../php/mysqli.php");

$stream_arr = getStreams();
$stream_arr[] = "distributor";

if (!isset($_GET['stream']) || !in_array($_GET['stream'], $stream_arr)) {
  header("HTTP/1.0 404 Not Found");
  die();
}
$stream = $_GET['stream'];

if (isset($_GET['timing'])) {
  $timing = $_GET['timing'];
  die(countTiming($stream, $timing));
}

$action_arr = ["show", "delete"];
if (!isset($_GET['action']) || !in_array($_GET['action'], $action_arr)) {
  header("HTTP/1.0 404 Not Found");
  die();
}
$action = $_GET['action'];

if ($action == "show") {
  if ($stream == "distributor") {
    $count = countDistributor();

    echo json_encode([
      'world' => [
        'single' => intval($count['ws']),
        'double' => intval($count['wd'])
      ],
      'europe' => [
        'single' => intval($count['es']),
        'double' => intval($count['ed'])
      ],
      'us' => [
        'single' => intval($count['us']),
        'double' => intval($count['ud'])
      ]
    ]);
  }
  else if ($stream == "xxx") {
    echo json_encode(countXXX());
  }
  else echo countStream($stream);
} else if ($action == "delete") {
  if ($stream == "distributor") clearDistributor();
  else clearCurrent($stream);
  if (mysqli_error($mysqli) == "") echo "done";
  else echo "0";
}


function getStreams() {
  global $mysqli;

  $streams = [];
  $result = mysqli_query($mysqli, "SELECT * FROM `streams`");
  while($row = mysqli_fetch_assoc($result)) {
    $streams[] = $row['name'];
  }

  return $streams;
}

function countStream($stream) {
  global $mysqli;

  $result = mysqli_query($mysqli, "SELECT `id`, `current_ts` FROM `streams` WHERE `name` = '$stream'");
  $row = mysqli_fetch_assoc($result);
  $current_ts = $row['current_ts'];
  $id = $row['id'];

  $result = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM `records` WHERE `timestamp` > $current_ts AND `streamid` = '$id' AND `type` = 'ok'");
  $row = mysqli_fetch_assoc($result);
  return $row['cnt'];
}

function countTiming($stream, $timing) {
  global $mysqli;

  $timings = [
    '5m' => 300,
    '15m' => 900,
    '30m' => 1800,
    '1h' => 3600,
    '3h' => 10800,
    '6h' => 21600,
    '12h' => 43200,
    '24h' => 86400,
  ];

  if (!isset($timings[$timing])) return -1;

  $result = mysqli_query($mysqli, "SELECT `id` FROM `streams` WHERE `name` = '$stream'");
  $row = mysqli_fetch_assoc($result);
  $id = $row['id'];

  $ts = time() - $timings[$timing];

  $result = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM `records` WHERE `timestamp` > $ts AND `streamid` = '$id' AND `type` = 'ok'");
  $row = mysqli_fetch_assoc($result);
  return $row['cnt'];
}

function countXXX() {
  global $mysqli;

  $streamid = 0;
  $current_ts = 0;
  
  $result = mysqli_query($mysqli, "SELECT * FROM `streams`");
  while($row = mysqli_fetch_assoc($result)) {
    if ($row['name'] == "xxx") { 
      $streamid = $row['id'];
      $current_ts = $row['current_ts'];
      break;
    }
  }

  $us = 0;
  $world = 0;
  
  $result = mysqli_query($mysqli, "SELECT * FROM `substreams`");
  while($row = mysqli_fetch_assoc($result)) {
    if ($row['streamid'] == $streamid && $row['name'] == "us") $us = $row['id'];
    if ($row['streamid'] == $streamid && $row['name'] == "world") $world = $row['id'];
  }

  $q = "SELECT
    COUNT(CASE WHEN `timestamp` >= {$current_ts} AND `substreamid` = $us THEN 1 END) AS us,
    COUNT(CASE WHEN `timestamp` >= {$current_ts} AND `substreamid` = $world THEN 1 END) AS world
  FROM `records`;";
  
  $result = mysqli_query($mysqli, $q);
  $row = mysqli_fetch_assoc($result);

  $out['us'] = $row['us'];
  $out['world'] = $row['world'];
  return $out;
}

function countDistributor() {
  global $mysqli;

  $mixone = 0;
  $mixtwo = 0;
  $eu = 0;
  $us = 0;
  $result = mysqli_query($mysqli, "SELECT * FROM `streams`");
  while($row = mysqli_fetch_assoc($result)) {
    if ($row['name'] == "mixone") $mixone = $row['id'];
    if ($row['name'] == "mixtwo") $mixtwo = $row['id'];
    if ($row['name'] == "eu") $eu = $row['id'];
    if ($row['name'] == "us") $us = $row['id'];
  }

  $result = mysqli_query($mysqli, "SELECT `value` AS val FROM `settings` WHERE `name` = 'current_distributor'");
  $row = mysqli_fetch_assoc($result);
  $current_ts = $row['val'];

  $q = "SELECT
    COUNT(CASE WHEN `timestamp` >= {$current_ts} AND (`streamid` = $mixone OR `streamid` = $mixtwo) AND `distributor` = 'single' THEN 1 END) AS ws,
    COUNT(CASE WHEN `timestamp` >= {$current_ts} AND (`streamid` = $mixone OR `streamid` = $mixtwo) AND `distributor` = 'double' THEN 1 END) AS wd,
    COUNT(CASE WHEN `timestamp` >= {$current_ts} AND `streamid` = $eu AND `distributor` = 'single' THEN 1 END) AS es,
    COUNT(CASE WHEN `timestamp` >= {$current_ts} AND `streamid` = $eu AND `distributor` = 'double' THEN 1 END) AS ed,
    COUNT(CASE WHEN `timestamp` >= {$current_ts} AND `streamid` = $us AND `distributor` = 'single' THEN 1 END) AS us,
    COUNT(CASE WHEN `timestamp` >= {$current_ts} AND `streamid` = $us AND `distributor` = 'double' THEN 1 END) AS ud
  FROM `records`;";
  
  $result = mysqli_query($mysqli, $q);
  $row = mysqli_fetch_assoc($result);

  $out['ws'] = $row['ws'];
  $out['wd'] = $row['wd'];

  $out['es'] = $row['es'];
  $out['ed'] = $row['ed'];

  $out['us'] = $row['us'];
  $out['ud'] = $row['ud'];

  return $out;
}

function clearCurrent($stream) {
  global $mysqli;

  $stream = mysqli_real_escape_string($mysqli, $stream);
  $time = time();

  $result = mysqli_query($mysqli, "SELECT `id` FROM `streams` WHERE `name` = '$stream'");
  $row = mysqli_fetch_assoc($result);
  $streamid = $row['id'];

  mysqli_query($mysqli, "UPDATE `streams` SET `current_ts` = '$time' WHERE `name` = '$stream'");
  mysqli_query($mysqli, "INSERT INTO `current` (`streamid`, `time`) VALUES ('$streamid', $time)");
}

function clearDistributor() {
  global $mysqli;

  $time = time();

  mysqli_query($mysqli, "UPDATE `settings` SET `value` = '$time' WHERE `name` = 'current_distributor'");
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

?>