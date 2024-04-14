/** Add Delay Js
 * @since 3.5.0
 */
(function () {
	"use strict";

	const hbUIEvents = [
		"keydown",
		"mousedown",
		"mousemove",
		"wheel",
		"touchmove",
		"touchstart",
		"touchend"
	];
	const hbAllDelayedScripts = {
		normal: [],
		defer: [],
		async: []
	};

	const jQueriesArray = [];
	const hbInterceptedClicks = [];
	let hbIsDOMLoaded = false;
	let hbTargetElement = '';

	const WPHBAddDelayJs = {
		init() {

			// Add dom listener event and then trigger scripts.
			const hbUserInteractionHandler = () => {
				if (typeof hbDelayTimer !== 'undefined') {
					clearTimeout(hbDelayTimer);
				}

				// Remove the Added dom listener event.
				hbUIEvents.forEach(function (event) {
					window.removeEventListener(event, hbUserInteractionHandler, {
						passive: true,
					});
				});

				// Remove visibilitychange listener here.
				document.removeEventListener("visibilitychange", hbUserInteractionHandler);

				// Add listener if page is still loading.
				if ("loading" === document.readyState) {
					document.addEventListener("DOMContentLoaded", hbInitializeEverything);
				} else {
					// Trigger process delayed script.
					hbInitializeEverything();
				}
			}

			/**
			 * Load everything here.
			 */
			const hbInitializeEverything = async () => {
				hbDelayEventListeners();
				hbJqueries();
				hbPrintDocumentWrite();
				hbFindAllDelayedScripts();
				hbPreloadAllDelayedScripts();

				/**
				 * Load the delayed scripts here.
				 */
				await hbLoadDelayedScriptsFromArray(hbAllDelayedScripts.normal);
				await hbLoadDelayedScriptsFromArray(hbAllDelayedScripts.defer);
				await hbLoadDelayedScriptsFromArray(hbAllDelayedScripts.async);

				/**
				 * Trigger delayed DOM events.
				 */
				await hbTriggerEvents();
				await hbTriggerWindowLoad();

				// Click replay event.
				window.dispatchEvent(new Event("wphb-allScriptsLoaded"));
				hbReplayClicksActions();
			}

			const hbDelayEventListeners = () => {
				let events = {};

				const delayDOMEvent = (object, event) => {

					const rewriteEventName = (eventName) => {
						return events[object].delayedEvents.indexOf(eventName) >= 0 ? "wphb-" + eventName : eventName;
					}

					if (!events[object]) {

						// Add object in events array.
						events[object] = {
							originalFunctions: {
								add: object.addEventListener,
								remove: object.removeEventListener
							},
							delayedEvents: []
						}

						object.addEventListener = function () {
							arguments[0] = rewriteEventName(arguments[0]);
							events[object].originalFunctions.add.apply(object, arguments);
						}
						object.removeEventListener = function () {
							arguments[0] = rewriteEventName(arguments[0]);
							events[object].originalFunctions.remove.apply(object, arguments);
						}
					}

					// Add event to delayed events array.
					events[object].delayedEvents.push(event);
				}

				// Delay dom event trigger here.
				const delayDOMEventTrigger = (object, event) => {
					const originalEvent = object[event];
					Object.defineProperty(object, event, {
						get: !originalEvent ? function () {
						} : originalEvent,
						set: function (n) {
							object["wphb-" + event] = n;
						},
					});
				}

				// Delay the dom events.
				delayDOMEvent(document, "DOMContentLoaded");
				delayDOMEvent(window, "DOMContentLoaded");
				delayDOMEvent(window, "load");
				delayDOMEvent(window, "pageshow");
				delayDOMEvent(document, "readystatechange");

				// Delay the dom events triggers.
				delayDOMEventTrigger(document, "onreadystatechange");
				delayDOMEventTrigger(window, "onload");
				delayDOMEventTrigger(window, "onpageshow");
			}

			const hbJqueries = () => {
				let actualJQuery = window.jQuery;
				Object.defineProperty(window, "jQuery", {
					get: () => actualJQuery,
					set(updatedJQuery) {
						if (updatedJQuery && updatedJQuery.fn && !jQueriesArray.includes(updatedJQuery)) {
							updatedJQuery.fn.ready = updatedJQuery.fn.init.prototype.ready = function (actualJQuery) {
								hbIsDOMLoaded ? actualJQuery.bind(document)(updatedJQuery) : document.addEventListener("wphb-DOMContentLoaded", () => actualJQuery.bind(document)(updatedJQuery));
							};
							const updatedJQueryOn = updatedJQuery.fn.on;
							(updatedJQuery.fn.on = updatedJQuery.fn.init.prototype.on = function () {
								if (this[0] === window) {
									function rewriteEventName(eventName) {
										return eventName
											.split(" ")
											.map((eventName) => ("load" === eventName || 0 === eventName.indexOf("load.") ? "wphb-jquery-load" : eventName))
											.join(" ");
									}

									"string" == typeof arguments[0] || arguments[0] instanceof String
										? (arguments[0] = rewriteEventName(arguments[0]))
										: "object" == typeof arguments[0] &&
										Object.keys(arguments[0]).forEach((argument) => {
											delete Object.assign(arguments[0], {[rewriteEventName(argument)]: arguments[0][argument]})[argument];
										});
								}
								return updatedJQueryOn.apply(this, arguments), this;
							});
							jQueriesArray.push(updatedJQuery);
						}
						actualJQuery = updatedJQuery;
					},
				});
			}

			// Override the document.writeln() so that we can print document write values directly after the parent script.
			const hbPrintDocumentWrite = () => {
				const map = new Map();

				document.write = document.writeln = function (value) {
					let script = document.currentScript;
					let range = document.createRange();

					// Check if script isn't in map.
					let mapScript = map.get(script);
					if (mapScript === void 0) {

						// Add script's next sibling to map.
						mapScript = script.nextSibling;
						map.set(script, mapScript);
					}

					let fragment = document.createDocumentFragment();
					range.setStart(fragment, 0);
					fragment.appendChild(range.createContextualFragment(value));
					script.parentElement.insertBefore(fragment, mapScript);
				};
			}

			/**
			 * Prepare delay JS array here.
			 */
			const hbFindAllDelayedScripts = () => {
				document.querySelectorAll("script[type=wphb-delay-type]").forEach(function (event) {
					if (event.hasAttribute("src")) {
						if (event.hasAttribute("defer") && event.defer !== false) {
							hbAllDelayedScripts.defer.push(event);
						} else if (event.hasAttribute("async") && event.async !== false) {
							hbAllDelayedScripts.async.push(event);
						} else {
							hbAllDelayedScripts.normal.push(event);
						}
					} else {
						hbAllDelayedScripts.normal.push(event);
					}
				});
			}

			const hbPreloadAllDelayedScripts = () => {
				let hbFragment = document.createDocumentFragment();
				[...hbAllDelayedScripts.normal, ...hbAllDelayedScripts.defer, ...hbAllDelayedScripts.async].forEach(function (script) {
					let getSrc = script.getAttribute("src");
					if (getSrc) {
						let createLink = document.createElement("link");
						createLink.href = getSrc;
						createLink.rel = "preload";
						createLink.as = "script";
						hbFragment.appendChild(createLink);
					}
				});

				document.head.appendChild(hbFragment);
			}

			const hbLoadDelayedScriptsFromArray = async (scripts) => {

				// Fetch first script form array.
				let script = scripts.shift();

				// Replace script with original one.
				if (script) {
					await hbReplaceDelayedScript(script);
					return hbLoadDelayedScriptsFromArray(scripts);
				}

				return Promise.resolve();
			}

			// Replace data-wphb-type with type.
			const hbReplaceDelayedScript = async (script) => {
				await hbPerformAnimate();

				return new Promise(function (replaceScript) {
					const newscript = document.createElement("script");

					[...script.attributes].forEach(function (attribute) {
						let attributeName = attribute.nodeName;

						if (attributeName !== "type") {

							if (attributeName === "data-wphb-type") {
								attributeName = "type";
							}

							newscript.setAttribute(attributeName, attribute.nodeValue);
						}
					});

					if (script.hasAttribute("src")) {
						newscript.addEventListener("load", replaceScript);
						newscript.addEventListener("error", replaceScript);
					} else {
						newscript.text = script.text;
						replaceScript();
					}

					script.parentNode.replaceChild(newscript, script);
				});
			}

			const hbTriggerEvents = async () => {
				// Set hbIsDOMLoaded flag.
				hbIsDOMLoaded = true;

				await hbPerformAnimate();
				document.dispatchEvent(new Event("wphb-DOMContentLoaded"));
				await hbPerformAnimate();
				window.dispatchEvent(new Event("wphb-DOMContentLoaded"));
				await hbPerformAnimate();
				document.dispatchEvent(new Event("wphb-readystatechange"));
				await hbPerformAnimate();
				document.wphm_onreadystatechange && document.wphm_onreadystatechange();
			}

			const hbTriggerWindowLoad = async () => {
				await hbPerformAnimate();
				window.dispatchEvent(new Event("wphb-load"));
				await hbPerformAnimate();
				window.wphm_onload && window.wphm_onload();
				await hbPerformAnimate();
				jQueriesArray.forEach((hbJquery) => hbJquery(window).trigger("wphb-jquery-load"));
				await hbPerformAnimate();
				const pageshow = new Event("wphm-pageshow");
				pageshow.persisted = window.hbPersisted;
				window.dispatchEvent(pageshow);
				await hbPerformAnimate();
				window.wphm_onpageshow && window.wphm_onpageshow({persisted: window.hbPersisted});
			}

			const hbPerformAnimate = async () => {
				return new Promise(function (event) {
					requestAnimationFrame(event);
				});
			}

			const hbOnClickHandler = (event) => {
				event.target.removeEventListener("click", hbOnClickHandler);
				hbUpdateDOMAttribute(event.target, "hb-onclick", "onclick", event);
				hbInterceptedClicks.push(event);
				event.preventDefault();
				event.stopPropagation();
				event.stopImmediatePropagation();
			}

			const hbReplayClicksActions = () => {
				window.removeEventListener("touchstart", hbStartTouchHandler, {passive: true});
				window.removeEventListener("mousedown", hbStartTouchHandler);
				hbInterceptedClicks.forEach((event) => {
					if (event.target.outerHTML === hbTargetElement) {
						event.target.dispatchEvent(new MouseEvent("click", {
							view: event.view,
							bubbles: true,
							cancelable: true
						}));
					}
				});
			}

			const hbStartTouchHandler = (event) => {

				if (event.target.tagName !== "HTML") {
					if (!hbTargetElement) {
						hbTargetElement = event.target.outerHTML;
					}

					window.addEventListener("touchend", hbEndTouchHandler);
					window.addEventListener("mouseup", hbEndTouchHandler);
					window.addEventListener("touchmove", hbTouchMoveHandler, {passive: true});
					window.addEventListener("mousemove", hbTouchMoveHandler);
					event.target.addEventListener("click", hbOnClickHandler);
					hbUpdateDOMAttribute(event.target, "onclick", "hb-onclick", event);
				}
			}

			const hbTouchMoveHandler = (event) => {
				window.removeEventListener("touchend", hbEndTouchHandler);
				window.removeEventListener("mouseup", hbEndTouchHandler);
				window.removeEventListener("touchmove", hbTouchMoveHandler, {passive: true});
				window.removeEventListener("mousemove", hbTouchMoveHandler);
				event.target.removeEventListener("click", hbOnClickHandler);
				hbUpdateDOMAttribute(event.target, "hb-onclick", "onclick", event);
			}

			const hbEndTouchHandler = () => {
				window.removeEventListener("touchend", hbEndTouchHandler);
				window.removeEventListener("mouseup", hbEndTouchHandler);
				window.removeEventListener("touchmove", hbTouchMoveHandler, {passive: true});
				window.removeEventListener("mousemove", hbTouchMoveHandler);
			}

			const hbUpdateDOMAttribute = (targetEvent, hbOnclick, onClick, originalEvent) => {
				if (targetEvent.hasAttribute && targetEvent.hasAttribute(hbOnclick)) {
					originalEvent.target.setAttribute(onClick, originalEvent.target.getAttribute(hbOnclick));
					originalEvent.target.removeAttribute(hbOnclick);
				}
			}

			// Add pageshow listener.
			window.addEventListener("pageshow", (event) => {
				window.hbPersisted = event.persisted;
			});

			// Attach an event handler to specified event.
			hbUIEvents.forEach((event) => {
				window.addEventListener(event, hbUserInteractionHandler, {
					passive: true,
				}); // Indicates that the function specified by listener will never call preventDefault().
			});

			// Fired when the contents of its tab have become visible or have been hidden.
			document.addEventListener("visibilitychange", hbUserInteractionHandler);

			if (typeof (delay_js_timeout_timer) != 'undefined' && delay_js_timeout_timer > 0) {
				var hbDelayTimer = setTimeout(function () {
					hbUserInteractionHandler();
				}, delay_js_timeout_timer);
			}
		},
	};

	WPHBAddDelayJs.init();
})();
