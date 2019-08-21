<?php

// enable user libxml error handling and clear the libxml error buffer
libxml_use_internal_errors(true);
libxml_clear_errors();

// check if error is set and if it isn't instantiate it as an empty generic object
if (!isset($error)) {
	$error = new stdClass();
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

// set the source data and the file name
$filename = $_GET["sourceName"];
$sourceData = $_GET["sourceData"];

// check if the file exists and if it doesn't return an error
if (!file_exists("xml/".$filename.".xml")) {
	$error->code = "error";
	$error->message = "File does not exist";
	echo json_encode($error);
	return;
}

// load the xml file
$xmlFile = simplexml_load_file("xml/".$filename.".xml");

// get any libxml errors and perform some checks to determine the error
$errors = libxml_get_errors();
if (empty($xmlFile)) {
	$error->code = "error";
	$error->message = "No contents in file";
	echo json_encode($error);
	return;	
}
if ($xmlFile->count() == 0) {
	$error->code = "error";
	$error->message = "No elements in file";
	echo json_encode($error);
	return;	
}
if ($errors) {
	$error->code = "error";
	$error->message = "No idea";
	echo json_encode($error);
	return;	
}

// decode the source data
$sourceDataArray = json_decode($sourceData, true);

// create a new element within the xml file
$element = $xmlFile->addChild($xmlFile->children()->getName());

// add children to new element using key and value pairs from source data array
foreach ($sourceDataArray as $key => $value) {
	$element->addChild($key, $value);
}

// create a new xml document
$dom = new DOMDocument("1.0");
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($xmlFile->asXML());

// create new xml element
$xmlFile = new SimpleXMLElement($dom->saveXML());
$xmlFile->saveXML("xml/".$filename.".xml");

// return a success message
echo json_encode("The data was inserted successfully");


?>