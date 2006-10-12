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


?>
