<?php
namespace FreePBX\modules\Findmefollow;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
	public function runBackup($id,$transaction){
		$this->addDependency('core');
		$this->addDependency('userman');
		$this->addDependency('recordings');

		$followmeList = $this->FreePBX->Findmefollow->listAll();
		foreach ($followmeList as $fl) {
			$dDial = $this->FreePBX->Findmefollow->getDDial($fl);
			$followmeArr = ['grpnum' => $fl, 'ddial' => $dDial];
			$fmlist = 'AMPUSER/' . $fl . '/followme';
			$changecidkey = '/AMPUSER/' . $fl . '/followme/changecid';
			$fixedcidkey = '/AMPUSER/' . $fl . '/followme/fixedcid';
			$astdbval = $this->FreePBX->astman->database_show($fmlist);
			if(array_key_exists($changecidkey,$astdbval)) {
				$followmeArr['changecid'] = $astdbval[$changecidkey];
			}
			if(array_key_exists($fixedcidkey,$astdbval)) {
				$followmeArr['fixedcid'] = $astdbval[$fixedcidkey];
			}
			$followmeStatus[] = $followmeArr;
		}
		$this->addConfigs([
			'tables' => $this->dumpTables(),
			'features' => $this->dumpFeatureCodes(),
			'fmstatus' => ($followmeStatus ?? [])
		]);
	}
}
