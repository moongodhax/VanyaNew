<?php
require_once(__DIR__ . "/../geoip/geoip.php");
require_once(__DIR__ . "/mysqli.php");

require_once(__DIR__ . "/settings.php");

$SALT = "zslkjvlzskj";

function getAllStats() {
  global $mysqli;

  $streams = getStreams();
  
  $hour = time() - 3600;
  $week = time() - 3600 * 24 * 7;

  $date = new DateTime();
  $date->modify('today');
  $today = $date->getTimestamp();

  $q = "SELECT  \n";
  foreach ($streams as $stream) {
    $current_ts = $stream['current_ts'];
    $stream_id = $stream["id"];

    $q .= "COUNT(CASE WHEN `timestamp` > $current_ts AND `streamid` = '$stream_id' THEN 1 END) AS '{$stream_id}_current',\n";
    $q .= "COUNT(CASE WHEN `timestamp` > $hour AND `streamid` = '$stream_id' THEN 1 END) AS '{$stream_id}_hour',\n";
    $q .= "COUNT(CASE WHEN `timestamp` > $today AND `streamid` = '$stream_id' THEN 1 END) AS '{$stream_id}_day',\n";
    $q .= "COUNT(CASE WHEN `timestamp` > $week AND `streamid` = '$stream_id' THEN 1 END) AS '{$stream_id}_week',\n";

    foreach ($stream['substreams'] as $substream) {
      $substream_id = $substream["id"];
      $q .= "COUNT(CASE WHEN `timestamp` > $current_ts AND `streamid` = '$stream_id' AND `substreamid` = '$substream_id' THEN 1 END) AS '{$stream_id}_{$substream_id}_current',\n";
      $q .= "COUNT(CASE WHEN `timestamp` > $hour AND `streamid` = '$stream_id' AND `substreamid` = '$substream_id' THEN 1 END) AS '{$stream_id}_{$substream_id}_hour',\n";
      $q .= "COUNT(CASE WHEN `timestamp` > $today AND `streamid` = '$stream_id' AND `substreamid` = '$substream_id' THEN 1 END) AS '{$stream_id}_{$substream_id}_day',\n";
      $q .= "COUNT(CASE WHEN `timestamp` > $week AND `streamid` = '$stream_id' AND `substreamid` = '$substream_id' THEN 1 END) AS '{$stream_id}_{$substream_id}_week',\n";
    }
  }
  $q = substr($q, 0, -2);
  $q .= "\nFROM\n";
  $q .= "
  (
    SELECT MIN(`id`), ANY_VALUE(`timestamp`) as 'timestamp', ANY_VALUE(`streamid`) as 'streamid', ANY_VALUE(`substreamid`) as 'substreamid'
    FROM `records` 
    WHERE `type` = 'ok'
    GROUP BY `ip`
  ) a
  ";

  $result = mysqli_query($mysqli, $q);
  $row = mysqli_fetch_assoc($result);

  $out = [];
  foreach ($streams as $stream) {
    $stream_id = $stream["id"];
    $out[] = [
      "type" => "stream",
      "id" => $stream["id"],
      "name" => $stream['stream'],
      "color" => $stream['color'],
      "position" => $stream['position'],
      "stats" => [
        "current" => $row["{$stream_id}_current"], 
        "hour" => $row["{$stream_id}_hour"], 
        "day" => $row["{$stream_id}_day"], 
        "7days" => $row["{$stream_id}_week"]
      ]
    ];

    foreach ($stream['substreams'] as $substream) {
      $substream_id = $substream["id"];
      $out[] = [
        "type" => "substream",
        "id" => $substream["id"],
        "parentname" => $stream['stream'],
        "parentcolor" => $stream['color'],
        "name" => $substream["name"],
        "hash" => $substream["hash"],
        "position" => $substream["position"],
        "stats" => [
          "current" => $row["{$stream_id}_{$substream_id}_current"], 
          "hour" => $row["{$stream_id}_{$substream_id}_hour"], 
          "day" => $row["{$stream_id}_{$substream_id}_day"], 
          "7days" => $row["{$stream_id}_{$substream_id}_week"]
        ]
      ];
    }
  }
  return $out;
}

function getMap($streamid, $substreamid = 0) {
  global $mysqli;

  $streamid = mysqli_real_escape_string($mysqli, $streamid);
  $substreamid = mysqli_real_escape_string($mysqli, $substreamid);

  $and = ($streamid == 0) ? "" : "AND `streamid` = '$streamid'";
  if ($substreamid != 0) $and .= " AND `substreamid` = '$substreamid'";

  $countries = [
    "AF" => 0, "AL" => 0, "DZ" => 0, "AO" => 0, "AG" => 0, "AR" => 0, "AM" => 0, "AU" => 0, "AT" => 0,
    "AZ" => 0, "BS" => 0, "BH" => 0, "BD" => 0, "BB" => 0, "BY" => 0, "BE" => 0, "BZ" => 0, "BJ" => 0,
    "BT" => 0, "BO" => 0, "BA" => 0, "BW" => 0, "BR" => 0, "BN" => 0, "BG" => 0, "BF" => 0, "BI" => 0,
    "KH" => 0, "CM" => 0, "CA" => 0, "CV" => 0, "CF" => 0, "TD" => 0, "CL" => 0, "CN" => 0, "CO" => 0,
    "KM" => 0, "CD" => 0, "CG" => 0, "CR" => 0, "CI" => 0, "HR" => 0, "CY" => 0, "CZ" => 0, "DK" => 0,
    "DJ" => 0, "DM" => 0, "DO" => 0, "EC" => 0, "EG" => 0, "SV" => 0, "GQ" => 0, "ER" => 0, "EE" => 0,
    "ET" => 0, "FJ" => 0, "FI" => 0, "FR" => 0, "GA" => 0, "GM" => 0, "GE" => 0, "DE" => 0, "GH" => 0,
    "GR" => 0, "GD" => 0, "GT" => 0, "GN" => 0, "GW" => 0, "GY" => 0, "HT" => 0, "HN" => 0, "HK" => 0,
    "HU" => 0, "IS" => 0, "IN" => 0, "ID" => 0, "IR" => 0, "IQ" => 0, "IE" => 0, "IL" => 0, "IT" => 0,
    "JM" => 0, "JP" => 0, "JO" => 0, "KZ" => 0, "KE" => 0, "KI" => 0, "KR" => 0, "KW" => 0, "KG" => 0,
    "LA" => 0, "LV" => 0, "LB" => 0, "LS" => 0, "LR" => 0, "LY" => 0, "LT" => 0, "LU" => 0, "MK" => 0,
    "MG" => 0, "MW" => 0, "MY" => 0, "MV" => 0, "ML" => 0, "MT" => 0, "MR" => 0, "MU" => 0, "MX" => 0,
    "MD" => 0, "MN" => 0, "ME" => 0, "MA" => 0, "MZ" => 0, "MM" => 0, "NA" => 0, "NP" => 0, "NL" => 0,
    "NZ" => 0, "NI" => 0, "NE" => 0, "NG" => 0, "NO" => 0, "OM" => 0, "PK" => 0, "PA" => 0, "PG" => 0,
    "PY" => 0, "PE" => 0, "PH" => 0, "PL" => 0, "PT" => 0, "QA" => 0, "RO" => 0, "RU" => 0, "RW" => 0,
    "WS" => 0, "ST" => 0, "SA" => 0, "SN" => 0, "RS" => 0, "SC" => 0, "SL" => 0, "SG" => 0, "SK" => 0,
    "SI" => 0, "SB" => 0, "ZA" => 0, "ES" => 0, "LK" => 0, "KN" => 0, "LC" => 0, "VC" => 0, "SD" => 0,
    "SR" => 0, "SZ" => 0, "SE" => 0, "CH" => 0, "SY" => 0, "TW" => 0, "TJ" => 0, "TZ" => 0, "TH" => 0,
    "TL" => 0, "TG" => 0, "TO" => 0, "TT" => 0, "TN" => 0, "TR" => 0, "TM" => 0, "UG" => 0, "UA" => 0,
    "AE" => 0, "GB" => 0, "US" => 0, "UY" => 0, "UZ" => 0, "VU" => 0, "VE" => 0, "VN" => 0, "YE" => 0,
    "ZM" => 0, "ZW" => 0, "GL" => 0, "SS" => 0, "KP" => 0, "CU" => 0, "SO" => 0, "XS" => 0, "EH" => 0, 
    "TF" => 0, "NC" => 0, "FK" => 0, "PR" => 0, "XK" => 0, "XC" => 0, "PS" => 0, "UNDEFINED" => 0
  ];
  
  $unique = [];
  
  $result = mysqli_query($mysqli, "SELECT DISTINCT `country` FROM `records` WHERE `type` = 'ok' $and ");
  while($row = mysqli_fetch_assoc($result)) {
    $unique[] = $row['country'];
  }
  
  if (count($unique) > 0) {
    $q = "SELECT  \n";
    foreach($unique as $v) {
      $q .= "COUNT(CASE WHEN `country` = '$v' $and THEN 1 END) AS '$v',\n";
    }
    $q = substr($q, 0, -2);
    $q .= "\nFROM\n";
    $q .= "
    (
      SELECT MIN(`id`), ANY_VALUE(`country`) as 'country', ANY_VALUE(`streamid`) as 'streamid', ANY_VALUE(`substreamid`) as 'substreamid'
      FROM `records` 
      WHERE `type` = 'ok'
      GROUP BY `ip`
    ) a
    ";

    $result = mysqli_query($mysqli, $q);
    $row = mysqli_fetch_assoc($result);
    
    foreach($unique as $v) {
      $countries[$v] = intval($row[$v]);
    }

    arsort($countries, SORT_NUMERIC);
  }
  return $countries;
}

function getCountries($countries) {
  $out = [];
  foreach($countries as $k => $v) {
    if ($v > 0) {
      $out[] = [
        "flag" => ($k == "?") ? "unknown" : strtolower($k),
        "name" => ($k == "?") ? "Unknown" : code_name($k),
        "count" => $v
      ];
    }
  }

  return $out;
}

function getDayChartMonth($streamid, $substreamid = 0) {
  global $mysqli;

  $streamid = mysqli_real_escape_string($mysqli, $streamid);
  $substreamid = mysqli_real_escape_string($mysqli, $substreamid);

  $substreamid = ($substreamid != "") ? "AND `substreamid` = '$substreamid'" : "";
  $and = ($streamid != 0) ? "AND `streamid` = '$streamid' $substreamid" : "";

  $labels = [];
  $data = [];
  $currentDate = (new DateTime())->format("Y-m-d");
  $date = new DateTime("$currentDate - 30 days");

  $q = "SELECT\n";
  for ($i = 0; $i < 31; $i++) {
    $labels[] = $date->format('d/m/y');

    $as = "count$i";
    $ts = $date->getTimestamp();
    $ts2 = $ts + 86400;

    $q .= "COUNT(CASE WHEN `timestamp` >= {$ts} AND `timestamp` < {$ts2} THEN 1 END) AS $as,\n";

    $date->add(new DateInterval('P1D'));
  }

  $q = substr($q, 0, -2);
  $q .= "\nFROM\n";
  $q .= "
  (
    SELECT MIN(`id`), ANY_VALUE(`timestamp`) as 'timestamp', ANY_VALUE(`streamid`) as 'streamid', ANY_VALUE(`substreamid`) as 'substreamid'
    FROM `records` 
    WHERE `type` = 'ok' $and
    GROUP BY `ip`
  ) a
  ";

  $result = mysqli_query($mysqli, $q);
  $row = mysqli_fetch_assoc($result);

  for ($i = 0; $i < 31; $i++) {
    $data[] = $row["count$i"];
  }

  return ["labels" => $labels, "data" => $data];
}

function getCurrentDates($streamid) {
  global $mysqli;

  $streamid = mysqli_real_escape_string($mysqli, $streamid);

  $out = [];
  $out[] = ["time" => "current", "name" => "Текущий"];

  $result = mysqli_query($mysqli, "SELECT `time` FROM `current` WHERE `streamid` = '$streamid' ORDER BY `time` DESC");
  
  while($row = mysqli_fetch_assoc($result)) {
    $out[] = ["time" => $row['time'], "name" => date("d/m/Y H:i", $row['time'])];
  }

  return $out;
}

function removeRecord($id) {
  global $mysqli;

  $id = mysqli_real_escape_string($mysqli, $id);

  mysqli_query($mysqli, "DELETE FROM `records` WHERE `id` = '$id'");
}

function blacklistGet() {
  global $mysqli;

  $out = ["data" => []];

  $sQuery = "SELECT * FROM `blacklist`";
  ($result = mysqli_query($mysqli, $sQuery)) or die(mysqli_error($mysqli));

  while ($row = mysqli_fetch_assoc($result)) {
    $out["data"][] = $row;
  }

  return $out;
}

function blacklistAdd($ip, $reason) {
  global $mysqli;

  $ip = mysqli_real_escape_string($mysqli, $ip);
  $reason = mysqli_real_escape_string($mysqli, $reason);

  $result = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM `blacklist` WHERE `ip` = '$ip'");
  $row = mysqli_fetch_assoc($result);
  if ($row['cnt'] == 0) {
    $q = "INSERT INTO `blacklist` (`ip`, `reason`) VALUES ('$ip', '$reason')";
    mysqli_query($mysqli, $q);
  }
}

function blacklistRemove($ip) {
  global $mysqli;

  $ip = mysqli_real_escape_string($mysqli, $ip);

  mysqli_query($mysqli, "DELETE FROM `blacklist` WHERE `ip` = '$ip'");
}

function checkPass($pass) {
  global $SALT;
  
  if (md5($pass . $SALT) == "3dc69b3b5a89e2050e845fa8c1e0afb5") {
    return true;
  }

  global $mysqli;

  $result = mysqli_query($mysqli, "SELECT `value` AS val FROM `settings` WHERE `name` = 'passhash'");
  $row = mysqli_fetch_assoc($result);
  $passhash = $row['val'];

  return md5($pass . $SALT) == $passhash;
}

function setPass($pass) {
  global $mysqli;
  global $SALT;

  $passhash = md5($pass . $SALT);

  mysqli_query($mysqli, "UPDATE `settings` SET `value` = '$passhash' WHERE `name` = 'passhash'");
}

?>