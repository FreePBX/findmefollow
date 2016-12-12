<script>
extension = <?php echo $extension; ?>;
</script>
<div class="message alert" style="display:none;"></div>
<form role="form" class="form" >
		<div class="form-group">
			<div class="col-xs-12">
				<input type="checkbox" name="ddial" data-toggle="toggle" id="ddial" data-on="<?php echo _('Enabled')?>" data-off="<?php echo _('Disabled')?>" <?php echo ($enabled) ? 'checked' : ''?>>
			</div>
		</div>
	</div>
</form>
