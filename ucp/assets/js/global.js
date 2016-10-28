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
	},
	displayWidget: function(widget_id,dashboard_id) {
		console.log(["normal:show",widget_id,dashboard_id]);
	},
	displayWidgetSettings: function(widget_id,dashboard_id) {
		console.log(["normal:settings:show",widget_id,dashboard_id]);
	},
	displaySmallWidget: function(widget_id) {
		console.log(["small:show",widget_id]);
	},
	displaySmallWidgetSettings: function(widget_id) {
		console.log(["small:settings:show",widget_id]);
	},
	deleteWidget: function(widget_id,dashboard_id) {
		console.log(["normal:delete",widget_id,dashboard_id]);
	},
	deleteSmallWidget: function(widget_id) {
		console.log(["small:delete",widget_id]);
	},
	showDashboard: function(dashboard_id) {
		console.log(["dashboard",dashboard_id]);
	}
});
