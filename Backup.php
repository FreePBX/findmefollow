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
			$followmeStatus[] = array('grpnum' => $fl, 'ddial' => $dDial);
		}
		$this->addConfigs([
			'tables' => $this->dumpTables(),
			'features' => $this->dumpFeatureCodes(),
			'fmstatus' => $followmeStatus
		]);
	}
}
