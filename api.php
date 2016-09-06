<?php
require_once(__DIR__."/src/config.php");
require_once(__DIR__.'/src/lib/restlib.php');
require_once(__DIR__.'/src/uploader.php');
require_once(__DIR__."/src/lib/usercake/models/config.php");

class Rest_Api extends Rest_Rest {
	public function __construct(){
		parent::__construct();				// Init parent contructor	
	}
	
	public function processApi(){
		global $loggedInUser;
		
		// Extract requested API
		$func = isset($_REQUEST['action']) ? strtolower(trim(str_replace("/","",$_REQUEST['action']))) : null;	
		if (!$func && isset($_POST['action'])) $func = strtolower(trim(str_replace("/","",$_POST['action']))) ;	
		
		// Could not extract function, and is not a DELETE request nor a DOWNLOAD request
		if (!$func && $this->get_request_method() != "DELETE" && !(isset($_REQUEST) && isset($_REQUEST['download']))) {
			$this->response('',406);
		}
		
		// Extract API key
		if(isUserLoggedIn() && $loggedInUser != NULL) // if logged in, we get it from current cookie
			$key = $loggedInUser->activationtoken();
		else {
			if (!isset($key) && isset($_REQUEST['token'])) 	$key = $_REQUEST['token'];
			if (!isset($key) && isset($_POST['token'])) 	$key = $_POST['token'];	
		}
					
		// Verify API key/ Save user id in REQUEST array
		if (!isset($key)) $this->response('',401);
		$is_api_valid 	= loggedInUser::checkapikey($key); 
		$user 			= loggedInUser::getuserbyapikey($key);		
		if ($user != null) $_REQUEST["user"] = $user;
				
		// Go to selected route
		if (!$is_api_valid)											$this->response('',401);		
		else if((int)method_exists($this,$func) > 0)				$this->$func();
		else if($this->get_request_method() == "DELETE" 
			|| (isset($_REQUEST) 
				&& isset($_REQUEST['_method']) 
				&& $_REQUEST['_method'] == 'DELETE')) 				$this->deletefile();
		else if(isset($_REQUEST) && isset($_REQUEST['download'])) 	$this->downloadfile();
		else														$this->response('',404);
	}
	
	private function getUploader() {
		$this->options 			= array (
			'upload_dir' => $GLOBALS["config"]["urls"]["storagePath"], 
			'upload_url' => $GLOBALS["config"]["urls"]["storageUrl"], 
			'script_url' => $GLOBALS["config"]["urls"]["baseUrl"]."api.php", 
			'delete_type' => 'DELETE', 
			'download_via_php' => 1 
		);		
		return new UploadHandler($this->options, false);
	}
	
	//===========================================================================
	// Routes
	
	public function downloadfile() {
		if($this->get_request_method() != "GET"){ $this->response('',406); }		
		$uploader = $this->getUploader();
		$uploader->downloadfile();
	}
	
	public function getfiles() {
		if($this->get_request_method() != "GET"){ $this->response('',406); }
		$uploader = $this->getUploader();
		$uploader->getfiles();
	}
	
	public function getcuckoo() {
		if($this->get_request_method() != "GET"){ $this->response('',406); }
		$uploader = $this->getUploader();
		$uploader->get_cuckoo_status();
	}
	
	public function getstorageinfo() {
		if($this->get_request_method() != "GET"){ $this->response('',406); }
		$uploader = $this->getUploader();
		$uploader->get_storage_info();
	}
	
	public function virustotalscan() {
		if($this->get_request_method() != "POST"){ $this->response('',406); }
		$uploader = $this->getUploader();
        if (!$uploader->virustotal_scan()) { $this->response('',500); }
	}
	
	public function virustotalcomment() {
		if($this->get_request_method() != "POST"){ $this->response('',406); }
		$uploader = $this->getUploader();
        if (!$uploader->virustotal_comment()) { $this->response('',500); }
	}
	
	public function cuckooscan() {
		if($this->get_request_method() != "POST"){ $this->response('',406); }
		$uploader = $this->getUploader();
		if (!$uploader->cuckoo_scan()) { $this->response('',404); }
	}
	
	public function deletefile() {		
		if($this->get_request_method() != "DELETE"){ $this->response('',406); }
		$uploader = $this->getUploader();
		if (!$uploader->delete_file())
			$this->response("Not enough rights",403);
	}
	
	public function updatefile() {
		if($this->get_request_method() != "POST"){ $this->response('',406); }
		$uploader = $this->getUploader();
		if (!$uploader->update_file())
			$this->response("Not enough rights",403);
	}
	
	public function uploadfiles() {
		if($this->get_request_method() != "POST"){ $this->response('',406); }		
		$uploader = $this->getUploader();
        if (!$uploader->upload_files()) { $this->response('',500); }
	}
}

// Initiiate Library
$api = new Rest_Api;
$api->processApi();

?>