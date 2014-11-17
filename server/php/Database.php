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
	return exec_query("INSERT INTO malware.storage(`timestamp`,`md5`,`filename`,`vendor`,`vtlink`,`filesize`,`vtscore`,`is_vtscanned`) VALUES (
							NOW(),
							'".$file->name."',
							'".$file->real_name."',
							'',
							'',
							".$file->size.",
							0,
							0
					  )");
}

function SetVTResults($file){
	return exec_query("UPDATE malware.storage SET vtlink='".$file->vtlink."', vtscore=".$file->vtscore.", is_vtscanned=".$file->scanned.", vendor='".$file->vendor."' WHERE md5='".$file->name."'");
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
	}
}

function GetFilesFromDatabase($filters){
	$md5 = ""; $filename = ""; $vendor = ""; $timestamp = "";
	if(isset($filters->timestamp)) 	$timestamp = $filters->timestamp;	
	if(isset($filters->md5)) 		$md5 = $filters->md5;
	if(isset($filters->filename)) 	$filename = $filters->filename;
	if(isset($filters->vendor)) 	$vendor = $filters->vendor;		
	$query = "SELECT md5 as md5 FROM malware.storage WHERE timestamp LIKE '%".$timestamp."%' AND md5 LIKE '%".$md5."%' AND filename LIKE '%".$filename."%' AND vendor LIKE '%".$vendor."%' ORDER BY timestamp DESC LIMIT 200";
	return exec_query($query);
}

function DeleteFileFromDatabase($md5){
	return exec_query("DELETE FROM malware.storage WHERE md5 = '".$md5."'");		
}