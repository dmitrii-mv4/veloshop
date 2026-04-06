// @ts-check
const { test, expect } = require('@playwright/test');

test('login and check structure', async ({ page }) => {
  // Login to WP admin
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.waitForLoadState('networkidle');
  await page.screenshot({ path: 'automation/screenshots/current/login-page.png', fullPage: true });
  
  // Try different selectors for login form
  const usernameInput = page.locator('#user_login').first();
  await expect(usernameInput).toBeVisible();
  await usernameInput.fill('konstantin.agafonov@gmail.com');
  
  const passwordInput = page.locator('#user_pass').first();
  await passwordInput.fill('q3AF52*!bV3N5@yvSu(LbKia');
  
  await page.locator('#wp-submit').click();
  await page.waitForLoadState('networkidle');
  await page.screenshot({ path: 'automation/screenshots/current/after-login.png', fullPage: true });
  
  console.log('Logged in, current URL:', page.url());
});
