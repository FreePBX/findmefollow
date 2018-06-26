<?php
namespace FreePBX\modules\__MODULENAME__;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
  public function runBackup($id,$transaction){
        $configs = $this->FreePBX->Findmefollow->bulkhandlerExport('extensions');
        $this->addConfigs($configs);
    }
    $this->addDependency('core');
    $this->addDependency('userman');
    $this->addDependency('recordings');
    $this->addConfigs($configs);
  }
}