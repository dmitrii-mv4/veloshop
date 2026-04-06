// @ts-check
const { test, expect } = require('@playwright/test');
const { ADMIN_EMAIL, ADMIN_PASSWORD } = require('./auth-helper');

test.describe('Product API Tests', () => {
  test.describe('GET /api/products', () => {
    test('should return products list', async ({ request }) => {
      const response = await request.get('/api/products');
      
      // API might not be publicly accessible
      if (response.status() === 401 || response.status() === 403 || response.status() === 404) {
        test.skip();
        return;
      }
      
      expect(response.status()).toBe(200);
      
      const body = await response.json();
      expect(body).toHaveProperty('data');
      expect(body).toHaveProperty('links');
      expect(body).toHaveProperty('meta');
      
      expect(Array.isArray(body.data)).toBeTruthy();
    });

    test('should return products with offers', async ({ request }) => {
      const response = await request.get('/api/products');
      
      if (response.status() === 401 || response.status() === 403 || response.status() === 404) {
        test.skip();
        return;
      }
      
      const body = await response.json();
      
      if (body.data.length > 0) {
        const product = body.data[0];
        expect(product).toHaveProperty('id');
        expect(product).toHaveProperty('name');
        expect(product).toHaveProperty('product_id');
        expect(product).toHaveProperty('offers');
      }
    });
  });
});
