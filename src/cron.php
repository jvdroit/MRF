<?php

require_once(__DIR__.'/storage.php');
require_once(__DIR__.'/functions.php');
require_once(__DIR__.'/uploader.php');
require_once(__DIR__."/lib/usercake/models/config.php");

function getUploader() {
	$options = array (
			'upload_dir' => $GLOBALS["config"]["urls"]["storagePath"],
			'upload_url' => $GLOBALS["config"]["urls"]["storageUrl"],
			'script_url' => $GLOBALS["config"]["urls"]["baseUrl"]."api.php",
			'delete_type' => 'DELETE',
			'download_via_php' => 1
	);
	return new UploadHandler($options, false);
}

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
		echo 'VirusTotal: ' . $file->name . ' (' . $file->scanned . ')' . '<br/>';
		ScanFileOnVirusTotal($file);
	}
}

function UpdateSha256Status() {
	$uploader = getUploader();
	
	// Get files to update
	$results = GetSha256FilesToUpdate();
	$files = array();
	for ($i = 0; $i < count($results); ++$i) {
		$file 		= new stdClass();
		$file->name = $results[$i]['md5'];
		$file 		= $uploader->get_file_object($file);
		if (!is_null($file))
			array_push($files, $file);
	}

	// For each file, compute sha256 and push to database
	foreach($files as &$file) {
		echo 'SHA256: ' . $file->name . '<br/>';
		$file->sha256 = hash_file('sha256', $file->path, False);	// Compute SHA256
		UpdateSha256($file->name, $file->sha256);
	}
}

function UpdateSsdeepStatus() {
	$uploader = getUploader();

	// Get files to update
	$results = GetSsdeepFilesToUpdate();
	$files = array();
	for ($i = 0; $i < count($results); ++$i) {
		$file 		= new stdClass();
		$file->name = $results[$i]['md5'];
		$file 		= $uploader->get_file_object($file);
		if (!is_null($file))
			array_push($files, $file);
	}

	// For each file, compute sha256 and push to database
	foreach($files as &$file) {
		echo 'SSDEEP: ' . $file->name . '<br/>';
		$file->ssdeep = GetSsdeep($file);	// Compute ssdeep
		UpdateSsdeep($file->name, $file->ssdeep);
	}
}

function UpdateMimeStatus() {
	$uploader = getUploader();

	// Get files to update
	$results = GetMimeFilesToUpdate();
	$files = array();
	for ($i = 0; $i < count($results); ++$i) {
		$file 		= new stdClass();
		$file->name = $results[$i]['md5'];
		$file 		= $uploader->get_file_object($file);
		if (!is_null($file))
			array_push($files, $file);
	}

	// For each file, compute sha256 and push to database
	foreach($files as &$file) {
		echo 'Mime: ' . $file->name . '<br/>';
		$file->mime = mime_content_type($file->path);				// Get content type
		UpdateMime($file->name, $file->mime);
	}
}

function UpdatePeDataStatus() {
	$uploader = getUploader();

	// Get files to update
	$results = GetPeDataFilesToUpdate();
	$files = array();
	for ($i = 0; $i < count($results); ++$i) {
		$file 		= new stdClass();
		$file->name = $results[$i]['md5'];
		$file 		= $uploader->get_file_object($file);
		if (!is_null($file))
			array_push($files, $file);
	}

	// For each file, compute sha256 and push to database
	foreach($files as &$file) {
		echo 'PeData: ' . $file->name . '<br/>';
		$file->pedata = GetPeData($file); // Compute pe data
		$file->icon   = ExtractIcon($file->pedata);
		UpdatePEData($file->name, $file->pedata);
		UpdateIcon($file->name, $file->icon);
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
		echo 'Cuckoo Update: ' . $file->name . ' (' . $file->ck_scanned . ')' . '<br/>';
		if ( $file->ck_scanned == CuckooAPI::ERROR_FILE_BEING_ANALYZED ) {
			GetFileResultsOnCuckoo($file);
		}
	}
	
	// Get files to search
	$results = GetCuckooFilesToSearch();
	$files = array();
	for ($i = 0; $i < count($results); ++$i) {
		$file 		= new stdClass();
		$file->name = $results[$i]['md5'];
		$file->ck_scanned = $results[$i]['is_cuckoo_scanned'];
		GetFileFromDatabase($file);
		array_push($files, $file);
	} 
	
	// For each file, search old analysis
	foreach($files as $file) {
		echo 'Cuckoo Search: ' . $file->name . ' (' . $file->ck_scanned . ')' . '<br/>';
		if ( $file->ck_scanned == CuckooAPI::ERROR_FILE_UNKNOWN ) {
			SearchFileOnCuckoo($file);
		}
	}
}

// Extract API key
if (!isset($key) && isset($_REQUEST['token'])) 	$key = $_REQUEST['token'];
if (!isset($key) && isset($_POST['token'])) 	$key = $_POST['token'];	
			
// Verify API key/ Save user id in REQUEST array
if (!isset($key)) return;
$is_api_valid 	= loggedInUser::checkapikey($key); 
$user 			= loggedInUser::getuserbyapikey($key);		
if ($user != null) $_REQUEST["user"] = $user;

UpdateVirusTotalStatus();
if ($GLOBALS["config"]["cuckoo"]["enabled"]) UpdateCuckooStatus();
UpdateSha256Status();
if ($GLOBALS["config"]["ssdeep"]["enabled"]) UpdateSsdeepStatus();
if ($GLOBALS["config"]["mime"]["enabled"]) UpdateMimeStatus();
if ($GLOBALS["config"]["pedata"]["enabled"]) UpdatePeDataStatus();