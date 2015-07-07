<?php
class CuckooAPI {
	const URL_API_BASIS 		= 'http://192.168.1.16:8090/';
	const URL_WEB_BASIS 		= 'http://192.168.1.16:8080/';
	const URL_WEB_BROWSE 		= 'browse/page/1';
	const URL_CREATE_TASK_FILE 	= 'tasks/create/file';
	const URL_CREATE_TASK_URL 	= 'tasks/create/url';
	const URL_TASKS_LIST 		= 'tasks/list';
	const URL_TASK_VIEW 		= 'tasks/view';
	const URL_TASK_DELETE 		= 'tasks/delete';
	const URL_TASK_REPORT		= 'tasks/report';
	const URL_TASK_SCREEN		= 'tasks/screenshots';
	const URL_FILE_VIEW 		= 'files/view';
	const URL_FILE_CONTENT 		= 'files/get';
	const URL_CUCKOO_STATUS		= 'cuckoo/status';
	const URL_VIEW_URL			= 'view';

	private $_available;
	
	public function __construct() {
		$this->_available = true;
		//$this->_available = !empty($this->getVersion());
	}
	
	public function getVersion() {
		$ret = $this->_doCall('GET', CuckooAPI::URL_CUCKOO_STATUS, array());
		if (isset($ret) && isset($ret->version)) {
			return $ret->version;
		}
		return '';
	}
	
	public function getInfos() {
		return $this->_doCall('GET', CuckooAPI::URL_CUCKOO_STATUS, array());
	}
	
	public function getReportUrl($id) {
		return CuckooAPI::URL_WEB_BASIS . CuckooAPI::URL_VIEW_URL . '/' . $id;
	}
	
	public function getBrowseUrl() {
		return CuckooAPI::URL_WEB_BASIS . CuckooAPI::URL_WEB_BROWSE;
	}
	
	public function getIdFromUrl($url) {
		if (strpos($url, 'http') === false)
			return $url;
		else
			return substr(strrchr($url, "/"), 1);
	}
	
	/**
	 * Send and scan a file.
	 *
	 * @param string $filePath A relative path to the file.
	 * @return object An object containing the scan ID for getting the report later:
	 */
	public function scanFile($filePath) {		
		if (!file_exists($filePath)) {
			return array(
					'response_code' => -4
				);
		}
		
		$realPath = realpath($filePath);

		if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
			$pathInfo = pathinfo($realPath);
			$fileName = $pathInfo['basename'];
		
			$parameters = array(
					'file' => '@' . $realPath . ';' .
					'type=' . mime_content_type($filePath) . ';' .
					'filename=' . $fileName
				);
		} else {
			// Due to a bug in some older curl versions
			// we only send the file without mime type or file name.
			$parameters = array(
					'file' => '@' . $realPath
				);
		}
		return $this->_doPost(CuckooAPI::URL_CREATE_TASK_FILE, $parameters);
	}
	
	public function scanUrl($url) {
	
		$parameters = array(
				'url' => $url
			);
		
		return $this->_doPost(CuckooAPI::URL_CREATE_TASK_URL, $parameters);
	}
	
	public function getTasks() {		
		return $this->_doGet(CuckooAPI::URL_TASKS_LIST);
	}
	
	public function getTask($id) {		
		$ret = $this->_doGet(CuckooAPI::URL_TASK_VIEW . '/' . $this->getIdFromUrl($id));
		if (is_array($ret) && isset($ret['response_code']) && $ret['response_code'] == '-3' && !empty($this->getVersion())){
			return array(
				'response_code' => -4		// === remove me
			);		
		}
		return $ret;
	}
	
	public function deleteTask($id) {		
		return $this->_doGet(CuckooAPI::URL_TASK_DELETE . '/' . $this->getIdFromUrl($id));
	}
	
	public function getReport($id) {
		return $this->_doGet(CuckooAPI::URL_TASK_REPORT . '/' . $this->getIdFromUrl($id) . '/json');
	}
	
	public function getScrenshots($id) {		
		return $this->_doGet(CuckooAPI::URL_TASK_SCREEN . '/' . $this->getIdFromUrl($id));
	}
	
	public function getFileReport($md5hash) {		
		return $this->_doGet(CuckooAPI::URL_FILE_VIEW . '/md5/' . $md5hash);
	}
	
	public function getFile($sha256hash) {		
		return $this->_doGet(CuckooAPI::URL_FILE_CONTENT . '/' . $sha256hash);
	}
	
	private function _doPost($apiTarget, $parameters) {
		if (!$this->_available){
			return array(
				'response_code' => -3
			);
		}			
		return $this->_doCall('POST', $apiTarget, $parameters);
	}
	
	private function _doGet($apiTarget) {
		if (!$this->_available){
			return array(
				'response_code' => -3
			);
		}
		return $this->_doCall('GET', $apiTarget, array());
	}
	
	private function _doCall($method, $apiTarget, $parameters) {
		$postFields = array();
		$postFields = array_merge($parameters, $postFields);

		$ch = curl_init(CuckooAPI::URL_API_BASIS . $apiTarget);
		@curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		@curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		@curl_setopt($ch, CURLOPT_VERBOSE, 0);
		
		if ($method == 'POST') {
			@curl_setopt($ch, CURLOPT_POST, true);
			@curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
		}
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
		curl_close($ch);

		if ($httpCode == '429') {
			return array(
					'response_code' => -3
				);
		} elseif ($httpCode == '403') {
			return array(
					'response_code' => -3
				);
		} elseif ($httpCode == '404') {
			return array(
					'response_code' => -3
				);
		} else
			return json_decode($response);
	}
}
?>