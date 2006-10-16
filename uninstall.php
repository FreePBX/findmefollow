<?php

require_once('common/php-asmanager.php');

// Delete all the followme trees. This function selects from the users table
// and not the findmefollow table because the uninstall code deletes the tables
// prior to running the uninstall script. (probably should be the opposite but...)
// It is probably better this way anyhow, as there is no harm done if the user
// has not followme settings and who knows ... maybe some stray ones got left
// behind somehow.
// 

checkAstMan();
global $amp_conf;
$sql = "SELECT * FROM users";
$userresults = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
	
//add details to astdb
$astman = new AGI_AsteriskManager();
if ($res = $astman->connect("127.0.0.1", $amp_conf["AMPMGRUSER"] , $amp_conf["AMPMGRPASS"])) {
	foreach($userresults as $usr) {

		extract($usr);

		$astman->database_del("AMPUSER",$extension."/followme/prering");
		$astman->database_del("AMPUSER",$extension."/followme/grptime");
		$astman->database_del("AMPUSER",$extension."/followme/grplist");
		$astman->database_del("AMPUSER",$extension."/followme/grpconf");
	}	
} else {
	echo _("Cannot connect to Asterisk Manager with ").$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"];
}
$astman->disconnect();

?>
