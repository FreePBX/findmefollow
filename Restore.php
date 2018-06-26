<?php
namespace FreePBX\modules\__MODULENAME__;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
  public function runRestore($jobid){
        $configs = $this->getConfigs();
        $this->FreePBX->Findmefollow->bulkhandlerImport('extensions', $configs);
  }
}