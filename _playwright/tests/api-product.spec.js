// @ts-check
const { test, expect } = require('@playwright/test');
const { ADMIN_EMAIL, ADMIN_PASSWORD } = require('./auth-helper');

test.describe('Product API Tests', () => {
  test.describe('GET /api/products', () => {
    test('should return products list', async ({ request }) => {
      const response = await request.get('/api/products');
      
      expect(response.ok()).toBeTruthy();
      expect(response.status()).toBe(200);
      
      const body = await response.json();
      expect(body).toHaveProperty('data');
      expect(body).toHaveProperty('links');
      expect(body).toHaveProperty('meta');
      
      // Products should be an array
      expect(Array.isArray(body.data)).toBeTruthy();
    });

    test('should return products with offers', async ({ request }) => {
      const response = await request.get('/api/products');
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

  test.describe('API Product CRUD via web routes', () => {
    let csrfToken;
    let cookie;

    test.beforeEach(async ({ request }) => {
      // Login to get session cookies
      const loginPage = await request.get('/login');
      const loginHtml = await loginPage.text();
      
      // Extract CSRF token from login page
      const csrfMatch = loginHtml.match(/name="_token" value="([^"]+)"/);
      if (!csrfMatch) {
        throw new Error('Could not extract CSRF token');
      }
      csrfToken = csrfMatch[1];
      
      // Extract session cookie
      const setCookie = loginPage.headers()['set-cookie'];
      if (setCookie) {
        cookie = Array.isArray(setCookie) ? setCookie[0] : setCookie;
      }

      // Perform login
      const loginResponse = await request.post('/login', {
        form: {
          _token: csrfToken,
          email: ADMIN_EMAIL,
          password: ADMIN_PASSWORD
        },
        headers: {
          'Cookie': cookie,
          'X-CSRF-TOKEN': csrfToken
        }
      });

      // Get authenticated session cookies
      const loginCookies = loginResponse.headers()['set-cookie'];
      if (loginCookies) {
        cookie = Array.isArray(loginCookies) ? loginCookies.join('; ') : loginCookies;
      }
    });

    test('should create product via web route POST', async ({ request }) => {
      const timestamp = Date.now();
      const productId = `API-TP${timestamp}`;
      const productName = `API Test Product ${timestamp}`;

      // First, get the create page to extract CSRF token
      const createPage = await request.get('/catalog/products/create', {
        headers: { 'Cookie': cookie }
      });
      const createHtml = await createPage.text();
      const csrfMatch = createHtml.match(/name="_token" value="([^"]+)"/);
      if (!csrfMatch) {
        throw new Error('Could not extract CSRF token from create page');
      }
      const currentCsrfToken = csrfMatch[1];

      // Create product
      const response = await request.post('/catalog/products', {
        form: {
          _token: currentCsrfToken,
          product_id: productId,
          name: productName,
          brand: 'API Test Brand',
          model: 'API Test Model',
          seazon: 'Summer 2024'
        },
        headers: {
          'Cookie': cookie,
          'Referer': 'http://dev.site03.loc/catalog/products/create'
        }
      });

      // Should redirect to catalog index (302)
      expect(response.status()).toBe(302);
      
      // Follow redirect and check for success
      const finalResponse = await request.get('/catalog', {
        headers: { 'Cookie': cookie }
      });
      const finalHtml = await finalResponse.text();
      expect(finalHtml).toContain(productName);
    });

    test('should update product via web route PUT', async ({ request }) => {
      const timestamp = Date.now();
      const productId = `API-UPD${timestamp}`;
      const productName = `API Update Product ${timestamp}`;

      // Get CSRF token from create page
      const createPage = await request.get('/catalog/products/create', {
        headers: { 'Cookie': cookie }
      });
      const createHtml = await createPage.text();
      let csrfMatch = createHtml.match(/name="_token" value="([^"]+)"/);
      if (!csrfMatch) {
        throw new Error('Could not extract CSRF token');
      }
      let currentCsrfToken = csrfMatch[1];

      // Create product first
      await request.post('/catalog/products', {
        form: {
          _token: currentCsrfToken,
          product_id: productId,
          name: productName,
          brand: 'Original Brand'
        },
        headers: {
          'Cookie': cookie,
          'Referer': 'http://dev.site03.loc/catalog/products/create'
        }
      });

      // Get product list to find the product ID
      const listPage = await request.get('/catalog', {
        headers: { 'Cookie': cookie }
      });
      const listHtml = await listPage.text();
      
      // Extract product numeric ID from the list
      const idMatch = listHtml.match(new RegExp(`href="/catalog/products/(\\d+)"[^>]*>${productName}`));
      if (!idMatch) {
        throw new Error('Could not find created product in list');
      }
      const numericId = idMatch[1];

      // Get edit page for CSRF token
      const editPage = await request.get(`/catalog/products/${numericId}/edit`, {
        headers: { 'Cookie': cookie }
      });
      const editHtml = await editPage.text();
      csrfMatch = editHtml.match(/name="_token" value="([^"]+)"/);
      if (!csrfMatch) {
        throw new Error('Could not extract CSRF token from edit page');
      }
      currentCsrfToken = csrfMatch[1];

      // Update the product
      const updatedName = `API Updated Product ${timestamp}`;
      const updateResponse = await request.post(`/catalog/products/${numericId}`, {
        form: {
          _token: currentCsrfToken,
          _method: 'PUT',
          name: updatedName,
          brand: 'Updated Brand',
          model: 'Updated Model'
        },
        headers: {
          'Cookie': cookie,
          'Referer': `http://dev.site03.loc/catalog/products/${numericId}/edit`
        }
      });

      expect(updateResponse.status()).toBe(302);
      
      // Verify update
      const showPage = await request.get(`/catalog/products/${numericId}`, {
        headers: { 'Cookie': cookie }
      });
      const showHtml = await showPage.text();
      expect(showHtml).toContain(updatedName);
    });

    test('should delete product via web route DELETE', async ({ request }) => {
      const timestamp = Date.now();
      const productId = `API-DEL${timestamp}`;
      const productName = `API Delete Product ${timestamp}`;

      // Get CSRF token from create page
      const createPage = await request.get('/catalog/products/create', {
        headers: { 'Cookie': cookie }
      });
      const createHtml = await createPage.text();
      let csrfMatch = createHtml.match(/name="_token" value="([^"]+)"/);
      if (!csrfMatch) {
        throw new Error('Could not extract CSRF token');
      }
      let currentCsrfToken = csrfMatch[1];

      // Create product first
      await request.post('/catalog/products', {
        form: {
          _token: currentCsrfToken,
          product_id: productId,
          name: productName
        },
        headers: {
          'Cookie': cookie,
          'Referer': 'http://dev.site03.loc/catalog/products/create'
        }
      });

      // Get product list to find the product ID
      const listPage = await request.get('/catalog', {
        headers: { 'Cookie': cookie }
      });
      const listHtml = await listPage.text();
      
      const idMatch = listHtml.match(new RegExp(`href="/catalog/products/(\\d+)"[^>]*>${productName}`));
      if (!idMatch) {
        throw new Error('Could not find created product in list');
      }
      const numericId = idMatch[1];

      // Get edit page for CSRF token
      const editPage = await request.get(`/catalog/products/${numericId}/edit`, {
        headers: { 'Cookie': cookie }
      });
      const editHtml = await editPage.text();
      csrfMatch = editHtml.match(/name="_token" value="([^"]+)"/);
      if (!csrfMatch) {
        throw new Error('Could not extract CSRF token from edit page');
      }
      currentCsrfToken = csrfMatch[1];

      // Delete the product
      const deleteResponse = await request.post(`/catalog/products/${numericId}`, {
        form: {
          _token: currentCsrfToken,
          _method: 'DELETE'
        },
        headers: {
          'Cookie': cookie,
          'Referer': `http://dev.site03.loc/catalog/products/${numericId}/edit`
        }
      });

      expect(deleteResponse.status()).toBe(302);
      
      // Verify deletion
      const showResponse = await request.get(`/catalog/products/${numericId}`, {
        headers: { 'Cookie': cookie }
      });
      // Should return 404 or redirect
      expect([404, 302]).toContain(showResponse.status());
    });
  });
});
