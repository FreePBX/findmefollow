var FindmefollowC = UCPMC.extend({
	init: function(){
	},
	settingsDisplay: function() {
		$('#module-Findmefollow input[type="text"], #module-Findmefollow textarea').change(function() {
			$(this).blur(function() {
				Findmefollow.saveSettings({key: $(this).prop('name'), value: $(this).val()});
				$(this).off('blur');
			});
		});
		$('#module-Findmefollow input[type="checkbox"]').change(function() {
			Findmefollow.saveSettings({key: $(this).prop('name'), value: $(this).is(':checked')});
		});
		$('#module-Findmefollow select').change(function() {
			Findmefollow.saveSettings({key: $(this).prop('name'), value: $(this).val()});
		});
	},
	settingsHide: function() {
		$('#module-Findmefollow input[type="text"], #module-Findmefollow textarea').off('change');
		$('#module-Findmefollow input[type="checkbox"]').off('change');
		$('#module-Findmefollow select').off('change');
	},
	saveSettings: function(data) {
		data.ext = ext;
		$.post( "index.php?quietmode=1&module=findmefollow&command=settings", data, function( data ) {
			$('#module-Findmefollow .message').text(data.message).addClass('alert-'+data.alert).fadeIn('fast', function() {
				$(this).delay(5000).fadeOut('fast', function() {
					$('.masonry-container').packery();
				});
			});
			$('.masonry-container').packery();
		});
	}
});
var Findmefollow = new FindmefollowC();
