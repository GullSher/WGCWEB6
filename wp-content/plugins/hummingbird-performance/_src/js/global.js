/* global wphbGlobal */

( function() {
	'use strict';

	const WPHBGlobal = {
		init() {
			this.registerClearAllCache();
			this.registerClearNetworkCache();
			this.registerClearCacheFromNotice();
			this.registerClearCloudflare();
			this.registerSafeModeActions();
		},

		/**
		 * Clear selected module from admin bar.
		 *
		 * @since 3.0.1
		 *
		 * @param {string} module Module ID.
		 */
		clearCache( module ) {
			jQuery
				.ajax( {
					url: wphbGlobal.ajaxurl,
					method: 'POST',
					data: {
						nonce: wphbGlobal.nonce,
						action: 'wphb_clear_caches',
						modules: [ module ],
					},
				} )
				.done( function() {
					location.reload();
				} );
		},

		/**
		 * Clear all cache from admin bar.
		 *
		 * @since 3.0.1
		 */
		registerClearAllCache() {
			const btn = document.getElementById(
				'wp-admin-bar-wphb-clear-all-cache'
			);

			if ( ! btn ) {
				return;
			}

			btn.addEventListener( 'click', () =>
				this.post( 'wphb_global_clear_cache' )
			);
		},

		/**
		 * Clear network cache.
		 */
		registerClearNetworkCache() {
			const btn = document.querySelector(
				'#wp-admin-bar-wphb-clear-cache-network-wide > a'
			);

			if ( ! btn ) {
				return;
			}

			btn.addEventListener( 'click', () => {
				if ( 'undefined' === typeof window.WPHB_Admin ) {
					window.location.href =
						'/wp-admin/network/admin.php?page=wphb-caching&update=open-ccnw';
					return;
				}

				window.SUI.openModal(
					'ccnw-modal',
					'wpbody',
					'ccnw-clear-now'
				);
			} );
		},

		/**
		 * Clear cache from notice regarding plugin/theme updates.
		 */
		registerClearCacheFromNotice() {
			const btn = document.getElementById(
				'wp-admin-notice-wphb-clear-cache'
			);

			if ( ! btn ) {
				return;
			}

			btn.addEventListener( 'click', () =>
				this.post( 'wphb_global_clear_cache' )
			);
		},

		/**
		 * Clear Cloudflare browser cache.
		 *
		 * @since 2.7.2
		 */
		registerClearCloudflare() {
			const btn = document.querySelector(
				'#wp-admin-bar-wphb-clear-cloudflare > a'
			);

			if ( ! btn ) {
				return;
			}

			btn.addEventListener( 'click', () =>
				this.post( 'wphb_front_clear_cloudflare' )
			);
		},

		copyTextToClipboard: (text) => {
			const textArea = document.createElement("textarea");
			textArea.value = text;

			// Avoid scrolling to bottom
			textArea.style.top = "0";
			textArea.style.left = "0";
			textArea.style.position = "fixed";

			document.body.appendChild(textArea);
			textArea.focus();
			textArea.select();

			try {
				document.execCommand('copy');
			} catch (err) {
				console.error('Oops, unable to copy', err);
			}

			document.body.removeChild(textArea);
		},

		/**
		 * Regsiter safe mode actions.
		 *
		 * @since 3.4.0
		 */
		registerSafeModeActions() {
			const saveButton = document.getElementById( 'wphb-ao-safe-mode-save' );
			if ( saveButton ) {
				saveButton.addEventListener('click', () => {
					saveButton.disabled = true;
					this.request('wphb_react_minify_publish_safe_mode')
						.then(() => {
							window.location.href = wphbGlobal.minify_url + '&safe_mode_status=published';
						});
				});
			}

			const copyButton = document.getElementById('wphb-ao-safe-mode-copy');
			if (copyButton) {
				copyButton.addEventListener('click', (e) => {
					e.preventDefault();
					this.copyTextToClipboard(window.location.href);

					const successClass = 'wphb-ao-safe-mode-copy-success';
					copyButton.classList.add(successClass);
					setTimeout(() => {
						copyButton.classList.remove(successClass);
					}, 3000);
				});
			}
		},

		/**
		 * Send AJAX request.
		 *
		 * @param {string}  action
		 * @param {boolean} reload
		 */
		post(action, reload = true) {
			this.request(action)
				.then(() => {
					if (reload) {
						location.reload(action)
					}
				});
		},

		request(action) {
			return new Promise(resolve => {
				const xhr = new XMLHttpRequest();
				xhr.open('POST', wphbGlobal.ajaxurl + '?action=' + action + '&_ajax_nonce=' + wphbGlobal.nonce);
				xhr.onload = function () {
					if (xhr.status === 200) {
						resolve();
					}
				};

				xhr.send();
			});
		}
	};

	document.addEventListener( 'DOMContentLoaded', function() {
		WPHBGlobal.init();
	} );

	window.WPHBGlobal = WPHBGlobal;
}() );
