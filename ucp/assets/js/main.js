var Findmefollow = new function() {
	this.initalized = false;
	this.init = function() {
		//prevent multiple loads of this class which end up destroying content and rebinding a gazillon times
		if(this.initalized) {
			return false;
		}
		$('#module-Findmefollow input[type="text"]').blur(function() {
			Findmefollow.saveSettings({key: $(this).prop('name'), value: $(this).val()});
		});
		$('#module-Findmefollow textarea').blur(function() {
			Findmefollow.saveSettings({key: $(this).prop('name'), value: $(this).val()});
		});
		$('#module-Findmefollow input[type="checkbox"]').change(function() {
			Findmefollow.saveSettings({key: $(this).prop('name'), value: $(this).is(':checked')});
		});
		$('#module-Findmefollow select').change(function() {
			Findmefollow.saveSettings({key: $(this).prop('name'), value: $(this).val()});
		});
	};
	this.saveSettings = function(data) {
		data.ext = ext;
		$.post( "index.php?quietmode=1&module=findmefollow&command=settings", data, function( data ) {
			$('#module-Findmefollow .message').text(data.message).addClass('alert-'+data.alert).fadeIn('fast', function() {
				$('.masonry-container').packery();
				$(this).delay(5000).fadeOut('fast', function() {
					$('.masonry-container').packery();
				});
			});
		});
	};
};

//MUST REMAIN AT BOTTOM!
//This might not be needed as most browser seem to run doc ready anyways
//TODO: This should be in the higher up. each module should have this functionality from here on out!
$(function() {
	Findmefollow.init();
});
