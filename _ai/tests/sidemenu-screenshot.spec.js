// @ts-check
const { test, expect } = require('@playwright/test');

test('verify sidemenu form exists with new class', async ({ page }) => {
  await page.goto('/', { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(3000);
  
  // Check that the old class doesn't exist
  const oldForm = page.locator('form.newsletter-form');
  const oldFormCount = await oldForm.count();
  console.log('Old class (newsletter-form) count: ' + oldFormCount);
  expect(oldFormCount).toBe(0);
  
  // Check that the new class exists
  const newForm = page.locator('form.sidemenu-callback-form');
  const newFormCount = await newForm.count();
  console.log('New class (sidemenu-callback-form) count: ' + newFormCount);
  expect(newFormCount).toBeGreaterThan(0);
  
  // Check phone input exists in DOM (may be hidden in collapsed menu)
  const phoneInput = page.locator('form.sidemenu-callback-form input[name="phone"]');
  const phoneInputCount = await phoneInput.count();
  console.log('Phone input count: ' + phoneInputCount);
  expect(phoneInputCount).toBe(1);
  
  // Check submit button exists in DOM
  const submitButton = page.locator('form.sidemenu-callback-form button[type="submit"]');
  const buttonCount = await submitButton.count();
  console.log('Submit button count: ' + buttonCount);
  expect(buttonCount).toBe(1);
  
  // Verify CSS styles are applied (check even if element is hidden)
  const formPosition = await page.locator('form.sidemenu-callback-form').first().evaluate(el => {
    return window.getComputedStyle(el).position;
  });
  console.log('Form computed position: ' + formPosition);
  expect(formPosition).toBe('relative');
  
  console.log('SUCCESS: All verification checks passed!');
  console.log('The form has been successfully renamed from newsletter-form to sidemenu-callback-form.');
});
