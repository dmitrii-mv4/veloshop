// @ts-check
const { test, expect } = require('@playwright/test');
const { adminLogin } = require('./auth-helper');

test.describe('Browser Console Error Tests', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page);
  });

  test('product create page should have no console errors', async ({ page }) => {
    // Collect console errors
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      }
    });

    // Navigate to product create page
    await page.goto('/catalog/products/create');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);

    // Check for console errors
    expect(consoleErrors).toEqual([]);
  });

  test('product edit page should have no console errors', async ({ page }) => {
    // Collect console errors
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      }
    });

    // Navigate to catalog and get first product
    await page.goto('/catalog');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    // Get first product edit URL
    const firstEditBtn = page.locator('a[title="Редактировать"]').first();
    await expect(firstEditBtn).toBeVisible();
    const editUrl = await firstEditBtn.getAttribute('href');

    // Navigate to edit page
    await page.goto(editUrl);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);

    // Check for console errors
    expect(consoleErrors).toEqual([]);
  });

  test('offer create page should have no console errors', async ({ page }) => {
    // Collect console errors
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      }
    });

    // Navigate to catalog and get first product
    await page.goto('/catalog');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    // Get first product offers URL
    const firstOffersBtn = page.locator('a[title="Предложения"]').first();
    await expect(firstOffersBtn).toBeVisible();
    const offersUrl = await firstOffersBtn.getAttribute('href');

    // Navigate to offers and click create
    await page.goto(offersUrl.replace('/offers', '/offers/create'));
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);

    // Check for console errors
    expect(consoleErrors).toEqual([]);
  });
});
