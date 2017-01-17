var FindmefollowC = UCPMC.extend({
	init: function(){
		this.stopPropagation = {};
	},
	prepoll: function() {
		var exts = [];
		$(".grid-stack-item[data-rawname=findmefollow]").each(function() {
			exts.push($(this).data("widget_type_id"));
		});
		return exts;
	},
	poll: function(data) {
		var self = this;
		var change = function(extension, state, el) {
			if(!el.length) {
				return;
			}
			var current = el.is(":checked");
			if(state && !current) {
				self.stopPropagation[extension] = true;
				el.bootstrapToggle('on');
				self.stopPropagation[extension] = false;
			} else if(!state && current) {
				self.stopPropagation[extension] = true;
				el.bootstrapToggle('off');
				self.stopPropagation[extension] = false;
			}
		};
		$.each(data.states, function(ext,state) {
			change(ext, state, $(".grid-stack-item[data-rawname=findmefollow][data-widget_type_id='"+ext+"'] input[name='ddial']"));
			change(ext, state, $(".widget-extra-menu[data-module='findmefollow'][data-widget_type_id='"+ext+"'] input[name='ddial']"));
		});
	},
	displayWidget: function(widget_id,dashboard_id) {
		var self = this;
		$("div[data-id='"+widget_id+"'] .widget-content input[type='checkbox']").change(function() {
			if(typeof self.stopPropagation[extension] !== "undefined" && self.stopPropagation[extension]) {
				return;
			}
			var extension = $("div[data-id='"+widget_id+"']").data("widget_type_id");
			self.saveSettings(extension, {key: $(this).prop('name'), value: $(this).is(':checked')});
		});
	},
	saveSettings: function(extension, data, callback) {
		var self = this;
		data.ext = extension;
		self.stopPropagation[extension] = true;
		data.module = "findmefollow";
		data.command = "settings";
		$.post( UCP.ajaxUrl, data, callback).always(function() {
			self.stopPropagation[extension] = false;
		});
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
			if(typeof self.stopPropagation[extension] !== "undefined" && self.stopPropagation[extension]) {
				return;
			}
			self.saveSettings(extension, {key: $(this).prop('name'), value: checked}, function(data){
				if (data.status) {
					//update elements on the current dashboard if there are any
					var el = $(".grid-stack-item[data-rawname='findmefollow'][data-widget_type_id='"+extension+"'] input[name='ddial']");
					if(checked) {
						el.bootstrapToggle('on');
					} else {
						el.bootstrapToggle('off');
					}
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
