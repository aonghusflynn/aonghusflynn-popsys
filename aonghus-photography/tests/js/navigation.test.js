/**
 * Tests for navigation.js hamburger toggle logic.
 * The module exports a single function: initNavigation(bodyEl)
 */
const { initNavigation } = require('../../assets/js/navigation');

describe('initNavigation', () => {
  let body, button;

  beforeEach(() => {
    document.body.innerHTML = `
      <button class="nav-toggle" aria-expanded="false" aria-label="Open menu"></button>
    `;
    body   = document.body;
    button = document.querySelector('.nav-toggle');
  });

  test('clicking toggle adds nav-open class to body', () => {
    initNavigation(body);
    button.click();
    expect(body.classList.contains('nav-open')).toBe(true);
  });

  test('clicking toggle again removes nav-open class', () => {
    initNavigation(body);
    button.click();
    button.click();
    expect(body.classList.contains('nav-open')).toBe(false);
  });

  test('clicking toggle sets aria-expanded to true', () => {
    initNavigation(body);
    button.click();
    expect(button.getAttribute('aria-expanded')).toBe('true');
  });

  test('clicking toggle again sets aria-expanded to false', () => {
    initNavigation(body);
    button.click();
    button.click();
    expect(button.getAttribute('aria-expanded')).toBe('false');
  });
});
