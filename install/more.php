<?php 
require_once(__DIR__."/../src/config.php");
require_once(__DIR__.'/../src/core.php');
require_once(__DIR__."/../src/lib/usercake/init.php");

//=================================================================
//Signatures DB

function Install()
{
	global $user_db;
	$success = true;
	
	//=================================================================
	// Permissions
	
	$permissions_entry = "
	INSERT INTO `".$user_db->Prefix()."permissions` (`id`, `name`) VALUES
	(3, 'Downloader'),
	(4, 'Editor'),
	(5, 'Uploader'),
	(6, 'Cuckoo Uploader'),
	(7, 'VirusTotal Uploader'),
	(8, 'VirusTotal Contributor')
	";
	
	if($user_db->Execute($permissions_entry))
	{
		echo "<p>Inserted custom permissions into ".$user_db->Prefix()."permissions table.....</p>";
	}
	else
	{
		echo "<p>Error inserting permissions.</p>";
		$success = false;
	}

	//=================================================================
	//Malware DB
	
	$core = new MRFCore();
	$success &= $core->CreateDatabase();
	
	return $success;
}

?>