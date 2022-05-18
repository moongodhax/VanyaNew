<?php
if (!isset($_GET["hash"])) {
  header("HTTP/1.0 404 Not Found");
  die();
}

require_once(__DIR__ . "/../php/mysqli.php");

$streamid = null;
$substreamid = null;

$hash = mysqli_real_escape_string($mysqli, $_GET['hash']);

$result = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM `substreams` WHERE `hash` = '$hash'");
$row = mysqli_fetch_assoc($result);

if ($row['cnt'] > 0) {
  $result = mysqli_query($mysqli, "SELECT * FROM `substreams` WHERE `hash` = '$hash'");
  $row = mysqli_fetch_assoc($result);
  $substreamid = $row["id"];
  $streamid = $row["streamid"];
} else {
  header("HTTP/1.0 404 Not Found");
  die();
}


/* DB table to use */
$sTable = "`records`";

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
            $sTable . ".`" . $pColumns[$i]["data"] . "` LIKE '%" .
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
            $sTable . ".`" . $pColumns[$i]["data"] . "` LIKE '%" . 
            mysqli_real_escape_string($mysqli, $pColumns[$i]["search"]["value"]) . "%' ";
    }
}

if ($sWhere == "") $sWhere = "WHERE ";
else $sWhere .= " AND ";
$sWhere .= $sTable . ".`streamid` = '" . mysqli_real_escape_string($mysqli, $streamid) . "'";
$sWhere .= "AND $sTable.`substreamid` = '" . mysqli_real_escape_string($mysqli, $substreamid) . "'";

/* date */
if (isset($_POST["date_start"]) && $_POST["date_start"] != "" && isset($_POST["date_end"]) && $_POST["date_end"] != "") {
  $date = DateTime::createFromFormat('Y-m-d', $_POST["date_start"]);
  $date->modify('today');
  $start = $date->getTimestamp();

  $date = DateTime::createFromFormat('Y-m-d', $_POST["date_end"]);
  $date->modify('tomorrow');
  $end = $date->getTimestamp();

  $sWhere .= " AND `timestamp` >= $start AND `timestamp` < $end ";
}


/*
 * SQL queries
 * Get data to display
 */

$out = [];

$out["draw"] = $draw;


/* count rows */

// $sQuery = "SELECT COUNT(*) AS cnt FROM $sTable";
// ($result = mysqli_query($mysqli, $sQuery)) or die(mysqli_error($mysqli));
// $row = mysqli_fetch_assoc($result);
// $out["recordsTotal"] = $row['cnt'];
$out["recordsTotal"] = 0;

$sQuery = "SELECT COUNT(*) AS cnt FROM $sTable $sWhere";
// echo $sQuery;
// file_put_contents("test.txt", $sQuery);
($result = mysqli_query($mysqli, $sQuery)) or die(mysqli_error($mysqli));
$row = mysqli_fetch_assoc($result);
$out["recordsFiltered"] = $row['cnt'];

/* join */
$sJoin = "
LEFT JOIN `streams` ON `records`.`streamid` = `streams`.`id`
LEFT JOIN `substreams` ON `records`.`substreamid` = `substreams`.`id`
";

/* get data */

$columnsArr = ["`records`.`id`", "`streams`.`name` as 'stream'", "`substreams`.`name` as 'substream'"];
foreach($pColumns as $c) {
  if ($c["data"] != "") $columnsArr[] = $c["data"];
}

$sQuery = "SELECT " . implode(", ", $columnsArr) . " FROM $sTable $sJoin $sWhere $sOrder $sLimit";
($result = mysqli_query($mysqli, $sQuery)) or die(mysqli_error($mysqli));

while ($row = mysqli_fetch_assoc($result)) {
  $out["data"][] = $row;
}

echo json_encode($out);
?>
