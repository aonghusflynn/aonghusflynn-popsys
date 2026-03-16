/**
 * lightbox.js — Initialises GLightbox on project grid images.
 * GLightbox is loaded via CDN (declared in functions.php enqueue).
 * No unit test needed: this is pure DOM wiring of a third-party library.
 */

'use strict';

if (typeof document !== 'undefined') {
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof GLightbox === 'undefined') return;

    GLightbox({
      selector: '.project__grid-item',
      touchNavigation: true,
      loop: true,
      keyboardNavigation: true,
      closeOnOutsideClick: true,
      skin: 'clean',
    });
  });
}
