<?php

date_default_timezone_set('Europe/Moscow');
$mysqli = mysqli_connect("localhost", "admin", "Pf7srQGPjt", "ipnew");

function writeRecord($mysqli, $streamid, $substreamid, $type, $reason, $ua, $sub,  $ip,$distributor, $country, $timestamp) {
  $q = "INSERT INTO `records` (`streamid`, `substreamid`, `type`, `reason`, `ua`, `sub`, `ip`, `distributor`, `country`, `timestamp`) VALUES 
  ($streamid, $substreamid, '$type', '$reason', '$ua', '$sub', '$ip', '$distributor', '$country', $timestamp)";
  mysqli_query($mysqli, $q);
}

for ($i = 0; $i < 100; $i++) {
  echo $i . ' ';

  $ip = random_int(11, 99) . "." . random_int(1, 254) . "." . random_int(1, 254) . "." . random_int(1, 254);

  $resp = json_decode(file_get_contents('http://www.geoplugin.net/json.gp?ip='.$ip));
  sleep(1);

  $country_code = $resp->geoplugin_countryCode ?? "?";

  writeRecord($mysqli, 20, 55, "ok", "", "testua", "testsub", $ip, "single", $country_code, time());
}