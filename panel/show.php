<?php
require_once("./php/mysqli.php");

$stream_arr = ["mix", "eu", "us", "distributor"];
if (!isset($_GET['stream']) || !in_array($_GET['stream'], $stream_arr)) {
  header("HTTP/1.0 404 Not Found");
  die();
}
$stream = $_GET['stream'];

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
  else echo countSteam($stream);
} else if ($action == "delete") {
  clearCurrent($stream);
  if (mysqli_error($mysqli) == "") echo "1";
  else echo "0";
}

function countSteam($stream) {
  global $mysqli;

  $result = mysqli_query($mysqli, "SELECT `value` AS val FROM `settings` WHERE `name` = 'current_$stream'");
  $row = mysqli_fetch_assoc($result);
  $current_ts = $row['val'];

  $result = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM `records` WHERE `timestamp` > $current_ts AND `type` = '$stream'");
  $row = mysqli_fetch_assoc($result);
  return $row['cnt'];
}

function countDistributor() {
  global $mysqli;

  $result = mysqli_query($mysqli, "SELECT `value` AS val FROM `settings` WHERE `name` = 'current_distributor'");
  $row = mysqli_fetch_assoc($result);
  $current_ts = $row['val'];

  $q = "SELECT
    COUNT(CASE WHEN `timestamp` >= {$current_ts} AND `type` = 'mix' AND `distributor` = 'single' THEN 1 END) AS ws,
    COUNT(CASE WHEN `timestamp` >= {$current_ts} AND `type` = 'mix' AND `distributor` = 'double' THEN 1 END) AS wd,
    COUNT(CASE WHEN `timestamp` >= {$current_ts} AND `type` = 'eu' AND `distributor` = 'single' THEN 1 END) AS es,
    COUNT(CASE WHEN `timestamp` >= {$current_ts} AND `type` = 'eu' AND `distributor` = 'double' THEN 1 END) AS ed,
    COUNT(CASE WHEN `timestamp` >= {$current_ts} AND `type` = 'us' AND `distributor` = 'single' THEN 1 END) AS us,
    COUNT(CASE WHEN `timestamp` >= {$current_ts} AND `type` = 'us' AND `distributor` = 'double' THEN 1 END) AS ud
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

  $time = time();

  mysqli_query($mysqli, "UPDATE `settings` SET `value` = '$time' WHERE `name` = 'current_$stream'");

  $q = "INSERT INTO `current` (`type`, `time`) VALUES ('$stream', $time)";
  mysqli_query($mysqli, $q);
}

?>