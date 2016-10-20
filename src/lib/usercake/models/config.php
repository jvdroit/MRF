<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/
require_once(__DIR__."/db-settings.php"); //Require DB connection
require_once(__DIR__."/../../../config.php");

//Retrieve settings
$stmt = $mysqli->prepare("SELECT id, name, value
	FROM ".$db_table_prefix."configuration");	
$stmt->execute();
$stmt->bind_result($id, $name, $value);

while ($stmt->fetch()){
	$settings[$name] = array('id' => $id, 'name' => $name, 'value' => $value);
}
$stmt->close();

//Fix missing fields
if (!isset($settings['website_short_name'])) {
	// Insert
	$stmt = $mysqli->prepare("INSERT INTO ".$db_table_prefix."configuration (id,name,value) VALUES (8, 'website_short_name', '')");
	$stmt->execute();
	$stmt->close();
	
	// Re-read
	$stmt = $mysqli->prepare("SELECT id, name, value FROM ".$db_table_prefix."configuration");
	$stmt->execute();
	$stmt->bind_result($id, $name, $value);
	
	while ($stmt->fetch()){
		$settings[$name] = array('id' => $id, 'name' => $name, 'value' => $value);
	}
	$stmt->close();	
}

//Set Settings
$emailActivation = $settings['activation']['value'];
$mail_templates_dir = __DIR__."/mail-templates/";
$websiteName = $settings['website_name']['value'];
$websiteShortName = $settings['website_short_name']['value'];
$websiteUrl = $GLOBALS["config"]["urls"]["baseUrl"];
$emailAddress = $settings['email']['value'];
$resend_activation_threshold = $settings['resend_activation_threshold']['value'];
$emailDate = date('dmy');
$language = $settings['language']['value'];
$template = $settings['template']['value'];

$master_account = -1;

$default_hooks = array("#WEBSITENAME#","#WEBSITEURL#","#DATE#");
$default_replace = array($websiteName,$websiteUrl,$emailDate);

if (!file_exists($language)) {
	$language = __DIR__."/languages/en.php";
}

if(!isset($language)) $language = __DIR__."/languages/en.php";

//Pages to require
require_once($language);
require_once(__DIR__."/class.mail.php");
require_once(__DIR__."/class.user.php");
require_once(__DIR__."/class.newuser.php");
require_once(__DIR__."/funcs.php");

session_start();

//Global User Object Var
//loggedInUser can be used globally if constructed
if(isset($_SESSION["userCakeUser"]) && is_object($_SESSION["userCakeUser"]))
{
	$loggedInUser = $_SESSION["userCakeUser"];
}

?>
