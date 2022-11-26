<?php
$ip = getIP();

// if ($ip != '109.234.35.187' && $ip != '212.192.246.217' && $ip != '185.197.75.169'){ 
//   header("HTTP/1.0 404 Not Found");
//   die();
// }

$stream = $_GET["stream"];
$action = $_GET["action"];

$first = "62.197.136.41";
$second = "45.15.156.54";

$show_db = json_decode(file_get_contents("show_db.json"), true);

if ($action == "show"){
    $total = 0;
    if ($stream == "mixone"){
        $count = @file_get_contents("http://$first/show.php?stream=mix&action=show");
        if ($count === false) $count = $show_db["1mix"];
        else  $show_db["1mix"] = $count;
        $total += intval($count);

        $count = file_get_contents("http://$second/lzkjfvzshbd/show.php?stream=mixone&action=show");
        if ($count === false) $count = $show_db["2mixone"];
        else  $show_db["2mixone"] = $count;
        $total += intval($count);
    }
    if ($stream == "mixtwo"){
        $count = file_get_contents("http://$second/lzkjfvzshbd/show.php?stream=mixtwo&action=show");
        if ($count === false) $count = $show_db["2mixtwo"];
        else  $show_db["2mixtwo"] = $count;
        $total += intval($count);
    }
    if ($stream == "eu"){
        $count = file_get_contents("http://$first/show.php?stream=eu&action=show");
        if ($count === false) $count = $show_db["1eu"];
        else  $show_db["1eu"] = $count;
        $total += intval($count);

        $count = file_get_contents("http://$second/lzkjfvzshbd/show.php?stream=eu&action=show");
        if ($count === false) $count = $show_db["2eu"];
        else  $show_db["2eu"] = $count;
        $total += intval($count);
    }
    if ($stream == "us"){
        $count = file_get_contents("http://$first/show.php?stream=us&action=show");
        if ($count === false) $count = $show_db["1us"];
        else  $show_db["1us"] = $count;
        $total += intval($count);

        $count = file_get_contents("http://$second/lzkjfvzshbd/show.php?stream=us&action=show");
        if ($count === false) $count = $show_db["2us"];
        else  $show_db["2us"] = $count;
        $total += intval($count);
    }

    file_put_contents("show_db.json", json_encode($show_db));

    echo $total; 
}

if ($action == "delete"){
    if ($stream == "mixone"){
		$show_db["1mix"] = 0;
		$show_db["2mixone"] = 0;
        file_get_contents("http://$first/show.php?stream=mix&action=delete");
        file_get_contents("http://$second/lzkjfvzshbd/show.php?stream=mixone&action=delete");
    }
    if ($stream == "mixtwo"){
		$show_db["2mixtwo"] = 0;
        file_get_contents("http://$second/lzkjfvzshbd/show.php?stream=mixtwo&action=delete");
    }
    if ($stream == "eu"){
		$show_db["1eu"] = 0;
		$show_db["2eu"] = 0;
        file_get_contents("http://$first/show.php?stream=eu&action=delete");
        file_get_contents("http://$second/lzkjfvzshbd/show.php?stream=eu&action=delete");
    }
    if ($stream == "us"){
		$show_db["1us"] = 0;
		$show_db["2us"] = 0;
        file_get_contents("http://$first/show.php?stream=us&action=delete");
        file_get_contents("http://$second/lzkjfvzshbd/show.php?stream=us&action=delete");
    }
	
    file_put_contents("show_db.json", json_encode($show_db));
	
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