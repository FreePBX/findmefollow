<?php
/**
 * This is the User Control Panel Object.
 *
 * Copyright (C) 2013 Schmooze Com, INC
 * Copyright (C) 2013 Andrew Nagy <andrew.nagy@schmoozecom.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   FreePBX UCP BMO
 * @author   Andrew Nagy <andrew.nagy@schmoozecom.com>
 * @license   AGPL v3
 */
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
