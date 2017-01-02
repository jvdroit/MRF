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
			utf8_encode_deep($result);
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

function utf8_encode_deep(&$input) {
	if (is_string($input)) {
		$input = utf8_encode($input);
	} else if (is_array($input)) {
		foreach ($input as &$value) {
			utf8_encode_deep($value);
		}
		unset($value);
	} else if (is_object($input)) {
		$vars = array_keys(get_object_vars($input));
		foreach ($vars as $var) {
			utf8_encode_deep($input->$var);
		}
	}
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
							-6,
							-2,
							0,
							".escape_string($file->user,false)."
					  )", false);
	
	close_database();	
	
    UpdateComment($file->name, $file->comment);
	UpdateTags($file->name, $file->tags);
	UpdateUrls($file->name, $file->urls);
	UpdateSha256($file->name, $file->sha256);
	UpdateSsdeep($file->name, $file->ssdeep);
	UpdatePEData($file->name, $file->pedata);
	UpdateIcon($file->name, $file->icon);
	UpdateMime($file->name, $file->mime);
	return $results;
}

// This needs to be queries separately as it's quite big data
function GetPEDataFromDatabase($hash){

	if (!open_database()) return array();
	$query = "SELECT value FROM storage_metas WHERE md5='".escape_string($hash,false)."' AND name = '_pedata'";
	$results = exec_query($query, false);
	$data = "";
	if (!empty($results)) {
		$data = $results[0]["value"];
	}
	close_database();
	return $data;
}

function GetSsdeepFilesToUpdate(){

	if (!open_database()) return array();
	$query = "SELECT s.md5 as md5 FROM storage s LEFT JOIN storage_metas m ON m.md5 = s.md5 AND m.name = '_ssdeep' WHERE m.value IS NULL LIMIT 100";
	$results = exec_query($query, false);
	close_database();
	return $results;
}

function GetSha256FilesToUpdate(){

	if (!open_database()) return array();
	$query = "SELECT s.md5 as md5 FROM storage s LEFT JOIN storage_metas m ON m.md5 = s.md5 AND m.name = '_sha256' WHERE m.value IS NULL LIMIT 300";
	$results = exec_query($query, false);
	close_database();
	return $results;
}

function GetPeDataFilesToUpdate(){

	if (!open_database()) return array();
	$query = "SELECT s.md5 as md5 FROM storage s LEFT JOIN storage_metas m ON m.md5 = s.md5 AND m.name = '_pedata' WHERE m.value IS NULL LIMIT 100";
	$results = exec_query($query, false);
	close_database();
	return $results;
}

function GetMimeFilesToUpdate(){

	if (!open_database()) return array();
	$query = "SELECT s.md5 as md5 FROM storage s LEFT JOIN storage_metas m ON m.md5 = s.md5 AND m.name = '_mime' WHERE m.value IS NULL LIMIT 100";
	$results = exec_query($query, false);
	close_database();
	return $results;
}

function GetVTFilesToUpdate(){
	
	if (!open_database()) return array();		
	$query = "SELECT md5 as md5 FROM storage WHERE is_vtscanned <= 0 AND is_vtscanned <> -5 AND is_vtscanned <> -6 ORDER BY timestamp DESC LIMIT 10";	
	$results = exec_query($query, false);	
	close_database();
	return $results;
}

function GetCuckooFilesToUpdate(){
	
	if (!open_database()) return array();		
	$query = "SELECT md5 as md5, is_cuckoo_scanned as is_cuckoo_scanned FROM storage WHERE is_cuckoo_scanned = -1 ORDER BY timestamp DESC LIMIT 50";	
	$results = exec_query($query, false);	
	close_database();
	return $results;
}

function GetCuckooFilesToSearch(){

	if (!open_database()) return array();
	$query = "SELECT md5 as md5, is_cuckoo_scanned as is_cuckoo_scanned FROM storage WHERE is_cuckoo_scanned = -2 ORDER BY RAND() LIMIT 10";
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

function UpdateUser($hash, $new_user){
	if (!open_database()) return array();

	$results = exec_query("UPDATE storage SET user='".escape_string($new_user,false)."' WHERE md5='".escape_string($hash,false)."'", false);

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

function UpdateSsdeep($hash, $ssdeep){
	if (!open_database()) return array();

	// Remove old hash
	$results = exec_query("DELETE FROM storage_metas WHERE md5='".escape_string($hash,false)."' AND name='_ssdeep'", false);

	// Add new hash
	if (!empty($ssdeep))
		$results = exec_query("INSERT INTO storage_metas (`md5`,`name`,`value`) VALUES ('".escape_string($hash,false)."', '_ssdeep', '".escape_string($ssdeep,false)."')", false);
		 
		close_database();
		return $results;
}

function UpdateSha256($hash, $sha256){
	if (!open_database()) return array();

	// Remove old hash
	$results = exec_query("DELETE FROM storage_metas WHERE md5='".escape_string($hash,false)."' AND name='_sha256'", false);

	// Add new hash
    if (!empty($sha256))
	    $results = exec_query("INSERT INTO storage_metas (`md5`,`name`,`value`) VALUES ('".escape_string($hash,false)."', '_sha256', '".escape_string($sha256,false)."')", false);
   
	close_database();
	return $results;
}

function UpdatePEData($hash, $data){
	if (!open_database()) return array();

	// Remove old data
	$results = exec_query("DELETE FROM storage_metas WHERE md5='".escape_string($hash,false)."' AND name='_pedata'", false);

	// Add new data
	if (!empty($data))
		$results = exec_query("INSERT INTO storage_metas (`md5`,`name`,`value`) VALUES ('".escape_string($hash,false)."', '_pedata', '".escape_string($data,false)."')", false);
		 
	close_database();
	return $results;
}

function UpdateIcon($hash, $icon){
	if (!open_database()) return array();

	// Remove old data
	$results = exec_query("DELETE FROM storage_metas WHERE md5='".escape_string($hash,false)."' AND name='_icon'", false);

	// Add new data
	if (!empty($icon))
		$results = exec_query("INSERT INTO storage_metas (`md5`,`name`,`value`) VALUES ('".escape_string($hash,false)."', '_icon', '".escape_string($icon,false)."')", false);
			
	close_database();
	return $results;
}

function UpdateMime($hash, $mime){
	if (!open_database()) return array();

	// Remove old data
	$results = exec_query("DELETE FROM storage_metas WHERE md5='".escape_string($hash,false)."' AND name='_mime'", false);

	// Add new data
	if (!empty($mime))
		$results = exec_query("INSERT INTO storage_metas (`md5`,`name`,`value`) VALUES ('".escape_string($hash,false)."', '_mime', '".escape_string($mime,false)."')", false);
			
		close_database();
		return $results;
}

function IsFilePresent($hash){
	if (!open_database()) return False;
	
	$results = exec_query("SELECT * FROM storage WHERE md5='".escape_string($hash,false)."'", false);
	
	close_database();
	return !empty($results);
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
    $file->sha256       = '';
    $file->ssdeep       = '';
    $file->icon         = '';
    $file->mime         = '';
    $results = exec_query("SELECT 
md5 as md5,
MAX(if(name='_comment',value,NULL)) as comment,
GROUP_CONCAT(if(name='_url',value,NULL) SEPARATOR ',') as urls,
GROUP_CONCAT(if(name='_tag',value,NULL) SEPARATOR ',') as tags,
MAX(if(name='_favorite' AND value='".escape_string($user,false)."',value,NULL)) as favorite,
MAX(if(name='_sha256',value,NULL)) as sha256,
MAX(if(name='_ssdeep',value,NULL)) as ssdeep,
MAX(if(name='_icon',value,NULL)) as icon,
MAX(if(name='_mime',value,NULL)) as mime
FROM storage_metas
WHERE md5 = '".escape_string($file->name,false)."'", false);		
	if (!empty($results)) {
		if (!is_null($results[0]["comment"])) $file->comment = $results[0]["comment"];
        $file->favorite = $results[0]["favorite"] > 0 ? True : False;
        if (!is_null($results[0]["tags"])) $file->tags       = $results[0]["tags"];
        if (!is_null($results[0]["urls"])) $file->urls       = $results[0]["urls"];
        if (!is_null($results[0]["sha256"])) $file->sha256   = $results[0]["sha256"];
        if (!is_null($results[0]["ssdeep"])) $file->ssdeep   = $results[0]["ssdeep"];
        if (!is_null($results[0]["icon"])) $file->icon   	 = $results[0]["icon"];
        if (!is_null($results[0]["mime"])) $file->mime   	 = $results[0]["mime"];
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
	if(isset($filters->cuckoo) && $filters->cuckoo != "none" ) { 
		if ($is_first_statement) { 
			$is_first_statement = false; 
			$flt_cuckoo 		= " WHERE ";
		}
		else {
			$flt_cuckoo 		= " AND ";
		}
		
		if ($filters->cuckoo == 'scanning') {
			$flt_cuckoo = $flt_cuckoo . "is_cuckoo_scanned = -1";
		} 
		else if ($filters->cuckoo == 'results') {
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
	if(isset($filters->favorite) && $filters->favorite != "none" && isset($user)) { 
		if ($is_first_statement) {
			$is_first_statement = false; 
			$flt_favorite 		= " WHERE ";
		}
		else {
			$flt_favorite 		= " AND ";
		}
		if ( $filters->favorite == "fav" )
			$flt_favorite = $flt_favorite . "(SELECT COUNT(*) FROM storage_metas WHERE storage_metas.md5 = storage.md5 AND storage_metas.name='_favorite' AND storage_metas.value=".escape_string($user,false).")";
		else 
			$flt_favorite = $flt_favorite . "(SELECT COUNT(*) FROM storage_metas WHERE storage_metas.md5 = storage.md5 AND storage_metas.name='_favorite' AND storage_metas.value=".escape_string($user,false).") = 0";
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
		$tags_str = escape_string($filters->tags, false);
		$flt_tags = $flt_tags . "(SELECT COUNT(*) FROM storage_metas WHERE storage_metas.md5 = storage.md5 AND storage_metas.name='_tag' AND storage_metas.value LIKE '%".$tags_str."%')";
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
		$urls_str = escape_string($filters->urls, false);
		$flt_urls = $flt_urls . "(SELECT COUNT(*) FROM storage_metas WHERE storage_metas.md5 = storage.md5 AND storage_metas.name='_url' AND storage_metas.value LIKE '%".$urls_str."%')";
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
    FROM storage " . $flt_timestamp . $flt_md5 . $flt_filename . $flt_vendor . $flt_size . $flt_virustotal . $flt_cuckoo . $flt_user . $flt_comment . $flt_tags . $flt_urls . $flt_favorite . "
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