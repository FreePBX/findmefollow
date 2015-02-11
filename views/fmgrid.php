<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2015 Sangoma Technologies.
//
$fmlist = findmefollow_list();
foreach($fmlist as $fm){
	$thisg = findmefollow_get($fm,1);
	$fmrows .= '<tr>';
	$fmrows .= '<td><a href="/admin/config.php?display=findmefollow&view=form&extdisplay=GRP-'.urlencode($fm).'"><i class="fa fa-edit"></i>&nbsp;'.$fm.'</a></td>';
	$fmrows .= '<td>';
	$fmrows .= '<span class="radioset">';
	$fmrows .= '<input type="checkbox" id="fmtoggle'.$fm.'" data-for="'.$fm.'" '.$thisg['ddial'].'>';
	$fmrows .= '<label for="fmtoggle'.$fm.'">'._("Disabled").'</label>';
}
?>

<table class="table table-striped">
<thead>
	<tr>
		<th><?php echo _("Followme Extension")?></th>
		<th><?php echo _("Status")?></th>
	</tr>	
</thead>
<tbody>
	<?php echo $fmrows ?>
</tbody>
</table>