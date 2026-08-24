(function () {
	'use strict';

	if (!window.inosLive || !inosLive.rest) {
		return;
	}

	var after = inosLive.after || '';
	var list = document.querySelector('[data-inos-live-updates]');
	var empty = document.querySelector('[data-inos-live-empty]');
	var countEl = document.querySelector('[data-inos-live-count-label]');
	var timer = null;
	var pollMs = parseInt(inosLive.poll, 10) || 0;

	if (!list) {
		return;
	}

	function escapeAttr(value) {
		return String(value || '')
			.replace(/&/g, '&amp;')
			.replace(/"/g, '&quot;')
			.replace(/</g, '&lt;');
	}

	function shareControl(update) {
		var wrap = document.createElement('div');
		wrap.className = 'inos-share inos-share--native-only inos-live-update__share is-native-ready';
		wrap.setAttribute('data-inos-share', '');
		wrap.setAttribute('data-inos-share-title', update.title || '');
		wrap.setAttribute('data-inos-share-text', update.share_text || update.title || '');
		wrap.innerHTML =
			'<ul class="inos-share__list">' +
			'<li class="inos-share__item">' +
			'<button type="button" class="inos-share__btn inos-share__btn--native" data-inos-share-network="native" data-inos-copy-url="' +
			escapeAttr(update.url || '') +
			'" aria-label="' +
			escapeAttr(inosLive.share || 'Share this update') +
			'">' +
			(inosLive.icon || '') +
			'<span class="screen-reader-text">' +
			escapeAttr(inosLive.share || 'Share this update') +
			'</span></button></li></ul>' +
			'<span class="inos-share__status" data-inos-share-status aria-live="polite"></span>';
		return wrap;
	}

	function prepend(update, isNew) {
		var article = document.createElement('article');
		article.className = 'inos-live-update' + (isNew ? ' is-new' : '');
		article.id = 'inos-update-' + update.id;
		article.setAttribute('data-update-id', String(update.id));

		var head = document.createElement('header');
		head.className = 'inos-live-update__head';

		var time = document.createElement('time');
		time.className = 'inos-live-update__time';
		time.setAttribute('datetime', update.date || '');
		time.textContent = update.display || '';
		head.appendChild(time);

		if (isNew) {
			var mark = document.createElement('span');
			mark.className = 'inos-live-update__new';
			mark.textContent = inosLive.fresh || 'New';
			head.appendChild(mark);
		}

		head.appendChild(shareControl(update));
		article.appendChild(head);

		var title = document.createElement('h2');
		title.className = 'inos-live-update__title';
		title.textContent = update.title || '';
		article.appendChild(title);

		var body = document.createElement('div');
		body.className = 'inos-live-update__content';
		body.innerHTML = update.content || '';
		article.appendChild(body);

		list.insertBefore(article, list.firstChild);
		if (empty) {
			empty.hidden = true;
		}
		if (countEl) {
			var n = list.querySelectorAll('.inos-live-update').length;
			countEl.hidden = n < 1;
			countEl.textContent = (inosLive.nUpdates || '%s updates').replace('%s', String(n));
		}
	}

	function revealHash() {
		var id = (window.location.hash || '').replace(/^#/, '');
		if (!id) {
			return;
		}
		var el = document.getElementById(id);
		if (!el) {
			return;
		}
		el.classList.add('is-target');
		window.setTimeout(function () {
			el.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}, 80);
	}

	function stop() {
		if (timer) {
			window.clearInterval(timer);
			timer = null;
		}
		pollMs = 0;
	}

	function poll() {
		if (!pollMs) {
			return;
		}
		var url = inosLive.rest + (inosLive.rest.indexOf('?') === -1 ? '?' : '&') + 'after=' + encodeURIComponent(after);
		fetch(url, { credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (data) {
				if (!data || !data.updates) {
					return;
				}
				data.updates.slice().reverse().forEach(function (u) {
					if (!list.querySelector('[data-update-id="' + u.id + '"]')) {
						prepend(u, true);
					}
					if (!after || Date.parse(u.date) > Date.parse(after)) {
						after = u.date;
					}
				});
				if (data.closed) {
					var badge = document.querySelector('[data-inos-live-status]');
					if (badge) {
						badge.textContent = inosLive.ended || 'Coverage ended';
						badge.classList.remove('inos-live__badge--on');
						badge.classList.add('inos-live__badge--off');
					}
					var hint = document.querySelector('[data-inos-live-hint]');
					if (hint) {
						hint.hidden = true;
					}
					stop();
				}
			})
			.catch(function () { /* ignore */ });
	}

	function start() {
		if (!pollMs || timer) {
			return;
		}
		poll();
		timer = window.setInterval(poll, pollMs);
	}

	document.addEventListener('visibilitychange', function () {
		if (document.hidden) {
			if (timer) {
				window.clearInterval(timer);
				timer = null;
			}
		} else {
			start();
		}
	});

	start();
	revealHash();
	window.addEventListener('hashchange', revealHash);
})();
