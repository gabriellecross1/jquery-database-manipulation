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
	$error->message = "No table name specified";
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

// set the source data and the table name
$tablename = $_GET["sourceName"];
$sourceData = $_GET["sourceData"];

// decode the source data
$sourceDataArray = json_decode($sourceData, true);

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

// start building our query
$query = "INSERT INTO ".$tablename." (";
$placeholder = "";
$values = array();
foreach ($sourceDataArray as $key => $value) {
	$query .= $key.", ";
	$placeholder .= "?, ";
	$values[] = $value;
}
$query = rtrim($query, ", ");
$placeholder = rtrim($placeholder, ", ");
$query .= ") VALUES (".$placeholder.")";

// create a prepared statement to avoid SQL injection and then execute it
$stmt = $pdo->prepare($query);
$counter = 1;
foreach ($sourceDataArray as $key => &$value) {
	$stmt->bindParam($counter, $value);
	$counter++;
}
$stmt->execute();

// return a success message
echo json_encode("The data was inserted successfully");

// close prepared statement and database connection
$exists = null;
$stmt = null;
$pdo = null;

?>