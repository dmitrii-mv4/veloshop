// @ts-check
const { test, expect } = require('@playwright/test');

test('find sidebar name and add widget', async ({ page }) => {
  // Login
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.locator('#user_login').fill('konstantin.agafonov@gmail.com');
  await page.locator('#user_pass').fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  // Navigate to widgets page
  await page.goto('http://dev.site07.loc/wp-admin/widgets.php');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);

  // Get full page content to understand structure
  const pageContent = await page.content();
  
  // Look for available widget areas/sidebars
  const widgetAreasSelect = page.locator('#widgets-right select, .widget-area-select, #available-widgets select').first();
  if (await widgetAreasSelect.count() > 0) {
    const options = await widgetAreasSelect.locator('option').allTextContents();
    console.log('Widget areas:', options);
  }

  // Try to find the sidebar in the page
  const sidebarTitles = page.locator('.sidebar-name h2, .sidebar-name h3, h2:has-text("Sidebar"), h3:has-text("Sidebar")');
  const sidebarTitlesCount = await sidebarTitles.count();
  console.log('Sidebar titles count:', sidebarTitlesCount);
  
  for (let i = 0; i < sidebarTitlesCount; i++) {
    const title = await sidebarTitles.nth(i).textContent();
    console.log('Sidebar title:', title);
  }

  // Check for available widgets
  const availableWidgets = page.locator('.widget .widget-title, .widget h3, .widget h4');
  const availableWidgetsCount = await availableWidgets.count();
  console.log('Available widgets count:', availableWidgetsCount);

  // Check if we have the Page Siblings widget available
  const pageSiblingsWidget = page.locator('text="Page Siblings"');
  if (await pageSiblingsWidget.count() > 0) {
    console.log('Page Siblings widget found in available widgets');
  }

  // Let's try to access the customizer instead
  await page.goto('http://dev.site07.loc/wp-admin/customize.php?url=' + encodeURIComponent('http://dev.site07.loc/advertising/context/google-ads/'));
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(3000);
  await page.screenshot({ path: 'automation/screenshots/current/customizer.png', fullPage: true });

  // Look for Widgets section in customizer
  const widgetsSection = page.locator('.accordion-section:has-text("Widgets")');
  if (await widgetsSection.count() > 0) {
    console.log('Widgets section found in customizer');
    await widgetsSection.click();
    await page.waitForTimeout(1000);
    await page.screenshot({ path: 'automation/screenshots/current/customizer-widgets.png', fullPage: true });
  }
});
