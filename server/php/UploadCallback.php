<?php

require_once('./VirusTotalApiV2.php');
require_once('./Database.php');

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

function ForceScanFileOnVirusTotal($file){
	// Run it in a thread, so that info is updated silently
	//$vtworker = new ASyncVTScan($file);
	//$vtworker->start();
	
	$api = new VirusTotalAPIV2($GLOBALS['vt_key']);	
	$result = $api->scanFile($file->path);
	
	if (isset($result->response_code)) {
		$file->scanned = $result->response_code;
	}
	
	if (isset($result->permalink)){
		$file->permalink = $result->permalink;		
	}
	
	SetVTResults($file);
}

function ScanFileOnVirusTotal($file){
	$file->vtscore = 0;
	$file->vtlink = "";
	$file->vendor = "";
	$file->scanned = 0;
	$api = new VirusTotalAPIV2($GLOBALS['vt_key']);
	
	// First, check if file exists
	$report = $api->getFileReport($file->name);
	if (isset($report->response_code))
	{
		if ($report->response_code == -3){
			//API limit exceeded. Retry later.
			$file->scanned = -3;	
		}		
		else if ($report->response_code == -2 && isset($report->permalink)){
			//Being scanned; Keep the permalink to check later
			$file->scanned = -2;	
			$file->vtlink = $report->permalink;
		}
		else if ($report->response_code == -1){
			//Error occured
			$file->scanned = -1;	
		}
		else if ($report->response_code == 0){
			//No results; upload the file
			ForceScanFileOnVirusTotal($file);
		}
		else if ($report->response_code == 1 && isset($report->permalink)){			
			//Results
			$file->vtscore = $report->positives;
			$file->vtlink = $report->permalink;
			$file->scanned = 1;	

			if (isset($report->scans)){
				if (isset($report->scans->Microsoft) && !empty($report->scans->Microsoft->result)) 				$file->vendor = $report->scans->Microsoft->result;
				else if (isset($report->scans->Kaspersky) && !empty($report->scans->Kaspersky->result)) 		$file->vendor = $report->scans->Kaspersky->result;				
				else if (isset($report->scans->BitDefender) && !empty($report->scans->BitDefender->result)) 	$file->vendor = $report->scans->BitDefender->result;
				else if (isset($report->scans->Malwarebytes) && !empty($report->scans->Malwarebytes->result)) 	$file->vendor = $report->scans->Malwarebytes->result;
			}
		}
		
		//==============
		
		if (!empty($file->vtlink)) {
			SetVTResults($file);	
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
	// On Synology system, uncomment line below and adapt to remove prefix
	// return str_replace('/volume1/web', '', $url);
	return $url;
}

// Modify file object to add additional fields
function OnGetFileObject($file){
	GetFileFromDatabase($file);	
	if ($file->scanned != 1){
		ScanFileOnVirusTotal($file);
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