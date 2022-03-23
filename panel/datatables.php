<?php
session_start();

if (!isset($_SESSION["logined"]) || $_SESSION["logined"] != true) {
  header("HTTP/1.0 404 Not Found");
  die();
}

require_once("php/mysqli.php");

/* DB table to use */
$sTable = "records";


$draw = $_POST["draw"];
$pColumns = $_POST["columns"];

/*
 * Paging
 */
$sLimit = "";
if (isset($_POST["start"]) && $_POST["length"] != "-1") {
    $sLimit =
        "LIMIT " . mysqli_real_escape_string($mysqli, $_POST["start"]) .
        ", " . mysqli_real_escape_string($mysqli, $_POST["length"]);
}

/*
 * Ordering
 */
$sOrder = "";
if (isset($_POST["order"])) {
    $pOrder = $_POST["order"];

    $sOrder = "ORDER BY  ";

    for ($i = 0; $i < count($pOrder); $i++) {
        if ($pColumns[intval($pOrder[$i]["column"])]["orderable"] == "true") {
            $sOrder .=
                $pColumns[intval($pOrder[$i]["column"])]["data"] . " " .
                mysqli_real_escape_string($mysqli, $pOrder[$i]["dir"]) . ", ";
        }
    }

    $sOrder = substr_replace($sOrder, "", -2);

    if ($sOrder == "ORDER BY") {
        $sOrder = "";
    }
}

/*
 * Filtering
 */
$sWhere = "";
if ($_POST["search"]["value"] != "") {
    $pSearch = $_POST["search"];

    $sWhere = "WHERE (";
    for ($i = 0; $i < count($pColumns); $i++) {
      if ($pColumns[$i]["data"] != "") {
        $sWhere .=
            $pColumns[$i]["data"] . " LIKE '%" .
            mysqli_real_escape_string($mysqli, $pSearch["value"]) . "%' OR ";
      }
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ")";
}

/* Individual column filtering */
for ($i = 0; $i < count($pColumns); $i++) {
    if ($pColumns[$i]["searchable"] == "true" && $pColumns[$i]["search"]["value"] != "" && $pColumns[$i]["data"] != "") {
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        $sWhere .=
            $pColumns[$i]["data"] . " LIKE '%" . 
            mysqli_real_escape_string($mysqli, $pColumns[$i]["search"]["value"]) . "%' ";
    }
}

/* stream */
if (isset($_GET["stream"]) && $_GET["stream"] != "") {
  if ($sWhere == "") {
      $sWhere = "WHERE ";
  } else {
      $sWhere .= " AND ";
  }

  $sWhere .= " stream = '" . mysqli_real_escape_string($mysqli, $_GET["stream"]) . "'";

  /* date */
  if (isset($_GET["timestamp"]) && $_GET["timestamp"] != "") {
    $timestamp = mysqli_real_escape_string($mysqli, $_GET["timestamp"]);
    if ($timestamp == "current") {
      $result = mysqli_query($mysqli, "SELECT `value` AS val FROM `settings` WHERE `name` = 'current_" . 
        mysqli_real_escape_string($mysqli, $_GET["stream"]) . "'");
      $row = mysqli_fetch_assoc($result);
      $timestamp = $row['val'];

      $sWhere .= " AND ";
      $sWhere .= " `timestamp` >= $timestamp ";
    } else {
      $sQuery = "SELECT MAX(`time`) as maxtime FROM `current` WHERE `time` < $timestamp";
      ($result = mysqli_query($mysqli, $sQuery)) or die(mysqli_error($mysqli));
      $row = mysqli_fetch_assoc($result);
  
      $maxtime = ($row['maxtime'] == NULL) ? 0 : $row['maxtime'];
  
      $sWhere .= " AND ";
      $sWhere .= " `timestamp` >= $maxtime AND `timestamp` < $timestamp ";
    }
  }
}


/*
 * SQL queries
 * Get data to display
 */

$out = [];

$out["draw"] = $draw;


/* count rows */

$sQuery = "SELECT COUNT(*) AS cnt FROM $sTable";
($result = mysqli_query($mysqli, $sQuery)) or die(mysqli_error($mysqli));
$row = mysqli_fetch_assoc($result);
$out["recordsTotal"] = $row['cnt'];

$sQuery = "SELECT COUNT(*) AS cnt FROM $sTable $sWhere $sOrder $sLimit";
// file_put_contents("test.txt", $sQuery);
($result = mysqli_query($mysqli, $sQuery)) or die(mysqli_error($mysqli));
$row = mysqli_fetch_assoc($result);
$out["recordsFiltered"] = $row['cnt'];


/* get data */

$columnsArr = ["id"];
foreach($pColumns as $c) {
  if ($c["data"] != "") $columnsArr[] = $c["data"];
}

$sQuery = "SELECT " . implode(", ", $columnsArr) . " FROM $sTable $sWhere $sOrder $sLimit";
($result = mysqli_query($mysqli, $sQuery)) or die(mysqli_error($mysqli));

while ($row = mysqli_fetch_assoc($result)) {
  $out["data"][] = $row;
}

echo json_encode($out);
?>
