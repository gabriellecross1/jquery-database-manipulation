var scripts = {
	"xml" : {
		dataSets: "server/getXmlFilenames.php",
		dataFile: "server/getXmlFile.php",
		search: "server/searchXML.php",
		insert: "server/insertXml.php"
	},
	"database" : {
		dataSets: "server/getDatabaseTablenames.php",
		dataFile: "server/getDatabaseTable.php",
		search: "server/searchDatabase.php",
		insert: "server/insertDatabase.php"
	}
}

var dataSource = "";
var dataSet = "";

function populateDataSet(dataSet) {

	// clear the data set select
	$("#data-set-select").empty();
	// get a handle to the options for data set select and add the default option
	var options = $("#data-set-select").prop("options");
	var standard = new Option("Please select a data set", "", true, true);
	standard.setAttribute("hidden", true);
	standard.setAttribute("disabled", true);
	options[options.length] = standard;
	// add each option
	$.each(dataSet, function(index, element) {
		options[options.length] = new Option(element, element);
	});
}

function getDataSets() {

	// call the php script expecting a JSON file in response
	$.getJSON(scripts[dataSource].dataSets, function(data) {
		// check if we get an error
		if (data["code"] == "error") {
			// log the error to the console
			displayError(data["message"]);
		} else {
			// populate the data set
			populateDataSet(data);
			// fade in data set
			$("#data-set").show(500);
		}
	});
}

function getDataFile() {

	// call the php script with the name of the file we want to retrieve expecting a JSON file in response
	$.getJSON(scripts[dataSource].dataFile, {sourceName: dataSet}, function(data) {
		// check if we get an error
		if (data["code"] == "error") {
			// log the error to the console
			displayError(data["message"]);
		} else {
			// xml file contains a single root element so data will always be an object containing a single key value pair
			$.each(data, function(index, element) {
				// index is the root key
				// element is the root value
				// prepare the data
				element = prepareData(element);
				// create the search form
				createSearchForm(element);
				createSearchHandler();
				// create the table
				createTable(element);
				// create the insert form
				createInsertForm(element);
				createInsertHandler();
				// fade in data presentation
				$("#data-presentation").show(500);
			});
		}
	});
}

function searchData(dataSetData) {

	// call the php script with the name of the file and info we want to insert expecting a JSON file in response
	$.getJSON(scripts[dataSource].search, {sourceName: dataSet, sourceData: dataSetData}, function(data) {
		// check if we get an error
		if (data["code"] == "error") {
			// display the error message
			displayError(data["message"]);
		} else {
			// file contains a single root element so data will always be an object containing a single key value pair
			$.each(data, function(index, element) {
				// index is the root key
				// element is the root value
				// prepare the data
				element = prepareData(element);
				// create the table
				createTable(element);
				// fade in data presentation
				$("#data-presentation").show(500);
			});
		}
	});

}

function insertData(dataSetData) {

	// call the php script with the name of the file and info we want to insert expecting a message in response
	$.getJSON(scripts[dataSource].insert, {sourceName: dataSet, sourceData: dataSetData}, function(data) {
		// check if we get an error
		if (data["code"] == "error") {
			// display the error message
			displayError(data["message"]);
		} else {
			// display the new data in the table
			getDataFile();
			
		}
	});
}

function createSearchForm(data) {

	// clear the data presentation search form
	$("#data-presentation-search-form").remove();

	// create the form
	var form = $("<form id='data-presentation-search-form'/>");

	// create required elements
	var row = $("<div class='form-row align-items-center'/>");
	var group = $("<div class='form-group'/>");
	var button = $("<input class='btn btn-primary'/>");

	// create select and options
	var selectGroup = group.clone();
	selectGroup.addClass("col-5");
	var select = $("<select class='form-control' name='key' required/>");
	var options = select.prop("options");
	var standard = new Option("Please select a column...", "", true, true);
	standard.setAttribute("disabled", true);
	standard.setAttribute("hidden", true);
	options[options.length] = standard;
	$.each(Object.keys(data[0]), function(index, element) {
		options[options.length] = new Option(element, element);
	});
	selectGroup.append(select);
	row.append(selectGroup);

	// create input
	var inputGroup = group.clone();
	inputGroup.addClass("col-5");
	var input = $("<input type='text' class='form-control' name='value' placeholder='Please enter a search value...' required/>");
	inputGroup.append(input);
	row.append(inputGroup);

	// create submit button
	var submitGroup = group.clone();
	submitGroup.addClass("col-1");
	var submit = button.clone().attr({"type":"submit", "value":"Submit"});
	submitGroup.append(submit);
	row.append(submitGroup);

	// create reset button
	var resetGroup = group.clone();
	resetGroup.addClass("col-1");
	var reset = button.clone().attr({"type":"reset", "value":"Reset"});
	resetGroup.append(reset);
	row.append(resetGroup);

	// append row to form
	form.append(row);

	// append form to data presentation search
	$("#data-presentation-search").append(form);
}

function createTable(data) {

	// remove the data presentation display table
	$("#data-presentation-display-table").remove();

	// create the table
	var table = $("<table id='data-presentation-display-table' class='table'/>");

	// create required elements
	var tr = $("<tr/>");
	var th = $("<th/>");
	var td = $("<td/>");

	// create header
	var head = $("<thead class='thead-dark'/>");
	var header = tr.clone();
	$.each(Object.keys(data[0]), function(index, element) {
		header.append(th.clone().text(element));
	})
	head.append(header);
	table.append(head);

	// create body
	var body = $("<tbody/>");
	for (var i = 0; i < data.length; i++) {
		var row = tr.clone();
		$.each(Object.values(data[i]), function(index, element) {
			row.append(td.clone().text(element));
		})
		body.append(row);
	}
	table.append(body);

	// append table to the data display
	$("#data-presentation-display").append(table);
}

function createInsertForm(data) {

	// clear the presentation insert form
	$("#data-presentation-insert-form").remove();

	// create the form
	var form = $("<form id='data-presentation-insert-form'/>");

	// create required elements
	var group = $("<div class='form-group form-row'/>");
	var label = $("<label class='col-2 col-form-label'/>");
	var input = $("<input type='text' class='col-10 form-control' required/>");
	var button = $("<input type='submit' class='btn btn-primary ml-auto'/>");

	// create labels and inputs
	$.each(Object.keys(data[0]), function(index, element) {
		var inputGroup = group.clone();
		inputGroup.append(label.clone().attr({"for":element}).text(element));
		var text = input.clone().attr({"id":element, "name":element});
		if (dataSource == "database" && index == 0) {
			text.attr({"placeholder":data.length + 1, "disabled":true})
		} else {
			text.attr({"placeholder":"Enter " + element});
		}
		inputGroup.append(text);
		form.append(inputGroup);
	});

	// create submit button
	var submitGroup = group.clone();
	var submit = button.clone().attr({"value":"Submit"});
	submitGroup.append(submit);
	form.append(submitGroup);

	// append form to data presentation insert
	$("#data-presentation-insert").append(form);
}

function createInsertHandler() {

	// override default submit function in data presentation insert form
	$("#data-presentation-insert-form").submit(function(event) {
		// encode form elements as an array of names and values
		var fields = $("#data-presentation-insert-form").serializeArray();
		// create an object that contains key and value pairs in the required format ready to convert to json
		var data = {};
		$.each(fields, function(index, element) {
			data[element.name] = element.value;
		});
		// insert the data
		insertData(JSON.stringify(data));
		// prevent default behaviour
		event.preventDefault();
	});
}

function createSearchHandler() {

	// override default submit function in data presentation search form
	$("#data-presentation-search-form").submit(function(event) {
		// encode form elements as an array of names and values
		var fields = $("#data-presentation-search-form").serializeArray();
		// create an object that contains key and value pair in the required format ready to convert to json
		var data = {};
		data[fields[0].value] = fields[1].value;
		// search the data
		searchData(JSON.stringify(data));
		// prevent default behaviour
		event.preventDefault();
	});

	// override default reset function in data presentation search form
	$("#data-presentation-search-form").on("reset", function(event) {
		getDataFile();
	});

}

function createDataSourceHandler() {

	// checks for a change in the data source select
	$("#data-source-select").change(function() {
		// set the data source value
		dataSource = $(this).val();
		// fade out data presentation
		$("#data-presentation").hide(500);
		// fade out data set
		$("#data-set").hide(500, function() {
			// check if the data source is valid
			if (scripts.hasOwnProperty(dataSource)) {
				// retrieve the data sets
				getDataSets();
			} else {
				// report an incorrect choice
				displayError("The selected option is not valid");
			}
		});
	});
}

function createDataSetHandler() {

	// checks for a change in the data set select
	$("#data-set-select").change(function() {
		//hide data presentation
		//hideDataPresentation();
		dataSet = $(this).val();
		$("#data-presentation").hide(500, function(){
			// check if the data source is valid
			if (scripts.hasOwnProperty(dataSource)) {
				// retrieve the data sets
				getDataFile();
			} else {
				// report an incorrect choice
				displayError("The selected option is not valid");
			}	
		});
	});

}

function prepareData(data) {

	// check if the data is not an array
	if (Array.isArray(data) === false) {
		// convert to array
		data = $.makeArray(data);
	}
	// return the prepared data
	return data;
}

function displayError(message) {

	// clear the body description
	$("#error-modal-body-description").remove();

	// create the paragraph
	var paragraph = $("<p id='error-modal-body-description'/>").text(message);

	// append paragraph to error modal body
	$("#error-modal-body").append(paragraph);

	// display the error
	$("#error-modal").modal({
		backdrop: true,
		keyboard: true,
		focus: true,
		show: true
	});
}

function init() {
	
	createDataSourceHandler();
	createDataSetHandler();
}

// ensure the document is ready 
$(function() {  
	init(); 
}); 