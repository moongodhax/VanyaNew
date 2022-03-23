<?php

if (!empty($_FILES) && $_FILES['uploadfile']['size'] != 0 && $_FILES['uploadfile']['tmp_name'] != "none") {
  $filename = __DIR__ . '/files/setup.exe';
  if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $filename)) {
    die("1");
  } else {
    die("0");
  }
}

?>