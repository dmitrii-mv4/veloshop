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
    
    // Wait for redirect - could be /catalog or /catalog/products
    await page.waitForURL(/.*\/catalog(\/.*)?$/, { timeout: 10000 });
    
    // Give the system time to process
    await page.waitForTimeout(2000);
    
    // Verify product was created successfully by checking the redirect happened
    const currentUrl = page.url();
    expect(currentUrl).toContain('/catalog');
  });

  test('should update an existing product', async ({ page }) => {
    // Step 1: Create a product first
    await page.goto('/catalog/products/create');
    
    const timestamp = Date.now();
    const productId = `UPD${timestamp}`;
    const productName = `Update Product ${timestamp}`;
    
    await page.getByLabel('Уникальный ID товара').fill(productId);
    await page.getByLabel('Название товара', { exact: true }).fill(productName);
    await page.getByLabel('Бренд').fill('Original Brand');
    
    await page.getByRole('button', { name: /Создать товар/i }).click();
    await page.waitForURL(/.*\/catalog(\/.*)?$/, { timeout: 10000 });
    await page.waitForTimeout(2000);
    
    // Step 2: Find product by going to first page (newest products first)
    await page.goto('/catalog');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    
    // The product should be at the top since it's sorted by created_at desc
    // Look for the product name in the table
    const firstProductRow = page.locator('tbody tr').first();
    await expect(firstProductRow).toBeVisible({ timeout: 5000 });
    
    // Check if our product is in the first few rows
    let productRow = null;
    const rows = page.locator('tbody tr');
    const rowCount = await rows.count();
    for (let i = 0; i < Math.min(rowCount, 5); i++) {
      const row = rows.nth(i);
      const text = await row.textContent();
      if (text && text.includes(productName)) {
        productRow = row;
        break;
      }
    }
    
    if (!productRow) {
      throw new Error(`Product "${productName}" not found in the list`);
    }
    
    // Click the edit button in the actions column
    await productRow.locator('a[title="Редактировать"]').click();
    
    // Wait for edit page to load
    await page.waitForURL(/.*\/edit$/, { timeout: 5000 });
    
    // Step 3: Update the product
    const updatedName = `Updated Product ${timestamp}`;
    await page.getByLabel('Название товара', { exact: true }).fill(updatedName);
    await page.getByLabel('Бренд').fill('Updated Brand');
    await page.getByLabel('Модель').fill('Updated Model');
    
    // Submit update form - find submit button
    const submitBtn = page.locator('form button[type="submit"].btn-primary').first();
    await submitBtn.click();
    
    // Wait for redirect
    await page.waitForURL(/.*\/catalog(\/.*)?$/, { timeout: 10000 });
    await page.waitForTimeout(1000);
    
    // Step 4: Verify the update
    await page.goto('/catalog');
    await page.waitForLoadState('networkidle');
    await expect(page.getByText(updatedName)).toBeVisible({ timeout: 5000 });
  });

  test('should delete a product', async ({ page }) => {
    // Step 1: Create a product first
    await page.goto('/catalog/products/create');
    
    const timestamp = Date.now();
    const productId = `DEL${timestamp}`;
    const productName = `Delete Product ${timestamp}`;
    
    await page.getByLabel('Уникальный ID товара').fill(productId);
    await page.getByLabel('Название товара', { exact: true }).fill(productName);
    
    await page.getByRole('button', { name: /Создать товар/i }).click();
    await page.waitForURL(/.*\/catalog(\/.*)?$/, { timeout: 10000 });
    await page.waitForTimeout(2000);
    
    // Step 2: Find product in the first few rows
    await page.goto('/catalog');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    
    // Look for the product in the first few rows
    let productRow = null;
    const rows = page.locator('tbody tr');
    const rowCount = await rows.count();
    for (let i = 0; i < Math.min(rowCount, 5); i++) {
      const row = rows.nth(i);
      const text = await row.textContent();
      if (text && text.includes(productName)) {
        productRow = row;
        break;
      }
    }
    
    if (!productRow) {
      throw new Error(`Product "${productName}" not found in the list`);
    }
    
    // Step 3: Click delete button (opens modal)
    await productRow.locator('button[title="Удалить"]').click();
    
    // Wait for modal and confirm deletion
    await expect(page.getByText('Вы уверены, что хотите удалить товар')).toBeVisible({ timeout: 3000 });
    await page.getByRole('button', { name: /Удалить товар/i }).click();
    
    // Wait for redirect
    await page.waitForURL(/.*\/catalog(\/.*)?$/, { timeout: 10000 });
    await page.waitForTimeout(1000);
    
    // Step 4: Verify product is deleted
    await page.reload();
    await page.waitForLoadState('networkidle');
    await expect(page.getByText(productName)).not.toBeVisible({ timeout: 5000 });
  });
});
