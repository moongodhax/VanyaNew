<?php
date_default_timezone_set("Europe/Moscow");

$ip = getIP();

if ($ip != '109.234.35.187' && $ip != '212.192.246.217'){
  header("HTTP/1.0 404 Not Found");
  die();
}

$stream = $_GET["stream"];
$action = $_GET["action"];

$first = "62.197.136.41";
$second = "212.192.246.217";

if ($action == "show"){
    $lines = 0;
    if ($stream == "mixone"){
        $xml = file_get_contents("http://$first/show.php?stream=mix&action=show");
        $lines += intval($xml);
        $xml = file_get_contents("http://$second/show.php?stream=mixone&action=show");
        $lines += intval($xml);
    }

    if ($stream == "mixtwo"){
        $xml = file_get_contents("http://$second/show.php?stream=mixtwo&action=show");
        $lines += intval($xml);
    }

    if ($stream == "eu"){
        $xml = file_get_contents("http://$first/show.php?stream=eu&action=show");
        $lines += intval($xml);
        $xml = file_get_contents("http://$second/show.php?stream=eu&action=show");
        $lines += intval($xml);
    }
    
    if ($stream == "us"){
        $xml = file_get_contents("http://$first/show.php?stream=us&action=show");
        $lines += intval($xml);
        $xml = file_get_contents("http://$second/show.php?stream=us&action=show");
        $lines += intval($xml);
    }
    echo $lines; 
}

if ($action == "delete"){
    if ($stream == "mixone"){
        file_get_contents("http://$first/show.php?stream=mix&action=delete");
        file_get_contents("http://$second/show.php?stream=mixone&action=delete");
    }

    if ($stream == "mixtwo"){
        file_get_contents("http://$second/show.php?stream=mixtwo&action=delete");
    }

    if ($stream == "eu"){
        file_get_contents("http://$first/show.php?stream=eu&action=delete");
        file_get_contents("http://$second/show.php?stream=eu&action=delete");

    }
    if ($stream == "us"){
        file_get_contents("http://$first/show.php?stream=us&action=delete");
        file_get_contents("http://$second/show.php?stream=us&action=delete");
    }  
    echo "done";
}

if ($action == "done"){
    if ($stream == "mixone"){
        file_get_contents("http://$first/show.php?stream=mix&action=delete");
        file_get_contents("http://$second/show.php?stream=mixone&action=delete");
    }

    if ($stream == "mixtwo"){
        file_get_contents("http://$second/show.php?stream=mixtwo&action=delete");
    }

    if ($stream == "eu"){
        file_get_contents("http://$first/show.php?stream=eu&action=delete");
        file_get_contents("http://$second/show.php?stream=eu&action=delete");

    }
    if ($stream == "us"){
        file_get_contents("http://$first/show.php?stream=us&action=delete");
        file_get_contents("http://$second/show.php?stream=us&action=delete");
    }  
    echo "done";
}

function getIP() {
  if ((!empty($_SERVER['GEOIP_ADDR'])) && (($_SERVER['GEOIP_ADDR']) <> '127.0.0.1'))
      $ip = $_SERVER['GEOIP_ADDR'];
  
  else if ((!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) && (($_SERVER['HTTP_X_FORWARDED_FOR']) <> '127.0.0.1') && (($_SERVER['HTTP_X_FORWARDED_FOR']) <> ($_SERVER['SERVER_ADDR'])))
      $ip = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR'])[0];
  
  else if ((!empty($_SERVER['HTTP_CLIENT_IP'])) && (($_SERVER['HTTP_CLIENT_IP']) <> '127.0.0.1') && (($_SERVER['HTTP_CLIENT_IP']) <> ($_SERVER['SERVER_ADDR'])))
      $ip = $_SERVER['HTTP_CLIENT_IP'];
  
  else if ((!empty($_SERVER['HTTP_X_REAL_IP'])) && (($_SERVER['HTTP_X_REAL_IP']) <> '127.0.0.1') && (($_SERVER['HTTP_X_REAL_IP']) <> ($_SERVER['SERVER_ADDR'])))
      $ip = $_SERVER['HTTP_X_REAL_IP'];
  
  else $ip = $_SERVER['REMOTE_ADDR'];
  
  if ($ip == 'unknown') $ip = $_SERVER['REMOTE_ADDR'];

  return $ip; 
}
?>