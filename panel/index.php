<?php
session_start();
require_once("php/data.php");

$url_array = parse_url($_SERVER['REQUEST_URI']);
$path = $url_array["path"];
if ($path[strlen($path)-1] == "/") $path = substr($path, 0, -1); 
if ($path == "") $path = "/";

$menu_active = [0, 0, 0, 0, 0];

$clck_ts = time() * 1000;

if ($path != "/auth") {
  if (!isset($_SESSION["logined"]) || $_SESSION["logined"] != true) {
    header('Location: /auth', true, 302);
    exit();
  }
}

switch ($path){
  case "/auth": 
    if (isset($_SESSION["logined"]) && $_SESSION["logined"] == true) {
      header('Location: /main', true, 302);
      exit();
    }

    $error = "";
    if (isset($_POST["password"])) {
      if (checkPass($_POST["password"])) {
        $_SESSION["logined"] = true;
        header('Location: /main', true, 302);
        exit();
      }
      else {
        $error = "Неверные данные";
        require_once("template/auth.php");
      }
    }
    else require_once("template/auth.php");
  break;
  case "/exit": 
    session_destroy();
    header('Location: /auth', true, 302);
    exit();
  break;
  case "/main": 
    $title = "Статистика :: Главная";
    $pagename = "Главная";
    $pagefile = "main.php";
    $menu_active[0] = 1;

    require_once("template/layout.php");
  break;
  case "/current": 
    $title = "Статистика :: Текущее";
    $pagename = "Текущие таблицы";
    $pagefile = "current.php";
    $menu_active[1] = 1;

    $current_dates = getCurrentDates("mix");

    require_once("template/layout.php");
  break;
  case "/all": 
    $title = "Статистика :: Все данные";
    $pagename = "Все записи";
    $pagefile = "all.php";
    $menu_active[2] = 1;
    require_once("template/layout.php");
  break;
  case "/blacklist": 
    $title = "Статистика :: Черный список";
    $pagename = "Черный список";
    $pagefile = "blacklist.php";
    $menu_active[3] = 1;
    require_once("template/layout.php");
  break;
  case "/settings": 
    $title = "Статистика :: Настройки";
    $pagename = "Настройки";
    $pagefile = "settings.php";
    $menu_active[4] = 1;
    require_once("template/layout.php");
  break;
}

session_write_close();

require_once("api.php");
?>