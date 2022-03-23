<?php
$links = json_decode(file_get_contents(__DIR__ . "/files/links.json"), true);

$codes = ["MIX", "EU", "US", "D1", "D2", "D3", "D4", "HB"];

$out = date("d/m/Y H:i\n");

foreach ($links as $stream => $link) {
  if (in_array(strtoupper($stream), $codes) && $link != "0") {
    file_put_contents(__DIR__ . "/files/" . strtoupper($stream), file_get_contents($link));
  }
}
?>