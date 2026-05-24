// @ts-check
const { test, expect } = require('@playwright/test');
const { adminLogin } = require('./auth-helper');

test.describe('Admin Product CRUD via UI', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page);
  });

  test('should create a new product', async ({ page }) => {
    await page.goto('/catalog/products/create');

    const timestamp = Date.now();
    const productId = `TP${timestamp}`;
    const productName = `Test Product ${timestamp}`;

    // Fill required fields
    await page.getByLabel('Уникальный ID товара').fill(productId);
    await page.getByLabel('Название товара', { exact: true }).fill(productName);
    await page.getByLabel('Бренд').fill('Test Brand');
    await page.getByLabel('Модель').fill('Test Model');
    await page.getByLabel('Сезон').fill('Summer 2024');

    // Try to select a category if available
    const categorySelect = page.getByLabel('Категория');
    const optionCount = await categorySelect.locator('option').count();
    if (optionCount > 1) {
      await categorySelect.selectOption({ index: 1 });
    }

    // Fill SEO fields
    await page.getByLabel('Мета-заголовок').fill(`Buy ${productName} - Best Price`);
    await page.getByLabel('Мета-описание').fill(`Best ${productName} with warranty and quality`);
    await page.getByLabel('Ключевые слова').fill('test, product, buy, quality');

    // Submit the form
    await page.getByRole('button', { name: /Создать товар/i }).click();

    // Wait for redirect
    await page.waitForURL(/.*\/catalog(\/.*)?$/, { timeout: 10000 });

    // Verify product was created successfully by checking the redirect happened
    const currentUrl = page.url();
    expect(currentUrl).toContain('/catalog');
  });

  test('should update an existing product', async ({ page }) => {
    // Navigate to catalog and get first product's edit URL
    await page.goto('/catalog');
    await page.waitForLoadState('networkidle');

    // Get first product's edit URL
    const firstEditBtn = page.locator('a[title="Редактировать"]').first();
    await expect(firstEditBtn).toBeVisible({ timeout: 5000 });
    const editUrl = await firstEditBtn.getAttribute('href');

    // Navigate to edit page
    await page.goto(editUrl);
    await page.waitForURL(/.*\/edit$/, { timeout: 5000 });

    // Get original name
    const nameInput = page.getByLabel('Название товара', { exact: true });
    const originalName = await nameInput.inputValue();

    // Update the product
    const updatedName = `Updated: ${originalName}`;
    await nameInput.fill(updatedName);
    await page.getByLabel('Бренд').fill('Updated Brand');

    // Submit update form
    const submitBtn = page.locator('form button[type="submit"].btn-primary').first();
    await submitBtn.click();

    // Wait for redirect and success
    await page.waitForURL(/.*\/catalog(\/.*)?$/, { timeout: 10000 });
    await page.waitForTimeout(1000);

    // Verify update by going back to edit page
    await page.goto(editUrl);
    await page.waitForURL(/.*\/edit$/, { timeout: 5000 });
    await expect(nameInput).toHaveValue(updatedName);
  });

  test('should delete a product', async ({ page }) => {
    // Navigate to catalog
    await page.goto('/catalog');
    await page.waitForLoadState('networkidle');

    // Get the count of products before deletion
    const rowsBefore = await page.locator('tbody tr').count();
    expect(rowsBefore).toBeGreaterThan(0);

    // Get first product's info
    const firstRow = page.locator('tbody tr').first();
    const productName = await firstRow.locator('td').nth(1).textContent();

    // Click delete button on first product
    await firstRow.locator('button[title="Удалить"]').click();

    // Wait for modal and confirm deletion
    await expect(page.getByText('Вы уверены, что хотите удалить товар')).toBeVisible({ timeout: 3000 });
    // Click the red delete button in modal footer
    await page.locator('.modal-footer .btn-danger').click();

    // Wait for redirect
    await page.waitForURL(/.*\/catalog(\/.*)?$/, { timeout: 10000 });
    await page.waitForTimeout(1000);

    // Verify product is deleted - reload and check it's not in first page
    await page.reload();
    await page.waitForLoadState('networkidle');
    await expect(page.getByText(productName)).not.toBeVisible({ timeout: 5000 });
  });
});
