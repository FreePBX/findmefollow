<div class="message alert" style="display:none;"></div>
<form role="form">
	<label >
		<?php echo _('Enable')?>
		<div class="onoffswitch">
			<input type="checkbox" name="ddial" class="onoffswitch-checkbox" id="ddial" <?php echo ($enabled) ? 'checked' : ''?>>
			<label class="onoffswitch-label" for="ddial">
				<div class="onoffswitch-inner"></div>
				<div class="onoffswitch-switch"></div>
			</label>
		</div>
	</label>
	<div class="form-group">
		<label for="grplist"><?php echo _('Follow Me List')?></label>
		<textarea id="grplist" name="grplist" class="form-control" rows="3"><?php echo implode("\n",$list)?></textarea>
	</div>
	<div class="form-group">
		<label for="annmsg_id"><?php echo _('Announcement') ?>:</label><br/>
		<select name="annmsg_id" id="annmsg_id" class="form-control">
			<option value="0"><?php echo _('None')?></option>
			<?php foreach($recordings as $value) { ?>
				<option value="<?php echo $value['id']?>" <?php echo ($annmsg_id == $value['id']) ? 'selected' : ''?>><?php echo $value['displayname']?></option>
			<?php } ?>
		</select>
	</div>
	<div class="form-group">
		<label for="pre_ring"><?php echo sprintf(_('Ring %s First For'),$exten) ?>:</label><br/>
		<select name="pre_ring" id="pre_ring" class="form-control">
			<?php foreach($prering_time as $key => $value) { ?>
				<option value="<?php echo $key?>" <?php echo ($prering == $key) ? 'selected' : ''?>><?php echo $value?> <?php echo _('Seconds')?></option>
			<?php } ?>
		</select>
	</div>
	<div class="form-group">
		<label for="grptime"><?php echo _('Ring Followme List For') ?>:</label><br/>
		<select name="grptime" id="grptime" class="form-control">
			<?php foreach($listring_time as $key => $value) { ?>
				<option value="<?php echo $key?>" <?php echo ($ringtime == $key) ? 'selected' : ''?>><?php echo $value?> <?php echo _('Seconds')?></option>
			<?php } ?>
		</select>
	</div>
	<label>
		<?php echo _('Use Confirmation')?>
		<div class="onoffswitch">
			<input type="checkbox" name="needsconf" class="onoffswitch-checkbox" id="needsconf" <?php echo ($confirm) ? 'checked' : ''?>>
			<label class="onoffswitch-label" for="needsconf">
				<div class="onoffswitch-inner"></div>
				<div class="onoffswitch-switch"></div>
			</label>
		</div>
	</label>
	<div class="form-group">
		<label for="remotealert_id"><?php echo _('Remote Announce') ?>:</label><br/>
		<select name="remotealert_id" id="remotealert_id" class="form-control">
			<option value="0"><?php echo _('None')?></option>
			<?php foreach($recordings as $value) { ?>
				<option value="<?php echo $value['id']?>" <?php echo ($remotealert_id == $value['id']) ? 'selected' : ''?>><?php echo $value['displayname']?></option>
			<?php } ?>
		</select>
	</div>
	<div class="form-group">
		<label for="toolate_id"><?php echo _('Too-Late Announce') ?>:</label><br/>
		<select name="toolate_id" id="toolate_id" class="form-control">
			<option value="0"><?php echo _('None')?></option>
			<?php foreach($recordings as $value) { ?>
				<option value="<?php echo $value['id']?>" <?php echo ($toolate_id == $value['id']) ? 'selected' : ''?>><?php echo $value['displayname']?></option>
			<?php } ?>
		</select>
	</div>
</form>
