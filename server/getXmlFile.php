<?php
libxml_use_internal_errors(true);
libxml_clear_errors();

if (!isset($error)){
  $error = new stdClass();
}

if(!isset($_GET["sourceName"])){
  $error->code ="error";
  $error->message = "no file name";
  echo json_encode($error);
  return;
}

$filename = $_GET["sourceName"];

if (!file_exists("xml/".$filename.".xml")){
  $error->code ="error";
  $error->message = "no such file";
  echo json_encode($error);
  return;
}


$xmlFile = simplexml_load_file("xml/".$filename.".xml");

$errors = libxml_get_errors();


if (empty($xmlFile)){
  $error->code ="error";
  $error->message = "no contents";
  echo json_encode($error);
  return;
}

if ($xmlFile->count() == 0){
  $error->code ="error";
  $error->message = "no elements";
  echo json_encode($error);
  return;
}

if ($errors){
  $error->code ="error";
  $error->message = "no idea";
  echo json_encode($error);
  return;
}

echo json_encode($xmlFile);
?>
