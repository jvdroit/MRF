<?php

function open_database() {
	global $mysqli;
	$mysqli = new mysqli('localhost', 'YOUR_LOGIN_HERE', 'YOUR_PASSWORD_HERE');
	if ($mysqli->connect_errno) {
		return false;
	}
	return true;
}

function close_database() {
	global $mysqli;
	$mysqli->close();
}

function exec_query($query, $should_open_close = true){
	global $mysqli;
	$rows = array();
	//echo $query;
	if ($should_open_close && !open_database()){
		return $rows;
	}
	
	$results = $mysqli->query($query);
	if (!is_bool($results)) {
		while($result = $results->fetch_assoc()) {
			$rows[] = $result;
		}
		$results->free();
	}
	
	if ($should_open_close) close_database();
	return $rows;
}

//===========================================

function AddFileToDatabase($file){	
	$file->timestamp = date("Y-m-d H:i:s");
	return exec_query("INSERT INTO malware.storage(`timestamp`,`md5`,`filename`,`vendor`,`vtlink`,`vt_scan_id`,`filesize`,`vtscore`,`is_vtscanned`,`cuckoo_link`,`is_cuckoo_scanned`) VALUES (
							NOW(),
							'".$file->name."',
							'".$file->real_name."',
							'',
							'',
							'',
							".$file->size.",
							0,
							0,
							'',
							-2
					  )");
}

function SetVTResults($file){
	return exec_query("UPDATE malware.storage SET vtlink='".$file->vtlink."', vt_scan_id='".$file->scan_id."', vtscore=".$file->vtscore.", is_vtscanned=".$file->scanned.", vendor='".$file->vendor."' WHERE md5='".$file->name."'");
}

function SetCuckooResults($file){
	return exec_query("UPDATE malware.storage SET cuckoo_link='".$file->cklink."', is_cuckoo_scanned=".$file->ck_scanned." WHERE md5='".$file->name."'");
}

function UpdateInfos($hash, $new_vendor){
	print("updating sample");
	return exec_query("UPDATE malware.storage SET vendor='".$new_vendor."' WHERE md5='".$hash."'");
}

function GetFileFromDatabase($file){
	$results = exec_query("SELECT * FROM malware.storage WHERE md5 = '".$file->name."'");		
	if (!empty($results)) {
		$file->timestamp 	= $results[0]["timestamp"];
		$file->real_name 	= $results[0]["filename"];
		$file->vendor 		= $results[0]["vendor"];
		$file->vtlink 		= $results[0]["vtlink"];
		$file->vtscore 		= $results[0]["vtscore"];
		$file->size 		= (int)$results[0]["filesize"];
		$file->scanned 		= $results[0]["is_vtscanned"];
		$file->scan_id 		= $results[0]["vt_scan_id"];
		$file->cklink 		= $results[0]["cuckoo_link"];
		$file->ck_scanned 	= $results[0]["is_cuckoo_scanned"];
	}
}

function GetFilesFromDatabase($filters){	

	$is_first_statement = true;
	$flt_timestamp 		= "";
	$flt_md5 			= ""; 
	$flt_filename 		= ""; 
	$flt_vendor 		= "";  
	$flt_size 			= "";
	$flt_vt_score		= "";
	$flt_cuckoo 		= "";
	$page 				= 1; 
	
	// Timestamp
	if(isset($filters->timestamp)) { 
		if ($is_first_statement) { 
			$is_first_statement = false; 
			$flt_timestamp 		= " WHERE ";
		}
		else {
			$flt_timestamp 		= " AND ";
		}
		$flt_timestamp = $flt_timestamp . "timestamp LIKE '%".$filters->timestamp."%'";
	}
	
	// md5
	if(isset($filters->md5)) { 
		if ($is_first_statement) { 
			$is_first_statement = false; 
			$flt_md5 		= " WHERE ";
		}
		else {
			$flt_md5 		= " AND ";
		}
		$flt_md5 = $flt_md5 . "md5 LIKE '%".$filters->md5."%'";
	}
	
	// filename
	if(isset($filters->filename)) { 
		if ($is_first_statement) { 
			$is_first_statement = false; 
			$flt_filename 		= " WHERE ";
		}
		else {
			$flt_filename 		= " AND ";
		}
		$flt_filename = $flt_filename . "filename LIKE '%".$filters->filename."%'";
	}
	
	// vendor
	if(isset($filters->vendor)) { 
		if ($is_first_statement) { 
			$is_first_statement = false; 
			$flt_vendor 		= " WHERE ";
		}
		else {
			$flt_vendor 		= " AND ";
		}
		$flt_vendor = $flt_vendor . "vendor LIKE '%".$filters->vendor."%'";
	}
	
	// size
	if(isset($filters->size)) { 
		if ($is_first_statement) { 
			$is_first_statement = false; 
			$flt_size 		= " WHERE ";
		}
		else {
			$flt_size 		= " AND ";
		}
		
		if (0 === strpos($filters->size, '>')) {
			$flt_size = $flt_size . "filesize >= " .substr($filters->size, 1);
		} 
		else if (0 === strpos($filters->size, '<')) {
			$flt_size = $flt_size . "filesize <= " .substr($filters->size, 1);
		}
		else {
			$flt_size = $flt_size . "filesize <= " .$filters->size;
		}
	}
	
	// flt_cuckoo
	if(isset($filters->cuckoo)) { 
		if ($is_first_statement) { 
			$is_first_statement = false; 
			$flt_cuckoo 		= " WHERE ";
		}
		else {
			$flt_cuckoo 		= " AND ";
		}
		
		if (strpos(strtolower($filters->cuckoo), 'scan') !== false) {
			$flt_cuckoo = $flt_cuckoo . "is_cuckoo_scanned = -1";
		} 
		else if (strpos(strtolower($filters->cuckoo), 'res') !== false) {
			$flt_cuckoo = $flt_cuckoo . "is_cuckoo_scanned = 0";
		}
		else {
			$flt_cuckoo = $flt_cuckoo . "is_cuckoo_scanned = -2";
		}
	}
	
	// pagination
	if(isset($filters->page)) {	
		$page = $filters->page;
		if ($page < 1) $page = 1;
	}
	
	$count_per_page = 20;
	$offset 		= ($page - 1) * $count_per_page;	
	
	$query = "SELECT md5 as md5 FROM malware.storage" . $flt_timestamp . $flt_md5 . $flt_filename . $flt_vendor . $flt_size . $flt_vt_score . $flt_cuckoo . " ORDER BY timestamp DESC LIMIT " . strval($offset) . "," . strval($count_per_page);
	//echo $query;
	return exec_query($query);
}

function DeleteFileFromDatabase($md5){
	return exec_query("DELETE FROM malware.storage WHERE md5 = '".$md5."'");		
}