var FindmefollowC = UCPMC.extend({
	init: function(){
	},
	display: function() {
		$('#module-page-findmefollow input[type="text"], #module-page-findmefollow textarea').change(function() {
			$(this).blur(function() {
				Findmefollow.saveSettings({key: $(this).prop('name'), value: $(this).val()});
				$(this).off('blur');
			});
		});
		$('#module-page-findmefollow input[type="checkbox"]').change(function() {
			Findmefollow.saveSettings({key: $(this).prop('name'), value: $(this).is(':checked')});
		});
		$('#module-page-findmefollow select').change(function() {
			Findmefollow.saveSettings({key: $(this).prop('name'), value: $(this).val()});
		});
	},
	saveSettings: function(data) {
		data.ext = extension;
		$.post( "index.php?quietmode=1&module=findmefollow&command=settings", data, function( data ) {
			$('#module-page-findmefollow .message').text(data.message).addClass('alert-'+data.alert).fadeIn('fast', function() {
				$(this).delay(5000).fadeOut('fast', function() {
					$('.masonry-container').packery();
				});
			});
			$('.masonry-container').packery();
		});
	}
});
var Findmefollow = new FindmefollowC();
