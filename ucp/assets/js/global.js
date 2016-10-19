var FindmefollowC = UCPMC.extend({
	init: function(){
	},
	/*
	display: function() {
		var $this = this;
		$('#module-page-findmefollow input[type="text"], #module-page-findmefollow textarea').change(function() {
			$(this).blur(function() {
				$this.saveSettings({key: $(this).prop('name'), value: $(this).val()});
				$(this).off('blur');
			});
		});
		$('#module-page-findmefollow input[type="checkbox"]').change(function() {
			$this.saveSettings({key: $(this).prop('name'), value: $(this).is(':checked')});
		});
		$('#module-page-findmefollow select').change(function() {
			$this.saveSettings({key: $(this).prop('name'), value: $(this).val()});
		});
	},
	*/
	displayWidget: function(widget_id) {
		console.log("I [findmefollowme] was told to display for ["+widget_id+"]!");
		$("#ddial").change(function() {
			console.log("thingy changed!");
		});
	},
	displayWidgetSettings: function(widget_id) {
	},
	saveSettings: function(data) {
		data.ext = extension;
		$.post( "index.php?quietmode=1&module=findmefollow&command=settings", data, function( data ) {
			$('#module-page-findmefollow .message').text(data.message).addClass('alert-'+data.alert).fadeIn('fast', function() {
				$(this).delay(5000).fadeOut('fast', function() {
					//$('.masonry-container').packery();
				});
			});
			//$('.masonry-container').packery();
		});
	}
});
