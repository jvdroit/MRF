<?php

require_once(__DIR__.'/server/php/restlib.php');
require_once(__DIR__.'/server/php/UploadHandler.php');
require_once(__DIR__."/models/config.php");

class Rest_Api extends Rest_Rest {
	const STORAGE_PATH 		= '/volume1/web/upload/storage/';

	public function __construct(){
		parent::__construct();				// Init parent contructor	
	}
	
	public function processApi(){
		global $loggedInUser;
		$func = strtolower(trim(str_replace("/","",$_REQUEST['action'])));	
		if (!$func && isset($_POST['action'])) $func = $_POST['action'];	
		
		if(isUserLoggedIn() && $loggedInUser != NULL) // if logged in, we get it from current cookie
			$key = $loggedInUser->activationtoken();
		else {
			$key = strtolower(trim(str_replace("/","",$_REQUEST['token'])));
			if (!$key && isset($_POST['token'])) $key = $_POST['token'];	
		}
					
		$is_api_valid 	= loggedInUser::checkapikey($key); // verify API key
		$user 			= loggedInUser::getuserbyapikey($key);		
		if ($user != null) $_REQUEST["user"] = $user;
				
		if (!$is_api_valid)
			$this->response('',401);		
		else if((int)method_exists($this,$func) > 0)
			$this->$func();
		else if($this->get_request_method() == "DELETE" || (isset($_REQUEST) && isset($_REQUEST['_method']) && $_REQUEST['_method'] == 'DELETE'))
			$this->deletefile();
		else if(isset($_REQUEST) && isset($_REQUEST['download']))
			$this->downloadfile();
		else
			$this->response('',404);
	}
	
	public function downloadfile() {
		if($this->get_request_method() != "GET"){ $this->response('',406); }
		
		$this->options 			= array ('upload_dir' => Rest_Api::STORAGE_PATH, 'upload_url' => Rest_Api::STORAGE_PATH, 'download_via_php' => 1 );
		$this->upload_handler 	= new UploadHandler($this->options, false);
		$this->upload_handler->downloadfile();
	}
	
	public function getfiles() {
		if($this->get_request_method() != "GET"){ $this->response('',406); }
		
		$script_url = UploadHandler::get_full_url_for_script(__FILE__);		
		$script_url = str_replace('/volume1/web', '', $script_url); // remove that code on not Synology system
		
		$this->options 			= array ('upload_dir' => Rest_Api::STORAGE_PATH, 'upload_url' => Rest_Api::STORAGE_PATH, 'script_url' => $script_url, 'delete_type' => 'DELETE', 'download_via_php' => 1 );
		$this->upload_handler 	= new UploadHandler($this->options, false);
		$this->upload_handler->getfiles();
	}
	
	public function getcuckoo() {
		if($this->get_request_method() != "GET"){ $this->response('',406); }
		$this->options 			= array ('upload_dir' => Rest_Api::STORAGE_PATH, 'upload_url' => Rest_Api::STORAGE_PATH );
		$this->upload_handler 	= new UploadHandler($this->options, false);
		$this->upload_handler->get_cuckoo_status();
	}
	
	public function virustotalscan() {
		if($this->get_request_method() != "POST"){ $this->response('',406); }
		$this->options 			= array ('upload_dir' => Rest_Api::STORAGE_PATH, 'upload_url' => Rest_Api::STORAGE_PATH );
		$this->upload_handler 	= new UploadHandler($this->options, false);	
		$this->upload_handler->virustotal_scan();	
	}
	
	public function cuckooscan() {
		if($this->get_request_method() != "POST"){ $this->response('',406); }
		$this->options 			= array ('upload_dir' => Rest_Api::STORAGE_PATH, 'upload_url' => Rest_Api::STORAGE_PATH );
		$this->upload_handler 	= new UploadHandler($this->options, false);	
		$this->upload_handler->cuckoo_scan();	
	}
	
	public function deletefile() {
		if($this->get_request_method() != "DELETE"){ $this->response('',406); }
		$this->options 			= array ('upload_dir' => Rest_Api::STORAGE_PATH, 'upload_url' => Rest_Api::STORAGE_PATH );
		$this->upload_handler 	= new UploadHandler($this->options, false);	
		if (!$this->upload_handler->delete_file())
			$this->response("Not enough rights",403);
	}
	
	public function updatefile() {
		if($this->get_request_method() != "POST"){ $this->response('',406); }
		$this->options 			= array ('upload_dir' => Rest_Api::STORAGE_PATH, 'upload_url' => Rest_Api::STORAGE_PATH );
		$this->upload_handler 	= new UploadHandler($this->options, false);	
		if (!$this->upload_handler->update_file())
			$this->response("Not enough rights",403);
	}
	
	public function uploadfiles() {
		if($this->get_request_method() != "POST"){ $this->response('',406); }
		
		$script_url = UploadHandler::get_full_url_for_script(__FILE__);		
		$script_url = str_replace('/volume1/web', '', $script_url); // remove that code on not Synology system
		
		$this->options 			= array ('upload_dir' => Rest_Api::STORAGE_PATH, 'upload_url' => Rest_Api::STORAGE_PATH, 'script_url' => $script_url, 'delete_type' => 'DELETE', 'download_via_php' => 1 );
		$this->upload_handler 	= new UploadHandler($this->options, false);	
		$this->upload_handler->upload_files();	
	}
}

// Initiiate Library
$api = new Rest_Api;
$api->processApi();

?>