<?php
$max_upload = (int)(ini_get('upload_max_filesize'));
$max_post = (int)(ini_get('post_max_size'));
$memory_limit = (int)(ini_get('memory_limit'));
$upload_mb = min($max_upload, $max_post, $memory_limit);

echo 'Max upload:' . $max_upload . "<br/>";
echo 'Max post:' . $max_post . "<br/>";
echo 'Memory limit:' . $memory_limit . "<br/>";
echo 'Max upload (effective):' . $upload_mb . "<br/>";
phpinfo();
?>