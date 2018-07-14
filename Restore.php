<?php
namespace FreePBX\modules\Findmefollow;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
  public function runRestore($jobid){
	dbug("HERE");
        $configs = $this->getConfigs();
        $this->FreePBX->Findmefollow->bulkhandlerImport('extensions', $configs);
  }
}
