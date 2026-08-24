(function () {
	'use strict';

	function bindLogoPicker() {
		var button = document.getElementById('inos-logo-select');
		var input = document.getElementById('inos_logo_id');
		if (!button || !input || typeof wp === 'undefined' || !wp.media) {
			return;
		}
		button.addEventListener('click', function (e) {
			e.preventDefault();
			var frame = wp.media({
				title: 'Select publisher logo',
				button: { text: 'Use logo' },
				multiple: false
			});
			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				input.value = att.id;
			});
			frame.open();
		});
	}

	function bindAvatarPicker() {
		var button = document.getElementById('inos-avatar-select');
		var remove = document.getElementById('inos-avatar-remove');
		var input = document.getElementById('inos_avatar_id');
		var preview = document.getElementById('inos-avatar-preview');
		if (!button || !input || typeof wp === 'undefined' || !wp.media) {
			return;
		}
		button.addEventListener('click', function (e) {
			e.preventDefault();
			var frame = wp.media({
				title: 'Select author photo',
				button: { text: 'Use photo' },
				library: { type: 'image' },
				multiple: false
			});
			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				input.value = att.id;
				if (preview) {
					var src = (att.sizes && att.sizes.thumbnail && att.sizes.thumbnail.url) || att.url;
					preview.innerHTML = '<img src="' + String(src).replace(/"/g, '&quot;') + '" alt="" style="width:96px;height:96px;object-fit:cover;border-radius:50%;" />';
				}
				if (remove) {
					remove.style.display = '';
				}
			});
			frame.open();
		});
		if (remove) {
			remove.addEventListener('click', function (e) {
				e.preventDefault();
				input.value = '0';
				if (preview) {
					preview.innerHTML = '';
				}
				remove.style.display = 'none';
			});
		}
	}

	function featuredImageQa() {
		if (typeof wp === 'undefined' || !wp.data || !wp.data.select) {
			return;
		}
		var minWidth = (window.inosAdmin && inosAdmin.minWidth) ? inosAdmin.minWidth : 1200;
		var warnText = (window.inosAdmin && inosAdmin.warnText) ? inosAdmin.warnText : '';
		var lastId = 0;

		function ensureNotice() {
			var el = document.getElementById('inos-discover-warning');
			if (!el) {
				el = document.createElement('div');
				el.id = 'inos-discover-warning';
				el.style.display = 'none';
				var box = document.querySelector('.editor-post-featured-image') || document.querySelector('#postimagediv .inside');
				if (box) {
					box.appendChild(el);
				}
			}
			return el;
		}

		if (wp.data.subscribe) {
			wp.data.subscribe(function () {
				try {
					var id = wp.data.select('core/editor').getEditedPostAttribute('featured_media');
					if (!id || id === lastId) {
						if (!id) {
							var n = document.getElementById('inos-discover-warning');
							if (n) {
								n.style.display = 'block';
								n.textContent = warnText;
							}
						}
						return;
					}
					lastId = id;
					wp.apiRequest({ path: '/wp/v2/media/' + id }).done(function (media) {
						var w = media.media_details && media.media_details.width ? media.media_details.width : 0;
						var el = ensureNotice();
						if (el && w && w < minWidth) {
							el.style.display = 'block';
							el.textContent = warnText + ' (' + w + 'px)';
						} else if (el) {
							el.style.display = 'none';
						}
					});
				} catch (err) {
					/* editor not ready */
				}
			});
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		bindLogoPicker();
		bindAvatarPicker();
		featuredImageQa();
		bindAlsoReadPicker();
	});

	function bindAlsoReadPicker() {
		var root = document.querySelector('[data-inos-also-read]');
		if (!root) {
			return;
		}
		var hidden = root.querySelector('#inos_also_read_ids');
		var picked = root.querySelector('.inos-also-read__picked');
		var search = root.querySelector('.inos-also-read__search');
		var results = root.querySelector('.inos-also-read__results');
		if (!hidden || !picked || !search || !results) {
			return;
		}
		var max = parseInt(root.getAttribute('data-max') || '4', 10);
		var exclude = root.getAttribute('data-exclude') || '0';
		var timer = null;

		function ids() {
			return (hidden.value || '').split(',').map(function (id) {
				return parseInt(id, 10);
			}).filter(function (id) {
				return id > 0;
			});
		}

		function sync() {
			var current = [];
			picked.querySelectorAll('li[data-id]').forEach(function (li) {
				current.push(li.getAttribute('data-id'));
			});
			hidden.value = current.join(',');
		}

		function addItem(id, title) {
			if (ids().indexOf(parseInt(id, 10)) !== -1) {
				return;
			}
			if (ids().length >= max) {
				return;
			}
			var li = document.createElement('li');
			li.setAttribute('data-id', String(id));
			li.innerHTML = '<span></span> <button type="button" class="button-link inos-also-read__remove"></button>';
			li.querySelector('span').textContent = title;
			li.querySelector('button').textContent = 'Remove';
			picked.appendChild(li);
			sync();
			results.hidden = true;
			search.value = '';
		}

		picked.addEventListener('click', function (event) {
			var btn = event.target.closest('.inos-also-read__remove');
			if (!btn) {
				return;
			}
			event.preventDefault();
			var li = btn.closest('li');
			if (li) {
				li.remove();
				sync();
			}
		});

		results.addEventListener('click', function (event) {
			var btn = event.target.closest('button[data-id]');
			if (!btn) {
				return;
			}
			event.preventDefault();
			addItem(btn.getAttribute('data-id'), btn.getAttribute('data-title') || btn.textContent);
		});

		function runSearch() {
			if (!window.inosAdmin || !inosAdmin.ajax) {
				return;
			}
			var q = search.value.trim();
			var body = new URLSearchParams();
			body.set('action', 'inos_search_posts');
			body.set('nonce', inosAdmin.nonce || '');
			body.set('q', q);
			body.set('exclude', exclude);
			body.set('picked', hidden.value || '');
			fetch(inosAdmin.ajax, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
				body: body.toString()
			}).then(function (res) {
				return res.json();
			}).then(function (json) {
				results.innerHTML = '';
				var items = json && json.data ? json.data : [];
				if (!items.length) {
					results.hidden = true;
					return;
				}
				items.forEach(function (item) {
					var li = document.createElement('li');
					var btn = document.createElement('button');
					btn.type = 'button';
					btn.setAttribute('data-id', String(item.id));
					btn.setAttribute('data-title', item.title || '');
					btn.textContent = item.title || ('#' + item.id);
					li.appendChild(btn);
					results.appendChild(li);
				});
				results.hidden = false;
			}).catch(function () {
				results.hidden = true;
			});
		}

		search.addEventListener('input', function () {
			window.clearTimeout(timer);
			timer = window.setTimeout(runSearch, 220);
		});
		search.addEventListener('focus', function () {
			if (!search.value.trim()) {
				runSearch();
			}
		});
		document.addEventListener('click', function (event) {
			if (!root.contains(event.target)) {
				results.hidden = true;
			}
		});
	}
})();
