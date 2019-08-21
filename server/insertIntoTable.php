<?php
if (!isset($error)) {
	$error = new stdClass();
}

include "dbinfo.info.php";

try {
	///$dbh = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
	$pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password, [PDO:: ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]);

	// check database connection
} catch (PDOException $e) {
	$error->code = "error";
	$error->message = "There was a problem connecting to the database";
	echo json_encode($error);
	$pdo = null;
	return;
}

$table = $_GET["tableName"];
$appendData = $_GET['appendData'];
$json_array = json_decode($appendData, true);

$query = "INSERT INTO ".$table." (";
$placeholder = "";
$values = array();

foreach($json_array as $key => $value) {
	 $query .= $key.", ";
	 $placeholder .= "?, ";
	 $values[] = $value;
}

$query = rtrim($query, ', ');
$placeholder = rtrim($placeholder, ', ');
$query .= ") VALUES (".$placeholder.")";

$stmt = $pdo->prepare($query);

$counter = 1;
foreach ($json_array as $key => &$val) {
	 $stmt->bindParam($counter, $val);
	 $counter++;
}

$stmt->execute();

echo $pdo->lastInsertId();
$stmt = null;
$pdo = null;
?>
