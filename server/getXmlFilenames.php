<?php

$directory = 'xml';

if (!chdir($directory)){
  $error->code = "error";
  $error->message = "The directory cannot be found";
  echo json_encode($error);
  return;
}

$files = glob("*.xml");

if (empty($files)){
  $error->code ="error";
  $error->message = "no files";
  echo json_encode($error);
  return;
}

$output = array();
foreach($files as $filename){
  array_push($output, pathinfo($filename, PATHINFO_FILENAME));
}

echo json_encode($output);

?>
