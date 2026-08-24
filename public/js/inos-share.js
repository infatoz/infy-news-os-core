(function () {
	'use strict';

	function dataLayerPush(payload) {
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push(payload);
	}

	function shareContext(network, extra) {
		var cfg = window.inosShare || {};
		var payload = {
			event: 'share',
			event_name: 'inos_share',
			method: network,
			content_type: cfg.contentType || 'article',
			item_id: cfg.itemId || '',
			item_name: cfg.itemName || '',
			utm_source: network,
			utm_medium: network === 'email' ? 'email' : (cfg.utmMedium || 'social'),
			utm_campaign: cfg.utmCampaign || 'article-share'
		};
		if (extra) {
			Object.keys(extra).forEach(function (key) {
				payload[key] = extra[key];
			});
		}
		return payload;
	}

	function trackShare(network, extra) {
		dataLayerPush(shareContext(network, extra));
	}

	function shareRoot(node) {
		return (node && node.closest && node.closest('[data-inos-share]')) || document;
	}

	function shareTitle(button) {
		var root = shareRoot(button);
		return (button && button.getAttribute('data-inos-share-title')) ||
			(root && root.getAttribute && root.getAttribute('data-inos-share-title')) ||
			document.title;
	}

	function shareText(button) {
		var root = shareRoot(button);
		return (button && button.getAttribute('data-inos-share-text')) ||
			(root && root.getAttribute && root.getAttribute('data-inos-share-text')) ||
			shareTitle(button);
	}

	document.addEventListener('click', function (event) {
		var shareBtn = event.target.closest('[data-inos-share-network]');
		if (shareBtn) {
			var network = shareBtn.getAttribute('data-inos-share-network') || '';
			if (network === 'copy') {
				event.preventDefault();
				copyShareLink(shareBtn);
				return;
			}
			if (network === 'native') {
				event.preventDefault();
				nativeShare(shareBtn);
				return;
			}
			if (network) {
				trackShare(network);
			}
			return;
		}

		var preferred = event.target.closest('[data-inos-preferred-source]');
		if (preferred) {
			dataLayerPush({
				event: 'inos_preferred_source',
				method: preferred.getAttribute('data-inos-preferred-source') || 'deeplink',
				item_id: (window.inosShare && inosShare.itemId) || '',
				item_name: (window.inosShare && inosShare.itemName) || ''
			});
			return;
		}

		var related = event.target.closest('[data-inos-related-link]');
		if (related) {
			dataLayerPush({
				event: 'select_content',
				content_type: related.getAttribute('data-inos-recirc') === 'also-read' ? 'also_read' : 'related_article',
				event_name: related.getAttribute('data-inos-recirc') === 'also-read' ? 'inos_also_read_click' : 'inos_related_click',
				item_id: related.getAttribute('data-inos-related-id') || '',
				item_name: related.getAttribute('data-inos-related-title') || '',
				source_item_id: (window.inosShare && inosShare.itemId) || ''
			});
		}
	});

	function nativeShare(button) {
		var url = button.getAttribute('data-inos-copy-url') || window.location.href;
		var payload = {
			title: shareTitle(button),
			text: shareText(button),
			url: url
		};

		if (navigator.share) {
			navigator.share(payload).then(function () {
				trackShare('native');
				announce(button, (window.inosShare && inosShare.shared) || 'Shared');
			}).catch(function (err) {
				if (err && err.name === 'AbortError') {
					return;
				}
				copyShareLink(button);
			});
			return;
		}

		copyShareLink(button);
	}

	function copyShareLink(button) {
		var url = button.getAttribute('data-inos-copy-url') || window.location.href;
		var done = function () {
			trackShare('copy');
			announce(button, (window.inosShare && inosShare.copied) || 'Link copied');
		};
		var fail = function () {
			announce(button, (window.inosShare && inosShare.copyFailed) || 'Could not copy link');
		};

		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(url).then(done).catch(fail);
			return;
		}

		var input = document.createElement('input');
		input.value = url;
		input.setAttribute('readonly', '');
		input.style.position = 'absolute';
		input.style.left = '-9999px';
		document.body.appendChild(input);
		input.select();
		try {
			document.execCommand('copy');
			done();
		} catch (err) {
			fail();
		}
		document.body.removeChild(input);
	}

	function announce(button, message) {
		var live = button.parentNode && button.parentNode.querySelector('[data-inos-share-status]');
		if (!live) {
			live = shareRoot(button).querySelector('[data-inos-share-status]');
		}
		if (!live) {
			live = document.querySelector('[data-inos-share-status]');
		}
		if (live) {
			live.textContent = message;
		}
		button.classList.add('is-copied');
		button.setAttribute('aria-label', message);
		window.setTimeout(function () {
			button.classList.remove('is-copied');
		}, 2000);
	}

	function markNativeReady() {
		document.querySelectorAll('[data-inos-share]').forEach(function (el) {
			if (navigator.share || el.classList.contains('inos-share--native-only')) {
				el.classList.add('is-native-ready');
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', markNativeReady);
	} else {
		markNativeReady();
	}
})();
