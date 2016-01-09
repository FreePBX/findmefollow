<?php
namespace FreePBX\modules;
// vim: set ai ts=4 sw=4 ft=php:
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
class Findmefollow implements \BMO {

	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
		}

		$this->FreePBX = $freepbx;
		$this->db = $freepbx->Database;
	}

	public function doConfigPageInit($page) {
		global $amp_conf;
		$dispnum = 'findmefollow'; //used for switch on config.php
		$request = $_REQUEST;
		isset($request['action'])?$action = $request['action']:$action='';
		//the extension we are currently displaying
		isset($request['extdisplay'])?$extdisplay=$request['extdisplay']:$extdisplay='';
		isset($request['account'])?$account = $request['account']:$account='';
		isset($request['grptime'])?$grptime = $request['grptime']:$grptime=$amp_conf['FOLLOWME_TIME'];
		isset($request['grppre'])?$grppre = $request['grppre']:$grppre='';
		isset($request['strategy'])?$strategy = $request['strategy']:$strategy=$amp_conf['FOLLOWME_RG_STRATEGY'];
		isset($request['annmsg_id'])?$annmsg_id = $request['annmsg_id']:$annmsg_id='';
		isset($request['dring'])?$dring = $request['dring']:$dring='';
		isset($request['needsconf'])?$needsconf = $request['needsconf']:$needsconf='';
		isset($request['remotealert_id'])?$remotealert_id = $request['remotealert_id']:$remotealert_id='';
		isset($request['toolate_id'])?$toolate_id = $request['toolate_id']:$toolate_id='';
		isset($request['ringing'])?$ringing = $request['ringing']:$ringing='';
		isset($request['pre_ring'])?$pre_ring = $request['pre_ring']:$pre_ring=$amp_conf['FOLLOWME_PRERING'];
		isset($request['changecid'])?$changecid = $request['changecid']:$changecid='default';
		isset($request['fixedcid'])?$fixedcid = $request['fixedcid']:$fixedcid='';

		if (isset($request['ddial'])) {
			$ddial =	$request['ddial'];
		}	else {
			$ddial = isset($request['ddial_value']) ? $request['ddial_value'] : ($amp_conf['FOLLOWME_DISABLED'] ? 'CHECKED' : '');
		}

		if (isset($request['goto0']) && isset($request[$request['goto0']."0"])) {
			$goto = $request[$request['goto0']."0"];
		} else {
			$goto = "ext-local,$extdisplay,dest";
		}

		if (isset($request["grplist"])) {
			$grplist = explode("\n",$request["grplist"]);

			if (!$grplist) {
				$grplist = null;
			}

			foreach (array_keys($grplist) as $key) {
				//trim it
				$grplist[$key] = trim($grplist[$key]);

				// remove invalid chars
				$grplist[$key] = preg_replace("/[^0-9#*+]/", "", $grplist[$key]);

				if ($grplist[$key] == ltrim($extdisplay,'GRP-').'#')
					$grplist[$key] = rtrim($grplist[$key],'#');

				// remove blanks
				if ($grplist[$key] == "") unset($grplist[$key]);
			}

			// check for duplicates, and re-sequence
			$grplist = array_values(array_unique($grplist));
		}

		// do if we are submitting a form
		if(isset($request['action'])){
			//check if the extension is within range for this user
			if (isset($account) && !checkRange($account)){
				echo "<script>javascript:alert('". _("Warning! Extension")." ".$account." "._("is not allowed for your account").".');</script>";
			} else {
				//add group
				if ($action == 'addGRP') {
					findmefollow_add($account,$strategy,$grptime,implode("-",$grplist),$goto,$grppre,$annmsg_id,$dring,$needsconf,$remotealert_id,$toolate_id,$ringing,$pre_ring,$ddial,$changecid,$fixedcid);

					needreload();
					redirect_standard();
				}

				//del group
				if ($action == 'delGRP') {
					findmefollow_del($account);
					needreload();
					redirect_standard();
				}

				//edit group - just delete and then re-add the extension
				if ($action == 'edtGRP') {
					findmefollow_del($account);
					findmefollow_add($account,$strategy,$grptime,implode("-",$grplist),$goto,$grppre,$annmsg_id,$dring,$needsconf,$remotealert_id,$toolate_id,$ringing,$pre_ring,$ddial,$changecid,$fixedcid);

					needreload();
					redirect_standard('extdisplay', 'view');
				}
			}
		}

	}

	public function install() {

	}
	public function uninstall() {

	}
	public function backup(){

	}
	public function restore($backup){

	}
	public function genConfig() {

	}

	public function getQuickCreateDisplay() {
		$fm = $this->FreePBX->Config->get('FOLLOWME_AUTO_CREATE');
		if($fm) {
			return array();
		}
		return array(
			1 => array(
				array(
					'html' => load_view(__DIR__.'/views/quickCreate.php',array("fmfm" => !$this->FreePBX->Config->get('FOLLOWME_DISABLED')))
				)
			)
		);
	}

	/**
	 * Delete user function, it's run twice because of scemantics with
	 * old freepbx but it's harmless
	 * @param  string $extension The extension number
	 * @param  bool $editmode  If we are in edit mode or not
	 */
	public function delUser($extension, $editmode=false) {
		if(!$editmode) {
			if(!function_exists('findmefollow_destinations')) {
				$this->FreePBX->Modules->loadFunctionsInc('findmefollow');
			}
			findmefollow_del($extension);
		}
	}

	/**
	 * Quick Create hook
	 * @param string $tech      The device tech
	 * @param int $extension The extension number
	 * @param array $data      The associated data
	 */
	public function processQuickCreate($tech, $extension, $data) {
		if($this->FreePBX->Config->get('FOLLOWME_AUTO_CREATE')) {
			if(!function_exists('findmefollow_add')) {
				include __DIR__."/functions.inc.php";
			}
			$ddial = $this->FreePBX->Config->get('FOLLOWME_DISABLED') ? 'CHECKED' : '';
			findmefollow_add($extension, $this->FreePBX->Config->get('FOLLOWME_RG_STRATEGY'), $this->FreePBX->Config->get('FOLLOWME_TIME'),$extension, 'ext-local,'.$extension.',dest', "", "", "", "", "", "","", $this->FreePBX->Config->get('FOLLOWME_PRERING'), $ddial,'default','');
		} elseif(!empty($data['fmfm']) && $data['fmfm'] == "yes") {
			if(!function_exists('findmefollow_add')) {
				include __DIR__."/functions.inc.php";
			}
			findmefollow_add($extension, 'ringallv2', '10', $extension, 'ext-local,'.$extension.',dest', "", "", "", "", "", "","", '20', "",'default','');
		}
	}

	/*
	 * Gets Follow Me Confirmation Setting
	 *
	 * @param string $exten Extension to get information about
	 * @return bool True is confirmed, False is not
	 */
	function getConfirm($exten) {
		$response = $this->FreePBX->astman->database_get("AMPUSER","$exten/followme/grpconf");
		return preg_match("/ENABLED/",$response);
	}

	/*
	 * Sets Follow Confirmation Setting
	 *
	 * @param string $exten Extension to modify
	 * @param bool $follow_me_cofirm Follow Me Confirm Setting
	 */
	function setConfirm($exten,$follow_me_confirm) {
		$value = ($follow_me_confirm)?'ENABLED':'DISABLED';
		$this->FreePBX->astman->database_put('AMPUSER', "$exten/followme/grpconf", $value);
	}

	/*
	 * Sets Follow Me List
	 *
	 * @param $exten Extension to modify
	 * @param $follow_me_list Follow Me List
	 */
	function setList($exten,$follow_me_list) {
		foreach($follow_me_list as &$value) {
			$value = $this->lookupSetExtensionFormat($value);
		}
		$follow_me_list = implode("-",$follow_me_list);
		$this->FreePBX->astman->database_put('AMPUSER', "$exten/followme/grplist", $follow_me_list);
	}

	/**
	 * Lookup extension format
	 * This should be depreciated eventually
	 * @param {int} $exten The Phone Number
	 */
	function lookupSetExtensionFormat($exten) {
		if (trim($exten) == "") {
			return $exten;
		};

		$exten = preg_replace("/[^0-9*+]/", "", $exten);

		//TODO: Should be using core user function here instead of cheating.
		$sql = "SELECT extension FROM users WHERE extension = ?";
		$sth = $this->db->prepare($sql);
		$sth->execute(array($exten));
		$result = $sth->fetch(\PDO::FETCH_ASSOC);

		if (!is_array($result)) {
			return $exten.'#';
		} else {
			return $exten;
		}
	}

	/*
	 * Gets Follow Me List if set
	 *
	 * @param $exten Extension to get information about
	 * @return $data follow me list if set
	 */
	function getList($exten) {
		$response = $this->FreePBX->astman->database_get("AMPUSER","$exten/followme/grplist");
		return preg_replace("/[^0-9*\-+]/", "", $response);
	}

	/*
	 * Sets Follow Me List Ring Time
	 *
	 * @param $exten Extension to modify
	 * @param $follow_me_listring_time List Ring Time to ring
	 */
	function setListRingTime($exten,$follow_me_listring_time) {
		$this->FreePBX->astman->database_put('AMPUSER', "$exten/followme/grptime", $follow_me_listring_time);
	}

	/*
	 * Gets Follow Me List-Ring Time if set
	 *
	 * @param $exten Extension to get information about
	 * @return $number follow me list-ring time returned if set
	 */
	function getListRingTime($exten) {
		$response = $this->FreePBX->astman->database_get("AMPUSER","$exten/followme/grptime");
		return is_numeric($response) ? $response : '';
	}

	/*
	 * Sets Follow Me Pre-Ring Time
	 *
	 * @param $exten Extension to modify
	 * @param $follow_me_prering_time Pre-Ring Time to ring
	 */
	function setPreRingTime($exten,$follow_me_prering_time) {
		$this->FreePBX->astman->database_put('AMPUSER', "$exten/followme/prering", $follow_me_prering_time);
	}

	/*
	 * Gets Follow Me Pre-Ring Time if set
	 *
	 * @param $exten Extension to get information about
	 * @return $number follow me pre-ring time returned if set
	 */
	function getPreRingTime($exten) {
		$response = $this->FreePBX->astman->database_get("AMPUSER","$exten/followme/prering");
		return is_numeric($response) ? $response : '';
	}

	/*
	 * Sets Follow Ddial Setting
	 *
	 * @param $exten Extension to modify
	 * @param $follow_me_ddial Follow Me Ddial Setting
	 */
	function setDDial($exten,$follow_me_ddial) {
		$value_opt = ($follow_me_ddial)?'DIRECT':'EXTENSION';
		$response = $this->FreePBX->astman->database_put('AMPUSER',"$exten/followme/ddial",$value_opt);

		// Now that we have set the state (DIRECT is enabled, EXTENSION is disabled)
		// Get the devices associated with this user first and then we will set them all as needed
		//
		//
		if ($this->FreePBX->Config->get_conf_setting('USEDEVSTATE')) {
			$value_opt = ($follow_me_ddial)?'BUSY':'NOT_INUSE';
			$devices = $this->FreePBX->astman->database_get("AMPUSER",$exten."/device");
			$device_arr = explode('&',$devices);
			foreach ($device_arr as $device) {
				$this->FreePBX->astman->set_global($this->FreePBX->Config->get_conf_setting('AST_FUNC_DEVICE_STATE') . "(Custom:FOLLOWME$device)", $value_opt);
			}
		}
		return $response;
	}

	/*
	 * Gets Follow Me Ddial Setting
	 *
	 * @param $exten Extension to get information about
	 * @return $data follow me ddial setting
	 */
	function getDDial($exten) {
		$response = $this->FreePBX->astman->database_get("AMPUSER",$exten."/followme/ddial");
		if (trim($response) == 'EXTENSION') {
			return true;
		} elseif (trim($response) == 'DIRECT') {
			return false;
		} else {
			// If here then followme must not be set so use default
			return $this->FreePBX->Config->get_conf_setting('FOLLOWME_DISABLED') ? true : false;
		}
	}

	/*
	 * Sets Follow-Me Settings in FreePBX MySQL Database
	 *
	 * @param $exten Extension to modify
	 * @param $follow_me_prering_time Pre-Ring Time to ring
	 * @param $follow_me_listring_time List Ring Time to ring
	 * @param $follow_me_list Follow Me List
	 * @param $follow_me_list Follow Me Confirm Setting
	 *
	 */
	function setMySQL($exten, $follow_me_prering_time, $follow_me_listring_time, $follow_me_list, $follow_me_confirm) {
		$db = $this->db;

		//format for SQL database
		$follow_me_confirm = ($follow_me_confirm)?'CHECKED':'';

		$sql = "UPDATE findmefollow SET grptime = '" . $follow_me_listring_time . "', grplist = '".
		$db->escapeSimple(trim($follow_me_list)) . "', pre_ring = '" . $follow_me_prering_time .
		"', needsconf = '" . $follow_me_confirm . "' WHERE grpnum = $exten LIMIT 1";
		$results = $db->query($sql);


		return 1;
	}

	function listAll() {
		$list = findmefollow_list();
		return !empty($list) ? $list : array();
	}

	function addSettingById($grpnum,$setting,$value='') {
		return $this->addSettingsById($grpnum, array($setting => $value));
	}

	function addSettingsById($grpnum,$settings) {
		$valid = array('strategy','grptime','grppre','grplist','annmsg_id','postdest','dring','needsconf','remotealert_id','toolate_id','ringing','pre_ring','ddial','changecid','fixedcid');

		$settings = array_intersect_key($settings, array_flip($valid));

		if (count($settings) == 0) {
			return false;
		}

		$ret = true;

		foreach ($settings as $setting => $value) {
			//TODO This should just be one query.
			$sql = "INSERT INTO findmefollow (grpnum,$setting) VALUES (:grpnum,:value) ON DUPLICATE KEY UPDATE $setting = :value";
			$sth = $this->db->prepare($sql);

			switch($setting) {
				case 'strategy':
					$sth->execute(array(':grpnum' => $grpnum, ':key' => $setting, ':value' => $value));
				break;
				case 'grptime':
					$sth->execute(array(':grpnum' => $grpnum, ':key' => $setting, ':value' => $value));
					$this->setListRingTime($grpnum,$value);
				break;
				case 'grppre':
					$sth->execute(array(':grpnum' => $grpnum, ':key' => $setting, ':value' => $value));
				break;
				case 'grplist':
					$this->setList($grpnum,$value);
					$sth->execute(array(':grpnum' => $grpnum, ':key' => $setting, ':value' => implode("-",$value)));
				break;
				case 'annmsg_id':
					$sth->execute(array(':grpnum' => $grpnum, ':key' => $setting, ':value' => $value));
				break;
				case 'postdest':
					$sth->execute(array(':grpnum' => $grpnum, ':key' => $setting, ':value' => $value));
				break;
				case 'dring':
					$sth->execute(array(':grpnum' => $grpnum, ':key' => $setting, ':value' => $value));
				break;
				case 'needsconf':
					$val = ($value) ? 'CHECKED' : '';
					$sth->execute(array(':grpnum' => $grpnum, ':key' => $setting, ':value' => $val));
					$val = ($value) ? 'ENABLED' : 'DISABLED';
					$this->FreePBX->astman->database_put("AMPUSER",$grpnum."/followme/grpconf",$val);
				break;
				case 'remotealert_id':
					$sth->execute(array(':grpnum' => $grpnum, ':key' => $setting, ':value' => $value));
				break;
				case 'toolate_id':
					$sth->execute(array(':grpnum' => $grpnum, ':key' => $setting, ':value' => $value));
				break;
				case 'ringing':
					$sth->execute(array(':grpnum' => $grpnum, ':key' => $setting, ':value' => $value));
				break;
				case 'pre_ring':
					$sth->execute(array(':grpnum' => $grpnum, ':key' => $setting, ':value' => $value));
					$this->setPreRingTime($grpnum,$value);
				break;
				case 'ddial':
					//(DIRECT is enabled, EXTENSION is disabled)
					$ddialstate = ($value) ? 'NOT_INUSE' : 'BUSY';
					$val = ($value) ? 'EXTENSION' : 'DIRECT';
					$this->FreePBX->astman->database_put("AMPUSER",$grpnum."/followme/ddial",$val);
					if ($this->FreePBX->Config->get_conf_setting('USEDEVSTATE')) {
						$devices = $this->FreePBX->astman->database_get("AMPUSER", $grpnum . "/device");
						$device_arr = explode('&', $devices);
						foreach ($device_arr as $device) {
							$this->FreePBX->astman->set_global($this->FreePBX->Config->get_conf_setting('AST_FUNC_DEVICE_STATE') . "(Custom:FOLLOWME$device)", $ddialstate);
						}
					}
					if(!$value) {
						$sql = "INSERT INTO findmefollow (grpnum,grptime,grplist) VALUES (:grpnum,20,:grpnum)";
						$sth = $this->db->prepare($sql);
						//wrapped into a try/catch incase the find me is already defined, then we won't do the additional steps.
						try {
							$sth->execute(array(':grpnum' => $grpnum));
							//these are the additional steps
							$this->setListRingTime($grpnum,20);
							$this->setList($grpnum,array($grpnum));
						} catch(\Exception $e) {}
					}
				break;
				case 'changecid':
					$this->FreePBX->astman->database_put("AMPUSER",$grpnum."/followme/changecid",$value);
				break;
				case 'fixedcid':
					$value = preg_replace("/[^0-9\+]/" ,"", trim($value));
					$this->FreePBX->astman->database_put("AMPUSER",$grpnum."/followme/fixedcid",$value);
				break;
				default:
					$ret = false;
				break;
			}
		}

		return $ret;
	}

	function getSettingsById($grpnum, $check_astdb=0) {
		$db = $this->db;
		$sql = "SELECT grpnum, strategy, grptime, grppre, grplist, annmsg_id, postdest, dring, needsconf, remotealert_id, toolate_id, ringing, pre_ring, voicemail FROM findmefollow INNER JOIN `users` ON `extension` = `grpnum` WHERE grpnum = ?";
		$sth = $db->prepare($sql);
		$sth->execute(array($grpnum));
		$results = $sth->fetch(\PDO::FETCH_ASSOC);

		if (empty($results)) {
			//defaults
			return array(
				"ddial" => true,
				"needsconf" => false,
				"grplist" => $grpnum,
				"pre_ring" => '',
				"grpnum" => $grpnum,
				"annmsg_id" => '',
				"remotealert_id" => '',
				"toolate_id" => '',
				"grptime" => '20'
			);
		}

		if (!isset($results['voicemail'])) {
			$sql = "SELECT `voicemail` FROM `users` WHERE `extension` = ?";
			$sth = $db->prepare($sql);
			$sth->execute(array($grpnum));
			$results['voicemail'] = $sth->fetchColumn();
		}

		if (!isset($results['strategy'])) {
			$results['strategy'] = $this->FreePBX->Config->get_conf_setting('FOLLOWME_RG_STRATEGY');
		}

		if ($check_astdb) {
			if ($this->FreePBX->astman->Connected()) {
				$astdb_prering = $this->getPreRingTime($grpnum);
				$astdb_grptime = $this->getListRingTime($grpnum);
				$astdb_grplist = $this->getList($grpnum);
				$astdb_grpconf = $this->getConfirm($grpnum);

				$astdb_changecid = strtolower($this->FreePBX->astman->database_get("AMPUSER",$grpnum."/followme/changecid"));
				switch($astdb_changecid) {
					case 'default':
					case 'did':
					case 'forcedid':
					case 'fixed':
					case 'extern':
					break;
					default:
						$astdb_changecid = 'default';
				}
				$results['changecid'] = $astdb_changecid;
				$fixedcid = $this->FreePBX->astman->database_get("AMPUSER",$grpnum."/followme/fixedcid");
				$results['fixedcid'] = preg_replace("/[^0-9\+]/" ,"", trim($fixedcid));
			}
			$astdb_ddial = $this->getDDial($grpnum);
			// If the values are different then use what is in astdb as it may have been changed.
			// If sql returned no results for pre_ring/grptime then it's not configued so we reset
			// the astdb defaults as well
			//
			$changed=0;
			if (!isset($results['pre_ring'])) {
				$results['pre_ring'] = $astdb_prering = $this->FreePBX->Config->get_conf_setting('FOLLOWME_PRERING');
			}
			if (!isset($results['grptime'])) {
				$results['grptime'] = $astdb_grptime = $this->FreePBX->Config->get_conf_setting('FOLLOWME_TIME');
			}
			if (!isset($results['grplist'])) {
				$results['grplist'] = '';
			}
			if (!isset($results['needsconf'])) {
				$results['needsconf'] = '';
			}
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

			$confvalue = ($astdb_grpconf) ? 'CHECKED' : '';
			if ($confvalue != trim($results['needsconf'])) {
				$results['needsconf'] = $confvalue;
				$changed=1;
			}

			$results['ddial'] = $astdb_ddial;

			if ($changed) {
				$sql = "UPDATE findmefollow SET grptime = ?, grplist = ?, pre_ring = ?, needsconf = ? WHERE grpnum = ? LIMIT 1";
				$sth = $db->prepare($sql);
				$sth->execute(array($results['grptime'],$results['grplist'],$results['pre_ring'],$results['needsconf'],$results['grpnum']));
			}
		} // if check_astdb
		$results['needsconf'] = ($results['needsconf'] == "CHECKED") ? true : false;
		return $results;
	}

	function del($grpnum) {
		$sql = "DELETE FROM findmefollow WHERE grpnum = ?";
		$sth = $db->prepare($sql);
		$sth->execute(array($grpnum));


		if ($this->FreePBX->astman->Connected()) {
			$this->FreePBX->astman->database_deltree("AMPUSER/".$grpnum."/followme");
		}
	}
	public function getActionBar($request) {
		if (empty($request['extdisplay']) || empty($request['view']) || $request['view'] != 'form') {
			return null;
		}
		switch($request['display']) {
			case 'findmefollow':
				$buttons = array(
					'submit' => array(
						'name' => 'submit',
						'id' => 'submit',
						'value' => _('Submit')
					),
					'reset' => array(
						'name' => 'reset',
						'id' => 'reset',
						'value' => _('Reset')
					)
				);
				break;
		}
		return $buttons;
	}

	public function bulkhandlerGetHeaders($type) {
		switch ($type) {
		case 'extensions':
			$headers = array(
				'findmefollow_enabled' => array(
					'description' => _('Follow Me Enabled [Blank to disable]')
				),
				'findmefollow_grplist' => array(
					'description' => _('Follow Me List'),
				),
				'findmefollow_postdest' => array(
					'description' => _('Follow Me No Answer Destination'),
					"display" => false,
					"type" => "destination"
				),
			);

			return $headers;
			break;
		}
	}

	public function bulkhandlerImport($type, $rawData) {
		$ret = NULL;

		switch ($type) {
		case 'extensions':
			foreach ($rawData as $data) {
				$extension = $data['extension'];

				foreach ($data as $key => $value) {
					if (substr($key, 0, 13) == 'findmefollow_') {
						$settingname = substr($key, 13);
						switch ($settingname) {
							case 'grplist':
								$settings[$settingname] = explode('-', $value);
							break;
							case 'enabled':
								//reversy and backwards yeah. I know.
								//ITS THE CODE FROM 7 YEARS AGO
								//:'(
								$settings['ddial'] = !empty($value) ? false : true;
							break;
							default:
								$settings[$settingname] = $value;
							break;
						}
					}
				}

				if (!empty($settings) && count($settings) > 0) {
					$this->addSettingsById($extension, $settings);
				}
			}

			$ret = array(
				'status' => true,
			);

			break;
		}

		return $ret;
	}

	public function bulkhandlerExport($type) {
		$data = NULL;

		switch ($type) {
		case 'extensions':
			$extensions = $this->listAll();

			foreach ($extensions as $extension) {
				$settings = $this->getSettingsById($extension, true);
				$psettings = array();
				foreach ($settings as $key => $value) {
					switch ($key) {
					case 'grpnum':
						break;
					case 'ddial':
						$psettings['findmefollow_' . 'enabled'] = $value ? 'yes' : '';
						break;
					default:
						$psettings['findmefollow_' . $key] = $value;
						break;
					}
				}
				$data[$extension] = $psettings;
			}

			break;
		}

		return $data;
	}
	public function ajaxRequest($req, &$setting) {
		switch ($req) {
			case 'toggleFM':
			case 'getJSON':
				return true;
			break;
			default:
				return false;
			break;
		}
	}
	public function ajaxHandler(){
		switch ($_REQUEST['command']) {
			case 'toggleFM':
				$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:'';
				$state = '';
				if($_REQUEST['state'] == 'enable'){
					$state = true;
				}
				if($_REQUEST['state'] == 'disable'){
					$state = false;
				}
				if($state === '' || empty($extdisplay)){
					return array('toggle' => 'invalid');
				}
				$ret = $this->setDDial($extdisplay,$state);
				return array('toggle' => 'received', 'return' => $ret );
			break;
			case 'getJSON':
				switch ($_REQUEST['jdata']) {
					case 'grid':
						$ret = array();
						foreach($this->listFollowme() as $fm){
							$ret[] = array('ext'=>$fm[0]);
						}
						return array_values($ret);
					break;

					default:
						return false;
					break;
				}
			break;

			default:
				return false;
			break;
		}
	}
	public function listFollowme($get_all=false){
		$sql = "SELECT grpnum FROM findmefollow ORDER BY CAST(grpnum as UNSIGNED)";
		$stmt = $this->db->prepare($sql);
		$stmt->execute();
		$results = $stmt->fetchall();
		if (isset($results)) {
			foreach($results as $result) {
				if ($get_all || checkRange($result)){
					$grps[] = $result;
				}
			}
		}
		if (isset($grps)) {
			return $grps;
		}
		else {
			return array();
		}
	}


public function getRightNav($request) {
	if(isset($request['view'])&& $request['view'] == 'form'){
  	return load_view(__DIR__."/views/bootnav.php",array('request' => $request));
	}
}

}
