<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="fmfm"><?php echo _('Enable Find Me/Follow Me')?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="fmfm"></i>
					</div>
					<div class="col-md-9">
						<span class="radioset">
							<input type="radio" name="fmfm" id="fmfm_on" value="yes" <?php echo ($fmfm) ? 'checked' : ''?>>
							<label for="fmfm_on"><?php echo _('Yes')?></label>
							<input type="radio" name="fmfm" id="fmfm_off" value="no" <?php echo ($fmfm) ? '' : 'checked'?>>
							<label for="fmfm_off"><?php echo _('No')?></label>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="fmfm-help" class="help-block fpbx-help-block"><?php echo _('Whether to enable voicemail for this extension')?></span>
		</div>
	</div>
</div>
