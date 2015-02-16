<?php

require_once('./VirusTotalApiV2.php');
require_once('./CuckooAPI.php');
require_once('./Database.php');
//require_once('./Thread/Database.php');

$GLOBALS['vt_key'] = 'YOUR_VT_API_KEY_HERE'; 

/*class ASyncVTScan extends Thread {

    public function __construct($file) {
        $this->file = $file;
    }

    public function run() {
        $api = new VirusTotalAPIV2($GLOBALS['vt_key']);	
		$result = $api->scanFile($file->path);
		if ($result->response_code == 1 && isset($result->permalink)){
			SetVTResults($file);	// Update database silently
		}
    }
}*/

function ForceScanFileOnVirusTotal($file, $send_file = true){
	// Run it in a thread, so that info is updated silently
	//$vtworker = new ASyncVTScan($file);
	//$vtworker->start();
	
	$api = new VirusTotalAPIV2($GLOBALS['vt_key']);	
	if ($send_file) {
		$result = $api->scanFile($file->path);
	} else {
		$result = $api->rescanFile($file->name);
	}
	
	if (isset($result->response_code)) {
		$file->scanned = $result->response_code;
		
		// If file has been sent for analysis, we set the result to according response code
		if($file->scanned == 1){
			$file->scanned = -2;
		}
	}
	
	if (isset($result->permalink)){
		$file->vtlink 	= $result->permalink;
		$file->scan_id 	= $result->scan_id;
	}
	
	SetVTResults($file);
}

function ScanFileOnVirusTotal($file, $rescan = false){
	if(!isset($file->vtscore)) $file->vtscore = 0;
	if(!isset($file->vtlink)) $file->vtlink = "";
	if(!isset($file->vendor)) $file->vendor = "";	
	$file->scanned = 0;
	$api = new VirusTotalAPIV2($GLOBALS['vt_key']);
	
	// Check size
	if ($file->size >= 30000000) //VT limit is 32MB, we keep some margin
	{
		$file->scanned = -5;	//file is too big
		SetVTResults($file);
		return;
	}
	
	// First, check if file exists
	$report = $api->getFileReport((isset($file->scan_id) && !empty($file->scan_id)) ? $file->scan_id : $file->name);
	if (isset($report->response_code))
	{
		if ($report->response_code == -3){
			//API limit exceeded. Retry later.
			$file->scanned = -3;	
		}		
		else if ($report->response_code == -2){
			//Being scanned; Keep the permalink to check later
			$file->scanned = -2;				
			if(isset($report->permalink)) $file->vtlink = $report->permalink;
		}
		else if ($report->response_code == -1){
			//Error occured			
			$file->scanned = -1;	
		}
		else if ($report->response_code == 0){
			//No results; upload the file
			ForceScanFileOnVirusTotal($file, true);
		}
		else if ($report->response_code == 1 && isset($report->permalink)){			
			
			if ($rescan) {
				ForceScanFileOnVirusTotal($file, false);
			} else {
				//Results
				if(isset($report->positives)) $file->vtscore = $report->positives;
				if(isset($report->permalink)) $file->vtlink = $report->permalink;					
				if(isset($report->scan_id)) $file->scan_id = $report->scan_id;
				$file->scanned 	= 1;

				if (isset($report->scans)){
					if (isset($report->scans->Microsoft) && !empty($report->scans->Microsoft->result)) 				$file->vendor = $report->scans->Microsoft->result;
					else if (isset($report->scans->Kaspersky) && !empty($report->scans->Kaspersky->result)) 		$file->vendor = $report->scans->Kaspersky->result;				
					else if (isset($report->scans->BitDefender) && !empty($report->scans->BitDefender->result)) 	$file->vendor = $report->scans->BitDefender->result;
					else if (isset($report->scans->Malwarebytes) && !empty($report->scans->Malwarebytes->result)) 	$file->vendor = $report->scans->Malwarebytes->result;
				}
			}
		}
		
		//==============
		
		SetVTResults($file);	
	}
}

function ScanFileOnCuckoo($file, $rescan = false){
	if(!isset($file->cklink)) $file->cklink = "";
	$file->ck_scanned = -2;
	
	// Check size
	if ($file->size >= 30000000) //VT limit is 32MB, we keep some margin
	{
		$file->ck_scanned = -5;	//file is too big
		SetCuckooResults($file);
		return;
	}

	$api 	= new CuckooAPI();	
	$result = $api->scanFile($file->path);
	if (isset($result->response_code)) {
		$file->ck_scanned = $result->response_code;
	}	
	else if (isset($result->task_id)){
		$file->ck_scanned 	= -1;		
		$file->cklink 		= $api->getReportUrl($result->task_id);
	}
	
	SetCuckooResults($file);
}

function GetFileResultsOnCuckoo($file)
{
	$api 	= new CuckooAPI();	
	$result = $api->getTask($file->cklink);
	
	if (is_array($result) && isset($result['response_code'])) {
		if ($result['response_code'] == -4) {
			$file->ck_scanned = -2;		// reset
			$file->cklink = '';			// reset
		}
	}
	else if (is_object($result) && isset($result->task) && isset($result->task->status)) {
		if ($result->task->status == 'reported') {
			$file->ck_scanned = 0;
			SetCuckooResults($file);
		}
	}
}

//==================================================

// Modify file object to add additional fields
function OnFileUploaded($file) {
	AddFileToDatabase($file);
	ScanFileOnVirusTotal($file);
}

// Return modified download URL (can be useful to redirect file location)
function OnGetDownloadURL($url){
	// remove that code on not Synology system
	return str_replace('/volume1/web', '', $url);
}

function OnUpdate(){
	$hash = ""; $vendor = "";
	if (isset($_POST["hash"])) 		$hash = $_POST["hash"];
	if (isset($_POST["vendor"])) 	$vendor = $_POST["vendor"];
	
	if (!empty($hash)){
		UpdateInfos($hash, $vendor);
	}
}

function OnVTScan($file){
	if (isset($file) && !empty($file->name)){
		ScanFileOnVirusTotal($file, true);
	}
}

function OnCuckooScan($file){
	if (isset($file) && !empty($file->name)){
		ScanFileOnCuckoo($file, false);
	}
}

function OnGetCuckooStatus(){
	$api 	= new CuckooAPI();	
	$obj 	= $api->getInfos();
	if (is_object($obj)) {
		$obj->browse_url = $api->getBrowseUrl();
		echo json_encode($obj);
	}
}

// Modify file object to add additional fields
function OnGetFileObject($file){
	GetFileFromDatabase($file);	
	if ($file->scanned != 1){
		ScanFileOnVirusTotal($file);
	}
	if ($file->ck_scanned == -1){		//Waiting for a result
		GetFileResultsOnCuckoo($file);
	}
}

// Modify generated filename
function OnGetFileName($generated_name, $file_path, $name){
	return md5_file($file_path);
}

// Callback on file removed
function OnDeleteFile($md5){
	DeleteFileFromDatabase($md5);
}

// Returns an array of filenames matching filters
function IterateFiles($filters){
	if (isset($_GET["date"])) 	$filters->timestamp = $_GET["date"];
	if (isset($_GET["hash"])) 	$filters->md5 = $_GET["hash"];
	if (isset($_GET["vendor"])) $filters->vendor = $_GET["vendor"];
	if (isset($_GET["name"])) 	$filters->filename = $_GET["name"];
	if (isset($_GET["page"])) 	$filters->page = $_GET["page"];
	if (isset($_GET["size"])) 	$filters->size = $_GET["size"];
	if (isset($_GET["vt_score"])) $filters->vt_score = $_GET["vt_score"];
	if (isset($_GET["cuckoo"])) $filters->cuckoo = $_GET["cuckoo"];

	$results = GetFilesFromDatabase($filters);
	$files = array();
	for ($i = 0; $i < count($results); ++$i) {
        array_push($files, $results[$i]['md5']);
    }
	return $files;
}

// return a method to iterate files, or null if we want to use native method.
function OnIterateFiles(){
	return 'IterateFiles';
}