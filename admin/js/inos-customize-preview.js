(function (api) {
	'use strict';

	function previewTheme(dark) {
		document.documentElement.classList.toggle('inos-dark', dark);
		document.querySelectorAll('[data-inos-theme]').forEach(function (btn) {
			btn.setAttribute('aria-pressed', dark ? 'true' : 'false');
		});
	}

	function bindColor(key, cssVar, mode) {
		api(key, function (value) {
			value.bind(function (to) {
				document.documentElement.style.setProperty(cssVar, to);
				if (mode === 'dark') {
					previewTheme(true);
				} else if (mode === 'light') {
					previewTheme(false);
				}
			});
		});
	}

	bindColor('inos_settings[accent_color]', '--inos-light-accent', 'light');
	bindColor('inos_settings[secondary_color]', '--inos-light-secondary', 'light');
	bindColor('inos_settings[paper_color]', '--inos-light-paper', 'light');
	bindColor('inos_settings[card_color]', '--inos-light-card', 'light');
	bindColor('inos_settings[ink_color]', '--inos-light-ink', 'light');
	bindColor('inos_settings[muted_color]', '--inos-light-muted', 'light');
	bindColor('inos_settings[line_color]', '--inos-light-line', 'light');
	bindColor('inos_settings[mast_color]', '--inos-light-mast', 'light');
	bindColor('inos_settings[breaking_color]', '--inos-light-breaking', 'light');
	bindColor('inos_settings[dark_accent_color]', '--inos-dark-accent', 'dark');
	bindColor('inos_settings[dark_secondary_color]', '--inos-dark-secondary', 'dark');
	bindColor('inos_settings[dark_paper_color]', '--inos-dark-paper', 'dark');
	bindColor('inos_settings[dark_card_color]', '--inos-dark-card', 'dark');
	bindColor('inos_settings[dark_ink_color]', '--inos-dark-ink', 'dark');
	bindColor('inos_settings[dark_muted_color]', '--inos-dark-muted', 'dark');
	bindColor('inos_settings[dark_line_color]', '--inos-dark-line', 'dark');
	bindColor('inos_settings[dark_mast_color]', '--inos-dark-mast', 'dark');
	bindColor('inos_settings[dark_breaking_color]', '--inos-dark-breaking', 'dark');
	bindColor('inos_settings[drawer_bg_color]', '--inos-drawer-bg');
	bindColor('inos_settings[drawer_link_color]', '--inos-drawer-link');
	bindColor('inos_settings[drawer_hover_color]', '--inos-drawer-hover');
	bindColor('inos_settings[drawer_muted_color]', '--inos-drawer-muted');

	function openDrawerPreview() {
		var drawer = document.getElementById('inos-drawer');
		var toggle = document.querySelector('[data-inos-nav-toggle]');
		if (!drawer) {
			return;
		}
		drawer.classList.add('is-open');
		document.body.classList.add('inos-nav-open');
		if (toggle) {
			toggle.setAttribute('aria-expanded', 'true');
		}
	}

	['drawer_bg_color', 'drawer_link_color', 'drawer_hover_color', 'drawer_muted_color'].forEach(function (key) {
		api('inos_settings[' + key + ']', function (value) {
			value.bind(openDrawerPreview);
		});
	});

	api('inos_settings[show_subscribe_cta]', function (value) {
		value.bind(function (to) {
			document.body.classList.toggle('inos-hide-subscribe', !to);
		});
	});

	api('inos_settings[show_breaking_ticker]', function (value) {
		value.bind(function (to) {
			document.body.classList.toggle('inos-hide-ticker', !to);
		});
	});

	api('inos_settings[sticky_header_desktop]', function (value) {
		value.bind(function (to) {
			var on = !!to;
			document.body.classList.toggle('inos-has-sticky-head-desktop', on);
			document.body.classList.toggle(
				'inos-has-sticky-head',
				on || document.body.classList.contains('inos-has-sticky-head-mobile')
			);
			if (!document.body.classList.contains('inos-has-sticky-head')) {
				document.body.classList.remove('inos-mast-stuck');
			}
			window.dispatchEvent(new Event('scroll'));
		});
	});

	api('inos_settings[sticky_header_mobile]', function (value) {
		value.bind(function (to) {
			var on = !!to;
			document.body.classList.toggle('inos-has-sticky-head-mobile', on);
			document.body.classList.toggle(
				'inos-has-sticky-head',
				on || document.body.classList.contains('inos-has-sticky-head-desktop')
			);
			if (!document.body.classList.contains('inos-has-sticky-head')) {
				document.body.classList.remove('inos-mast-stuck');
			}
			window.dispatchEvent(new Event('scroll'));
		});
	});

	api('inos_settings[sticky_share]', function (value) {
		value.bind(function (to) {
			var on = !!to;
			document.body.classList.toggle('inos-has-sticky-share', on);
			document.body.classList.toggle('inos-hide-sticky-share', !on);
			var el = document.querySelector('[data-inos-sticky-share]');
			var amp = document.querySelector('.inos-amp-share-bar');
			if (!on) {
				if (el) {
					el.hidden = true;
					el.classList.remove('is-visible');
				}
				if (amp) {
					amp.hidden = true;
				}
				return;
			}
			if (amp) {
				amp.hidden = false;
			}
			if (!el) {
				return;
			}
			var headerShare = document.querySelector('.inos-article__header .inos-article-tools');
			if (!headerShare) {
				el.hidden = false;
				el.classList.add('is-visible');
				return;
			}
			var rect = headerShare.getBoundingClientRect();
			var inView = rect.bottom > 0 && rect.top < window.innerHeight;
			el.hidden = inView;
			el.classList.toggle('is-visible', !inView);
		});
	});

	function topbarText() {
		var setting = api('inos_settings[topbar_text]');
		var raw = setting ? String(setting.get() || '').trim() : '';
		return raw || (window.inosCustomize && inosCustomize.tagline) || '';
	}

	function syncTopbarText() {
		var show = api('inos_settings[show_topbar_text]');
		var visible = !show || !!show.get();
		var text = topbarText();
		document.querySelectorAll('[data-inos-topbar-text]').forEach(function (el) {
			el.textContent = text;
			el.hidden = !visible || !text;
		});
	}

	api('inos_settings[topbar_text]', function (value) {
		value.bind(syncTopbarText);
	});
	api('inos_settings[show_topbar_text]', function (value) {
		value.bind(syncTopbarText);
	});

	api('inos_settings[masthead_identity]', function (value) {
		value.bind(function (to) {
			var brand = document.querySelector('.inos-brand');
			if (!brand) {
				return;
			}
			var mode = to || 'logo';
			var hasLogo = !!brand.querySelector('.custom-logo-link');
			if (!hasLogo) {
				if (mode === 'logo' || mode === 'logo_title') {
					mode = 'title';
				} else if (mode === 'logo_tagline' || mode === 'logo_title_tagline') {
					mode = 'title_tagline';
				}
			}
			brand.setAttribute('data-inos-brand', mode);
		});
	});

	function bindFont(key, cssVar, mapKey) {
		api(key, function (value) {
			value.bind(function (to) {
				var map = (window.inosCustomize && inosCustomize[mapKey]) || {};
				if (map[to]) {
					document.documentElement.style.setProperty(cssVar, map[to]);
				}
			});
		});
	}

	bindFont('inos_settings[font_sans]', '--inos-sans', 'sans');
	bindFont('inos_settings[font_serif]', '--inos-serif', 'serif');

	function applyPresetChrome(id) {
		var presets = (window.inosCustomize && inosCustomize.presets) || {};
		var ids = Object.keys(presets);
		ids.forEach(function (name) {
			document.body.classList.remove('inos-preset-' + name);
		});
		document.body.classList.remove('inos-preset-app');
		if (!id) {
			id = 'editorial';
		}
		document.body.classList.add('inos-preset-' + id);
		if (id === 'app') {
			document.body.classList.add('inos-preset-app');
		}
		var preset = presets[id] || {};
		if (preset.radius) {
			document.documentElement.style.setProperty('--inos-radius', preset.radius);
		}
		if (preset.shadow) {
			document.documentElement.style.setProperty('--inos-shadow', preset.shadow);
		}
		var titles = ['bar', 'underline', 'boxed', 'pill', 'minimal'];
		titles.forEach(function (style) {
			document.body.classList.remove('inos-titles--' + style);
			document.querySelectorAll('.inos-home').forEach(function (el) {
				el.classList.remove('inos-titles--' + style);
			});
		});
		if (preset.title_style) {
			document.body.classList.add('inos-titles--' + preset.title_style);
			document.querySelectorAll('.inos-home').forEach(function (el) {
				el.classList.add('inos-titles--' + preset.title_style);
			});
		}
	}

	api('inos_settings[theme_preset]', function (value) {
		value.bind(applyPresetChrome);
	});

	api('inos_settings[home_title_style]', function (value) {
		value.bind(function (to) {
			var titles = ['bar', 'underline', 'boxed', 'pill', 'minimal'];
			titles.forEach(function (style) {
				document.body.classList.toggle('inos-titles--' + style, style === to);
				document.querySelectorAll('.inos-home').forEach(function (el) {
					el.classList.toggle('inos-titles--' + style, style === to);
				});
			});
		});
	});
})(wp.customize);
