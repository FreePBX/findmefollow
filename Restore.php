<?php
namespace FreePBX\modules\Findmefollow;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
	public function runRestore(){
		$configs = $this->getConfigs();
		$this->importTables($configs['tables']);
		$this->importFeatureCodes($configs['features']);

		$astman = $this->FreePBX->astman;
		$rows = $this->FreePBX->Database->query("SELECT * FROM findmefollow")->fetchAll(\PDO::FETCH_ASSOC);
		foreach($rows as $row) {
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/strategy",$row['strategy']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/prering",$row['pre_ring']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/grptime",$row['grptime']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/grplist",$row['grplist']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/grppre",$row['grppre']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/rvolume",$row['rvolume']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/dring",$row['dring']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/annmsg",$row['annmsg_id']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/remotealertmsg",$row['remotealert_id']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/toolatemsg",$row['toolate_id']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/postdest",$row['postdest']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/ringing",$row['ringing']);
			$value = (isset($row['needsconf']) && $row['needsconf']=='CHECKED')? 'ENABLED' : 'DISABLED';
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/grpconf",$value);
		}
		$fmstatus = $configs['fmstatus'];
		if(!empty($fmstatus)) {
			foreach($fmstatus as $rows) {
				$rows['ddial'] = ($rows['ddial'])?false:true;
				$this->FreePBX->Findmefollow->setDDial($rows['grpnum'],$rows['ddial']);
				if(isset($rows['changecid'])) {
					$astman->database_put("AMPUSER",$rows['grpnum']."/followme/changecid",$rows['changecid']);
				} else {
					$astman->database_put("AMPUSER",$rows['grpnum']."/followme/changecid",'default');
				}

				if(isset($rows['fixedcid'])) {
					$astman->database_put("AMPUSER",$rows['grpnum']."/followme/fixedcid",$rows['fixedcid']);
				} else {
					$astman->database_put("AMPUSER",$rows['grpnum']."/followme/fixedcid",'default');
				}
			}
		}

	}

	public function processLegacy($pdo, $data, $tables, $unknownTables){
		$this->restoreLegacyDatabase($pdo);
		$this->restoreLegacyFeatureCodes($pdo);

		$astman = $this->FreePBX->astman;
		$rows = $this->FreePBX->Database->query("SELECT * FROM findmefollow")->fetchAll(\PDO::FETCH_ASSOC);
		foreach($rows as $row) {
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/strategy",$row['strategy']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/prering",$row['pre_ring']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/grptime",$row['grptime']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/grplist",$row['grplist']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/grppre",$row['grppre']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/rvolume",$row['rvolume']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/dring",$row['dring']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/annmsg",$row['annmsg_id']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/remotealertmsg",$row['remotealert_id']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/toolatemsg",$row['toolate_id']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/postdest",$row['postdest']);
			$astman->database_put("AMPUSER",$row['grpnum']."/followme/ringing",$row['ringing']);
		}

		if(isset($data['astdb']['AMPUSER'])) {
			foreach ($data['astdb']['AMPUSER'] as $key => $value) {
				if(!str_contains((string) $key, 'ddial')) {
					continue;
				}
				$parts = explode('/', (string) $key);
				if($parts[2] !== 'ddial') {
					continue;
				}
				$value = ($value == 'EXTENSION')?false:true;
				$this->FreePBX->Findmefollow->setDDial($parts[0],$value);
			}
		}
	}
}
