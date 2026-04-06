// @ts-check
const { test, expect } = require('@playwright/test');
const { adminLogin } = require('./auth-helper');

test.describe('Admin Product Offer CRUD via UI', () => {
  let productNumericId;

  test.beforeEach(async ({ page }) => {
    await adminLogin(page);
    
    // Create a product once for all offer tests
    await page.goto('/catalog/products/create');
    
    const timestamp = Date.now();
    const productId = `OFFER-TEST-PRODUCT-${timestamp}`;
    const productName = `Offer Test Product ${timestamp}`;
    
    await page.getByLabel('Уникальный ID товара').fill(productId);
    await page.getByLabel('Название товара', { exact: true }).fill(productName);
    await page.getByLabel('Бренд').fill('Offer Test Brand');
    
    await page.getByRole('button', { name: /Создать товар/i }).click();
    await page.waitForURL(/.*\/catalog(\/.*)?$/, { timeout: 10000 });
    await page.waitForTimeout(2000);
    
    // Navigate to product list and find the product
    await page.goto('/catalog');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    
    // Find product in the first few rows
    const rows = page.locator('tbody tr');
    const rowCount = await rows.count();
    for (let i = 0; i < Math.min(rowCount, 5); i++) {
      const row = rows.nth(i);
      const text = await row.textContent();
      if (text && text.includes(productName)) {
        const editUrl = await row.locator('a[title="Редактировать"]').getAttribute('href');
        const match = editUrl.match(/\/catalog\/products\/(\d+)\/edit/);
        if (match) {
          productNumericId = match[1];
          break;
        }
      }
    }
    
    if (!productNumericId) {
      throw new Error('Could not find created product to get numeric ID');
    }
  });

  test('should create a new product offer', async ({ page }) => {
    // Navigate to create offer page
    await page.goto(`/catalog/products/${productNumericId}/offers/create`);
    
    const timestamp = Date.now();
    const offerId = `OFF${timestamp}`;
    const offerName = `Test Offer ${timestamp}`;
    
    // Fill offer details
    await page.getByLabel('Уникальный ID предложения').fill(offerId);
    await page.getByLabel('Название предложения', { exact: true }).fill(offerName);
    await page.getByLabel('Артикул поставщика').fill(`SUPPLIER-${timestamp}`);
    
    // Submit form
    await page.getByRole('button', { name: /Создать предложение/i }).click();
    
    // Wait for redirect
    await page.waitForURL(/.*\/offers$/, { timeout: 10000 });
    await page.waitForTimeout(1000);
    
    // Verify offer appears in the list
    await page.reload();
    await page.waitForLoadState('networkidle');
    await expect(page.getByText(offerName)).toBeVisible();
  });

  test('should update an existing product offer', async ({ page }) => {
    // Step 1: Create an offer first
    await page.goto(`/catalog/products/${productNumericId}/offers/create`);
    
    const timestamp = Date.now();
    const offerId = `UPD-OFF${timestamp}`;
    const offerName = `Update Offer ${timestamp}`;
    
    await page.getByLabel('Уникальный ID предложения').fill(offerId);
    await page.getByLabel('Название предложения', { exact: true }).fill(offerName);
    
    await page.getByRole('button', { name: /Создать предложение/i }).click();
    await page.waitForURL(/.*\/offers$/, { timeout: 10000 });
    await page.waitForTimeout(2000);
    
    // Step 2: Find the offer in the list (should be at the top)
    await page.goto(`/catalog/products/${productNumericId}/offers`);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    
    // Find offer in the first few rows
    const rows = page.locator('tbody tr');
    const rowCount = await rows.count();
    let offerRow = null;
    for (let i = 0; i < Math.min(rowCount, 5); i++) {
      const row = rows.nth(i);
      const text = await row.textContent();
      if (text && text.includes(offerName)) {
        offerRow = row;
        break;
      }
    }
    
    if (!offerRow) {
      throw new Error(`Offer "${offerName}" not found in the list`);
    }
    
    // Click edit button
    await offerRow.locator('a[title="Редактировать"]').click();
    await page.waitForURL(/.*\/offers\/\d+\/edit$/, { timeout: 5000 });
    
    // Step 3: Update the offer
    const updatedName = `Updated Offer ${timestamp}`;
    await page.getByLabel('Название предложения', { exact: true }).fill(updatedName);
    await page.getByLabel('Артикул поставщика').fill(`UPDATED-SUPPLIER-${timestamp}`);
    
    // Submit update form
    const submitBtn = page.locator('form button[type="submit"].btn-primary').first();
    await submitBtn.click();
    
    // Wait for redirect
    await page.waitForURL(/.*\/offers$/, { timeout: 10000 });
    await page.waitForTimeout(1000);
    
    // Step 4: Verify the update
    await page.reload();
    await page.waitForLoadState('networkidle');
    await expect(page.getByText(updatedName)).toBeVisible();
  });

  test('should delete a product offer', async ({ page }) => {
    // Step 1: Create an offer first
    await page.goto(`/catalog/products/${productNumericId}/offers/create`);
    
    const timestamp = Date.now();
    const offerId = `DEL-OFF${timestamp}`;
    const offerName = `Delete Offer ${timestamp}`;
    
    await page.getByLabel('Уникальный ID предложения').fill(offerId);
    await page.getByLabel('Название предложения', { exact: true }).fill(offerName);
    
    await page.getByRole('button', { name: /Создать предложение/i }).click();
    await page.waitForURL(/.*\/offers$/, { timeout: 10000 });
    await page.waitForTimeout(2000);
    
    // Step 2: Find the offer in the list
    await page.goto(`/catalog/products/${productNumericId}/offers`);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    
    // Find offer in the first few rows
    const rows = page.locator('tbody tr');
    const rowCount = await rows.count();
    let offerRow = null;
    for (let i = 0; i < Math.min(rowCount, 5); i++) {
      const row = rows.nth(i);
      const text = await row.textContent();
      if (text && text.includes(offerName)) {
        offerRow = row;
        break;
      }
    }
    
    if (!offerRow) {
      throw new Error(`Offer "${offerName}" not found in the list`);
    }
    
    // Step 3: Click delete button (opens modal)
    await offerRow.locator('button[title="Удалить"]').click();
    
    // Wait for modal and confirm deletion
    await expect(page.getByText('Вы уверены, что хотите удалить предложение')).toBeVisible({ timeout: 3000 });
    await page.getByRole('button', { name: /Удалить предложение/i }).click();
    
    // Wait for redirect
    await page.waitForURL(/.*\/offers$/, { timeout: 10000 });
    await page.waitForTimeout(1000);
    
    // Step 4: Verify offer is deleted
    await page.reload();
    await page.waitForLoadState('networkidle');
    await expect(page.getByText(offerName)).not.toBeVisible({ timeout: 5000 });
  });
});
