<script>
extension = <?php echo $extension; ?>;
</script>
<div class="message alert" style="display:none;"></div>
<form role="form">
	<div class="form-group">
		<label for="ddial-h" class="help"><?php echo _('Enable')?> <i class="fa fa-question-circle"></i></label>
		<div class="onoffswitch">
			<input type="checkbox" name="ddial" class="onoffswitch-checkbox" id="ddial" <?php echo ($enabled) ? 'checked' : ''?>>
			<label class="onoffswitch-label" for="ddial">
				<div class="onoffswitch-inner"></div>
				<div class="onoffswitch-switch"></div>
			</label>
		</div>
		<span class="help-block help-hidden" data-for="ddial-h"><?php echo _('When enabled any call to this extension will go to this Follow-Me instead, including directory calls by name from IVRs. If disabled, calls will go only to the extension.')?></span>
	</div>
</form>
