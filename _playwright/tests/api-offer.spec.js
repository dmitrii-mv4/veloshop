// @ts-check
const { test, expect } = require('@playwright/test');
const { ADMIN_EMAIL, ADMIN_PASSWORD } = require('./auth-helper');

test.describe('Product Offer API Tests', () => {
  let cookie;
  let productNumericId;

  test.beforeAll(async ({ request }) => {
    // Login once to get session cookies
    const loginPage = await request.get('/login');
    const loginHtml = await loginPage.text();
    
    const csrfMatch = loginHtml.match(/name="_token" value="([^"]+)"/);
    if (!csrfMatch) {
      throw new Error('Could not extract CSRF token');
    }
    const csrfToken = csrfMatch[1];

    const loginResponse = await request.post('/login', {
      form: {
        _token: csrfToken,
        email: ADMIN_EMAIL,
        password: ADMIN_PASSWORD
      },
      headers: {
        'Cookie': loginPage.headers()['set-cookie']?.join('; ') || ''
      }
    });

    const loginCookies = loginResponse.headers()['set-cookie'];
    if (loginCookies) {
      cookie = Array.isArray(loginCookies) ? loginCookies.join('; ') : loginCookies;
    }

    // Create a product to work with
    const createPage = await request.get('/catalog/products/create', {
      headers: { 'Cookie': cookie }
    });
    const createHtml = await createPage.text();
    const createCsrfMatch = createHtml.match(/name="_token" value="([^"]+)"/);
    const createCsrfToken = createCsrfMatch[1];

    const timestamp = Date.now();
    await request.post('/catalog/products', {
      form: {
        _token: createCsrfToken,
        product_id: `OFFER-API-${timestamp}`,
        name: `Offer API Test Product ${timestamp}`,
        brand: 'Offer Test Brand'
      },
      headers: {
        'Cookie': cookie,
        'Referer': 'http://dev.site03.loc/catalog/products/create'
      }
    });

    // Get the product numeric ID
    const listPage = await request.get('/catalog', {
      headers: { 'Cookie': cookie }
    });
    const listHtml = await listPage.text();
    const idMatch = listHtml.match(new RegExp(`href="/catalog/products/(\\d+)"[^>]*>Offer API Test Product`));
    if (idMatch) {
      productNumericId = idMatch[1];
    }
  });

  test.describe('Offer CRUD via web routes', () => {
    test('should create offer via web route POST', async ({ request }) => {
      if (!productNumericId) {
        throw new Error('Product ID not found');
      }

      // Get create page for CSRF token
      const createPage = await request.get(
        `/catalog/products/${productNumericId}/offers/create`,
        { headers: { 'Cookie': cookie } }
      );
      const createHtml = await createPage.text();
      const csrfMatch = createHtml.match(/name="_token" value="([^"]+)"/);
      if (!csrfMatch) {
        throw new Error('Could not extract CSRF token from offer create page');
      }
      const csrfToken = csrfMatch[1];

      const timestamp = Date.now();
      const offerId = `API-OFF${timestamp}`;
      const offerName = `API Test Offer ${timestamp}`;

      // Create offer
      const response = await request.post(
        `/catalog/products/${productNumericId}/offers`,
        {
          form: {
            _token: csrfToken,
            offer_id: offerId,
            name: offerName,
            articul_supplier: `SUPPLIER-${timestamp}`
          },
          headers: {
            'Cookie': cookie,
            'Referer': `http://dev.site03.loc/catalog/products/${productNumericId}/offers/create`
          }
        }
      );

      // Should redirect (302)
      expect(response.status()).toBe(302);

      // Verify offer was created
      const offersPage = await request.get(
        `/catalog/products/${productNumericId}/offers`,
        { headers: { 'Cookie': cookie } }
      );
      const offersHtml = await offersPage.text();
      expect(offersHtml).toContain(offerName);
    });

    test('should update offer via web route PUT', async ({ request }) => {
      if (!productNumericId) {
        throw new Error('Product ID not found');
      }

      // First create an offer
      const createPage = await request.get(
        `/catalog/products/${productNumericId}/offers/create`,
        { headers: { 'Cookie': cookie } }
      );
      const createHtml = await createPage.text();
      let csrfMatch = createHtml.match(/name="_token" value="([^"]+)"/);
      let csrfToken = csrfMatch[1];

      const timestamp = Date.now();
      const offerId = `API-UPD-OFF${timestamp}`;
      const offerName = `API Update Offer ${timestamp}`;

      await request.post(
        `/catalog/products/${productNumericId}/offers`,
        {
          form: {
            _token: csrfToken,
            offer_id: offerId,
            name: offerName
          },
          headers: {
            'Cookie': cookie,
            'Referer': `http://dev.site03.loc/catalog/products/${productNumericId}/offers/create`
          }
        }
      );

      // Get offers list to find offer ID
      const offersPage = await request.get(
        `/catalog/products/${productNumericId}/offers`,
        { headers: { 'Cookie': cookie } }
      );
      const offersHtml = await offersPage.text();
      
      const offerIdMatch = offersHtml.match(
        new RegExp(`href="/catalog/products/${productNumericId}/offers/(\\d+)"[^>]*>${offerName}`)
      );
      if (!offerIdMatch) {
        throw new Error('Could not find created offer');
      }
      const offerNumericId = offerIdMatch[1];

      // Get edit page for CSRF token
      const editPage = await request.get(
        `/catalog/products/${productNumericId}/offers/${offerNumericId}/edit`,
        { headers: { 'Cookie': cookie } }
      );
      const editHtml = await editPage.text();
      csrfMatch = editHtml.match(/name="_token" value="([^"]+)"/);
      csrfToken = csrfMatch[1];

      // Update the offer
      const updatedName = `API Updated Offer ${timestamp}`;
      const updateResponse = await request.post(
        `/catalog/products/${productNumericId}/offers/${offerNumericId}`,
        {
          form: {
            _token: csrfToken,
            _method: 'PUT',
            name: updatedName,
            articul_supplier: `UPDATED-SUPPLIER-${timestamp}`
          },
          headers: {
            'Cookie': cookie,
            'Referer': `http://dev.site03.loc/catalog/products/${productNumericId}/offers/${offerNumericId}/edit`
          }
        }
      );

      expect(updateResponse.status()).toBe(302);

      // Verify update
      const showPage = await request.get(
        `/catalog/products/${productNumericId}/offers/${offerNumericId}`,
        { headers: { 'Cookie': cookie } }
      );
      const showHtml = await showPage.text();
      expect(showHtml).toContain(updatedName);
    });

    test('should delete offer via web route DELETE', async ({ request }) => {
      if (!productNumericId) {
        throw new Error('Product ID not found');
      }

      // First create an offer
      const createPage = await request.get(
        `/catalog/products/${productNumericId}/offers/create`,
        { headers: { 'Cookie': cookie } }
      );
      const createHtml = await createPage.text();
      let csrfMatch = createHtml.match(/name="_token" value="([^"]+)"/);
      let csrfToken = csrfMatch[1];

      const timestamp = Date.now();
      const offerId = `API-DEL-OFF${timestamp}`;
      const offerName = `API Delete Offer ${timestamp}`;

      await request.post(
        `/catalog/products/${productNumericId}/offers`,
        {
          form: {
            _token: csrfToken,
            offer_id: offerId,
            name: offerName
          },
          headers: {
            'Cookie': cookie,
            'Referer': `http://dev.site03.loc/catalog/products/${productNumericId}/offers/create`
          }
        }
      );

      // Get offers list to find offer ID
      const offersPage = await request.get(
        `/catalog/products/${productNumericId}/offers`,
        { headers: { 'Cookie': cookie } }
      );
      const offersHtml = await offersPage.text();
      
      const offerIdMatch = offersHtml.match(
        new RegExp(`href="/catalog/products/${productNumericId}/offers/(\\d+)"[^>]*>${offerName}`)
      );
      if (!offerIdMatch) {
        throw new Error('Could not find created offer');
      }
      const offerNumericId = offerIdMatch[1];

      // Get edit page for CSRF token
      const editPage = await request.get(
        `/catalog/products/${productNumericId}/offers/${offerNumericId}/edit`,
        { headers: { 'Cookie': cookie } }
      );
      const editHtml = await editPage.text();
      csrfMatch = editHtml.match(/name="_token" value="([^"]+)"/);
      csrfToken = csrfMatch[1];

      // Delete the offer
      const deleteResponse = await request.post(
        `/catalog/products/${productNumericId}/offers/${offerNumericId}`,
        {
          form: {
            _token: csrfToken,
            _method: 'DELETE'
          },
          headers: {
            'Cookie': cookie,
            'Referer': `http://dev.site03.loc/catalog/products/${productNumericId}/offers/${offerNumericId}/edit`
          }
        }
      );

      expect(deleteResponse.status()).toBe(302);

      // Verify deletion
      const showResponse = await request.get(
        `/catalog/products/${productNumericId}/offers/${offerNumericId}`,
        { headers: { 'Cookie': cookie } }
      );
      expect([404, 302]).toContain(showResponse.status());
    });
  });
});
