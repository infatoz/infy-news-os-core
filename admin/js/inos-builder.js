(function ($) {
	'use strict';

	var cfg = window.inosBuilder || {};
	var root = document.querySelector('[data-inos-builder]');
	var list = (root && root.querySelector('.inos-builder__list')) || document.getElementById('inos-builder-list');
	var jsonField = (root && root.querySelector('.inos-builder__json')) || document.getElementById('inos-home-modules');
	if (!list || !jsonField) {
		return;
	}

	function parseModules() {
		try {
			var data = JSON.parse(jsonField.value || '[]');
			return Array.isArray(data) ? data : [];
		} catch (err) {
			return [];
		}
	}

	function uid() {
		return 'mod_' + Math.random().toString(36).slice(2, 10);
	}

	function typeLabel(type) {
		return (cfg.types && cfg.types[type] && cfg.types[type].label) || type;
	}

	function typeFields(type) {
		return (cfg.types && cfg.types[type] && cfg.types[type].fields) || [];
	}

	function layoutsFor(type) {
		if (type === 'hero') {
			return cfg.layouts.hero || {};
		}
		if (type === 'web_stories') {
			return cfg.layouts.stories || {};
		}
		if (type === 'split') {
			return cfg.layouts.split || {};
		}
		return cfg.layouts.block || {};
	}

	function optionsHtml(map, selected) {
		var html = '';
		Object.keys(map || {}).forEach(function (key) {
			html += '<option value="' + escapeAttr(key) + '"' + (String(selected) === String(key) ? ' selected' : '') + '>' + escapeHtml(map[key]) + '</option>';
		});
		return html;
	}

	function escapeHtml(str) {
		return String(str == null ? '' : str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function escapeAttr(str) {
		return escapeHtml(str);
	}

	function fieldRow(label, inner) {
		return '<p class="inos-builder__field"><label>' + escapeHtml(label) + '</label>' + inner + '</p>';
	}

	function checkbox(name, checked, label) {
		return '<label class="inos-builder__check"><input type="checkbox" data-key="' + name + '"' + (checked ? ' checked' : '') + ' /> ' + escapeHtml(label) + '</label>';
	}

	function renderFields(mod) {
		var fields = typeFields(mod.type);
		var html = '';
		fields.forEach(function (key) {
			if (key === 'title') {
				html += fieldRow('Heading', '<input type="text" data-key="title" value="' + escapeAttr(mod.title || '') + '" />');
			} else if (key === 'subtitle') {
				html += fieldRow('Intro / subtitle', '<textarea data-key="subtitle" rows="2">' + escapeHtml(mod.subtitle || '') + '</textarea>');
			} else if (key === 'category') {
				html += fieldRow('Category', '<select data-key="category">' + optionsHtml(cfg.cats, mod.category || 0) + '</select>');
			} else if (key === 'tag') {
				html += fieldRow('Tag', '<select data-key="tag">' + optionsHtml(cfg.tags, mod.tag || 0) + '</select>');
			} else if (key === 'count') {
				html += fieldRow('Number of items', '<input type="number" min="1" max="24" data-key="count" value="' + escapeAttr(mod.count || 4) + '" />');
			} else if (key === 'layout') {
				html += fieldRow('Layout', '<select data-key="layout">' + optionsHtml(layoutsFor(mod.type), mod.layout || '') + '</select>');
			} else if (key === 'orderby') {
				html += fieldRow('Order', '<select data-key="orderby">' + optionsHtml(cfg.orderby, mod.orderby || 'date') + '</select>');
			} else if (key === 'more_text') {
				html += fieldRow('More link text', '<input type="text" data-key="more_text" value="' + escapeAttr(mod.more_text || '') + '" placeholder="More" />');
			} else if (key === 'tabs') {
				html += fieldRow('Tab categories (IDs, comma-separated)', '<input type="text" data-key="tabs" value="' + escapeAttr(mod.tabs || '') + '" placeholder="3,12,18" />');
			} else if (key === 'ad_slot') {
				html += fieldRow('Ad slot', '<select data-key="ad_slot">' + optionsHtml(cfg.slots, mod.ad_slot || 'between_cards') + '</select>');
			} else if (key === 'html') {
				html += fieldRow('HTML', '<textarea data-key="html" rows="5">' + escapeHtml(mod.html || '') + '</textarea>');
			} else if (key === 'menu') {
				html += fieldRow('WordPress menu', '<select data-key="menu">' + optionsHtml(cfg.menus, mod.menu || 0) + '</select>');
			} else if (key === 'unique') {
				html += '<p class="inos-builder__field">' + checkbox('unique', !!parseInt(mod.unique, 10), 'Skip stories already shown above') + '</p>';
			} else if (key === 'show_more') {
				html += '<p class="inos-builder__field">' + checkbox('show_more', !!parseInt(mod.show_more, 10), 'Show “More” link') + '</p>';
			} else if (key === 'show_excerpt') {
				html += '<p class="inos-builder__field">' + checkbox('show_excerpt', !!parseInt(mod.show_excerpt, 10), 'Show excerpt / dek') + '</p>';
			} else if (key === 'show_meta') {
				html += '<p class="inos-builder__field">' + checkbox('show_meta', !!parseInt(mod.show_meta, 10), 'Show date') + '</p>';
			} else if (key === 'show_thumb') {
				html += '<p class="inos-builder__field">' + checkbox('show_thumb', !!parseInt(mod.show_thumb, 10), 'Show thumbnails') + '</p>';
			} else if (key === 'dark') {
				html += '<p class="inos-builder__field">' + checkbox('dark', !!parseInt(mod.dark, 10), 'Dark band') + '</p>';
			}
		});
		return html;
	}

	function renderModule(mod) {
		var li = document.createElement('li');
		li.className = 'inos-builder__item' + (parseInt(mod.enabled, 10) ? '' : ' is-off');
		li.setAttribute('data-id', mod.id);
		li.setAttribute('data-type', mod.type);
		li.innerHTML =
			'<div class="inos-builder__bar">' +
				'<span class="inos-builder__handle dashicons dashicons-menu" aria-hidden="true"></span>' +
				'<strong class="inos-builder__name">' + escapeHtml(typeLabel(mod.type)) + '</strong>' +
				'<span class="inos-builder__hint">' + escapeHtml(mod.title || '') + '</span>' +
				'<label class="inos-builder__on"><input type="checkbox" data-key="enabled"' + (parseInt(mod.enabled, 10) ? ' checked' : '') + ' /> On</label>' +
				'<button type="button" class="button-link inos-builder__toggle">Edit</button>' +
				'<button type="button" class="button-link inos-builder__remove">Remove</button>' +
			'</div>' +
			'<div class="inos-builder__body" hidden>' + renderFields(mod) + '</div>';
		return li;
	}

	function readModule(li) {
		var current = JSON.parse(JSON.stringify(cfg.blank || {}));
		current.id = li.getAttribute('data-id') || uid();
		current.type = li.getAttribute('data-type') || 'posts';
		li.querySelectorAll('[data-key]').forEach(function (el) {
			var key = el.getAttribute('data-key');
			if (el.type === 'checkbox') {
				current[key] = el.checked ? 1 : 0;
			} else if (key === 'category' || key === 'tag' || key === 'count' || key === 'menu') {
				current[key] = parseInt(el.value, 10) || 0;
			} else {
				current[key] = el.value;
			}
		});
		var hint = li.querySelector('.inos-builder__hint');
		if (hint) {
			hint.textContent = current.title || '';
		}
		li.classList.toggle('is-off', !parseInt(current.enabled, 10));
		return current;
	}

	function sync() {
		var mods = [];
		list.querySelectorAll('.inos-builder__item').forEach(function (li) {
			mods.push(readModule(li));
		});
		jsonField.value = JSON.stringify(mods);
	}

	function paint(mods) {
		list.innerHTML = '';
		(mods || []).forEach(function (mod) {
			if (!mod.id) {
				mod.id = uid();
			}
			list.appendChild(renderModule(mod));
		});
		jsonField.value = JSON.stringify(mods || []);
		if (window.jQuery) {
			window.jQuery(list).sortable({
				handle: '.inos-builder__handle',
				placeholder: 'inos-builder__placeholder',
				update: sync
			});
		}
	}

	function addType(type) {
		if (!type || !cfg.types[type]) {
			return;
		}
		var mod = JSON.parse(JSON.stringify(cfg.blank || {}));
		mod.id = uid();
		mod.type = type;
		mod.enabled = 1;
		if (type === 'hero') {
			mod.layout = 'lead-grid';
			mod.count = 4;
		} else if (type === 'web_stories') {
			mod.layout = 'circles';
			mod.count = 10;
		} else if (type === 'split') {
			mod.layout = 'latest-trending';
		} else if (type === 'ad' && cfg.defaultAdSlot) {
			mod.ad_slot = cfg.defaultAdSlot;
		}
		var mods = parseModules();
		mods.push(mod);
		paint(mods);
	}

	list.addEventListener('click', function (event) {
		var item = event.target.closest('.inos-builder__item');
		if (!item) {
			return;
		}
		if (event.target.classList.contains('inos-builder__toggle')) {
			var body = item.querySelector('.inos-builder__body');
			if (body) {
				body.hidden = !body.hidden;
				event.target.textContent = body.hidden ? 'Edit' : 'Close';
			}
		}
		if (event.target.classList.contains('inos-builder__remove')) {
			item.remove();
			sync();
		}
	});

	list.addEventListener('change', sync);
	list.addEventListener('input', sync);

	var addSelect = (root && root.querySelector('[data-inos-builder-add]')) || document.getElementById('inos-builder-add');
	var addBtn = (root && root.querySelector('[data-inos-builder-add-btn]')) || document.getElementById('inos-builder-add-btn');
	if (addBtn && addSelect) {
		addBtn.addEventListener('click', function () {
			addType(addSelect.value);
			addSelect.value = '';
		});
	}

	var resetBtn = (root && root.querySelector('[data-inos-builder-reset]')) || document.getElementById('inos-builder-reset');
	if (resetBtn) {
		resetBtn.addEventListener('click', function () {
			var msg = cfg.resetConfirm || 'Replace the stack with the current homepage layout (hero, sections, latest/trending, newsletter)?';
			if (window.confirm(msg)) {
				paint(cfg.defaults || []);
			}
		});
	}

	paint(parseModules());
	window.addEventListener('submit', sync, true);
})(window.jQuery);
