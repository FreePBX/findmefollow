var FindmefollowC = UCPMC.extend({
	init: function(){
	},
	displayWidget: function() {
		var self = this;

		$("div[data-rawname='findmefollow'] input[type='text'], div[data-rawname='findmefollow'] textarea").change(function() {
			$(this).blur(function() {
				self.saveSettings({key: $(this).prop('name'), value: $(this).val()});
				$(this).off('blur');
			});
		});
		$("div[data-rawname='findmefollow'] input[type='checkbox']").change(function() {
			self.saveSettings({key: $(this).prop('name'), value: $(this).is(':checked')});
		});
		$("div[data-rawname='findmefollow'] select").change(function() {
			self.saveSettings({key: $(this).prop('name'), value: $(this).val()});
		});
	},
	saveSettings: function(data) {
		data.ext = extension;
		$.post( "index.php?quietmode=1&module=findmefollow&command=settings", data, function( data ) {
		});
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
