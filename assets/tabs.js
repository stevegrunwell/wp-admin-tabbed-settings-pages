/**
 * Scripting for WP-Admin Tabs.
 */

'use strict';

(function () {
	var tabWrapper = document.querySelector('.nav-tab-wrapper');
	var tabs = tabWrapper.querySelectorAll('.nav-tab');
	var panels = document.querySelectorAll('section[role="tabpanel"]');

	/**
	 * Set the current tab by its ID.
	 *
	 * @param {string} tabId - The ID of the active tab.
	 */
	function setActiveTab(tabId) {
		tabs.forEach(function (tab) {
			var current = tab.id === tabId;
			tab.classList.toggle('nav-tab-active', current);
			tab.setAttribute('aria-selected', current);
		});

		panels.forEach(function (panel) {
			if (panel.getAttribute('aria-labelledby') === tabId) {
				panel.removeAttribute('hidden');
			} else {
				panel.setAttribute('hidden', true);
			}
		});
	}

	// Return early if there are no tabs on the page.
	if (! tabs || ! panels) {
		return;
	}

	// Determine which tab should be selected.
	var currentTab = window.location.hash.substr(1);
	currentTab = currentTab ? 'nav-tab-' + currentTab : tabs[0].getAttribute('id');

	// Set the current tab and register the event listener.
	setActiveTab(currentTab);
	tabWrapper.addEventListener('click', function (e) {
		if ('A' !== e.target.tagName) {
			return;
		}

		setActiveTab(e.target.id);
	})
}());
