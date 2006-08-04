<?php /* $Id: functions.inc.php 32 2006-04-11 02:51:19Z abrown $ */

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
	
	return $extens;
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
                                                $ext->add($contextname, $grpnum, '', new ext_setvar("_ALERT_INFO", $dring));
                                        }

					// recording stuff
					$ext->add($contextname, $grpnum, '', new ext_setvar('RecordMethod','Group'));
					$ext->add($contextname, $grpnum, '', new ext_macro('record-enable','${MACRO_EXTEN},${RecordMethod}'));

					// group dial
					$ext->add($contextname, $grpnum, '', new ext_setvar('RingGroupMethod',$strategy));
					if ((isset($annmsg) ? $annmsg : '') != '') {
						// should always answer before playing anything, shouldn't we ?
						$ext->add($contextname, $grpnum, '', new ext_gotoif('$["${DIALSTATUS}" = "ANSWER"]','DIALGRP'));			
						$ext->add($contextname, $grpnum, '', new ext_answer(''));
						$ext->add($contextname, $grpnum, '', new ext_wait(1));
						$ext->add($contextname, $grpnum, '', new ext_playback($annmsg));
					}





					if ($needsconf == "CHECKED") {
						$len=strlen($grpnum)+4;
						$ext->add("grps", "_RG-${grpnum}-.", '', new ext_macro('dial',$grptime.
							",M(confirm^${remotealert}^${toolate}^${grpnum})$dialopts".',${EXTEN:'.$len.'}'));
						$ext->add($contextname, $grpnum, 'DIALGRP', new ext_macro('dial-confirm',"$grptime,$dialopts,$grplist,$grpnum"));
					} else {
						$ext->add($contextname, $grpnum, 'DIALGRP', new ext_macro('dial',$grptime.",$dialopts,".$grplist));
					}
					$ext->add($contextname, $grpnum, '', new ext_setvar('RingGroupMethod',''));

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

function findmefollow_add($grpnum,$strategy,$grptime,$grplist,$postdest,$grppre='',$annmsg='',$dring,$needsconf,$remotealert,$toolate,$ringing) {
	$sql = "INSERT INTO findmefollow (grpnum, strategy, grptime, grppre, grplist, annmsg, postdest, dring, needsconf, remotealert, toolate, ringing) VALUES (".$grpnum.", '".str_replace("'", "''", $strategy)."', ".str_replace("'", "''", $grptime).", '".str_replace("'", "''", $grppre)."', '".str_replace("'", "''", $grplist)."', '".str_replace("'", "''", $annmsg)."', '".str_replace("'", "''", $postdest)."', '".str_replace("'", "''", $dring)."', '$needsconf', '$remotealert', '$toolate', '$ringing')";
	$results = sql($sql);
}

function findmefollow_del($grpnum) {
	$results = sql("DELETE FROM findmefollow WHERE grpnum = $grpnum","query");
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

function findmefollow_get($grpnum) {
	$results = sql("SELECT grpnum, strategy, grptime, grppre, grplist, annmsg, postdest, dring, needsconf, remotealert, toolate, ringing FROM findmefollow WHERE grpnum = $grpnum","getRow",DB_FETCHMODE_ASSOC);
	return $results;
}

function findmefollow_hook_core($viewing_itemid, $target_menuid) {
	if ($viewing_itemid != "" & ($target_menuid == 'extensions' | $target_menuid == 'users')) { 
		$set_findmefollow = findmefollow_list();
		$grpURL = $_SERVER['PHP_SELF'].'?'.'display=findmefollow&extdisplay=GRP-'.$viewing_itemid;
		if (is_array($set_findmefollow)) {
			$grpTEXT = (in_array($viewing_itemid,$set_findmefollow) ? "Edit" : "Add")." Follow Me Settings";
		} else {
			$grpTEXT = "Add Follow Me Settings";
		}
		return "<p><a href=\"".$grpURL.'">'.$grpTEXT."</a></p>";
	}
}

?>
