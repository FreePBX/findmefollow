<?php
namespace FreePBX\modules\Findmefollow;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
	public function runBackup($id,$transaction){
		$this->addDependency('core');
		$this->addDependency('userman');
		$this->addDependency('recordings');
		$this->addConfigs([
			'data' => $this->FreePBX->Findmefollow->bulkhandlerExport('extensions'),
			'features' => $this->dumpFeatureCodes()
		]);
	}
}
