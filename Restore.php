<?php
namespace FreePBX\modules\Findmefollow;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
	public function runRestore($jobid){
				$configs = $this->getConfigs();
				$this->FreePBX->Findmefollow->bulkhandlerImport('extensions', $configs);
	}

	public function processLegacy($pdo, $data, $tables, $unknownTables, $tmpfiledir){
		$this->restoreLegacyDatabase($pdo);
	}
}
