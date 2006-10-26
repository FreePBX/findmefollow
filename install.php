<?php

global $db;

// Adding support for a pre_ring before follow-me group
$sql = "SELECT pre_ring FROM findmefollow";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
    $sql = "ALTER TABLE findmefollow ADD pre_ring SMALLINT( 6 ) NOT NULL DEFAULT 0 ;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die($result->getDebugInfo()); }
}
// Version 2.0 upgrade. Yeah. 2.0 baby! 
$sql = "SELECT remotealert FROM findmefollow";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
    $sql = "ALTER TABLE findmefollow ADD remotealert VARCHAR( 80 ) NULL ;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die($result->getDebugInfo()); }

    $sql = "ALTER TABLE findmefollow ADD needsconf VARCHAR( 10 ) NULL ;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die($result->getDebugInfo()); }

    $sql = "ALTER TABLE findmefollow ADD toolate VARCHAR( 80 ) NULL ;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die($result->getDebugInfo()); }
}
// Version 2.1 upgrade. Add support for ${DIALOPTS} override, playing MOH
$sql = "SELECT ringing FROM findmefollow";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
    $sql = "ALTER TABLE findmefollow ADD ringing VARCHAR( 80 ) NULL ;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die($result->getDebugInfo()); }
}

// this function builds the AMPUSER/<grpnum>/followme tree for each user who has a group number
// it's purpose is to convert after an upgrade


// TODO, is this needed...?
// is this global...? what if we include this files
// from a function...?
global $astman;
global $amp_conf;

$sql = "SELECT * FROM findmefollow";
$userresults = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
	
//add details to astdb
if ($astman) {
	foreach($userresults as $usr) {
		extract($usr);

		$astman->database_put("AMPUSER",$grpnum."/followme/prering",isset($pre_ring)?$pre_ring:'');
		$astman->database_put("AMPUSER",$grpnum."/followme/grptime",isset($grptime)?$grptime:'');
		$astman->database_put("AMPUSER",$grpnum."/followme/grplist",isset($grplist)?$grplist:'');
		$confvalue = ($needsconf == 'CHECKED')?'ENABLED':'DISABLED';
		$astman->database_put("AMPUSER",$grpnum."/followme/grpconf",isset($needsconf)?$confvalue:'');
	}	
} else {
	echo _("Cannot connect to Asterisk Manager with ").$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"];
}

?>
