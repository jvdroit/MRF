<?php
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

error_reporting(E_ALL | E_STRICT);
//error_reporting(0);
require('UploadHandler.php');


$storage = 'YOUR_STORAGE_FOLDER_FULL_PATH_HERE';
$options = array ('upload_dir' => $storage, 'upload_url' => $storage );
$upload_handler = new UploadHandler($options);
