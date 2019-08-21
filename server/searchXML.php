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
$sourceData = $_GET["sourceData"];
$filename = $_GET["sourceName"];

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

// set the search key and value
$searchKey = key($sourceDataArray);
$searchValue = $sourceDataArray[$searchKey];

// create a new xml document
$dom = new DOMDocument("1.0");
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($xmlFile->asXML());

// find all elements that match the key
$matchingElements = $dom->getElementsByTagName($searchKey);

// create an array of elements to delete
// we need to do this because iterating over the elements and deleting them will cause issues with the iterator
$elementsToDelete = array();
foreach ($matchingElements as $value) {
	// check if the node value doesn't match the search value
	if (stripos($value->nodeValue, $searchValue) === false) {
		// add the value to the elements to delete array
		$elementsToDelete[] = $value->parentNode;
	}
}

// remove each element in the elements to delete array
foreach ($elementsToDelete as $elementToDelete) {
	$elementToDelete->parentNode->removeChild($elementToDelete);
}

// create new xml element
$xmlFile = new SimpleXMLElement($dom->saveXML());

// check if there are any elements in the new xml file
if ($xmlFile->count() > 0) {
	// return the encoded xml file
	echo json_encode($xmlFile);
} else {
	$error->code = "error";
	$error->message = "No elements match the search criteria";
	echo json_encode($error);
	return;	
}

?>