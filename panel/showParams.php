<?php
require_once("php/data.php");

// проверка юзер-агента
if ($_SERVER['HTTP_USER_AGENT'] != "1") {
  header("HTTP/1.0 404 Not Found");
  die();
}

$substreams = [];
$result = mysqli_query($mysqli, "SELECT * FROM `substreams`");
while($row = mysqli_fetch_assoc($result)) {
  $substreams[] = $row['name'];
}

$params = json_decode(getSetting("params"), true);

echo json_encode(["substreams" => $substreams, "params" => $params]);

?>