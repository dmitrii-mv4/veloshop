// @ts-check
const { test, expect } = require('@playwright/test');

test('Check for console errors on homepage', async ({ page }) => {
  const errors = [];
  const jsHandles = [];

  page.on('console', msg => {
    if (msg.type() === 'error') {
      errors.push(msg.text());
    }
  });

  page.on('pageerror', error => {
    jsHandles.push(error.message);
  });

  await page.goto('http://dev.site07.loc/');
  await page.waitForLoadState('networkidle');

  // Log any console errors found
  if (errors.length > 0) {
    console.log('Console errors found:');
    errors.forEach(e => console.log('  -', e));
  }

  if (jsHandles.length > 0) {
    console.log('Page errors found:');
    jsHandles.forEach(e => console.log('  -', e));
  }

  expect(errors).toEqual([]);
  expect(jsHandles).toEqual([]);
});
