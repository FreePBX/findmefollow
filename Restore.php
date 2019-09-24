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
		}
		$fmstatus = $configs['fmstatus'];
		foreach($fmstatus as $rows) {
			$rows['ddial'] = ($rows['ddial'])?false:true;
			$this->FreePBX->Findmefollow->setDDial($rows['grpnum'],$rows['ddial']);
		}

	}

	public function processLegacy($pdo, $data, $tables, $unknownTables){
		$this->restoreLegacyDatabase($pdo);
		$this->restoreLegacyFeatureCodes($pdo);
		if(isset($data['astdb']['AMPUSER'])) {
			foreach ($data['astdb']['AMPUSER'] as $key => $value) {
				if(strpos($key, 'ddial') === false) {
					continue;
				}
				$parts = explode('/', $key);
				if($parts[2] !== 'ddial') {
					continue;
				}
				$value = ($value == 'EXTENSION')?false:true;
				$this->FreePBX->Findmefollow->setDDial($parts[0],$value);
			}
		}
	}
}
