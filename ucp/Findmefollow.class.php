<?php

namespace UCP\Modules;
use \UCP\Modules as Modules;

class Findmefollow extends Modules{
	protected $module = 'Findmefollow';
	private $user = null;
	private $userId = false;

	function __construct($Modules) {
		$this->Modules = $Modules;
		$this->user = $this->UCP->User->getUser();
		$this->userId = $this->user ? $this->user["id"] : false;
	}

	function poll($data) {
		$states = [];
		foreach($data as $ext) {
			if(!$this->_checkExtension($ext)) {
				continue;
			}
			$settings = $this->UCP->FreePBX->Findmefollow->getSettingsById($ext, 1);
			$states[$ext] = $settings['ddial'] ? false : true;
		}

		return ["states" => $states];
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
		return match ($command) {
      'settings' => true,
      default => false,
  };
	}

	/**
	 * The Handler for all ajax events releated to this class
	 *
	 * Used by Ajax Class to process commands
	 *
	 * @return mixed Output if success, otherwise false will generate a 500 error serverside
	 */
	function ajaxHandler() {
		$return = ["status" => false, "message" => ""];
		switch($_REQUEST['command']) {
			case 'settings':
				$_POST['value'] = ($_POST['key'] == 'grplist') ? explode("\n",(string) $_POST['value']) : $_POST['value'];
				if($_POST['key'] == 'ddial') {
					$_POST['value'] = ($_POST['value'] == 'true') ? false : true;
				}
				if($_POST['key'] == 'needsconf') {
					$_POST['value'] = ($_POST['value'] == 'true') ? true : false;
				}
				$this->UCP->FreePBX->Findmefollow->addSettingById($_POST['ext'],$_POST['key'],$_POST['value']);
				return ["status" => true, "alert" => "success", "message" => _('Find Me/Follow Me Has Been Updated!')];
				break;
			default:
				return $return;
			break;
		}
	}

	private function _checkExtension($extension) {
		$extensions = $this->UCP->getCombinedSettingByID($this->userId,'Findmefollow','assigned');
		$extensions = is_array($extensions) ? $extensions : [];
		return in_array($extension,$extensions);
	}

	public function getWidgetList() {
		$widgetList = $this->getSimpleWidgetList();

		return $widgetList;
	}

	public function getSimpleWidgetList() {
		$widgets = [];

		$enable = $this->UCP->getCombinedSettingByID($this->userId,'Findmefollow','enable');
		if($enable == 'no')
		{ return [];
		}
		$extensions = $this->UCP->getCombinedSettingByID($this->userId,'Findmefollow','assigned');

		if (!empty($extensions)) {
			foreach($extensions as $extension) {
				$data = $this->UCP->FreePBX->Core->getDevice($extension);
				if(empty($data) || empty($data['description'])) {
					$data = $this->UCP->FreePBX->Core->getUser($extension);
					$name = $data['name'];
				} else {
					$name = $data['description'];
				}

				$widgets[$extension] = ["display" => $name, "description" => sprintf(_("Find Me/Follow Me for %s"),$name), "hasSettings" => true, "defaultsize" => ["height" => 2, "width" => 1], "minsize" => ["height" => 2, "width" => 1]];
			}
		}

		if (empty($widgets)) {
			return [];
		}

		return ["rawname" => "findmefollow", "display" => _("Follow Me"), "icon" => "fa fa-binoculars", "list" => $widgets];
	}

	public function getWidgetDisplay($id) {
		if (!$this->_checkExtension($id)) {
			return [];
		}
		$settings = $this->UCP->FreePBX->Findmefollow->getSettingsById($id, 1);
		$displayvars = ["extension" => $id, "enabled" => $settings['ddial'] ? false : true];

		$display = ['title' => _("Follow Me"), 'html' => $this->load_view(__DIR__.'/views/widget.php',$displayvars)];

		return $display;
	}

	public function getSimpleWidgetSettingsDisplay($id) {
		return $this->getWidgetSettingsDisplay($id);
	}

	public function getWidgetSettingsDisplay($id) {
		if (!$this->_checkExtension($id)) {
			return [];
		}

		$fmr = $this->UCP->getCombinedSettingByID($this->userId,'Findmefollow','fmr');
		// need to get the group settings

		$settings = $this->UCP->FreePBX->Findmefollow->getSettingsById($id,1);
		$displayvars = ["extension" => $id, "confirm" => $settings['needsconf'], "list" => explode("-",(string) $settings['grplist']), "ringtime" => $settings['grptime'], "fmr" => $fmr, "strategy" => $settings['strategy'], "prering" => $settings['pre_ring']];
		for($i = 0;$i<=30;$i++) {
			$displayvars['prering_time'][$i] = $i;
		}
		for($i = 0;$i<=60;$i++) {
			$displayvars['listring_time'][$i] = $i;
		}

		$display = ['title' => _("Follow Me"), 'html' => $this->load_view(__DIR__.'/views/settings.php',$displayvars)];

		return $display;
	}
}
