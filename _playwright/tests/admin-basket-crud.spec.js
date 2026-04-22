// @ts-check
const { test, expect } = require('@playwright/test');
const { adminLogin } = require('./auth-helper');

test.describe('Admin Basket CRUD via UI', () => {
  // Basket pages load many offers, need more time
  test.describe.configure({ timeout: 60000 });

  test.beforeEach(async ({ page }) => {
    await adminLogin(page);
  });

  test('should create a new basket', async ({ page }) => {
    await page.goto('/catalog/basket/create');
    await page.waitForLoadState('networkidle');

    // Select a user - use the select element directly by ID
    await page.selectOption('#user_id', { index: 1 });

    // Submit the form
    await page.getByRole('button', { name: /Создать корзину/i }).click();

    // Wait for redirect to edit page - basket page has many offers so may be slow
    await page.waitForURL(/.*\/basket\/\d+\/edit$/, { timeout: 45000 });

    // Verify we're on the edit page (basket was created)
    expect(page.url()).toMatch(/\/basket\/\d+\/edit$/);
  });

  test('should update an existing basket', async ({ page }) => {
    // Navigate to basket list and get first basket
    await page.goto('/catalog/basket');
    await page.waitForLoadState('networkidle');

    // Get first basket's edit URL from the table
    const firstEditLink = page.locator('tbody tr').first().locator('a').nth(0);
    await expect(firstEditLink).toBeVisible({ timeout: 5000 });
    const editUrl = await firstEditLink.getAttribute('href');

    // Navigate to edit page - may be slow due to many offers
    await page.goto(editUrl);
    await page.waitForLoadState('networkidle');

    // Get current user selection and change it
    await page.selectOption('#user_id', { index: 1 });

    // Submit update form
    await page.locator('button[type="submit"].btn-primary').first().click();

    // Wait for redirect back to edit page - basket page is slow
    await page.waitForURL(/.*\/basket\/\d+\/edit$/, { timeout: 45000 });

    // Verify we're still on edit page (update succeeded)
    expect(page.url()).toMatch(/\/basket\/\d+\/edit$/);
  });

  test('should delete a basket', async ({ page }) => {
    // Navigate to basket list
    await page.goto('/catalog/basket');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    // Get basket count before deletion
    const rowsBefore = await page.locator('tbody tr').count();
    expect(rowsBefore).toBeGreaterThan(0);

    // Get first basket's ID
    const firstRow = page.locator('tbody tr').first();
    const basketId = await firstRow.locator('td').first().textContent();

    // Click delete button - uses class delete-basket-btn
    await firstRow.locator('button.delete-basket-btn').click();

    // Wait for modal and confirm deletion
    await expect(page.getByText('Вы уверены, что хотите удалить корзину')).toBeVisible({ timeout: 3000 });
    await page.locator('#deleteBasketModal .btn-danger').click();

    // Wait for redirect
    await page.waitForURL(/.*\/basket$/, { timeout: 10000 });
    await page.waitForTimeout(1000);

    // Verify basket is deleted
    await page.reload();
    await page.waitForLoadState('networkidle');
    await expect(page.getByText(basketId)).not.toBeVisible({ timeout: 5000 });
  });
});
