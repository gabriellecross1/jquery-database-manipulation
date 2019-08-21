<?php

// check if error is set and if it isn't instantiate it as an empty generic object
if (!isset($error)) {
	$error = new stdClass();
}

// include file that holds details of the database and connection credentials
include "dbinfo.info.php";

// attempt to connect to the database
try {
	$pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]);
} catch (PDOexception $e) {
	$error->code = "error";
	$error->message = $e->getMessage();
	echo json_encode($error);
	$pdo = null;
	return;
}

// check if passed data source name is set and if it isn't return an error
if (!isset($_GET["sourceName"])) {
	$error->code = "error";
	$error->message = "No file name specified";
	echo json_encode($error);
	return;
}
// check if passed source data is set and if it isn't return an error
if (!isset($_GET["sourceData"])) {
	$error->code = "error";
	$error->message = "No data specified";
	echo json_encode($error);
	return;
}

// set the table name
$tablename = $_GET["sourceName"];
$sourceData = $_GET["sourceData"];

// decode the source data
$sourceDataArray = json_decode($sourceData, true);

// set the search key and value
$searchKey = key($sourceDataArray);
$searchValue = $sourceDataArray[$searchKey];

// check if a table exists
try {
	$exists = $pdo->prepare("SELECT 1 FROM $tablename LIMIT 1");
} catch (Exception $e) {
	$error->code = "error";
	$error->message = "Table doesn't exist";
	echo json_encode($error);
	$pdo = null;
	return;
}

// create a prepared statement to avoid SQL injection and then execute it
$stmt = $pdo->prepare("SELECT * FROM {$tablename} WHERE {$searchKey} LIKE :value");
$stmt->bindValue(':value', "%{$searchValue}%", PDO::PARAM_STR);
$result = $stmt->execute();

// check if there are any rows and if not return an error
if ($stmt->rowCount() > 0) {
	// store data in an array
	$table = array();
	$table[] = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($table);
} else {
	$error->code = "error";
	$error->message = "No elements match the search criteria";
	echo json_encode($error);
}

// close prepared statement and database connection
$exists = null;
$stmt = null;
$pdo = null;

?>