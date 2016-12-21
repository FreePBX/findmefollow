var FindmefollowC = UCPMC.extend({
	init: function(){
	},
	displayWidget: function(widget_id,dashboard_id) {
		var self = this;
		$("div[data-id='"+widget_id+"'] .widget-settings-content input[type='checkbox']").change(function() {
			var extension = $("div[data-id='"+widget_id+"']").data("widget_type_id");
			self.saveSettings(extension, {key: $(this).prop('name'), value: $(this).is(':checked')});
		});
	},
	saveSettings: function(extension, data, callback) {
		data.ext = extension;
		$.post( "index.php?quietmode=1&module=findmefollow&command=settings", data, callback);
	},
	displayWidgetSettings: function(widget_id,dashboard_id) {
		var self = this;
		var extension = $("div[data-id='"+widget_id+"']").data("widget_type_id");

		$("#widget_settings .widget-settings-content textarea").blur(function() {
			self.saveSettings(extension, {key: $(this).prop('name'), value: $(this).val()}, function(data){
				if (data.status) {
					$("#widget_settings .message").addClass("alert-success");
					$("#widget_settings .message").text(_("Your settings have been saved"));
					$("#widget_settings .message").fadeIn( "slow", function() {
						setTimeout(function() { $("#widget_settings .message").fadeOut("slow"); }, 2000);
					});
				} else {
					$("#widget_settings .message").addClass("alert-error");
					$("#widget_settings .message").text(data.message);
					return false;
				}
			});
		});
		$("#widget_settings .widget-settings-content select").change(function() {
			self.saveSettings(extension, {key: $(this).prop('name'), value: $(this).is(':checked')}, function(data){
				if (data.status) {
					$("#widget_settings .message").addClass("alert-success");
					$("#widget_settings .message").text(_("Your settings have been saved"));
					$("#widget_settings .message").fadeIn( "slow", function() {
						setTimeout(function() { $("#widget_settings .message").fadeOut("slow"); }, 2000);
					});
				} else {
					$("#widget_settings .message").addClass("alert-error");
					$("#widget_settings .message").text(data.message);
					return false;
				}
			});
		});
		$("#widget_settings .widget-settings-content input[type='checkbox']").change(function() {
			self.saveSettings(extension, {key: $(this).prop('name'), value: $(this).is(':checked')}, function(data){
				if (data.status) {
					$("#widget_settings .message").addClass("alert-success");
					$("#widget_settings .message").text(_("Your settings have been saved"));
					$("#widget_settings .message").fadeIn( "slow", function() {
						setTimeout(function() { $("#widget_settings .message").fadeOut("slow"); }, 2000);
					});
				} else {
					$("#widget_settings .message").addClass("alert-error");
					$("#widget_settings .message").text(data.message);
					return false;
				}
			});
		});
	},
	displaySimpleWidget: function(widget_type_id) {
		var self = this;
		$(".widget-extra-menu[data-module=findmefollow] input[type='checkbox']").change(function() {
			var extension = widget_type_id,
					checked = $(this).is(':checked');
			self.saveSettings(extension, {key: $(this).prop('name'), value: checked}, function(data){
				if (data.status) {
					//update elements on the current dashboard if there are any
					var el = $(".grid-stack-item[data-rawname='findmefollow'][data-widget_type_id='"+extension+"'] input[name='ddial']");
					el.prop("checked",checked);
					el.bootstrapToggle('destroy');
					el.bootstrapToggle();
				}
			});
		});
	},
	displaySimpleWidgetSettings: function(widget_id) {
		this.displayWidgetSettings(widget_id);
	},
	deleteWidget: function(widget_id,dashboard_id) {
		//console.log(["normal:delete",widget_id,dashboard_id]);
	},
	deleteSimpleWidget: function(widget_id) {
		//console.log(["small:delete",widget_id]);
	},
	showDashboard: function(dashboard_id) {
		//console.log(["dashboard",dashboard_id]);
	}
});
