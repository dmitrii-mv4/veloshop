// @ts-check
const { test, expect } = require('@playwright/test');

test('add Page Siblings widget using classic widgets', async ({ page }) => {
  // Login
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.locator('#user_login').fill('konstantin.agafonov@gmail.com');
  await page.locator('#user_pass').fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  // Try classic widgets page
  await page.goto('http://dev.site07.loc/wp-admin/widgets.php');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'automation/screenshots/current/classic-widgets.png', fullPage: true });

  // Check if we can drag/drop widgets
  // Find the Blog Sidebar
  const blogSidebar = page.locator('#widgets-right .sidebar:has-text("Blog Sidebar"), #widgets-right .widgets-holder-wrap:has-text("Blog Sidebar")').first();
  
  // Alternative - look for Blog Sidebar by ID
  const blogSidebarById = page.locator('#sidebar-avrix-blog-sidebar');
  if (await blogSidebarById.count() > 0) {
    console.log('Found Blog Sidebar by ID');
    await page.screenshot({ path: 'automation/screenshots/current/blog-sidebar-found.png', fullPage: true });
  } else {
    console.log('Blog Sidebar not found by ID, searching...');
    // List all sidebars
    const sidebars = page.locator('#widgets-right .sidebar, #widgets-right .widgets-holder-wrap');
    const count = await sidebars.count();
    console.log('Total sidebars found:', count);
    for (let i = 0; i < Math.min(count, 5); i++) {
      const title = await sidebars.nth(i).locator('h2, h3, .sidebar-name').first().textContent();
      console.log(`Sidebar ${i}:`, title);
    }
  }

  // Let's try a different approach - use AJAX to add the widget
  console.log('Attempting to add widget via REST API...');
  
  // First, get nonce and current widget configuration
  await page.goto('http://dev.site07.loc/wp-admin/widgets.php');
  await page.waitForLoadState('networkidle');
  
  // Use browser console to add widget
  const result = await page.evaluate(async () => {
    // Check if we can use the widgets API
    return {
      hasWidgetsApi: typeof wp !== 'undefined' && wp.widgets !== undefined,
      currentUrl: window.location.href
    };
  });
  console.log('Widgets API available:', result);

  // Let's just manually add the widget to the sidebar via direct link or button
  // Look for available widgets in left column
  const availableWidgets = page.locator('#widgets-left .widget');
  const pageSiblingsWidget = availableWidgets.filter({ hasText: 'Page Siblings' }).first();
  
  if (await pageSiblingsWidget.count() > 0) {
    console.log('Found Page Siblings widget in available widgets');
    // Try to add it by clicking and dragging or using the "Add" button
    await pageSiblingsWidget.click();
    await page.waitForTimeout(1000);
    await page.screenshot({ path: 'automation/screenshots/current/widget-clicked.png', fullPage: true });
  } else {
    console.log('Page Siblings widget not visible in available widgets');
  }
});
