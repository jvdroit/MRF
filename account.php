<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
require_once("models/header.php");

echo "
<body>
<div id='wrapper'>
<div id='top'><div id='logo'></div></div>
<div id='content'>
<h1>UserCake</h1>
<h2>Account</h2>
<div id='left-nav'>";

include("left-nav.php");

echo "
</div>
<div id='main'>
<p>Hey, <b>$loggedInUser->displayname</b>. You registered this account on " . date("M d, Y", $loggedInUser->signupTimeStamp()) . ".</p>
<p>Title: <b>$loggedInUser->title</b></p>
<p>Your API token: <b>". $loggedInUser->activationtoken() ."</b></p>
</div>
<div id='bottom'></div>
</div>
</body>
</html>";

?>
