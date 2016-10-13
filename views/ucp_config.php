<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="findmefollow_enable"><?php echo _("Enable Follow Me")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="findmefollow_enable"></i>
					</div>
					<div class="col-md-9">
						<span class="radioset">
							<input type="radio" name="findmefollow_enable" id="findmefollow_enable_yes" value="yes" <?php echo ($enabled) ? 'checked' : ''?>>
							<label for="findmefollow_enable_yes"><?php echo _('Yes')?></label>
							<input type="radio" name="findmefollow_enable" id="findmefollow_enable_no" value="no" <?php echo (!is_null($enabled) && !$enabled) ? 'checked' : ''?>>
							<label for="findmefollow_enable_no"><?php echo _('No')?></label>
							<?php if($mode == "user") {?>
								<input type="radio" id="findmefollow_enable_inherit" name="findmefollow_enable" value='inherit' <?php echo is_null($enabled) ? 'checked' : ''?>>
								<label for="findmefollow_enable_inherit"><?php echo _('Inherit')?></label>
							<?php } ?>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="findmefollow_enable-help" class="help-block fpbx-help-block"><?php echo _("Whether to allow this user to be able to configure Follow me in UCP")?></span>
		</div>
	</div>
</div>
