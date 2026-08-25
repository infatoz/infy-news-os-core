(function () {
	'use strict';

	var cfg = window.inosPush;
	if (!cfg || !cfg.config || !cfg.config.vapidKey) {
		return;
	}
	if (!('Notification' in window) || !('serviceWorker' in navigator)) {
		return;
	}
	var host = window.location.hostname;
	if (window.location.protocol !== 'https:' && host !== 'localhost' && host !== '127.0.0.1') {
		return;
	}

	var sdk = cfg.sdk || '11.6.0';
	var root = 'https://www.gstatic.com/firebasejs/' + sdk + '/';
	var dismissedKey = 'inos-push-dismissed';
	var storageOk = true;
	try {
		window.localStorage.setItem('inos-push-ping', '1');
		window.localStorage.removeItem('inos-push-ping');
	} catch (e) {
		storageOk = false;
	}

	function loadFirebase() {
		return Promise.all([
			import(root + 'firebase-app.js'),
			import(root + 'firebase-messaging.js')
		]).then(function (mods) {
			return {
				initializeApp: mods[0].initializeApp,
				getMessaging: mods[1].getMessaging,
				getToken: mods[1].getToken,
				onMessage: mods[1].onMessage,
				isSupported: mods[1].isSupported
			};
		});
	}

	function showNotification(reg, payload) {
		var data = Object.assign({}, payload.data || {}, payload.notification || {});
		if (!data.title || Notification.permission !== 'granted') {
			return;
		}
		reg.showNotification(data.title, {
			body: data.body || '',
			icon: data.icon || cfg.logo || '',
			image: data.image || '',
			badge: data.badge || data.icon || cfg.logo || '',
			tag: data.tag || 'inos-article',
			renotify: true,
			data: { url: data.url || '/' },
			actions: [{ action: 'read_more', title: cfg.readMore || 'Read more' }]
		});
	}

	function postToken(token) {
		return fetch(cfg.rest, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': cfg.nonce
			},
			body: JSON.stringify({ token: token })
		});
	}

	function subscribe(fb, app, reg) {
		return fb.getToken(fb.getMessaging(app), {
			vapidKey: cfg.config.vapidKey,
			serviceWorkerRegistration: reg
		}).then(function (token) {
			if (!token) {
				return;
			}
			return postToken(token);
		});
	}

	function renderPrompt(onAllow, onDismiss) {
		var mount = document.getElementById('inos-push-root');
		if (!mount) {
			return;
		}
		mount.hidden = false;
		mount.innerHTML =
			'<div class="inos-push" role="dialog" aria-labelledby="inos-push-title">' +
			(cfg.logo ? '<img class="inos-push__logo" src="' + cfg.logo.replace(/"/g, '&quot;') + '" alt="" width="48" height="48" />' : '') +
			'<div class="inos-push__copy">' +
			'<p class="inos-push__title" id="inos-push-title"></p>' +
			'<p class="inos-push__text"></p>' +
			'<p class="inos-push__actions">' +
			'<button type="button" class="inos-push__allow"></button>' +
			'<button type="button" class="inos-push__dismiss"></button>' +
			'</p></div></div>';
		mount.querySelector('.inos-push__title').textContent = cfg.title || '';
		mount.querySelector('.inos-push__text').textContent = cfg.text || '';
		mount.querySelector('.inos-push__allow').textContent = cfg.allow || 'Allow';
		mount.querySelector('.inos-push__dismiss').textContent = cfg.dismiss || 'Not now';
		mount.querySelector('.inos-push__allow').addEventListener('click', onAllow);
		mount.querySelector('.inos-push__dismiss').addEventListener('click', onDismiss);
	}

	function hidePrompt() {
		var mount = document.getElementById('inos-push-root');
		if (mount) {
			mount.hidden = true;
			mount.innerHTML = '';
		}
	}

	function boot() {
		loadFirebase().then(function (fb) {
			return fb.isSupported().then(function (ok) {
				if (!ok) {
					return;
				}
				var app = fb.initializeApp({
					apiKey: cfg.config.apiKey,
					authDomain: cfg.config.authDomain,
					projectId: cfg.config.projectId,
					storageBucket: cfg.config.storageBucket,
					messagingSenderId: cfg.config.messagingSenderId,
					appId: cfg.config.appId
				});
				return navigator.serviceWorker.register(cfg.swUrl, { scope: '/' }).then(function (reg) {
					fb.onMessage(fb.getMessaging(app), function (payload) {
						showNotification(reg, payload);
					});

					if (Notification.permission === 'granted') {
						return subscribe(fb, app, reg);
					}
					if (Notification.permission === 'denied') {
						return;
					}
					if (storageOk) {
						var until = parseInt(window.localStorage.getItem(dismissedKey) || '0', 10);
						if (until && Date.now() < until) {
							return;
						}
					}

					window.setTimeout(function () {
						renderPrompt(
							function () {
								hidePrompt();
								Notification.requestPermission().then(function (perm) {
									if (perm === 'granted') {
										subscribe(fb, app, reg);
									}
								});
							},
							function () {
								hidePrompt();
								if (storageOk) {
									window.localStorage.setItem(dismissedKey, String(Date.now() + 7 * 24 * 60 * 60 * 1000));
								}
							}
						);
					}, cfg.delay || 8000);
				});
			});
		}).catch(function () {});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
