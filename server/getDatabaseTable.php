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
	$pdo = null;
	return;
}

// set the table name
$tablename = $_GET["sourceName"];

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
$stmt = $pdo->prepare("SELECT * FROM {$tablename}");
$result = $stmt->execute();

// check if there are any rows and if not return an error
if ($stmt->rowCount() > 0) {
	// store data in an array
	$table = array();
	$table[] = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($table);
} else {
	$error->code = "error";
	$error->message = "The table: ".$tablename." contains no rows";
	echo json_encode($error);
}

// close prepared statement and database connection
$exists = null;
$stmt = null;
$pdo = null;

?>