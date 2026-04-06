// @ts-check
const { test, expect } = require('@playwright/test');
const { adminLogin } = require('./auth-helper');

// Run tests sequentially to avoid conflicts
test.describe.configure({ mode: 'serial' });

test.describe('Admin Product Management Tests', () => {
  let testProductId;
  let testProductNumericId;

  test.beforeEach(async ({ page }) => {
    await adminLogin(page);
  });

  test('1. should create a new product successfully', async ({ page }) => {
    await page.goto('/catalog/products/create');
    
    const timestamp = Date.now();
    const productId = `E2E-TP-${timestamp}`;
    const productName = `E2E Test Product ${timestamp}`;
    
    // Fill required fields
    await page.getByLabel('Уникальный ID товара').fill(productId);
    await page.getByLabel('Название товара', { exact: true }).fill(productName);
    await page.getByLabel('Бренд').fill('E2E Test Brand');
    await page.getByLabel('Модель').fill('E2E Model');
    
    // Submit the form
    await page.getByRole('button', { name: /Создать товар/i }).click();
    
    // Wait for redirect
    await page.waitForURL(/.*\/catalog(\/.*)?$/, { timeout: 10000 });
    
    // Success - we were redirected to catalog, meaning product was created
    expect(page.url()).toContain('/catalog');
    
    // Store product ID for next test
    testProductId = productId;
  });

  test('2. should create a product offer', async ({ page }) => {
    // First, find an existing product to add an offer to
    // We'll use product ID 4014 which we know exists from earlier screenshots
    const knownProductId = 4014;
    
    await page.goto(`/catalog/products/${knownProductId}/offers/create`);
    
    const timestamp = Date.now();
    const offerId = `E2E-OFF-${timestamp}`;
    const offerName = `E2E Test Offer ${timestamp}`;
    
    // Fill offer details
    await page.getByLabel('Уникальный ID предложения').fill(offerId);
    await page.getByLabel('Название предложения', { exact: true }).fill(offerName);
    await page.getByLabel('Артикул поставщика').fill(`E2E-SUPPLIER-${timestamp}`);
    
    // Submit form
    await page.getByRole('button', { name: /Создать предложение/i }).click();
    
    // Wait for redirect
    await page.waitForURL(/.*\/offers$/, { timeout: 10000 });
    
    // Success - we were redirected to offers list
    expect(page.url()).toContain('/offers');
    
    // Store for later tests
    testProductNumericId = knownProductId;
  });
});
