// @ts-check
const { test, expect } = require('@playwright/test');

const ADMIN_EMAIL = 'admin@kotiks.local';
const ADMIN_PASSWORD = 'kotiks2025';
const BASE_URL = 'http://dev.site03.loc';

async function getXsrfToken(request) {
  const cookies = await request.storageState();
  const xsrfCookie = cookies.cookies?.find(c => c.name === 'XSRF-TOKEN');
  if (!xsrfCookie) return null;
  return decodeURIComponent(xsrfCookie.value);
}

async function loginAndGetToken(request) {
  await request.get('/sanctum/csrf-cookie', {
    headers: {
      'Accept': 'application/json',
      'Referer': BASE_URL,
      'Origin': BASE_URL,
    },
  });

  const xsrfToken = await getXsrfToken(request);
  if (!xsrfToken) throw new Error('Failed to get CSRF token');

  const loginResponse = await request.post('/api/users/login', {
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Referer': BASE_URL,
      'Origin': BASE_URL,
      'X-XSRF-TOKEN': xsrfToken,
    },
    data: {
      email: ADMIN_EMAIL,
      password: ADMIN_PASSWORD,
    },
  });

  if (loginResponse.status() !== 200) {
    throw new Error('Login failed');
  }

  return xsrfToken;
}

async function getOfferId(request, xsrfToken) {
  const response = await request.get('/api/products', {
    headers: {
      'Accept': 'application/json',
      'Referer': BASE_URL,
      'Origin': BASE_URL,
      'X-XSRF-TOKEN': xsrfToken,
    },
  });

  if (response.status() !== 200) {
    return null;
  }

  const body = await response.json();
  if (body.data && body.data.length > 0) {
    const product = body.data[0];
    if (product.offers && product.offers.length > 0) {
      return product.offers[0].offer_id;
    }
  }
  return null;
}

test.describe('Basket API Tests', () => {
  test('should return 401 without authentication', async ({ request }) => {
    const response = await request.post('/api/catalog/basket/add', {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Referer': BASE_URL,
        'Origin': BASE_URL,
      },
      data: {
        offer_id: 1,
        quantity: 1,
      },
    });

    expect([401, 419]).toContain(response.status());
  });

  test('should add offer to basket with valid authentication', async ({ request }) => {
    const xsrfToken = await loginAndGetToken(request);

    const offerId = await getOfferId(request, xsrfToken);
    if (!offerId) {
      test.skip();
      return;
    }

    const response = await request.post('/api/catalog/basket/add', {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Referer': BASE_URL,
        'Origin': BASE_URL,
        'X-XSRF-TOKEN': xsrfToken,
      },
      data: {
        offer_id: offerId,
        quantity: 2,
      },
    });

    expect([200, 201]).toContain(response.status());

    const body = await response.json();
    expect(body).toHaveProperty('message');
    expect(['Оффер добавлен в корзину.', 'Количество оффера обновлено в корзине.']).toContain(body.message);
  });

  test('should fail with invalid offer_id', async ({ request }) => {
    const xsrfToken = await loginAndGetToken(request);

    const response = await request.post('/api/catalog/basket/add', {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Referer': BASE_URL,
        'Origin': BASE_URL,
        'X-XSRF-TOKEN': xsrfToken,
      },
      data: {
        offer_id: 999999999,
        quantity: 1,
      },
    });

    expect(response.status()).toBe(422);

    const body = await response.json();
    expect(body).toHaveProperty('errors');
    expect(body.errors).toHaveProperty('offer_id');
  });

  test('should fail with missing quantity', async ({ request }) => {
    const xsrfToken = await loginAndGetToken(request);

    const offerId = await getOfferId(request, xsrfToken);
    if (!offerId) {
      test.skip();
      return;
    }

    const response = await request.post('/api/catalog/basket/add', {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Referer': BASE_URL,
        'Origin': BASE_URL,
        'X-XSRF-TOKEN': xsrfToken,
      },
      data: {
        offer_id: offerId,
      },
    });

    expect(response.status()).toBe(422);

    const body = await response.json();
    expect(body).toHaveProperty('errors');
    expect(body.errors).toHaveProperty('quantity');
  });
});