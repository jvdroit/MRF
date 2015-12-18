<?php

require_once(__DIR__.'/VirusTotalApiV2.php');
require_once(__DIR__.'/CuckooAPI.php');
require_once(__DIR__.'/Database.php');
require_once(__DIR__.'/../../models/config.php');
//require_once('./Thread/Database.php');

$GLOBALS['vt_key'] = 'YOUR_VT_API_KEY_HERE'; 
$GLOBALS['automatic_vt'] = True;

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

/**
 * Copy remote file over HTTP one small chunk at a time.
 *
 * @param $infile The full URL to the remote file
 * @param $outfile The path where to save the file
 */
function copyfile_chunked($infile, $outfile) {
    $chunksize = 10 * (1024 * 1024); // 10 Megs

    /**
     * parse_url breaks a part a URL into it's parts, i.e. host, path,
     * query string, etc.
     */
    $parts = parse_url($infile);
    $i_handle = fsockopen($parts['host'], 80, $errstr, $errcode, 5);
    $o_handle = fopen($outfile, 'wb');

    if ($i_handle == false || $o_handle == false) {
        return false;
    }

    if (!empty($parts['query'])) {
        $parts['path'] .= '?' . $parts['query'];
    }

    /**
     * Send the request to the server for the file
     */
    $request = "GET {$parts['path']} HTTP/1.1\r\n";
    $request .= "Host: {$parts['host']}\r\n";
    $request .= "User-Agent: Mozilla/5.0\r\n";
    $request .= "Keep-Alive: 115\r\n";
    $request .= "Connection: keep-alive\r\n\r\n";
    fwrite($i_handle, $request);

    /**
     * Now read the headers from the remote server. We'll need
     * to get the content length.
     */
    $headers = array();
    while(!feof($i_handle)) {
        $line = fgets($i_handle);
        if ($line == "\r\n") break;
        $headers[] = $line;
    }

    /**
     * Look for the Content-Length header, and get the size
     * of the remote file.
     */
    $length = 0;
    foreach($headers as $header) {
        if (stripos($header, 'Content-Length:') === 0) {
            $length = (int)str_replace('Content-Length: ', '', $header);
            break;
        }
    }

    /**
     * Start reading in the remote file, and writing it to the
     * local file one chunk at a time.
     */
    $cnt = 0;
    while(!feof($i_handle)) {
        $buf = '';
        $buf = fread($i_handle, $chunksize);
        $bytes = fwrite($o_handle, $buf);
        if ($bytes == false) {
            return false;
        }
        $cnt += $bytes;

        /**
         * We're done reading when we've reached the conent length
         */
        if ($cnt >= $length) break;
    }

    fclose($i_handle);
    fclose($o_handle);
    return $cnt;
}

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
			if ($GLOBALS['automatic_vt'] == True || $rescan == True) {
				ForceScanFileOnVirusTotal($file, true);
			}
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
	if(!isset($file->ckcontent)) $file->ckcontent = "";
	$file->ck_scanned = -2;
	
	// Check size
	if ($file->size >= 30000000) //Cuckoo limit is 32MB, we keep some margin
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
		$file->ckcontent 	= "";
	}
	else if (isset($result->task_ids) && count($result->task_ids) > 0){	// Cuckoo modified, we only handle first ID.
		$file->ck_scanned 	= -1;		
		$file->cklink 		= $api->getReportUrl($result->task_ids[0]);
		$file->ckcontent 	= "";
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
			$file->ckcontent = '';		// reset
		}
	}
	else if (is_object($result) && isset($result->task) && isset($result->task->status)) {
		if ($result->task->status == 'reported') {
			$file->ck_scanned = 0;
			if ($file->cklink) {
				$file->ckcontent = $file->path . '.cko';
				exec("curl -J -L -o " .$file->ckcontent. " " .$file->cklink);
			}			
			SetCuckooResults($file);
		}
	}
}

function OnOpenCuckooResults($file){
	if (!empty($file->ckcontent)) {
		echo file_get_contents($file->ckcontent);
	}
}

//==================================================

// Modify file object to add additional fields
function OnFileUploaded($file) {
	AddFileToDatabase($file);
	OnGetFileObject($file);
	ScanFileOnVirusTotal($file);
}

function OnUpdate(){
	if (!isset($_POST["hash"])) return False;	
	if (!CanModifyFile($hash)) return False;
	
	if (isset($_POST["vendor"])) {
		UpdateVendor($_POST["hash"], $_POST["vendor"]);
	}
	if (isset($_POST["comment"])) {
		UpdateComment($_POST["hash"], $_POST["comment"]);
	}	
	return True;
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
	global $loggedInUser;
	
	GetFileFromDatabase($file);	
	if ($file->scanned != 1 && $file->scanned != 0){
		ScanFileOnVirusTotal($file);
	}
	if ($file->ck_scanned == -1){		//Waiting for a result
		GetFileResultsOnCuckoo($file);
	}	
	
	// Get user data
	$file->user_avatar 	= loggedInUser::getavatar($file->user);
	$file->user_name 	= loggedInUser::getname($file->user);
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
	GetFileFromDatabase($file);	
	
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
	if (isset($_GET["date"])) 	$filters->timestamp = $_GET["date"];
	if (isset($_GET["hash"])) 	$filters->md5 = $_GET["hash"];
	if (isset($_GET["vendor"])) $filters->vendor = $_GET["vendor"];
	if (isset($_GET["name"])) 	$filters->filename = $_GET["name"];
	if (isset($_GET["page"])) 	$filters->page = $_GET["page"];
	if (isset($_GET["size"])) 	$filters->size = $_GET["size"];
	if (isset($_GET["virustotal"])) $filters->virustotal = $_GET["virustotal"];
	if (isset($_GET["cuckoo"])) $filters->cuckoo = $_GET["cuckoo"];	
	if (isset($_GET["user"])) 	$filters->user = loggedInUser::getusersbyname($_GET["user"]);

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