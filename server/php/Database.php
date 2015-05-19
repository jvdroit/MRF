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

function escape_string($str, $should_open_close = true){
	global $mysqli;
	if ($should_open_close && !open_database()){
		return $str;
	}
	
	$escaped_str = $mysqli->real_escape_string($str);	
	
	if ($should_open_close) close_database();
	return $escaped_str;
}

//===========================================

function AddFileToDatabase($file){	
	if (!open_database()) return array();
	
	$file->timestamp = date("Y-m-d H:i:s");
	$results = exec_query("INSERT INTO malware.storage(`timestamp`,`md5`,`filename`,`vendor`,`vtlink`,`vt_scan_id`,`filesize`,`vtscore`,`is_vtscanned`,`cuckoo_link`,`is_cuckoo_scanned`) VALUES (
							NOW(),
							'".escape_string($file->name,false)."',
							'".escape_string($file->real_name,false)."',
							'',
							'',
							'',
							".escape_string($file->size,false).",
							0,
							0,
							'',
							-2
					  )", false);
	
	close_database();
	return $results;
}

function SetVTResults($file){
	if (!open_database()) return array();
	
	$results = exec_query("UPDATE malware.storage SET vtlink='".escape_string($file->vtlink,false)."', vt_scan_id='".escape_string($file->scan_id,false)."', vtscore=".escape_string($file->vtscore,false).", is_vtscanned=".escape_string($file->scanned,false).", vendor='".escape_string($file->vendor,false)."' WHERE md5='".escape_string($file->name,false)."'", false);
	
	close_database();
	return $results;
}

function SetCuckooResults($file){
	if (!open_database()) return array();
	
	$results = exec_query("UPDATE malware.storage SET cuckoo_link='".escape_string($file->cklink,false)."',is_cuckoo_scanned=".escape_string($file->ck_scanned,false).",cuckoo_report='".escape_string($file->ckcontent,false)."' WHERE md5='".escape_string($file->name,false)."'", false);	
	
	close_database();
	return $results;
}

function UpdateInfos($hash, $new_vendor){
	if (!open_database()) return array();
	
	$results = exec_query("UPDATE malware.storage SET vendor='".escape_string($new_vendor,false)."' WHERE md5='".escape_string($hash,false)."'", false);
	
	close_database();
	return $results;
}

function GetFileFromDatabase($file){
	if (!open_database()) return array();
	$results = exec_query("SELECT * FROM malware.storage WHERE md5 = '".escape_string($file->name,false)."'", false);		
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
		$file->ckcontent 	= $results[0]["cuckoo_report"];
	}	
	close_database();
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
	
	if (!open_database()) return array();
	
	// Timestamp
	if(isset($filters->timestamp)) { 
		if ($is_first_statement) { 
			$is_first_statement = false; 
			$flt_timestamp 		= " WHERE ";
		}
		else {
			$flt_timestamp 		= " AND ";
		}
		$flt_timestamp = $flt_timestamp . "timestamp LIKE '%".escape_string($filters->timestamp,false)."%'";
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
		$flt_md5 = $flt_md5 . "md5 LIKE '%".escape_string($filters->md5,false)."%'";
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
		$flt_filename = $flt_filename . "filename LIKE '%".escape_string($filters->filename,false)."%'";
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
		$flt_vendor = $flt_vendor . "vendor LIKE '%".escape_string($filters->vendor,false)."%'";
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
			$flt_size = $flt_size . "filesize >= " .escape_string(substr($filters->size, 1),false);
		} 
		else if (0 === strpos($filters->size, '<')) {
			$flt_size = $flt_size . "filesize <= " .escape_string(substr($filters->size, 1),false);
		}
		else {
			$flt_size = $flt_size . "filesize <= " .escape_string($filters->size,false);
		}
	}
	
	// vt_score
	if(isset($filters->vt_score)) { 
		if ($is_first_statement) { 
			$is_first_statement = false; 
			$flt_vt_score 		= " WHERE ";
		}
		else {
			$flt_vt_score 		= " AND ";
		}
		
		if (0 === strpos($filters->vt_score, '>')) {
			$flt_vt_score = $flt_vt_score . "vtscore >= " .escape_string(substr($filters->vt_score, 1),false);
		} 
		else if (0 === strpos($filters->vt_score, '<')) {
			$flt_vt_score = $flt_vt_score . "vtscore <= " .escape_string(substr($filters->vt_score, 1),false);
		}
		else {
			$flt_vt_score = $flt_vt_score . "vtscore <= " .escape_string($filters->vt_score,false);
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
	$results = exec_query($query, false);
	
	close_database();
	return $results;
}

function DeleteFileFromDatabase($md5){
	if (!open_database()) return array();
	
	$results = exec_query("DELETE FROM malware.storage WHERE md5 = '".escape_string($md5,false)."'", false);

	close_database();
	return $results;
}