<?php
//for translation only
if (false) {
_("Findme Follow Toggle");
}

global $db;
global $amp_conf;
global $astman;

if (! function_exists("out")) {
	function out($text) {
		echo $text."<br />";
	}
}

if (! function_exists("outn")) {
	function outn($text) {
		echo $text;
	}
}

$sql = "
CREATE TABLE IF NOT EXISTS `findmefollow` 
( 
	`grpnum` VARCHAR( 20 ) NOT NULL , 
	`strategy` VARCHAR( 50 ) NOT NULL , 
	`grptime` SMALLINT NOT NULL , 
	`grppre` VARCHAR( 100 ) NULL , 
	`grplist` VARCHAR( 255 ) NOT NULL , 
	`annmsg_id` INTEGER,
	`postdest` VARCHAR( 255 ) NULL , 
	`dring` VARCHAR ( 255 ) NULL , 
	`remotealert_id` INTEGER,
	`needsconf` VARCHAR ( 10 ), 
	`toolate_id` INTEGER,
	`pre_ring` SMALLINT NOT NULL DEFAULT 0, 
	PRIMARY KEY  (`grpnum`) 
)
";
$check = $db->query($sql);
if(DB::IsError($check)) {
	die_freepbx("Can not create annoucment table");
}

//TODO: Also need to create all the states if enabled

$fcc = new featurecode('findmefollow', 'fmf_toggle');
$fcc->setDescription('Findme Follow Toggle'); 
$fcc->setDefault('*21');
$fcc->update();
unset($fcc);

// Adding support for a pre_ring before follow-me group
$sql = "SELECT pre_ring FROM findmefollow";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
    $sql = "ALTER TABLE findmefollow ADD pre_ring SMALLINT( 6 ) NOT NULL DEFAULT 0 ;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
}
// If there is no needsconf then this is a really old upgrade. We create the 2 old fields
// here  and then the migration code below will change them as needed but will work properly
// since it now has the fields it is expecting
//
$sql = "SELECT needsconf FROM findmefollow";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
    $sql = "ALTER TABLE findmefollow ADD remotealert VARCHAR( 80 ) NULL ;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }

    $sql = "ALTER TABLE findmefollow ADD needsconf VARCHAR( 10 ) NULL ;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }

    $sql = "ALTER TABLE findmefollow ADD toolate VARCHAR( 80 ) NULL ;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
}
// Version 2.1 upgrade. Add support for ${DIALOPTS} override, playing MOH
$sql = "SELECT ringing FROM findmefollow";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
    $sql = "ALTER TABLE findmefollow ADD ringing VARCHAR( 80 ) NULL ;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_freepbx($result->getDebugInfo()); }
}
// increase size for older installs, ignore sqlite3, doesn't support ALTER...CHANGE syntax, table created properly above
if($amp_conf["AMPDBENGINE"] != "sqlite3")  {
	$db->query("ALTER TABLE findmefollow CHANGE dring dring VARCHAR( 255 ) NULL");
}
$results = array();
$sql = "SELECT grpnum, postdest FROM findmefollow";
$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
if (!DB::IsError($results)) { // error - table must not be there
	foreach ($results as $result) {
		$old_dest  = $result['postdest'];
		$grpnum    = $result['grpnum'];

		$new_dest = merge_ext_followme(trim($old_dest));
		if ($new_dest != $old_dest) {
			$sql = "UPDATE findmefollow SET postdest = '$new_dest' WHERE grpnum = '$grpnum'  AND postdest = '$old_dest'";
			$results = $db->query($sql);
			if(DB::IsError($results)) {
				die_freepbx($results->getMessage());
			}
		}
	}
}

// this function builds the AMPUSER/<grpnum>/followme tree for each user who has a group number
// it's purpose is to convert after an upgrade


// TODO, is this needed...?
// is this global...? what if we include this files
// from a function...?

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
		$ddial = $astman->database_get("AMPUSER",$grpnum."/followme/ddial");                                     
		$ddial = ($ddial == 'EXTENSION' || $ddial == 'DIRECT')?$ddial:'DIRECT';
		$astman->database_put("AMPUSER",$grpnum."/followme/ddial",$ddial);
	}	
} else {
	echo _("Cannot connect to Asterisk Manager with ").$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"];
}

// Version 2.4.13 change (#1961)
// Ignore sqlite3, doesn't support ALTER...CHANGE syntax, table created properly above
if($amp_conf["AMPDBENGINE"] != "sqlite3")  {
	$sql = "ALTER TABLE `findmefollow` CHANGE `grpnum` `grpnum` VARCHAR( 20 ) NOT NULL";
	$results = $db->query($sql);
	if(DB::IsError($results)) {
		echo $results->getMessage();
		return false;
	}
}

// Version 2.5 migrate to recording ids
//
// Do not do upgrades for sqlite3.  Assume full support begins in 2.5 and our CREATE syntax is correct
if($amp_conf["AMPDBENGINE"] != "sqlite3") {
	outn(_("Checking if recordings need migration.."));
	$sql = "SELECT annmsg_id FROM findmefollow";
	$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($check)) {
		//  Add recording_id field
		//
		out(_("migrating"));	
		outn(_("adding annmsg_id field.."));
  	$sql = "ALTER TABLE findmefollow ADD annmsg_id INTEGER";
  	$result = $db->query($sql);
  	if(DB::IsError($result)) {
			out(_("fatal error"));
			die_freepbx($result->getDebugInfo()); 	
		} else {
			out(_("ok"));
		}
		outn(_("adding remotealert_id field.."));
  	$sql = "ALTER TABLE findmefollow ADD remotealert_id INTEGER";
  	$result = $db->query($sql);
  	if(DB::IsError($result)) {
			out(_("fatal error"));
			die_freepbx($result->getDebugInfo()); 
		} else {
			out(_("ok"));
		}
		outn(_("adding toolate_id field.."));
 	 	$sql = "ALTER TABLE findmefollow ADD toolate_id INTEGER";
		  $result = $db->query($sql);
 		 if(DB::IsError($result)) {
				out(_("fatal error"));
				die_freepbx($result->getDebugInfo()); 
			} else {
				out(_("ok"));
			}

		// Get all the valudes and replace them with recording_id
		//
		outn(_("migrate annmsg to ids.."));
 	 $sql = "SELECT `grpnum`, `annmsg` FROM `findmefollow`";
		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if(DB::IsError($results)) {
			out(_("fatal error"));
			die_freepbx($results->getDebugInfo());	
		}
		$migrate_arr = array();
		$count = 0;
		foreach ($results as $row) {
			if (trim($row['annmsg']) != '') {
				$rec_id = recordings_get_or_create_id($row['annmsg'], 'findmefollow');
				$migrate_arr[] = array($rec_id, $row['grpnum']);
				$count++;
			}
		}
		if ($count) {
			$compiled = $db->prepare('UPDATE `findmefollow` SET `annmsg_id` = ? WHERE `grpnum` = ?');
			$result = $db->executeMultiple($compiled,$migrate_arr);
			if(DB::IsError($result)) {
				out(_("fatal error"));
				die_freepbx($result->getDebugInfo());	
			}
		}
		out(sprintf(_("migrated %s entries"),$count));

		outn(_("migrate remotealert to ids.."));
  	$sql = "SELECT `grpnum`, `remotealert` FROM `findmefollow`";
		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if(DB::IsError($results)) {
			out(_("fatal error"));
			die_freepbx($results->getDebugInfo());	
		}
		$migrate_arr = array();
		$count = 0;
		foreach ($results as $row) {
			if (trim($row['remotealert']) != '') {
				$rec_id = recordings_get_or_create_id($row['remotealert'], 'findmefollow');
				$migrate_arr[] = array($rec_id, $row['grpnum']);
				$count++;
			}
		}
		if ($count) {
			$compiled = $db->prepare('UPDATE `findmefollow` SET `remotealert_id` = ? WHERE `grpnum` = ?');
			$result = $db->executeMultiple($compiled,$migrate_arr);
			if(DB::IsError($result)) {
				out(_("fatal error"));
				die_freepbx($result->getDebugInfo());	
			}
		}
		out(sprintf(_("migrated %s entries"),$count));	

		outn(_("migrate toolate to  ids.."));
 	 $sql = "SELECT `grpnum`, `toolate` FROM `findmefollow`";
		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if(DB::IsError($results)) {
			out(_("fatal error"));
			die_freepbx($results->getDebugInfo());	
		}
		$migrate_arr = array();
		$count = 0;
		foreach ($results as $row) {
			if (trim($row['toolate']) != '') {
				$rec_id = recordings_get_or_create_id($row['toolate'], 'findmefollow');
				$migrate_arr[] = array($rec_id, $row['grpnum']);
				$count++;
			}
		}
		if ($count) {
			$compiled = $db->prepare('UPDATE `findmefollow` SET `toolate_id` = ? WHERE `grpnum` = ?');
			$result = $db->executeMultiple($compiled,$migrate_arr);
			if(DB::IsError($result)) {
				out(_("fatal error"));
				die_freepbx($result->getDebugInfo());	
			}
		}
		out(sprintf(_("migrated %s entries"),$count));

		// Now remove the old recording field replaced by new id field
		//
		outn(_("dropping annmsg field.."));
  	$sql = "ALTER TABLE `findmefollow` DROP `annmsg`";
  	$result = $db->query($sql);
  	if(DB::IsError($result)) { 
			out(_("no annmsg field???"));
		} else {
			out(_("ok"));
		}
		outn(_("dropping remotealert field.."));
	  $sql = "ALTER TABLE `findmefollow` DROP `remotealert`";
 	 $result = $db->query($sql);
  	if(DB::IsError($result)) { 
			out(_("no remotealert field???"));
		} else {
			out(_("ok"));
		}
		outn(_("dropping toolate field.."));
 	 $sql = "ALTER TABLE `findmefollow` DROP `toolate`";
  	$result = $db->query($sql);
  	if(DB::IsError($result)) { 
			out(_("no toolate field???"));
		} else {
			out(_("ok"));
		}

	} else {
		out(_("already migrated"));
	}
}

?>
