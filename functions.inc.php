<?php /* $Id: functions.inc.php 175 2006-10-03 19:12:39Z plindheimer $ */

// The destinations this module provides
// returns a associative arrays with keys 'destination' and 'description'
function findmefollow_destinations() {
	//get the list of findmefollow
	$results = findmefollow_full_list();
	
	// return an associative array with destination and description
	if (isset($results)) {
		foreach($results as $result){
				$thisgrp = findmefollow_get(ltrim($result['0']));
				$extens[] = array('destination' => 'ext-findmefollow,'.ltrim($result['0']).',1', 'description' => $thisgrp['grppre'].' <'.ltrim($result['0']).'>');
		}
	}
	
	return isset($extens)?$extens:null;
}

/* 	Generates dialplan for findmefollow
	We call this with retrieve_conf
*/
function findmefollow_get_config($engine) {
	global $ext;  // is this the best way to pass this?
	switch($engine) {
		case "asterisk":
			$ext->addInclude('from-internal-additional','ext-findmefollow');
			$contextname = 'ext-findmefollow';
			$ringlist = findmefollow_full_list();
			if (is_array($ringlist)) {
				foreach($ringlist as $item) {
					$grpnum = ltrim($item['0']);
					$grp = findmefollow_get($grpnum);
					
					$strategy = $grp['strategy'];
					$grptime = $grp['grptime'];
					$grplist = $grp['grplist'];
					$postdest = $grp['postdest'];
					$grppre = (isset($grp['grppre'])?$grp['grppre']:'');
					$annmsg = $grp['annmsg'];
					$dring = $grp['dring'];

					$needsconf = $grp['needsconf'];
					$remotealert = $grp['remotealert'];
					$toolate = $grp['toolate'];
					$ringing = $grp['ringing'];
					$pre_ring = $grp['pre_ring'];

					if($ringing == 'Ring' || empty($ringing) ) {
						$dialopts = '${DIAL_OPTIONS}';
					} else {
						// We need the DIAL_OPTIONS variable
						$sops = sql("SELECT value from globals where variable='DIAL_OPTIONS'", "getRow");
						$dialopts = "m(${ringing})".str_replace('r', '', $sops[0]);
					}


					$ext->add($contextname, $grpnum, '', new ext_macro('user-callerid'));

					// deal with group CID prefix
					$ext->add($contextname, $grpnum, '', new ext_gotoif('$["foo${RGPREFIX}" = "foo"]', 'REPCID'));
					$ext->add($contextname, $grpnum, '', new ext_noop('Current RGPREFIX is ${RGPREFIX}....stripping from Caller ID'));
					$ext->add($contextname, $grpnum, '', new ext_setvar('CALLERID(name)', '${CALLERID(name):${LEN(${RGPREFIX})}}'));
					$ext->add($contextname, $grpnum, '', new ext_setvar('RGPREFIX', ''));
					$ext->add($contextname, $grpnum, 'REPCID', new ext_noop('CALLERID(name) is ${CALLERID(name)}'));
					if ($grppre != '') {
						$ext->add($contextname, $grpnum, '', new ext_setvar('RGPREFIX', $grppre));
						$ext->add($contextname, $grpnum, '', new ext_setvar('CALLERID(name)','${RGPREFIX}${CALLERID(name)}'));
					}

					// MODIFIED (PL)
					// Add Alert Info if set
					//
					if ((isset($dring) ? $dring : '') != '') {
						$ext->add($contextname, $grpnum, '', new ext_setvar("_ALERT_INFO", str_replace(';', '\;', $dring)));
					}
					// If pre_ring is set, then ring this number of seconds prior to moving on
					$ext->add($contextname, $grpnum, '', new ext_gotoif('$[$[ "${DB(AMPUSER/'.$grpnum.'/followme/prering)}" = "0" ] | $[ "${DB(AMPUSER/'.$grpnum.'/followme/prering)}" = "" ]] ', 'skipsimple'));
					$ext->add($contextname, $grpnum, '', new ext_macro('simple-dial',$grpnum.',${DB(AMPUSER/'."$grpnum/followme/prering)}"));

					// recording stuff
					$ext->add($contextname, $grpnum, 'skipsimple', new ext_setvar('RecordMethod','Group'));
					$ext->add($contextname, $grpnum, '', new ext_macro('record-enable','${DB(AMPUSER/'."$grpnum/followme/grplist)}".',${RecordMethod}'));

					// group dial
					$ext->add($contextname, $grpnum, '', new ext_setvar('RingGroupMethod',$strategy));
					if ((isset($annmsg) ? $annmsg : '') != '') {
						// should always answer before playing anything, shouldn't we ?
						$ext->add($contextname, $grpnum, '', new ext_gotoif('$["${DIALSTATUS}" = "ANSWER"]','DIALGRP'));			
						$ext->add($contextname, $grpnum, '', new ext_answer(''));
						$ext->add($contextname, $grpnum, '', new ext_wait(1));
						$ext->add($contextname, $grpnum, '', new ext_playback($annmsg));
					}

					// Create the confirm target
					$len=strlen($grpnum)+4;
					$ext->add("grps", "_RG-${grpnum}-.", '', new ext_macro('dial','${DB(AMPUSER/'."$grpnum/followme/grptime)},".
						"M(confirm^${remotealert}^${toolate}^${grpnum})$dialopts".',${EXTEN:'.$len.'}'));

					// If grpconf == ENABLED call with confirmation ELSE call normal
					$ext->add($contextname, $grpnum, 'DIALGRP', new 
					    ext_gotoif('$[ "${DB(AMPUSER/'.$grpnum.'/followme/grpconf)}" = "ENABLED" ]', 'doconfirm'));

					// Normal call
					$ext->add($contextname, $grpnum, '', new 
					    ext_macro('dial','${DB(AMPUSER/'."$grpnum/followme/grptime)},$dialopts,".'${DB(AMPUSER/'."$grpnum/followme/grplist)}"));
					$ext->add($contextname, $grpnum, '', new ext_goto('nextstep'));

					// Call Confirm call
					$ext->add($contextname, $grpnum, 'doconfirm', new 
					    ext_macro('dial-confirm','${DB(AMPUSER/'."$grpnum/followme/grptime)},$dialopts,".'${DB(AMPUSER/'."$grpnum/followme/grplist)},".$grpnum));

					$ext->add($contextname, $grpnum, 'nextstep', new ext_setvar('RingGroupMethod',''));

					// where next?
					if ((isset($postdest) ? $postdest : '') != '') {
						$ext->add($contextname, $grpnum, '', new ext_goto($postdest));
					} else {
						$ext->add($contextname, $grpnum, '', new ext_hangup(''));
					}
				}
			}
		break;
	}
}

function findmefollow_add($grpnum,$strategy,$grptime,$grplist,$postdest,$grppre='',$annmsg='',$dring,$needsconf,$remotealert,$toolate,$ringing,$pre_ring) {
	global $amp_conf;
	global $astman;

	$sql = "INSERT INTO findmefollow (grpnum, strategy, grptime, grppre, grplist, annmsg, postdest, dring, needsconf, remotealert, toolate, ringing, pre_ring) VALUES (".$grpnum.", '".str_replace("'", "''", $strategy)."', ".str_replace("'", "''", $grptime).", '".str_replace("'", "''", $grppre)."', '".str_replace("'", "''", $grplist)."', '".str_replace("'", "''", $annmsg)."', '".str_replace("'", "''", $postdest)."', '".str_replace("'", "''", $dring)."', '$needsconf', '$remotealert', '$toolate', '$ringing', '$pre_ring')";
	$results = sql($sql);

	if ($astman) {
		$astman->database_put("AMPUSER",$grpnum."/followme/prering",isset($pre_ring)?$pre_ring:'');
		$astman->database_put("AMPUSER",$grpnum."/followme/grptime",isset($grptime)?$grptime:'');
		$astman->database_put("AMPUSER",$grpnum."/followme/grplist",isset($grplist)?$grplist:'');
		$confvalue = ($needsconf == 'CHECKED')?'ENABLED':'DISABLED';
		$astman->database_put("AMPUSER",$grpnum."/followme/grpconf",isset($needsconf)?$confvalue:'');
	} else {
		fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
	}
}

function findmefollow_del($grpnum) {
	global $amp_conf;
	global $astman;

	$results = sql("DELETE FROM findmefollow WHERE grpnum = $grpnum","query");

	if ($astman) {
		$astman->database_del("AMPUSER",$grpnum."/followme/prering");
		$astman->database_del("AMPUSER",$grpnum."/followme/grptime");
		$astman->database_del("AMPUSER",$grpnum."/followme/grplist");
		$astman->database_del("AMPUSER",$grpnum."/followme/grpconf");
	} else {
		fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
	}
}

function findmefollow_full_list() {
	$results = sql("SELECT grpnum FROM findmefollow ORDER BY grpnum","getAll",DB_FETCHMODE_ASSOC);
	foreach ($results as $result) {
		if (isset($result['grpnum']) && checkRange($result['grpnum'])) {
			$grps[] = array($result['grpnum']);
		}
	}
	if (isset($grps))
		return $grps;
	else
		return null;
}

function findmefollow_list() {

        global $db;
        $sql = "SELECT grpnum FROM findmefollow ORDER BY grpnum";
        $results = $db->getCol($sql);
        if(DB::IsError($results)) {
                $results = null;
        }
        if (isset($results)) {
		foreach($results as $result) {
			if (checkRange($result)){
				$grps[] = $result;
			}
		}
        }
        if (isset($grps)) {
		sort($grps); // hmm, should be sorted already
        	return $grps;
	}
	else {
		return null;
	}
}

// This gets the list of all active users so that the Find Me Follow display can limit the options to only created users.
// the returned arrays contain [0]:extension [1]:name
//
// This was pulled straight out of previous 1.x version, might need cleanup laster
//
function findmefollow_allusers() {
        global $db;
        $sql = "SELECT extension,name FROM users ORDER BY extension";
        $results = $db->getAll($sql);
        if(DB::IsError($results)) {
                $results = null;
        }
        foreach($results as $result){
                if (checkRange($result[0])){
                        $users[] = array($result[0],$result[1]);
                }
        }
        if (isset($users)) sort($users);
        return $users;
}

// Only check astdb if check_astdb is not 0. For some reason, this fails if the asterisk manager code
// is included (executed) by all calls to this function. This results in silently not generating the
// extensions_additional.conf file. page.findmefollow.php does set it to 1 which means that when running
// the GUI, any changes not reflected in SQL will be detected and written back to SQL so that they are
// in sync. Ideally, anything that changes the astdb should change SQL. (in some ways, these should both
// not be here but ...
//
function findmefollow_get($grpnum, $check_astdb=0) {
	global $amp_conf;
	global $astman;

	$results = sql("SELECT grpnum, strategy, grptime, grppre, grplist, annmsg, postdest, dring, needsconf, remotealert, toolate, ringing, pre_ring FROM findmefollow WHERE grpnum = $grpnum","getRow",DB_FETCHMODE_ASSOC);

	if ($check_astdb) {
		if ($astman) {
			$astdb_prering = $astman->database_get("AMPUSER",$grpnum."/followme/prering");
			$astdb_grptime = $astman->database_get("AMPUSER",$grpnum."/followme/grptime");
			$astdb_grplist = $astman->database_get("AMPUSER",$grpnum."/followme/grplist");
			$astdb_grpconf = $astman->database_get("AMPUSER",$grpnum."/followme/grpconf");
		} else {
			fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
		}
		// If the values are different then use what is in astdb as it may have been changed.
		//
		$changed=0;
		if (($astdb_prering != $results['pre_ring']) && ($astdb_prering >= 0)) {
			$results['pre_ring'] = $astdb_prering;
			$changed=1;
		}
		if (($astdb_grptime != $results['grptime']) && ($astdb_grptime > 0)) {
			$results['grptime'] = $astdb_grptime;
			$changed=1;
		}
		if ((trim($astdb_grplist) != trim($results['grplist'])) && (trim($astdb_grplist) != '')) {
			$results['grplist'] = $astdb_grplist;
			$changed=1;
		}

		if (trim($astdb_grpconf) == 'ENABLED') {
			$confvalue = 'CHECKED';
		} elseif (trim($astdb_grpconf) == 'DISABLED') {
			$confvalue = '';
		} else {
			//Bogus value, should not get here but treat as disabled
			$confvalue = '';
		}
		if ($confvalue != trim($results['needsconf'])) {
			$results['needsconf'] = $confvalue;
			$changed=1;
		}

		if ($changed) {
			$sql = "UPDATE findmefollow SET grptime = '".$results['grptime']."', grplist = '".
				str_replace("'", "''", trim($results['grplist']))."', pre_ring = '".$results['pre_ring'].
				"', needsconf = '".$results['needsconf']."' WHERE grpnum = $grpnum LIMIT 1";
			$sql_results = sql($sql);
		}
	} // if check_astdb

	return $results;
}

function findmefollow_configpageinit($dispnum) {
	global $currentcomponent;

	if ( ($dispnum == 'users' || $dispnum == 'extensions') ) {
		$currentcomponent->addguifunc('findmefollow_configpageload');
	}
}

function findmefollow_configpageload() {
	global $currentcomponent;

	$viewing_itemid =  isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$action =  isset($_REQUEST['action'])?$_REQUEST['action']:null;
	if ( $viewing_itemid != '' && $action != 'del') {
		$set_findmefollow = findmefollow_list();
		$grpURL = $_SERVER['PHP_SELF'].'?'.'display=findmefollow&extdisplay=GRP-'.$viewing_itemid;
		if (is_array($set_findmefollow)) {
			$grpTEXT = (in_array($viewing_itemid,$set_findmefollow) ? "Edit" : "Add")." Follow Me Settings";
		} else {
			$grpTEXT = "Add Follow Me Settings";
		}
		$currentcomponent->addguielem('_top', new gui_link('findmefollowlink', $grpTEXT, $grpURL));
	}	
}

?>
