// @ts-check
const { test, expect } = require('@playwright/test');
const { adminLogin } = require('./auth-helper');

test.describe('No Console Errors During CRUD Operations', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page);
  });

  test('product CRUD operations should have no console errors', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      }
    });

    // Test 1: Navigate to product create page
    await page.goto('/catalog/products/create');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    // Create a product
    const timestamp = Date.now();
    const productId = `CONSOLE-TEST-${timestamp}`;
    const productName = `Console Test Product ${timestamp}`;
    
    await page.getByLabel('Уникальный ID товара').fill(productId);
    await page.getByLabel('Название товара', { exact: true }).fill(productName);
    await page.getByLabel('Бренд').fill('Console Test Brand');
    
    // Fill SEO fields to trigger SEO analysis
    await page.getByLabel('Мета-заголовок').fill(`Buy ${productName} - Best Price`);
    await page.getByLabel('Мета-описание').fill(`Best ${productName} with warranty and quality guaranteed`);
    await page.getByLabel('Ключевые слова').fill('test, product, buy, quality');
    
    // Submit
    await page.getByRole('button', { name: /Создать товар/i }).click();
    await page.waitForURL(/.*\/catalog(\/.*)?$/, { timeout: 10000 });
    await page.waitForTimeout(1000);

    // Test 2: Navigate to catalog and find product to edit
    await page.goto('/catalog');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    // Find product in first few rows
    const rows = page.locator('tbody tr');
    const rowCount = await rows.count();
    let productRow = null;
    for (let i = 0; i < Math.min(rowCount, 5); i++) {
      const row = rows.nth(i);
      const text = await row.textContent();
      if (text && text.includes(productName)) {
        productRow = row;
        break;
      }
    }

    if (productRow) {
      // Click edit button
      await productRow.locator('a[title="Редактировать"]').click();
      await page.waitForURL(/.*\/edit$/, { timeout: 5000 });
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(1000);

      // Update product - trigger SEO analysis
      const updatedName = `Updated Console Test ${timestamp}`;
      await page.getByLabel('Название товара', { exact: true }).fill(updatedName);
      await page.getByLabel('Бренд').fill('Updated Console Test Brand');
      
      // Trigger blur to test auto-fill SEO
      await page.getByLabel('Бренд').press('Tab');
      await page.waitForTimeout(500);

      // Submit update
      const submitBtn = page.locator('form button[type="submit"].btn-primary').first();
      await submitBtn.click();
      await page.waitForURL(/.*\/catalog(\/.*)?$/, { timeout: 10000 });
      await page.waitForTimeout(1000);

      // Test 3: Delete product
      await page.goto('/catalog');
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(1000);

      // Find updated product
      const updatedRows = page.locator('tbody tr');
      const updatedRowCount = await updatedRows.count();
      let updatedProductRow = null;
      for (let i = 0; i < Math.min(updatedRowCount, 5); i++) {
        const row = updatedRows.nth(i);
        const text = await row.textContent();
        if (text && text.includes(updatedName)) {
          updatedProductRow = row;
          break;
        }
      }

      if (updatedProductRow) {
        // Click delete button
        await updatedProductRow.locator('button[title="Удалить"]').click();
        await page.waitForTimeout(500);

        // Confirm deletion
        const confirmBtn = page.getByRole('button', { name: /Удалить товар/i });
        if (await confirmBtn.isVisible({ timeout: 2000 })) {
          await confirmBtn.click();
        }
        await page.waitForURL(/.*\/catalog(\/.*)?$/, { timeout: 10000 });
        await page.waitForTimeout(1000);
      }
    }

    // Check for console errors
    expect(consoleErrors).toEqual([]);
  });

  test('offer CRUD operations should have no console errors', async ({ page }) => {
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

    // Get first product's offers URL
    const firstOffersBtn = page.locator('a[title="Предложения"]').first();
    await expect(firstOffersBtn).toBeVisible({ timeout: 5000 });
    const offersUrl = await firstOffersBtn.getAttribute('href');
    const productMatch = offersUrl.match(/\/catalog\/products\/(\d+)\/offers/);
    
    if (!productMatch) {
      throw new Error('Could not extract product ID from offers URL');
    }
    
    const productNumericId = productMatch[1];

    // Test 1: Navigate to offer create page
    await page.goto(`/catalog/products/${productNumericId}/offers/create`);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    // Create an offer
    const timestamp = Date.now();
    const offerId = `CONSOLE-OFF-${timestamp}`;
    const offerName = `Console Test Offer ${timestamp}`;
    
    await page.getByLabel('Уникальный ID предложения').fill(offerId);
    await page.getByLabel('Название предложения', { exact: true }).fill(offerName);
    await page.getByLabel('Артикул поставщика').fill(`CONSOLE-SUPPLIER-${timestamp}`);
    
    // Submit
    await page.getByRole('button', { name: /Создать предложение/i }).click();
    await page.waitForURL(/.*\/offers$/, { timeout: 10000 });
    await page.waitForTimeout(1000);

    // Check for console errors after offer CRUD
    expect(consoleErrors).toEqual([]);
  });

  test('all product pages should have no console errors', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      }
    });

    // Test catalog index page
    await page.goto('/catalog');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    // Test product create page
    await page.goto('/catalog/products/create');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    // Get first product for edit test
    await page.goto('/catalog');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    const firstEditBtn = page.locator('a[title="Редактировать"]').first();
    if (await firstEditBtn.isVisible({ timeout: 5000 })) {
      const editUrl = await firstEditBtn.getAttribute('href');
      
      // Test product edit page
      await page.goto(editUrl);
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(1000);
    }

    // Check for console errors
    expect(consoleErrors).toEqual([]);
  });
});
