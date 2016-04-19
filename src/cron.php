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
		echo 'VirusTotal: ' . $file->name . '<br/>';
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
		$file->ck_scanned = $results[$i]['is_cuckoo_scanned'];	
		GetFileFromDatabase($file);
        array_push($files, $file);
    }
	
	// For each file, check status on Cuckoo and update model in database
	foreach($files as $file) {	
		echo 'Cuckoo: ' . $file->name . ' (' . $file->ck_scanned . ')' . '<br/>';
		if ( $file->ck_scanned == CuckooAPI::ERROR_FILE_BEING_ANALYZED ) {
			GetFileResultsOnCuckoo($file);
		}		
		// Search old analysis
		else if ( $file->ck_scanned == CuckooAPI::ERROR_FILE_UNKNOWN ) {
			SearchFileOnCuckoo($file);
		}
	}
}

UpdateVirusTotalStatus();
UpdateCuckooStatus();