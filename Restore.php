<?php
namespace FreePBX\modules\Findmefollow;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
  public function runRestore($jobid){
        $configs = $this->getConfigs();
        $this->FreePBX->Findmefollow->bulkhandlerImport('extensions', $configs);
  }
  
  public function processLegacy($pdo, $data, $tables, $unknownTables, $tmpfiledir){
    $tables = array_flip($tables+$unknownTables);
    if(!isset($tables['findmefollow'])){
      return $this;
    }
    $bmo = $this->FreePBX->Findmefollow;
    $bmo->setDatabase($pdo);
    $configs = $bmo->bulkhandlerExport('extensions', false);
    $bmo->resetDatabase();
    $bmo->bulkhandlerImport('extensions', $configs);

    return $this;
  }
}
