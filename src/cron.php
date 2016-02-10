<?php

require_once(__DIR__.'/storage.php');
require_once(__DIR__.'/functions.php');

function UpdateVirusTotalStatus() {
	// Get files to update
	$results = GetVTFilesToUpdate();
	$files = array();
	for ($i = 0; $i < count($results); ++$i) {
		$file 		= new stdClass();	
		$file->name = $results[$i]['md5'];		
		GetFileFromDatabase($file);
        array_push($files, $file);
    }
	
	// For each file, check status on VirusTotal and update model in database
	foreach($files as $file) {	
		ScanFileOnVirusTotal($file);
	}
}

function UpdateCuckooStatus() {
	// Get files to update
	$results = GetCuckooFilesToUpdate();
	$files = array();
	for ($i = 0; $i < count($results); ++$i) {
		$file 		= new stdClass();	
		$file->name = $results[$i]['md5'];		
		GetFileFromDatabase($file);
        array_push($files, $file);
    }
	
	// For each file, check status on VirusTotal and update model in database
	foreach($files as $file) {	
		GetFileResultsOnCuckoo($file);
	}
}

UpdateVirusTotalStatus();
UpdateCuckooStatus();