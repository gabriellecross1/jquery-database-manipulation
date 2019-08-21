<?php

if(!isset($error)){
  $error = new stdClass();
}

include "dbinfo.info.php";

try {
  $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password, [PDO:: ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]);
} catch (PDOexception $e) {
  $error->code = "error";
  $error->message = $e->getMessage();
  echo json_encode($error);
  $pdo = null;
  return;
}

$tables = array();
$result = $pdo->query("SHOW TABLES");
while ($row = $result->fetch(PDO::FETCH_NUM)){
  $tables[] = $row[0];
}

echo json_encode($tables);
$pdo = null;
?>
