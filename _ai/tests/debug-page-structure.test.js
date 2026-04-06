// @ts-check
const { test, expect } = require('@playwright/test');

test('check page structure and widgets', async ({ page }) => {
  // Login
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.locator('#user_login').fill('konstantin.agafonov@gmail.com');
  await page.locator('#user_pass').fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  // Visit the target page
  await page.goto('http://dev.site07.loc/advertising/context/google-ads/');
  await page.waitForLoadState('networkidle');
  await page.screenshot({ path: 'automation/screenshots/current/google-ads-page.png', fullPage: true });

  // Check for sidebar and widgets
  const sidebar = page.locator('.sidebar, .widget-area, aside').first();
  if (await sidebar.count() > 0) {
    console.log('Sidebar found');
    const sidebarContent = await sidebar.first().textContent();
    console.log('Sidebar content preview:', sidebarContent?.substring(0, 500));
  }

  // Check for nav menu widget specifically
  const navMenuWidget = page.locator('.widget_nav_menu').first();
  if (await navMenuWidget.count() > 0) {
    console.log('Nav Menu Widget found');
    const widgetHTML = await navMenuWidget.first().innerHTML();
    console.log('Widget HTML structure:', widgetHTML.substring(0, 1000));
  }

  // Get page ID from body class
  const bodyClasses = await page.locator('body').getAttribute('class');
  const pageIdMatch = bodyClasses?.match(/page-id-(\d+)/);
  const pageId = pageIdMatch ? pageIdMatch[1] : null;
  console.log('Page ID:', pageId);

  // Navigate to widgets admin
  await page.goto('http://dev.site07.loc/wp-admin/widgets.php');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'automation/screenshots/current/widgets-page.png', fullPage: true });

  // Check if it's block or classic widgets
  const bodyClasses2 = await page.locator('body').getAttribute('class');
  console.log('Widgets page classes:', bodyClasses2);

  // Look for widget areas
  const widgetAreas = page.locator('.widget-area, .block-editor-widget-area');
  const widgetAreasCount = await widgetAreas.count();
  console.log('Widget areas count:', widgetAreasCount);
});
