/**
 * Tests for slideshow.js dot indicator logic.
 * Exports: initSlideshow(containerEl, dotsEl)
 */
const { getActiveDotIndex } = require('../../assets/js/slideshow');

describe('getActiveDotIndex', () => {
  test('returns 0 when scrollTop is 0', () => {
    expect(getActiveDotIndex(0, 800)).toBe(0);
  });

  test('returns 1 when scrolled past one full slide height', () => {
    expect(getActiveDotIndex(800, 800)).toBe(1);
  });

  test('returns 2 when scrolled past two slide heights', () => {
    expect(getActiveDotIndex(1700, 800)).toBe(2);
  });

  test('returns 0 for negative scroll (overscroll at top)', () => {
    expect(getActiveDotIndex(-10, 800)).toBe(0);
  });
});
