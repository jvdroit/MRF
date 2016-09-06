<?php
require_once(__DIR__."/config.php");

function open_database() {
	global $mysqli_samples;
	$mysqli_samples = new mysqli(
		$GLOBALS["config"]["db"]["storage"]["host"], 
		$GLOBALS["config"]["db"]["storage"]["username"], 
		$GLOBALS["config"]["db"]["storage"]["password"],
		$GLOBALS["config"]["db"]["storage"]["dbname"]
	);
	if ($mysqli_samples->connect_errno) {
		return false;
	}
	return true;
}

function close_database() {
	global $mysqli_samples;
	$mysqli_samples->close();
}

function exec_query($query, $should_open_close = true){
	global $mysqli_samples;
	$rows = array();
	if ($should_open_close && !open_database()){
		return $rows;
	}
	
	$results = $mysqli_samples->query($query);
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
	global $mysqli_samples;
	if ($should_open_close && !open_database()){
		return $str;
	}
	
	$escaped_str = $mysqli_samples->real_escape_string($str);	
	
	if ($should_open_close) close_database();
	return $escaped_str;
}

//===========================================

function AddFileToDatabase($file){	
	if (!open_database()) return array();
	
	$file->timestamp = date("Y-m-d H:i:s");
	$results = exec_query("INSERT INTO storage(`timestamp`,`md5`,`filename`,`vendor`,`vtlink`,`vt_scan_id`,`filesize`,`vtscore`,`is_vtscanned`,`is_cuckoo_scanned`,`cuckoo_id`,`user`) VALUES (
							NOW(),
							'".escape_string($file->name,false)."',
							'".escape_string($file->filename,false)."',
							'',
							'',
							'',
							".escape_string($file->size,false).",
							0,
							0,
							-2,
							0,
							".escape_string($file->user,false)."
					  )", false);
	
	close_database();	
	
    UpdateComment($file->name, $file->comment);
	UpdateTags($file->name, $file->tags);
	UpdateUrls($file->name, $file->urls);
	return $results;
}

function GetVTFilesToUpdate(){
	
	if (!open_database()) return array();		
	$query = "SELECT md5 as md5 FROM storage WHERE is_vtscanned <= 0 AND is_vtscanned <> -5 ORDER BY timestamp DESC LIMIT 10";	
	$results = exec_query($query, false);	
	close_database();
	return $results;
}

function GetCuckooFilesToUpdate(){
	
	if (!open_database()) return array();		
	$query = "SELECT md5 as md5, is_cuckoo_scanned as is_cuckoo_scanned FROM storage WHERE is_cuckoo_scanned < 0 AND is_cuckoo_scanned <> -5 ORDER BY timestamp DESC LIMIT 10";	
	$results = exec_query($query, false);	
	close_database();
	return $results;
}

function SetVTResults($file){
	if (!open_database()) return array();
	
	$results = exec_query("UPDATE storage SET vtlink='".escape_string($file->vtlink,false)."', vt_scan_id='".escape_string($file->scan_id,false)."', vtscore=".escape_string($file->vtscore,false).", is_vtscanned=".escape_string($file->scanned,false).", vendor='".escape_string($file->vendor,false)."' WHERE md5='".escape_string($file->name,false)."'", false);
	
	close_database();
	return $results;
}

function SetCuckooResults($file){
	if (!open_database()) return array();
	
	$results = exec_query("UPDATE storage SET cuckoo_id=".escape_string($file->ckid,false).",is_cuckoo_scanned=".escape_string($file->ck_scanned,false)." WHERE md5='".escape_string($file->name,false)."'", false);	
	
	close_database();
	return $results;
}

function UpdateVendor($hash, $new_vendor){
	if (!open_database()) return array();
	
	$results = exec_query("UPDATE storage SET vendor='".escape_string($new_vendor,false)."' WHERE md5='".escape_string($hash,false)."'", false);
	
	close_database();
	return $results;
}

function UpdateFavorite($hash, $user, $favorite){
	if (!open_database()) return array();
	if ($favorite) {
		$results = exec_query("INSERT INTO storage_metas (`md5`,`name`,`value`) VALUES ('".escape_string($hash,false)."', '_favorite', '".escape_string($user,false)."')", false);
		close_database();
		return $results;
	} else {
		$results = exec_query("DELETE FROM storage_metas WHERE md5='".escape_string($hash,false)."' AND name='_favorite' AND value=".escape_string($user,false), false);
		close_database();
		return $results;
	}
}

function UpdateComment($hash, $new_comment){
	if (!open_database()) return array();
	
	// Remove old comment
	$results = exec_query("DELETE FROM storage_metas WHERE md5='".escape_string($hash,false)."' AND name='_comment'", false);	
	
	// Add new comment
    if (!empty($new_comment))
	    $results = exec_query("INSERT INTO storage_metas (`md5`,`name`,`value`) VALUES ('".escape_string($hash,false)."', '_comment', '".escape_string($new_comment,false)."')", false);
	
	close_database();
	return $results;
}

function UpdateTags($hash, $new_tags){
	if (!open_database()) return array();
	
	// Remove old tags
	$results = exec_query("DELETE FROM storage_metas WHERE md5='".escape_string($hash,false)."' AND name='_tag'", false);	
	
	// Add new tags
	$tags = explode(",", $new_tags);
	foreach( $tags as $tag )
        if (!empty($tag))
		    $results = exec_query("INSERT INTO storage_metas (`md5`,`name`,`value`) VALUES ('".escape_string($hash,false)."', '_tag', '".escape_string($tag,false)."')", false);
	
	close_database();
	return $results;
}

function UpdateUrls($hash, $new_urls){
	if (!open_database()) return array();
	
	// Remove old urls
	$results = exec_query("DELETE FROM storage_metas WHERE md5='".escape_string($hash,false)."' AND name='_url'", false);	
	
	// Add new urls
	$urls = explode(",", $new_urls);
	foreach( $urls as $url )
        if (!empty($url))
		    $results = exec_query("INSERT INTO storage_metas (`md5`,`name`,`value`) VALUES ('".escape_string($hash,false)."', '_url', '".escape_string($url,false)."')", false);
	
	close_database();
	return $results;
}

function GetFileFromDatabase($file, $user = null, $only_metas = False){
	if (!open_database()) return False;
    
	// Get user data
    if ( !$only_metas ) 
    {
        $results = exec_query("SELECT * FROM storage WHERE md5 = '".escape_string($file->name,false)."'", false);		
        if (!empty($results)) {
            $file->timestamp 	= $results[0]["timestamp"];
            $file->filename 	= $results[0]["filename"];
            $file->vendor 		= $results[0]["vendor"];
            $file->vtlink 		= $results[0]["vtlink"];
            $file->vtscore 		= (int)$results[0]["vtscore"];
            $file->size 		= (int)$results[0]["filesize"];
            $file->scanned 		= (int)$results[0]["is_vtscanned"];
            $file->scan_id 		= $results[0]["vt_scan_id"];
            $file->ck_scanned 	= (int)$results[0]["is_cuckoo_scanned"];
            $file->ckid 		= (int)$results[0]["cuckoo_id"];
            $file->user 		= (int)$results[0]["user"];
        }
    }
    
    // Get user metas
    $file->comment      = '';
    $file->favorite     = False;
    $file->tags         = '';
    $file->urls         = '';    
    $results = exec_query("SELECT 
md5 as md5,
GROUP_CONCAT(if(name='_comment',value,NULL) SEPARATOR ',') as comment,
GROUP_CONCAT(if(name='_url',value,NULL) SEPARATOR ',') as urls,
GROUP_CONCAT(if(name='_tag',value,NULL) SEPARATOR ',') as tags,
GROUP_CONCAT(if(name='_favorite' AND value='".escape_string($user,false)."',value,NULL) SEPARATOR ',') as favorite
FROM storage_metas
WHERE md5 = '".escape_string($file->name,false)."'", false);		
	if (!empty($results)) {
		if (!is_null($results[0]["comment"])) $file->comment = $results[0]["comment"];
        $file->favorite = $results[0]["favorite"] > 0 ? True : False;
        if (!is_null($results[0]["tags"])) $file->tags       = $results[0]["tags"];
        if (!is_null($results[0]["urls"])) $file->urls       = $results[0]["urls"];
	} else {
        return False;
    }
    
	close_database();
    return True;
}

function GetFilesFromDatabase($filters, $user = null){	

	$is_first_statement = true;
	$flt_timestamp 		= "";
	$flt_md5 			= ""; 
	$flt_filename 		= ""; 
	$flt_vendor 		= "";  
	$flt_size 			= "";
	$flt_virustotal		= "";
	$flt_cuckoo 		= "";
	$flt_user 			= "";
	$flt_comment		= "";
	$flt_favorite		= "";
	$flt_tags			= "";
    $flt_urls           = "";
	$page 				= 1; 
	
	if (!open_database()) return array();
	
    // Fast Search 
    //===============================================
    
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
	
	// virustotal
	if(isset($filters->virustotal)) { 
		if ($is_first_statement) { 
			$is_first_statement = false; 
			$flt_virustotal 		= " WHERE ";
		}
		else {
			$flt_virustotal 		= " AND ";
		}
		
		if (0 === strpos($filters->virustotal, '>')) {
			$flt_virustotal = $flt_virustotal . "vtscore >= " .escape_string(substr($filters->virustotal, 1),false);
		} 
		else if (0 === strpos($filters->virustotal, '<')) {
			$flt_virustotal = $flt_virustotal . "vtscore <= " .escape_string(substr($filters->virustotal, 1),false);
		}
		else {
			$flt_virustotal = $flt_virustotal . "vtscore <= " .escape_string($filters->virustotal,false);
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
	
	// $flt_user
	if(isset($filters->user) && is_array($filters->user) && count($filters->user) > 0) { 
		if ($is_first_statement) { 
			$is_first_statement = false; 
			$flt_user 			= " WHERE ";
		}
		else {
			$flt_user 			= " AND ";
		}	
		$flt_user = $flt_user . "user IN (" . escape_string(implode(',', array_map('intval', $filters->user)),false) . ")";
	}
    
    // Slow Search 
    //===============================================
	
	// $flt_comment
	if(isset($filters->comment)) { 
		if ($is_first_statement) { 
			$is_first_statement = false; 
			$flt_comment 		= " WHERE ";
		}
		else {
			$flt_comment 		= " AND ";
		}	
        $flt_comment = $flt_comment . "(SELECT COUNT(*) FROM storage_metas WHERE storage_metas.md5 = storage.md5 AND storage_metas.name='_comment' AND storage_metas.value LIKE '%".escape_string($filters->comment,false)."%')";
	}
	
	// $flt_favorite
	if(isset($filters->favorite) && isset($user)) { 
		if ($is_first_statement) {
			$is_first_statement = false; 
			$flt_favorite 		= " WHERE ";
		}
		else {
			$flt_favorite 		= " AND ";
		}	
		$flt_favorite = $flt_favorite . "(SELECT COUNT(*) FROM storage_metas WHERE storage_metas.md5 = storage.md5 AND storage_metas.name='_favorite' AND storage_metas.value=".escape_string($user,false).")";
	}
	
	// $flt_tags
	if(isset($filters->tags)) { 
		if ($is_first_statement) { 
			$is_first_statement = false; 
			$flt_tags 		= " WHERE ";
		}
		else {
			$flt_tags 		= " AND ";
		}	
		// Should be IN ('val', 'val2', ...)
		$tags = explode(",", $filters->tags);		
		foreach( $tags as &$tag ) {
			$tag = escape_string($tag,false);
		}		
		$tags_str = implode("','", $tags);	
		$flt_tags = $flt_tags . "(SELECT COUNT(*) FROM storage_metas WHERE storage_metas.md5 = storage.md5 AND storage_metas.name='_tag' AND storage_metas.value IN ('".$tags_str."'))";
	}
    
    // $flt_urls
	if(isset($filters->urls)) { 
		if ($is_first_statement) { 
			$is_first_statement = false; 
			$flt_urls 		= " WHERE ";
		}
		else {
			$flt_urls 		= " AND ";
		}	
		// Should be IN ('val', 'val2', ...)
		$urls = explode(",", $filters->urls);		
		foreach( $urls as &$url ) {
			$url = escape_string($url,false);
		}		
		$urls_str = implode("','", $urls);	
		$flt_urls = $flt_urls . "(SELECT COUNT(*) FROM storage_metas WHERE storage_metas.md5 = storage.md5 AND storage_metas.name='_url' AND storage_metas.value IN ('".$urls_str."'))";
	}
	
	// pagination
	if(isset($filters->page)) {	
		$page = $filters->page;
		if ($page < 1) $page = 1;
	}
	
	$count_per_page = $GLOBALS["config"]["ui"]["files_per_page"];
	$offset 		= ($page - 1) * $count_per_page;	
    
    $query = "SELECT 
        storage.md5 as name,
        storage.timestamp as timestamp,
        storage.filename as filename,
        storage.vendor as vendor,
        storage.vtlink as vtlink,
        storage.vtscore as vtscore,
        storage.filesize as size,
        storage.is_vtscanned as scanned,
        storage.vt_scan_id as scan_id,
        storage.is_cuckoo_scanned as ck_scanned,
        storage.cuckoo_id as ckid,
        storage.user as user        
    FROM storage " . $flt_timestamp . $flt_md5 . $flt_filename . $flt_vendor . $flt_size . $flt_virustotal . $flt_cuckoo . $flt_user . $flt_comment . $flt_tags . $flt_favorite . "
    GROUP BY storage.md5 
    ORDER BY timestamp DESC 
    LIMIT " . strval($offset) . "," . strval($count_per_page);
    //echo $query;
    
	$results = exec_query($query, false);	
    
    // Cast to wanted type
    for ($i = 0; $i < count($results); ++$i) 
    {
        $results[$i]["vtscore"] 		    = (int)$results[$i]["vtscore"];
        $results[$i]["size"]		        = (int)$results[$i]["size"];
        $results[$i]["scanned"] 	        = (int)$results[$i]["scanned"];
        $results[$i]["ck_scanned"]          = (int)$results[$i]["ck_scanned"];
        $results[$i]["ckid"]		        = (int)$results[$i]["ckid"];
        $results[$i]["user"]		        = (int)$results[$i]["user"];
    }
	close_database();
	return $results;
}

function GetFilesCount() {
	if (!open_database()) return array();
	
	$results = exec_query("SELECT count(*) as count FROM storage", false);

	close_database();
	return $results[0]["count"];	
}

function GetFilesTotalSize() {
	if (!open_database()) return array();
	
	$results = exec_query("SELECT SUM(filesize) as total FROM storage", false);

	close_database();
	return $results[0]["total"];	
}

function DeleteFileFromDatabase($md5){
	if (!open_database()) return array();
	
	$results = exec_query("DELETE FROM storage WHERE md5 = '".escape_string($md5,false)."'", false);

	close_database();
	return $results;
}