<?php

namespace UCP\Modules;
use \UCP\Modules as Modules;

class Findmefollow extends Modules{
	protected $module = 'Findmefollow';

	function __construct($Modules) {
		$this->Modules = $Modules;
	}

	public function getSettingsDisplay($ext) {
		$displayvars = array(
			"enabled" => $this->UCP->FreePBX->Findmefollow->getDDial($ext) ? false : true,
			"confirm" => $this->UCP->FreePBX->Findmefollow->getConfirm($ext),
			"list" => explode("-",$this->UCP->FreePBX->Findmefollow->getList($ext)),
			"ringtime" => $this->UCP->FreePBX->Findmefollow->getListRingTime($ext),
			"prering" => $this->UCP->FreePBX->Findmefollow->getPreRingTime($ext),
			"exten" => $ext
		);
		$displayvars['extras'] = $this->UCP->FreePBX->Findmefollow->getSettingsById($ext);
		//$z = $this->UCP->FreePBX->Recordings->getRecordingsById($displayvars['extras']['annmsg_id']);
		//dbug($z);
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
				"size" => 6
			)
		);
		return $out;
	}

}
