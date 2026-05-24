// @ts-check
const { test: baseTest } = require('@playwright/test');

const ADMIN_EMAIL = 'admin@kotiks.local';
const ADMIN_PASSWORD = 'kotiks2025';

/**
 * Perform admin login
 * @param {import('@playwright/test').Page} page 
 * @param {string} email 
 * @param {string} password 
 */
async function adminLogin(page, email = ADMIN_EMAIL, password = ADMIN_PASSWORD) {
  await page.goto('/login');
  // Wait for page to load
  await page.waitForLoadState('networkidle');
  
  // Fill email - the label might be "Email address" or "Email адрес"
  const emailField = page.getByLabel(/Email address|Email адрес/i).or(
    page.getByPlaceholder(/admin@kotiks/i)
  );
  await emailField.waitFor({ timeout: 5000 });
  await emailField.fill(email);
  
  // Fill password
  const passwordField = page.getByLabel(/Password|Пароль/i).or(
    page.getByPlaceholder(/password/i, { ignoreCase: true })
  );
  await passwordField.fill(password);
  
  // Click login button
  const loginButton = page.getByRole('button', { name: /Авторизоваться|Sign In|Log In/i });
  await loginButton.click();
  
  // Wait for navigation after login - wait for catalog page or any authenticated page
  await page.waitForURL('**/catalog**', { timeout: 15000 }).catch(async () => {
    // If redirect doesn't happen, check if we're still on login page
    const currentUrl = page.url();
    if (currentUrl.includes('/login')) {
      throw new Error('Login failed - still on login page');
    }
  });
}

exports.adminLogin = adminLogin;
exports.ADMIN_EMAIL = ADMIN_EMAIL;
exports.ADMIN_PASSWORD = ADMIN_PASSWORD;
