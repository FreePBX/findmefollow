<?php

namespace UCP\Modules;
use \UCP\Modules as Modules;

class Findmefollow extends Modules{
	protected $module = 'Findmefollow';

	function __construct($Modules) {
		$this->Modules = $Modules;
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

	public function getMenuItems() {
		$menu = $this->getWidgetList();

		return $menu;
	}

	public function getWidgetList() {
		$widgets = array(
			"rawname" => "findmefollow",
			"name" => _("Find Me / Follow Me"),
			"badge" => false
		);
		$user = $this->UCP->User->getUser();
		$extensions = $this->UCP->getCombinedSettingByID($user['id'],'Settings','assigned');
		foreach($extensions as $extension) {
			$data = $this->UCP->FreePBX->Core->getDevice($extension);
			if(empty($data) || empty($data['description'])) {
				$data = $this->UCP->FreePBX->Core->getUser($extension);
				$name = $data['name'];
			} else {
				$name = $data['description'];
			}

			$widgets["menu"][] = array(
				"rawname" => $extension,
				"name" => $name,
			);
		}

		return $widgets;
	}

	function getDisplay() {
		$display = $this->getWidgetDisplay();

		return $display;
	}

	public function getWidgetDisplay() {
		$sub = $_REQUEST['sub'];

		if (!$this->_checkExtension($sub)) {
			return false;
		}

		$settings = $this->UCP->FreePBX->Findmefollow->getSettingsById($sub, 1);
		$displayvars = array(
			"extension" => $sub,
			"enabled" => $settings['ddial'] ? false : true,
		);

		$html = $this->load_view(__DIR__.'/views/widget.php',$displayvars);
		return $html;
	}

	public function getWidgetSettingsDisplay() {
		$sub = $_REQUEST['sub'];

		if (!$this->_checkExtension($sub)) {
			return false;
		}

		$settings = $this->UCP->FreePBX->Findmefollow->getSettingsById($sub,1);
		$displayvars = array(
			"extension" => $sub,
			"confirm" => $settings['needsconf'],
			"list" => explode("-",$settings['grplist']),
			"ringtime" => $settings['grptime'],
			"prering" => $settings['pre_ring']
		);
		for($i = 0;$i<=30;$i++) {
			$displayvars['prering_time'][$i] = $i;
		}
		for($i = 0;$i<=60;$i++) {
			$displayvars['listring_time'][$i] = $i;
		}
		$html = $this->load_view(__DIR__.'/views/settings.php',$displayvars);

		return $html;
	}
}
