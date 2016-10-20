<?php 

require_once(__DIR__."/../src/config.php");

//=================================================================
//Signatures DB

function Install()
{
	global $db_table_prefix, $mysqli;
	$db_issue = false;
	
	//=================================================================
	// Permissions
	
	$permissions_entry = "
	INSERT INTO `".$db_table_prefix."permissions` (`id`, `name`) VALUES
	(3, 'Downloader'),
	(4, 'Editor'),
	(5, 'Uploader'),
	(6, 'Cuckoo Uploader'),
	(7, 'VirusTotal Uploader'),
	(8, 'VirusTotal Contributor')
	";
	
	$stmt = $mysqli->prepare($permissions_entry);
	if($stmt->execute())
	{
		echo "<p>Inserted custom permissions into ".$db_table_prefix."permissions table.....</p>";
	}
	else
	{
		echo "<p>Error inserting permissions.</p>";
		$db_issue = true;
	}

	//=================================================================
	//Malware DB
	
	/* Create a new mysqli object with database connection parameters */
	$mysqli_storage = new mysqli(
		$GLOBALS["config"]["db"]["storage"]["host"], 
		$GLOBALS["config"]["db"]["storage"]["username"], 
		$GLOBALS["config"]["db"]["storage"]["password"], 
		$GLOBALS["config"]["db"]["storage"]["dbname"]
	);

	if(mysqli_connect_errno()) {
		echo "Connection Failed: " . mysqli_connect_errno();
		exit();
	}
	
	$storage_sql = "
	CREATE TABLE IF NOT EXISTS `storage` (
	  `md5` varchar(32) NOT NULL,
	  `filename` text NOT NULL,
	  `vendor` text NOT NULL,
	  `vtlink` text NOT NULL,
	  `vt_scan_id` text NOT NULL,
	  `filesize` int(11) NOT NULL,
	  `vtscore` int(11) NOT NULL DEFAULT '0',
	  `is_vtscanned` int(11) NOT NULL DEFAULT '0',
	  `timestamp` datetime NOT NULL,
	  `is_cuckoo_scanned` int(11) NOT NULL DEFAULT '-2',
	  `cuckoo_id` int(11) NOT NULL,
	  `user` int(11) NOT NULL,
	  PRIMARY KEY (`md5`),
	  UNIQUE KEY `md5` (`md5`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	";	
	
	$stmt = $mysqli_storage->prepare($storage_sql);
	if($stmt->execute())
	{
		echo "<p>storage table created.....</p>";
	}
	else
	{
		echo "<p>Error constructing storage table.</p>";
		$db_issue = true;
	}
	
	$storage_metas_sql = "
	CREATE TABLE IF NOT EXISTS `storage_metas` (
	  `meta_index` int(11) NOT NULL AUTO_INCREMENT,
	  `md5` varchar(32) NOT NULL,
	  `name` text NOT NULL,
	  `value` text NOT NULL,
	  PRIMARY KEY (`meta_index`)
	) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
	";
	
	$stmt = $mysqli_storage->prepare($storage_metas_sql);
	if($stmt->execute())
	{
		echo "<p>storage metas table created.....</p>";
	}
	else
	{
		echo "<p>Error constructing storage metas table.</p>";
		$db_issue = true;
	}
	
	return $db_issue;
}

?>