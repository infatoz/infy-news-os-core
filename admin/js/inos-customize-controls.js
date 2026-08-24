(function (api) {
	'use strict';

	var catalog = (window.inosCustomizeControls && inosCustomizeControls.presets) || {};

	function setSetting(key, value) {
		var setting = api(key);
		if (setting) {
			setting.set(value);
		}
	}

	api('inos_settings[theme_preset]', function (value) {
		value.bind(function (to) {
			var preset = catalog[to];
			if (!preset) {
				return;
			}
			if (preset.colors) {
				Object.keys(preset.colors).forEach(function (key) {
					setSetting('inos_settings[' + key + ']', preset.colors[key]);
				});
			}
			if (preset.font_sans) {
				setSetting('inos_settings[font_sans]', preset.font_sans);
			}
			if (preset.font_serif) {
				setSetting('inos_settings[font_serif]', preset.font_serif);
			}
			if (preset.title_style) {
				setSetting('inos_settings[home_title_style]', preset.title_style);
			}
		});
	});
})(wp.customize);
