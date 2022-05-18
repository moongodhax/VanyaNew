<?php
// $client  = @$_SERVER['HTTP_CLIENT_IP'];
// $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
// $remote  = @$_SERVER['REMOTE_ADDR'];
 
// if(filter_var($client, FILTER_VALIDATE_IP)) $ip = $client;
// elseif(filter_var($forward, FILTER_VALIDATE_IP)) $ip = $forward;
// else $ip = $remote;

// if ($ip == '109.234.35.187') $ip = 'SVOI';
// else {
  // header("HTTP/1.0 404 Not Found");
  // die();
// }

$status = $_GET['status']; 

$xml = file_get_contents('http://adsymbol.com/gcleaner/control.php?token=tDX3dPSVObchmKR6d4qZzvKC3YPQ0rCF&status='.$status.'&id=WW');
$xml = file_get_contents('http://adsymbol.com/gcleaner/control.php?token=tDX3dPSVObchmKR6d4qZzvKC3YPQ0rCF&status='.$status.'&id=US');
$xml = file_get_contents('http://adsymbol.com/gcleaner/control.php?token=tDX3dPSVObchmKR6d4qZzvKC3YPQ0rCF&status='.$status.'&id=EU');
echo "1";
?>