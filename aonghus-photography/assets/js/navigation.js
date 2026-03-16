/**
 * navigation.js — Mobile hamburger toggle.
 * Adds/removes `nav-open` class on the body element.
 * Exported for unit testing; auto-initialises in browser.
 */

'use strict';

function initNavigation(bodyEl) {
  const toggle = bodyEl.querySelector('.nav-toggle');
  if (!toggle) return;

  toggle.addEventListener('click', function () {
    // Use aria-expanded as the source of truth to avoid stale class state
    const wasOpen = toggle.getAttribute('aria-expanded') === 'true';
    const isOpen = !wasOpen;
    if (isOpen) {
      bodyEl.classList.add('nav-open');
    } else {
      bodyEl.classList.remove('nav-open');
    }
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });
}

// Auto-init in browser environment
if (typeof document !== 'undefined') {
  document.addEventListener('DOMContentLoaded', function () {
    initNavigation(document.body);
  });
}

// Export for Jest
if (typeof module !== 'undefined') {
  module.exports = { initNavigation };
}
