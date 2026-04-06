// @ts-check
const { test, expect } = require('@playwright/test');

test('add Page Siblings widget to Blog Sidebar - working approach', async ({ page }) => {
  // Login
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.locator('#user_login').fill('konstantin.agafonov@gmail.com');
  await page.locator('#user_pass').fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  await page.goto('http://dev.site07.loc/wp-admin/widgets.php');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);

  // Find the Blog Sidebar container - it's the first one
  const blogSidebarContainer = page.locator('#widgets-right .sidebar, #widgets-right .widgets-holder-wrap').first();
  
  // Check what widgets are already in Blog Sidebar
  const existingWidgets = blogSidebarContainer.locator('.widget');
  const existingWidgetsCount = await existingWidgets.count();
  console.log('Existing widgets in Blog Sidebar:', existingWidgetsCount);

  // Find the "Add a widget" button or similar
  const addWidgetBtn = page.locator('#widgets-left .widget-tpl-add, .add-widget, button:has-text("Add"), .button:has-text("Добавить")').first();
  
  // Try to find and click on Page Siblings widget to add it
  const pageSiblingsAvailable = page.locator('#widgets-left .widget-tpl, .widget.ui-draggable').filter({ hasText: 'Page Siblings' }).first();
  
  if (await pageSiblingsAvailable.count() > 0) {
    console.log('Found Page Siblings in available widgets');
    
    // Get the target sidebar position
    const blogSidebarBox = blogSidebarContainer.locator('.inside, .widget-area').first();
    
    // Try to click and add the widget
    await pageSiblingsAvailable.click();
    await page.waitForTimeout(1500);
    await page.screenshot({ path: 'automation/screenshots/current/after-click-page-siblings.png', fullPage: true });
    
    // Check if widget appeared in Blog Sidebar
    const widgetsAfter = await blogSidebarContainer.locator('.widget:not(.ui-draggable)').count();
    console.log('Widgets in sidebar after click:', widgetsAfter);
    
    await page.screenshot({ path: 'automation/screenshots/current/check-if-added.png', fullPage: true });
  } else {
    console.log('Page Siblings not found in available widgets list');
  }

  // Save widgets
  const saveBtn = page.locator('#savewidget');
  if (await saveBtn.count() > 0) {
    await saveBtn.click();
    await page.waitForTimeout(2000);
    console.log('Saved widgets');
  }

  // Check frontend
  await page.goto('http://dev.site07.loc/advertising/context/google-ads/');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'automation/screenshots/current/frontend-check.png', fullPage: true });
  
  const content = await page.content();
  console.log('Has widget_page_siblings:', content.includes('widget_page_siblings'));
  console.log('Has Page Siblings text:', content.includes('Page Siblings'));
});
