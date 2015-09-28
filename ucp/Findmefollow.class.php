<?php

namespace UCP\Modules;
use \UCP\Modules as Modules;

class Findmefollow extends Modules{
	protected $module = 'Findmefollow';

	function __construct($Modules) {
		$this->Modules = $Modules;
	}

	public function getSettingsDisplay($ext) {
		$settings = $this->UCP->FreePBX->Findmefollow->getSettingsById($ext,1);
		$displayvars = array(
			"enabled" => $settings['ddial'] ? false : true,
			"confirm" => $settings['needsconf'],
			"list" => explode("-",$settings['grplist']),
			"ringtime" => $settings['grptime'],
			"prering" => $settings['pre_ring'],
			"exten" => $settings['grpnum'],
			"recordings" => $this->UCP->FreePBX->Recordings->getAllRecordings()
		);
		for($i = 0;$i<=30;$i++) {
			$displayvars['prering_time'][$i] = $i;
		}
		for($i = 0;$i<=60;$i++) {
			$displayvars['listring_time'][$i] = $i;
		}
		$out = array(
			array(
				"title" => _('Find Me/Follow Me'),
				"content" => $this->load_view(__DIR__.'/views/settings.php',$displayvars),
				"size" => 6,
				"order" => 0
			)
		);
		return $out;
	}

	/**
	 * Determine what commands are allowed
	 *
	 * Used by Ajax Class to determine what commands are allowed by this class
	 *
	 * @param string $command The command something is trying to perform
	 * @param string $settings The Settings being passed through $_POST or $_PUT
	 * @return bool True if pass
	 */
	function ajaxRequest($command, $settings) {
		if(!$this->_checkExtension($_POST['ext'])) {
			return false;
		}
		switch($command) {
			case 'settings':
				return true;
			default:
				return false;
			break;
		}
	}

	/**
	 * The Handler for all ajax events releated to this class
	 *
	 * Used by Ajax Class to process commands
	 *
	 * @return mixed Output if success, otherwise false will generate a 500 error serverside
	 */
	function ajaxHandler() {
		$return = array("status" => false, "message" => "");
		switch($_REQUEST['command']) {
			case 'settings':
				$_POST['value'] = ($_POST['key'] == 'grplist') ? explode("\n",$_POST['value']) : $_POST['value'];
				if($_POST['key'] == 'ddial') {
					$_POST['value'] = ($_POST['value'] == 'true') ? false : true;
				}
				if($_POST['key'] == 'needsconf') {
					$_POST['value'] = ($_POST['value'] == 'true') ? true : false;
				}
				$this->UCP->FreePBX->Findmefollow->addSettingById($_POST['ext'],$_POST['key'],$_POST['value']);
				return array("status" => true, "alert" => "success", "message" => _('Find Me/Follow Me Has Been Updated!'));
				break;
			default:
				return $return;
			break;
		}
	}

	private function _checkExtension($extension) {
		$user = $this->UCP->User->getUser();
		$extensions = $this->UCP->getCombinedSettingByID($user['id'],'Settings','assigned');
		$extensions = is_array($extensions) ? $extensions : array();
		return in_array($extension,$extensions);
	}
}
