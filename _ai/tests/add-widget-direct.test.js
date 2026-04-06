// @ts-check
const { test, expect } = require('@playwright/test');

test('add widget by duplicating existing widget', async ({ page }) => {
  // Login
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.locator('#user_login').fill('konstantin.agafonov@gmail.com');
  await page.locator('#user_pass').fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  await page.goto('http://dev.site07.loc/wp-admin/widgets.php');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);

  // Look at the existing widgets in Blog Sidebar
  const blogSidebar = page.locator('#widgets-right .sidebar, #widgets-right .widgets-holder-wrap').first();
  
  // Get the HTML of existing widgets to understand structure
  const existingWidgetsHTML = await blogSidebar.innerHTML();
  console.log('Existing widgets structure (first 1000 chars):', existingWidgetsHTML.substring(0, 1000));

  // Find the "Add Widget" interface - usually there's an "Inactive Widgets" or similar
  // Try looking for a button or link to add widgets
  const buttons = await page.locator('#widgets-left .button, .add-new-widget, .add-widget').all();
  console.log('Found buttons:', buttons.length);

  // Let's try to directly manipulate the DOM and then save
  const added = await page.evaluate(() => {
    try {
      // Find Page Siblings widget template
      const tpl = Array.from(document.querySelectorAll('#widgets-left .widget, .widget-tpl'))
        .find(el => el.textContent.includes('Page Siblings'));
      
      if (!tpl) return { success: false, error: 'Template not found' };

      // Clone it
      const clone = tpl.cloneNode(true);
      clone.id = clone.id.replace('tpl-', 'page_siblings-');
      clone.classList.remove('ui-draggable');
      
      // Add to first sidebar in right column
      const sidebars = document.querySelectorAll('#widgets-right .sidebar, #widgets-right .widgets-holder-wrap');
      const sidebar = sidebars[0];
      const container = sidebar.querySelector('.inside, .widget-area') || sidebar;
      
      // Append the cloned widget
      container.appendChild(clone);

      return { 
        success: true, 
        newWidgetId: clone.id,
        containerWidgets: container.querySelectorAll('.widget').length
      };
    } catch (e) {
      return { success: false, error: e.message };
    }
  });

  console.log('Added:', added);
  await page.screenshot({ path: 'automation/screenshots/current/dom-manipulation.png', fullPage: true });

  // Click Save button
  const saveBtn = page.locator('#savewidget, #savewidgets');
  if (await saveBtn.count() > 0) {
    await saveBtn.first().click();
    await page.waitForTimeout(2000);
    await page.screenshot({ path: 'automation/screenshots/current/after-save.png', fullPage: true });
    console.log('Save clicked');
  }

  // Verify on frontend
  await page.goto('http://dev.site07.loc/advertising/context/google-ads/');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'automation/screenshots/current/frontend-verify-final.png', fullPage: true });

  const content = await page.content();
  if (content.includes('widget_page_siblings')) {
    console.log('✓ SUCCESS: Widget found on frontend!');
  } else {
    console.log('✗ Widget not found, checking sidebar...');
    // Check if widget is at least in the sidebar
    const sidebarText = await page.locator('aside, .sidebar').first().textContent();
    console.log('Sidebar text preview:', sidebarText?.substring(0, 300));
  }
});
