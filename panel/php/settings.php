<?php

function getSetting($name) {
  global $mysqli;

  $name = mysqli_real_escape_string($mysqli, $name);

  $result = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM `settings` WHERE `name` = '$name'");
  $row = mysqli_fetch_assoc($result);
  if ($row['cnt'] < 1) return false;
  
  $result = mysqli_query($mysqli, "SELECT `value` AS val FROM `settings` WHERE `name` = '$name'");
  $row = mysqli_fetch_assoc($result);
  return $row['val'];
}

function setSetting($name, $value) {
  global $mysqli;

  $name = mysqli_real_escape_string($mysqli, $name);
  $value = mysqli_real_escape_string($mysqli, $value);
  
  mysqli_query($mysqli, "UPDATE `settings` SET `value` = '$value' WHERE `name` = '$name'");
}



function addStream($name) {
  global $mysqli;

  $name = mysqli_real_escape_string($mysqli, $name);
  $name = strtolower($name);

  $result = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM `streams` WHERE `name` = '$name'");
  $row = mysqli_fetch_assoc($result);
  if ($row['cnt'] > 0) return false;

  mysqli_query($mysqli, "INSERT INTO `streams` (`name`) VALUES ('$name')");
  mysqli_query($mysqli, "INSERT INTO `settings` (`name`, `value`) VALUES ('current_$name', 0)");
  return true;
}

function addSubstream($streamid, $name) {
  global $mysqli;

  $streamid = mysqli_real_escape_string($mysqli, $streamid);
  $name = mysqli_real_escape_string($mysqli, $name);
  $name = strtolower($name);

  $streamid = mysqli_real_escape_string($mysqli, $streamid);
  $name = mysqli_real_escape_string($mysqli, $name);

  $result = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM `substreams` WHERE `streamid` = $streamid AND `name` = '$name'");
  $row = mysqli_fetch_assoc($result);
  if ($row['cnt'] > 0) return false;

  mysqli_query($mysqli, "INSERT INTO `substreams` (`streamid`, `name`) VALUES ($streamid, '$name')");
  return true;
}

function getStreams() {
  global $mysqli;

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

function removeStreams($stream_ids) {
  global $mysqli;

  $stream_ids = mysqli_real_escape_string($mysqli, $stream_ids);

  mysqli_query($mysqli, "DELETE FROM `substreams` WHERE `streamid` IN ($stream_ids)");
  mysqli_query($mysqli, "DELETE FROM `streams` WHERE `id` IN ($stream_ids)");
}

function removeSubstreams($substream_ids) {
  global $mysqli;

  $substream_ids = mysqli_real_escape_string($mysqli, $substream_ids);

  mysqli_query($mysqli, "DELETE FROM `substreams` WHERE `id` IN ($substream_ids)");
}

function addPayload($name) {
  global $mysqli;

  $name = mysqli_real_escape_string($mysqli, $name);
  $name = strtolower($name);

  $result = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM `payloads` WHERE `name` = '$name'");
  $row = mysqli_fetch_assoc($result);
  if ($row['cnt'] > 0) return false;

  mysqli_query($mysqli, "INSERT INTO `payloads` (`name`) VALUES ('$name')");
  return true;
}

function removePayloads($ids) {
  global $mysqli;

  $ids = mysqli_real_escape_string($mysqli, $ids);

  mysqli_query($mysqli, "DELETE FROM `payloads` WHERE `id` IN ($ids)");
}

function getPayloads() {
  global $mysqli;

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

?>