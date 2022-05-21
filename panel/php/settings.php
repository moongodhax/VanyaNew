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

  $result = mysqli_query($mysqli, "SELECT MAX(`position`) AS pos FROM `streams`");
  $row = mysqli_fetch_assoc($result);
  $position = $row['position'] + 1;

  mysqli_query($mysqli, "INSERT INTO `streams` (`name`, `current_ts`, `color`, `position`) VALUES ('$name', 0, 'D81B60', $position)");
  return true;
}

function addSubstream($streamid, $name) {
  global $mysqli;
  global $SALT;

  $streamid = mysqli_real_escape_string($mysqli, $streamid);
  $name = mysqli_real_escape_string($mysqli, $name);
  $name = strtolower($name);

  $result = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM `substreams` WHERE `streamid` = $streamid AND `name` = '$name'");
  $row = mysqli_fetch_assoc($result);
  if ($row['cnt'] > 0) return false;

  $hash = md5($name . $SALT);

  $result = mysqli_query($mysqli, "SELECT MAX(`position`) AS pos FROM `substreams` WHERE `streamid` = $streamid");
  $row = mysqli_fetch_assoc($result);
  $position = $row['position'] + 1;

  mysqli_query($mysqli, "INSERT INTO `substreams` (`streamid`, `name`, `hash`, `position`) VALUES ($streamid, '$name', '$hash', $position)");
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
      "current_ts" => $row['current_ts'],
      "color" => $row['color'],
      "position" => $row['position'],
      "substreams" => []
    ];
  }

  $result = mysqli_query($mysqli, "SELECT * FROM `substreams`");
  while($row = mysqli_fetch_assoc($result)) {
    $streams[$row['streamid']]["substreams"][] = [
      "id" => $row['id'],
      "name" => $row['name'],
      "hash" => $row['hash'],
      "position" => $row['position'],
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

function setStreamColor($stream, $color) {
  global $mysqli;
  $stream = mysqli_real_escape_string($mysqli, $stream);
  $color = mysqli_real_escape_string($mysqli, $color);
  mysqli_query($mysqli, "UPDATE `streams` SET `color` = '$color' WHERE `name` = '$stream'");
}

function renameStream($oldName, $newName) {
  global $mysqli;
  $newName = mysqli_real_escape_string($mysqli, $newName);
  $oldName = mysqli_real_escape_string($mysqli, $oldName);
  mysqli_query($mysqli, "UPDATE `streams` SET `name` = '$newName' WHERE `name` = '$oldName'");
}

function renameSubstream($oldName, $newName) {
  global $mysqli;
  $oldName = mysqli_real_escape_string($mysqli, $oldName);
  $newName = mysqli_real_escape_string($mysqli, $newName);
  mysqli_query($mysqli, "UPDATE `substreams` SET `name` = '$newName' WHERE `name` = '$oldName'");
}

function updateStreamsOrder($streams) {
  global $mysqli;

  $counter = 0;
  foreach ($streams as $s) {
    mysqli_query($mysqli, "UPDATE `streams` SET `position` = $counter WHERE `id` = " . mysqli_real_escape_string($mysqli, $s));
    $counter++;
  }
}

function updateSubstreamsOrder($substreams) {
  global $mysqli;

  $counter = 0;
  foreach ($substreams as $s) {
    mysqli_query($mysqli, "UPDATE `substreams` SET `position` = $counter WHERE `id` = " . mysqli_real_escape_string($mysqli, $s));
    $counter++;
  }
}

function clearSubstream($substreamid) {
  global $mysqli;
  $substreamid = mysqli_real_escape_string($mysqli, $substreamid);

  mysqli_query($mysqli, "DELETE FROM `records` WHERE `substreamid` = '$substreamid'");
}

?>