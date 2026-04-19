// @ts-check
const { test, expect } = require('@playwright/test');

const ADMIN_EMAIL = 'admin@kotiks.local';
const ADMIN_PASSWORD = 'kotiks2025';

test.describe('Login 419 Issue - Cookie Investigation', () => {
  test('should analyze cookies on login page', async ({ page }) => {
    await page.goto('/login');
    await page.waitForLoadState('networkidle');
    
    // Check initial cookies - there should be session cookie!
    const cookies = await page.context().cookies();
    console.log('=== COOKIES ON LOGIN PAGE ===');
    console.log('Count:', cookies.length);
    for (const c of cookies) {
      console.log(`\nCookie: ${c.name}`);
      console.log(`  Value: ${c.value.substring(0, 50)}...`);
      console.log(`  Domain: ${c.domain}`);
      console.log(`  Path: ${c.path}`);
      console.log(`  HttpOnly: ${c.httpOnly}`);
      console.log(`  Secure: ${c.secure}`);
      console.log(`  SameSite: ${c.sameSite}`);
      console.log(`  Expires: ${c.expires}`);
    }
    
    // Check if session cookie exists
    const sessionCookies = cookies.filter(c => c.name.includes('session'));
    console.log('\n=== SESSION COOKIES ===');
    console.log('Found:', sessionCookies.length);
    
    if (sessionCookies.length === 0) {
      console.log('WARNING: No session cookie found on login page!');
      console.log('This is the ROOT CAUSE of the 419 error.');
    }
  });
  
  test('should manually set cookies and try to login', async ({ page }) => {
    // First visit login to get CSRF token
    await page.goto('/login');
    await page.waitForLoadState('networkidle');
    
    const csrfToken = await page.locator('input[name="_token"]').first().inputValue();
    console.log('CSRF Token:', csrfToken);
    
    // Try to login and see what happens
    await page.getByLabel(/Email address|Email адрес/i).or(
      page.getByPlaceholder(/admin@kotiks/i)
    ).fill(ADMIN_EMAIL);
    
    await page.getByLabel(/Password Пароль/i).or(
      page.getByPlaceholder(/password/i)
    ).fill(ADMIN_PASSWORD);
    
    await page.getByRole('button', { name: /Авторизоваться/i }).click();
    
    await page.waitForTimeout(3000);
    
    console.log('URL after login:', page.url());
    
    // Check cookies after login attempt
    const cookies = await page.context().cookies();
    console.log('\n=== COOKIES AFTER LOGIN ===');
    for (const c of cookies) {
      console.log(`${c.name}: ${c.value.substring(0, 30)}...`);
    }
    
    // Check for 419 error
    const content = await page.content();
    if (content.includes('419')) {
      console.log('\n!!! 419 ERROR DETECTED !!!');
    }
  });
  
  test('check headers from login response', async ({ page }) => {
    const responsePromise = page.waitForResponse(resp => resp.url().includes('/login') && resp.request().method() === 'GET');
    
    await page.goto('/login');
    
    const response = await responsePromise;
    const headers = response.headers();
    
    console.log('=== LOGIN PAGE RESPONSE HEADERS ===');
    for (const [key, value] of Object.entries(headers)) {
      if (key.toLowerCase().includes('cookie') || key.toLowerCase().includes('set-')) {
        console.log(`${key}: ${value}`);
      }
    }
    
    console.log('\n=== SET-COOKIE HEADER ===');
    console.log(headers['set-cookie'] || 'NO SET-COOKIE HEADER FOUND');
  });
});