/**
 * slideshow.js — Homepage dot indicator.
 * Highlights the dot corresponding to the currently visible slide.
 * Pure functions exported for unit testing; DOM wiring in initSlideshow().
 */

'use strict';

/**
 * Returns the zero-based index of the active slide.
 * @param {number} scrollTop  - Current scrollTop of the slides container.
 * @param {number} slideHeight - Height of one slide (viewport height).
 * @returns {number}
 */
function getActiveDotIndex(scrollTop, slideHeight) {
  if (scrollTop < 0) return 0;
  return Math.round(scrollTop / slideHeight);
}

/**
 * Wires up the scroll listener and dot updates.
 * @param {Element} containerEl - The scroll-snap container (.slideshow).
 * @param {NodeList} dots        - All `.slideshow__dot` elements.
 */
function initSlideshow(containerEl, dots) {
  if (!containerEl || !dots.length) return;

  function update() {
    const idx = getActiveDotIndex(containerEl.scrollTop, containerEl.clientHeight);
    dots.forEach((dot, i) => {
      dot.classList.toggle('slideshow__dot--active', i === idx);
      dot.setAttribute('aria-current', i === idx ? 'true' : 'false');
    });
  }

  containerEl.addEventListener('scroll', update, { passive: true });
  update(); // Set initial state
}

if (typeof document !== 'undefined') {
  document.addEventListener('DOMContentLoaded', function () {
    const container = document.querySelector('.slideshow');
    const dots      = document.querySelectorAll('.slideshow__dot');
    initSlideshow(container, dots);
  });
}

if (typeof module !== 'undefined') {
  module.exports = { getActiveDotIndex, initSlideshow };
}
