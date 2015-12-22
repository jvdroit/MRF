<?php
 
/*
    The important thing to realize is that the config file should be included in every
    page of your project, or at least any page you want access to these settings.
    This allows you to confidently use these settings throughout a project because
    if something changes such as your database credentials, or a path to a specific resource,
    you'll only need to update it here.
*/
 
$config = array(
    "db" => array(
        "storage" => array(
            "dbname" => "mrf",
            "username" => "root",
            "password" => "",
            "host" => "localhost"
        ),
        "usercake" => array(
            "dbname" => "mrf",
            "username" => "root",
            "password" => "",
            "host" => "localhost"
        )
    ),
    "urls" => array(
        "baseUrl" => "http://localhost/mrf/",
        "storagePath" => "/path/to/samples/storage",
        "storageUrl"  => "http://localhost/mrf/storage/"
    ),
    "cuckoo" => array(
        "enabled" => True,
        "api_base_url" => 'http://cuckoo.home:8090/',
        "web_base_url" => 'http://cuckoo.home:8080/'
    ),
    "virustotal" => array(
        "key" => 'YOUR_API_KEY',
        "automatic_upload" => True
    ),
    "ui" => array(
        "files_per_page" => 40
    ),
);

$GLOBALS["config"] = $config;
 
/*
    I will usually place the following in a bootstrap file or some type of environment
    setup file (code that is run at the start of every page request), but they work 
    just as well in your config file if it's in php (some alternatives to php are xml or ini files).
*/
 
/*
    Creating constants for heavily used paths makes things a lot easier.
    ex. require_once(LIBRARY_PATH . "Paginator.php")
*/
//defined("LIBRARY_PATH")
//    or define("LIBRARY_PATH", realpath(dirname(__FILE__) . '/library'));
     
//defined("TEMPLATES_PATH")
//    or define("TEMPLATES_PATH", realpath(dirname(__FILE__) . '/templates'));
 
/*
    Error reporting.
*/
ini_set("error_reporting", "true");
error_reporting(E_ALL|E_STRCT);
 
?>