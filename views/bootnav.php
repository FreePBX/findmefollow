<?php
if (isset($amp_conf["AMPEXTENSIONS"]) && ($amp_conf["AMPEXTENSIONS"] == "deviceanduser")) {
	$editURL = '?display=users&extdisplay='.ltrim($request['extdisplay'],'GRP-') ;
	$EXTorUSER = _("User");
}else{
	$editURL = '?display=extensions&extdisplay='.ltrim($request['extdisplay'],'GRP-') ;
	$EXTorUSER = _("Extension");
}

?>
<a href="config.php?display=findmefollow" class="list-group-item <?php echo ($request['view'] == ''? 'hidden':'')?>"><i class="fa fa-list"></i>&nbsp; <?php echo _("List Followme Groups") ?></a>
<a href="<?php echo $editURL?>" class="list-group-item"><i class="fa fa-edit"></i>&nbsp; <?php echo $EXTorUSER . " " . ltrim($request['extdisplay'],'GRP-') ?></a>
