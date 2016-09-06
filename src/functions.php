<?php

require_once(__DIR__.'/virustotal.php');
require_once(__DIR__.'/cuckoo.php');
require_once(__DIR__.'/storage.php');
require_once(__DIR__.'/lib/usercake/models/config.php');
require_once(__DIR__."/config.php");

$cuckoo_available = false;
$users_table = array();

function ForceScanFileOnVirusTotal($file, $send_file = true){
	// Run it in a thread, so that info is updated silently
	//$vtworker = new ASyncVTScan($file);
	//$vtworker->start();
	
	$api = new VirusTotalAPIV2($GLOBALS["config"]["virustotal"]["key"]);	
	if ($send_file) {
		$result = $api->scanFile($file->path,$file->filename);
	} else {
		$result = $api->rescanFile($file->name);
	}
	
	if (isset($result->response_code)) {
		$file->scanned = $result->response_code;
		
		// If file has been sent for analysis, we set the result to according response code
		if($file->scanned == VirusTotalAPIV2::ERROR_FILE_FOUND){
			$file->scanned = VirusTotalAPIV2::ERROR_FILE_BEING_ANALYZED;
		}
	}
	
	if (isset($result->permalink)){
		$file->vtlink 	= $result->permalink;
		$file->scan_id 	= $result->scan_id;
	}
	
	SetVTResults($file);
}

function SendFileCommentOnVirusTotal($hash, $comment){
	$api 	= new VirusTotalAPIV2($GLOBALS["config"]["virustotal"]["key"]);		
	$result = $api->makeComment($hash, $comment);
	if (isset($result->response_code)) {
		return $result->response_code == 1;
	}
	return False;
}

function ScanFileOnVirusTotal($file, $force_upload = false){
	if(!isset($file->vtscore)) $file->vtscore = 0;
	if(!isset($file->vtlink)) $file->vtlink = "";
	if(!isset($file->vendor)) $file->vendor = "";	
	$file->scanned = VirusTotalAPIV2::ERROR_FILE_UNKNOWN;
	$api = new VirusTotalAPIV2($GLOBALS["config"]["virustotal"]["key"]);
    $success = False;
		
	// Check size
	if ($file->size >= 30000000) //VT limit is 32MB, we keep some margin
	{
		$file->scanned = VirusTotalAPIV2::ERROR_FILE_TOO_BIG;
		SetVTResults($file);
		return $success;
	}
	
	// First, check if file exists
	$report = $api->getFileReport((isset($file->scan_id) && !empty($file->scan_id)) ? $file->scan_id : $file->name);
	if (isset($report->response_code))
	{
		if ($report->response_code == VirusTotalAPIV2::ERROR_API_LIMIT){
			//API limit exceeded. Retry later.
			$file->scanned = VirusTotalAPIV2::ERROR_API_LIMIT;	
		}		
		else if ($report->response_code == VirusTotalAPIV2::ERROR_FILE_BEING_ANALYZED){
			//Being scanned; Keep the permalink to check later
			$file->scanned = VirusTotalAPIV2::ERROR_FILE_BEING_ANALYZED;				
			if(isset($report->permalink)) $file->vtlink = $report->permalink;
            $success = True;
		}
		else if ($report->response_code == VirusTotalAPIV2::ERROR_API_ERROR){
			//Error occured			
			$file->scanned = VirusTotalAPIV2::ERROR_API_ERROR;	
		}
		else if ($report->response_code == VirusTotalAPIV2::ERROR_FILE_UNKNOWN){
			//No results; upload the file
			if ($GLOBALS["config"]["virustotal"]["automatic_upload"] == True || $force_upload == True) {
				ForceScanFileOnVirusTotal($file, true);
			}
            $success = True;
		}
		else if ($report->response_code == VirusTotalAPIV2::ERROR_FILE_FOUND && isset($report->permalink)){			
			
			if ($force_upload) {
				ForceScanFileOnVirusTotal($file, false);
			} else {
				//Results
				if(isset($report->positives)) $file->vtscore = $report->positives;
				if(isset($report->permalink)) $file->vtlink = $report->permalink;					
				if(isset($report->scan_id)) $file->scan_id = $report->scan_id;
				$file->scanned 	= VirusTotalAPIV2::ERROR_FILE_FOUND;

				if (isset($report->scans)){
					if (isset($report->scans->Microsoft) && !empty($report->scans->Microsoft->result)) 				$file->vendor = $report->scans->Microsoft->result;
					else if (isset($report->scans->Kaspersky) && !empty($report->scans->Kaspersky->result)) 		$file->vendor = $report->scans->Kaspersky->result;				
					else if (isset($report->scans->BitDefender) && !empty($report->scans->BitDefender->result)) 	$file->vendor = $report->scans->BitDefender->result;
					else if (isset($report->scans->Malwarebytes) && !empty($report->scans->Malwarebytes->result)) 	$file->vendor = $report->scans->Malwarebytes->result;
				}
			}
            $success = True;
		}
		
		//==============
		
		SetVTResults($file);	
	}
    return $success;
}

function ScanFileOnCuckoo($file, $rescan = false){
	if(!isset($file->cklink)) $file->cklink = "";
	if(!isset($file->ckid)) $file->ckid = 0;
	$file->ck_scanned = CuckooAPI::ERROR_FILE_UNKNOWN;
    $success = False;
	
	// Check size
	if ($file->size >= 30000000) //Cuckoo limit is 32MB, we keep some margin
	{
		$file->ck_scanned = CuckooAPI::ERROR_FILE_TOO_BIG;	//file is too big
		SetCuckooResults($file);
		return $success;
	}

	$api 	= new CuckooAPI();	
	$result = $api->scanFile($file->path,$file->filename);
		
	if (isset($result->response_code)) {
		$file->ck_scanned = $result->response_code;
	}	
	else if (isset($result->task_id)){
		$file->ck_scanned 	= CuckooAPI::ERROR_FILE_BEING_ANALYZED;		
		$file->ckid 		= $result->task_id;
		$file->cklink 		= $api->getReportUrl($result->task_id);
        $success            = True;
	}
	
	SetCuckooResults($file);
    return $success;
}

function GetFileResultsOnCuckoo($file)
{
	$api 	= new CuckooAPI();	
	$result = $api->getTask($file->ckid);
	
	if (is_array($result) && isset($result['response_code'])) {
		if ($result['response_code'] == CuckooAPI::ERROR_API_ERROR) {
			$file->ck_scanned 	= CuckooAPI::ERROR_FILE_UNKNOWN;		// reset
			$file->ckid 		= 0;									// reset
			$file->cklink 		= '';									// reset
		}
	}
	else if (is_object($result) && isset($result->task) && isset($result->task->status)) {
		if ($result->task->status == 'reported') {		
			$file->ck_scanned = CuckooAPI::ERROR_FILE_FOUND;
			$file->cklink 	  = $api->getReportUrl($file->ckid);
			SetCuckooResults($file);
		}
	}
}

function SearchFileOnCuckoo($file)
{
	$api 	= new CuckooAPI();	
	$result = $api->getFileReport($file->name);
	
	if (is_object($result) && isset($result->sample) && isset($result->sample->id)) {
		$file->ck_scanned = CuckooAPI::ERROR_FILE_FOUND;
		$file->ckid		  = $result->sample>id;
		$file->cklink 	  = $api->getReportUrl($file->ckid);
		SetCuckooResults($file);
	}
}

//==================================================

function OnHandleFormData($file, $index) {
	// Handle form data, e.g. $_REQUEST['description'][$index]
	
	// explode files_data
	//files_data => [{"index":0,"vtsubmit":true,"cksubmit":true,"tags":"tag1,tag2,tag3"}]
	$file->vt_submit 		= False;
	$file->cuckoo_submit 	= False;
	$file->tags 			= '';
	$file->urls				= '';
	if (isset($_REQUEST['files_data'])) {
		$data_files = json_decode($_REQUEST['files_data']);
		if ($data_files && is_array($data_files)) {
			foreach($data_files as $data_file) {				
				if (property_exists($data_file, 'index') && $data_file->index == $index) {
					if (property_exists($data_file, 'vtsubmit') && $data_file->vtsubmit == True) 	$file->vt_submit = True;
					if (property_exists($data_file, 'cksubmit') && $data_file->cksubmit == True) 	$file->cuckoo_submit = True;
					if (property_exists($data_file, 'tags') && $data_file->tags) 					$file->tags = $data_file->tags;
					if (property_exists($data_file, 'urls') && $data_file->urls) 					$file->urls = $data_file->urls;
				} 
			}
		}
	}
}

// Modify file object to add additional fields
function OnFileUploaded($file) {
	AddFileToDatabase($file);
	OnGetFileObject($file, True);
		
	if (isset($file->vt_submit) && $file->vt_submit == True) 			ScanFileOnVirusTotal($file);
	if (isset($file->cuckoo_submit) && $file->cuckoo_submit == True) 	ScanFileOnCuckoo($file);
}

function OnUpdate(){
	if (!isset($_POST["hash"])) return False;
	
	if (isset($_REQUEST["user"]) && isset($_POST["favorite"])) {
		UpdateFavorite($_POST["hash"], $_REQUEST["user"], $_POST["favorite"] === "true");
	}
	
	// This part is under permissions constraints.
	//==================================================
	
	if (isset($_POST["vendor"])) {
		if (!CanModifyFile($_POST["hash"])) return False;
		UpdateVendor($_POST["hash"], $_POST["vendor"]);
	}
	if (isset($_POST["comment"])) {
		if (!CanModifyFile($_POST["hash"])) return False;
		UpdateComment($_POST["hash"], $_POST["comment"]);
	}	
	if (isset($_POST["tags"])) {
		if (!CanModifyFile($_POST["hash"])) return False;
		UpdateTags($_POST["hash"], $_POST["tags"]);
	}
	if (isset($_POST["urls"])) {
		if (!CanModifyFile($_POST["hash"])) return False;
		UpdateUrls($_POST["hash"], $_POST["urls"]);
	}
	return True;
}

function OnVTScan($file){
	if (isset($file) && !empty($file->name)){
		return ScanFileOnVirusTotal($file, true);
	}
    return False;
}

function OnVTComment(){
	if (!isset($_POST["hash"])) return False;	
	if (!isset($_POST["comment"])) return False;
	
	return SendFileCommentOnVirusTotal($_POST["hash"], $_POST["comment"]);
}

function OnCuckooScan($file){
	if (isset($file) && !empty($file->name)){
		return ScanFileOnCuckoo($file, false);
	}
    return False;
}

function OnRefreshCuckooStatus(){
	global $cuckoo_available;
	$api 	= new CuckooAPI();	
	$obj 	= $api->getInfos();
	if (is_object($obj)) {
		$cuckoo_available = true;	
	}
}

function OnGetCuckooStatus(){
	global $cuckoo_available;
	$api 	= new CuckooAPI();	
	$obj 	= $api->getInfos();
	if (is_object($obj)) {
		$cuckoo_available = true;		
		$obj->browse_url = $api->getBrowseUrl();
		echo json_encode($obj);
	}
}

function OnGetStorageInfo(){
	$obj = new stdClass();
	$obj->count 	= GetFilesCount();	
	$obj->total 	= GetFilesTotalSize();
	$obj->max_page 	= $obj->count == 0 ? 1 : ceil($obj->count / $GLOBALS["config"]["ui"]["files_per_page"]);
	echo json_encode($obj);
}

function ResizeAvatar($avatar, $width, $height){
    if (empty($avatar)) return "";
    
    $image      = imagecreatefromstring(base64_decode($avatar));   
    if ($image == false) {
        return "";
    }
    
    $image_n    = imagecreatetruecolor($width, $height);
    if ($image_n == false) {
        return "";
    }
    
    if (imagecopyresampled($image_n, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image)) == false) {
        return "";
    }
    
    ob_start();
    imagepng($image_n, null, 9);
    $stream = ob_get_clean();
    return base64_encode($stream);
}

// Modify file object to add additional fields
function OnGetFileObject($file, $fullinfo){
	global $loggedInUser;
	global $cuckoo_available;
	global $users_table;
    
	if ( !GetFileFromDatabase($file, isset($_REQUEST["user"]) ? $_REQUEST["user"] : null, !$fullinfo) )
        return;
    
	// VT refresh
	if ((!$GLOBALS["config"]["cron"]["enabled"] || !$GLOBALS["config"]["cron"]["virus_total"])
	&& $file->scanned != VirusTotalAPIV2::ERROR_FILE_FOUND 
	&& $file->scanned != VirusTotalAPIV2::ERROR_FILE_UNKNOWN 
	&& $file->scanned != VirusTotalAPIV2::ERROR_FILE_TOO_BIG)
	{
		ScanFileOnVirusTotal($file);
	}
	// Cuckoo refresh
	if (!$GLOBALS["config"]["cron"]["enabled"] || !$GLOBALS["config"]["cron"]["cuckoo"]){
		// Refresh state
		if ( $file->ck_scanned == CuckooAPI::ERROR_FILE_BEING_ANALYZED && $cuckoo_available ) {
			GetFileResultsOnCuckoo($file);
		}		
		// Search old analysis
		else if ( $file->ck_scanned == CuckooAPI::ERROR_FILE_UNKNOWN && $cuckoo_available ) {
			SearchFileOnCuckoo($file);
		}
	}	
	// Cuckoo data
	if ( $file->ck_scanned == CuckooAPI::ERROR_FILE_FOUND && $cuckoo_available ) {
		GetFileResultsOnCuckoo($file);
	}
    
    // Fetch user data
    if (!array_key_exists($file->user, $users_table)) {
        $user                       = new stdClass();     
        $user->avatar 	            = ResizeAvatar(loggedInUser::getavatar($file->user), 24, 24);
	    $user->name 	            = loggedInUser::getname($file->user);
        $users_table[$file->user]   = $user;
    }
	
	// Get user data
	$file->user_avatar 	= $users_table[$file->user]->avatar;
	$file->user_name 	= $users_table[$file->user]->name;
}

// Modify generated filename
function OnGetFileName($generated_name, $file_path, $name){
	return md5_file($file_path);
}

// Check if we can touch the file
function CanModifyFile($md5){
	global $loggedInUser;
	
	$file = new stdClass();
	$file->name = $md5;
	GetFileFromDatabase($file, isset($_REQUEST["user"]) ? $_REQUEST["user"] : null);	
	
	// Check we have the right to touch it.
	if (isset($_REQUEST["user"])) {	
		if ( $loggedInUser && $loggedInUser->checkPermission(array(2), $_REQUEST["user"]))
			return True; // Admin		
		
		else if ($_REQUEST["user"] != $file->user)	// Not same user
			return False;
	}	
	return True;
}

// Callback on before file is removed
function OnDeleteFile($md5){
	DeleteFileFromDatabase($md5);
}

// Returns an array of filenames matching filters
function IterateFiles($filters){	
	OnRefreshCuckooStatus();	// To update cuckoo machine availability
	
	if (isset($_GET["date"])) 	$filters->timestamp = $_GET["date"];
	if (isset($_GET["hash"])) 	$filters->md5 = $_GET["hash"];
	if (isset($_GET["vendor"])) $filters->vendor = $_GET["vendor"];
	if (isset($_GET["name"])) 	$filters->filename = $_GET["name"];
	if (isset($_GET["page"])) 	$filters->page = $_GET["page"];
	if (isset($_GET["size"])) 	$filters->size = $_GET["size"];
	if (isset($_GET["virustotal"])) $filters->virustotal = $_GET["virustotal"];
	if (isset($_GET["cuckoo"])) $filters->cuckoo = $_GET["cuckoo"];	
	if (isset($_GET["user"])) 	$filters->user = loggedInUser::getusersbyname($_GET["user"]);
	if (isset($_GET["comment"])) $filters->comment = $_GET["comment"];	
	if (isset($_GET["favorite"])) $filters->favorite = $_GET["favorite"];	
	if (isset($_GET["tags"])) $filters->tags = $_GET["tags"];	
    if (isset($_GET["urls"])) $filters->urls = $_GET["urls"];	

	$results = GetFilesFromDatabase($filters, isset($_REQUEST["user"]) ? $_REQUEST["user"] : null);
    $files = array();
	for ($i = 0; $i < count($results); ++$i) {
        array_push($files, (object) $results[$i]);
    }	
    //print_r($files);	
	return $files;
}

// return a method to iterate files, or null if we want to use native method.
function OnIterateFiles(){
	return 'IterateFiles';
}