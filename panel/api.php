<?php

switch ($path) {
  case "/api/removeRecord":
    removeRecord($_GET["id"]);
  break;

  /*
   *  Главная
   */

  case "/api/getAllStats":
    echo json_encode(getAllStats());
  break;

  case "/api/getStreamStats":
    $map = getMap($_GET["streamid"], $_GET["substreamid"]);
    $countries = getCountries($map);
    $chart = getDayChartMonth($_GET["streamid"], (isset($_GET["substreamid"]) ? $_GET["substreamid"] : ""));
    echo json_encode(["map" => $map, "countries" => $countries, "chart" => $chart]);
  break;

  case "/api/setStreamColor":
    setStreamColor($_GET["stream"], $_GET["color"]);
    echo json_encode(["success" => true]);
  break;

  case "/api/clearSubstream":
    clearSubstream($_GET["substream"]);
    echo json_encode(["success" => true]);
  break;

  /*
   *  Текущее
   */
  
  case "/api/getCurrentDates":
    if (!isset($_GET["streamid"]) || $_GET["streamid"] == "") die("[]");
    echo json_encode(getCurrentDates($_GET["streamid"]));
  break;

  /*
   *  черный список
   */
  
  case "/api/getBlacklist":
    $out = blacklistGet();
    echo json_encode($out);
  break;
  case "/api/addBlacklist":
    blacklistAdd($_POST["ip"], $_POST["reason"]);
  break;
  case "/api/removeBlacklist":
    blacklistRemove($_GET["ip"]);
  break;

  /*
   *  Настройки
   */

  case "/api/changePass":
    if (isset($_POST["password"])) {
      if (checkPass($_POST["password"])) {
        if ($_POST["newpass"] == "" || $_POST["repeat"] == "") {
          echo json_encode(["success" => false, "error" => "Пароль не может быть пустым"]);
        }
        else if ($_POST["newpass"] != $_POST["repeat"]) {
          echo json_encode(["success" => false, "error" => "Пароли не совпадают"]);
        }
        else {
          $success = "Пароль успешно изменен";
          setPass($_POST["newpass"]);
          echo json_encode(["success" => true]);
        }
      }
      else {
        echo json_encode(["success" => false, "error" => "Неверный пароль"]);
      }
    } else echo json_encode(["success" => false]);
  break;

  case "/api/addStream":
    $res = preg_match("/^[A-Za-z_]*$/", $_POST["stream"], $matches);
    if ($res === 1) {
      if (addStream($_POST["stream"])) {
        echo json_encode(["success" => true]);
      } else {
        echo json_encode(["success" => false, "error" => "Имя потока занято"]);
      }
    } else {
      echo json_encode(["success" => false, "error" => "Имя потока не должно содержать спецсимволы"]);
    }
  break;
  case "/api/getStreams":
    echo json_encode(getStreams());
  break;
  case "/api/removeStreams":
    removeStreams($_POST["ids"]);
    echo json_encode(["success" => true]);
  break;
  case "/api/renameStream":
    $res = preg_match("/^[A-Za-z_]*$/", $_POST["newName"], $matches);
    if ($res === 1) {
      renameStream($_POST["oldName"], $_POST["newName"]);
      echo json_encode(["success" => true]);
    } else {
      echo json_encode(["success" => false, "error" => "Имя не должно содержать спецсимволы"]);
    }
  break;

  case "/api/addSubstream":
    $res = preg_match("/^[A-Za-z_]*$/", $_POST["name"], $matches);
    if ($res === 1) {
      if (addSubstream($_POST["streamid"], $_POST["name"])) {
        echo json_encode(["success" => true]);
      } else {
        echo json_encode(["success" => false, "error" => "Имя подпотока занято"]);
      }
    } else {
      echo json_encode(["success" => false, "error" => "Имя подпотока не должно содержать спецсимволы"]);
    }
  break;
  case "/api/removeSubstreams":
    removeSubstreams($_POST["ids"]);
    echo json_encode(["success" => true]);
  break;
  case "/api/renameSubstream":
    $res = preg_match("/^[A-Za-z_]*$/", $_POST["newName"], $matches);
    if ($res === 1) {
      renameSubstream($_POST["oldName"], $_POST["newName"]);
      echo json_encode(["success" => true]);
    } else {
      echo json_encode(["success" => false, "error" => "Имя не должно содержать спецсимволы"]);
    }
  break;

  case "/api/addParam":
    $params = json_decode(getSetting("params"), true);
    $params[] = $_POST["name"];
    setSetting("params", json_encode($params));
    echo json_encode(["success" => true]);
  break;
  case "/api/getParams":
    echo getSetting("params");
  break;
  case "/api/removeParams":
    $params = json_decode(getSetting("params"), true);
    
    foreach (explode(",", $_POST["names"]) as $name) {
      $index = array_search($name, $params);
      if($index !== false) unset($params[$index]);
    }

    setSetting("params", json_encode($params));

    echo json_encode(["success" => true]);
  break;
  case "/api/getBannedCountries":
    echo json_encode(getSetting("banned_countries"));
  break;
  case "/api/banCountries":
    setSetting("banned_countries", $_GET["countries"]);
    echo json_encode(["success" => true]);
  break;

  case "/api/setDark": 
    setSetting("dark_version", $_GET["dark"]);
  break;
  case "/api/setSidebar": 
    setSetting("menu_color", $_GET["color"]);
  break;
  case "/api/setNavbar": 
    setSetting("active_color", $_GET["color"]);
  break;

  case "/api/updateStreamsOrder": 
    updateStreamsOrder($_POST["streams"]);
    echo json_encode(["success" => true]);
  break;
  case "/api/updateSubstreamsOrder": 
    updateSubstreamsOrder($_POST["substreams"]);
    echo json_encode(["success" => true]);
  break;
}

?>