(function () {
	'use strict';

	function qs(id) {
		return document.getElementById(id);
	}

	function runImport() {
		var cfg = window.inosDemo || {};
		var button = qs('inos-demo-import');
		var wrap = qs('inos-demo-progress');
		var bar = wrap ? wrap.querySelector('span') : null;
		var meter = wrap ? wrap.querySelector('.inos-demo-progress__bar') : null;
		var label = wrap ? wrap.querySelector('.inos-demo-progress__label') : null;
		var done = qs('inos-demo-done');

		if (!cfg.ajax || !button) {
			return;
		}

		button.disabled = true;
		if (wrap) {
			wrap.hidden = false;
		}
		if (done) {
			done.hidden = true;
		}

		function setProgress(pct, text) {
			if (bar) {
				bar.style.width = pct + '%';
			}
			if (meter) {
				meter.setAttribute('aria-valuenow', String(pct));
			}
			if (label && text) {
				label.textContent = text;
			}
		}

		function fail(message) {
			button.disabled = false;
			setProgress(0, message || cfg.error || 'Import failed.');
		}

		function step(name, offset) {
			var body = new FormData();
			body.append('action', 'inos_demo_import');
			body.append('nonce', cfg.nonce);
			body.append('step', name);
			body.append('offset', String(offset || 0));

			fetch(cfg.ajax, {
				method: 'POST',
				credentials: 'same-origin',
				body: body
			})
				.then(function (res) {
					return res.text().then(function (text) {
						try {
							return JSON.parse(text);
						} catch (err) {
							throw new Error(text ? text.replace(/<[^>]+>/g, ' ').slice(0, 220) : cfg.error);
						}
					});
				})
				.then(function (json) {
					if (!json || !json.success) {
						fail(json && json.data && json.data.message ? json.data.message : cfg.error);
						return;
					}
					var data = json.data || {};
					setProgress(data.percent || 0, data.label || '');
					if (data.next === 'done') {
						button.disabled = false;
						if (done) {
							done.hidden = false;
							if (data.home) {
								var home = done.querySelector('a.button-primary');
								if (home) {
									home.href = data.home;
								}
							}
							if (data.customize) {
								var custom = done.querySelector('a.button:not(.button-primary)');
								if (custom) {
									custom.href = data.customize;
								}
							}
						}
						return;
					}
					step(data.next, data.offset || 0);
				})
				.catch(function (err) {
					fail(err && err.message ? err.message : cfg.error);
				});
		}

		setProgress(4, cfg.starting || 'Starting…');
		step('prepare', 0);
	}

	document.addEventListener('DOMContentLoaded', function () {
		var button = qs('inos-demo-import');
		if (button) {
			button.addEventListener('click', runImport);
		}
	});
})();
