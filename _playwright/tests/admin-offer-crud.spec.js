// @ts-check
const { test, expect } = require('@playwright/test');
const { adminLogin } = require('./auth-helper');

test.describe('Admin Product Offer CRUD via UI', () => {
  let productNumericId;

  test.beforeEach(async ({ page }) => {
    await adminLogin(page);

    // Navigate to catalog and get first product's ID
    await page.goto('/catalog');
    await page.waitForLoadState('networkidle');

    // Get first product's edit URL to extract numeric ID
    const firstEditBtn = page.locator('a[title="Редактировать"]').first();
    await expect(firstEditBtn).toBeVisible({ timeout: 5000 });
    const editUrl = await firstEditBtn.getAttribute('href');
    const match = editUrl.match(/\/catalog\/products\/(\d+)\/edit/);
    if (match) {
      productNumericId = match[1];
    } else {
      throw new Error('Could not extract product numeric ID');
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

    // Verify offer appears in the list (first row)
    const firstRow = page.locator('tbody tr').first();
    await expect(firstRow).toBeVisible();
  });

  test('should update an existing product offer', async ({ page }) => {
    // Navigate to offers list
    await page.goto(`/catalog/products/${productNumericId}/offers`);
    await page.waitForLoadState('networkidle');

    // Check if there are any offers
    const rows = await page.locator('tbody tr').count();
    
    if (rows === 0) {
      // Create an offer first if none exist
      await page.goto(`/catalog/products/${productNumericId}/offers/create`);
      const timestamp = Date.now();
      await page.getByLabel('Уникальный ID предложения').fill(`OFF${timestamp}`);
      await page.getByLabel('Название предложения', { exact: true }).fill(`Offer ${timestamp}`);
      await page.getByRole('button', { name: /Создать предложение/i }).click();
      await page.waitForURL(/.*\/offers$/, { timeout: 10000 });
      await page.waitForTimeout(1000);
    }

    // Reload offers list
    await page.goto(`/catalog/products/${productNumericId}/offers`);
    await page.waitForLoadState('networkidle');

    // Get first offer's edit URL
    const firstEditBtn = page.locator('a[title="Редактировать"]').first();
    await expect(firstEditBtn).toBeVisible({ timeout: 5000 });
    const editUrl = await firstEditBtn.getAttribute('href');

    // Navigate to edit page
    await page.goto(editUrl);
    await page.waitForURL(/.*\/offers\/\d+\/edit$/, { timeout: 5000 });

    // Update the offer
    const nameInput = page.getByLabel('Название предложения', { exact: true });
    const updatedName = `Updated Offer ${Date.now()}`;
    await nameInput.fill(updatedName);

    // Submit update form
    const submitBtn = page.locator('form button[type="submit"].btn-primary').first();
    await submitBtn.click();

    // Wait for redirect
    await page.waitForURL(/.*\/offers$/, { timeout: 10000 });
    await page.waitForTimeout(1000);

    // Verify update
    await page.reload();
    await page.waitForLoadState('networkidle');
    await expect(page.getByText(updatedName)).toBeVisible();
  });

  test('should delete a product offer', async ({ page }) => {
    // Navigate to offers list
    await page.goto(`/catalog/products/${productNumericId}/offers`);
    await page.waitForLoadState('networkidle');

    // Check if there are any offers
    const rows = await page.locator('tbody tr').count();
    
    if (rows === 0) {
      // Create an offer first if none exist
      await page.goto(`/catalog/products/${productNumericId}/offers/create`);
      const timestamp = Date.now();
      await page.getByLabel('Уникальный ID предложения').fill(`OFF${timestamp}`);
      await page.getByLabel('Название предложения', { exact: true }).fill(`Delete Test Offer ${timestamp}`);
      await page.getByRole('button', { name: /Создать предложение/i }).click();
      await page.waitForURL(/.*\/offers$/, { timeout: 10000 });
      await page.waitForTimeout(1000);
    }

    // Reload offers list
    await page.goto(`/catalog/products/${productNumericId}/offers`);
    await page.waitForLoadState('networkidle');

    // Get first offer's info
    const firstRow = page.locator('tbody tr').first();
    const offerName = await firstRow.locator('td').nth(1).textContent();

    // Click delete button
    await firstRow.locator('button[title="Удалить"]').click();

    // Wait for modal and confirm deletion
    await expect(page.getByText('Вы уверены, что хотите удалить предложение')).toBeVisible({ timeout: 3000 });
    // Click the red delete button in modal footer
    await page.locator('.modal-footer .btn-danger').click();

    // Wait for redirect
    await page.waitForURL(/.*\/offers$/, { timeout: 10000 });
    await page.waitForTimeout(1000);

    // Verify offer is deleted
    await page.reload();
    await page.waitForLoadState('networkidle');
    await expect(page.getByText(offerName)).not.toBeVisible({ timeout: 5000 });
  });
});
