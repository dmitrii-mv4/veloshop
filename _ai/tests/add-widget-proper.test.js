// @ts-check
const { test, expect } = require('@playwright/test');

test('add widget properly with form save', async ({ page }) => {
  // Login
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.locator('#user_login').fill('konstantin.agafonov@gmail.com');
  await page.locator('#user_pass').fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  await page.goto('http://dev.site07.loc/wp-admin/widgets.php');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);

  // Add the widget using proper WordPress mechanism
  const result = await page.evaluate(() => {
    return new Promise((resolve) => {
      try {
        // Find Page Siblings template
        const tpl = Array.from(document.querySelectorAll('#widgets-left .widget'))
          .find(el => el.textContent.includes('Page Siblings'));
        
        if (!tpl) {
          resolve({ success: false, error: 'Template not found' });
          return;
        }

        // Get the ID base from template
        const tplId = tpl.id; // something like "page_siblings"
        
        // Click on it to add to first sidebar
        tpl.click();
        
        setTimeout(() => {
          // Check if it appeared in sidebar
          const sidebars = document.querySelectorAll('#widgets-right .sidebar, #widgets-right .widgets-holder-wrap');
          const sidebar = sidebars[0];
          const container = sidebar.querySelector('.inside, .widget-area') || sidebar;
          const addedWidget = container.querySelector('[id*="page_siblings"]');
          
          if (addedWidget) {
            // Expand the widget
            const toggleBtn = addedWidget.querySelector('.widget-title-action button, .widget-action');
            if (toggleBtn) {
              toggleBtn.click();
            }
            
            resolve({ 
              success: true, 
              widgetId: addedWidget.id,
              widgetHTML: addedWidget.innerHTML.substring(0, 500)
            });
          } else {
            resolve({ success: false, error: 'Widget not added to sidebar' });
          }
        }, 1500);
      } catch (e) {
        resolve({ success: false, error: e.message });
      }
    });
  });

  console.log('Result:', result);
  await page.screenshot({ path: 'automation/screenshots/current/widget-added-state.png', fullPage: true });

  // Wait a bit then save
  await page.waitForTimeout(1000);
  
  // Find and click save button
  const saveBtn = page.locator('input#savewidget, button#savewidget, #savewidget');
  const saveBtnCount = await saveBtn.count();
  
  if (saveBtnCount > 0) {
    await saveBtn.first().click();
    await page.waitForTimeout(3000);
    await page.screenshot({ path: 'automation/screenshots/current/after-save-button.png', fullPage: true });
    console.log('✓ Save button clicked');
  } else {
    console.log('Save button not found');
    // Try to find any save button
    const allButtons = await page.locator('input[type="submit"], button[type="submit"]').allTextContents();
    console.log('All submit buttons:', allButtons);
  }

  // Wait for save to complete
  await page.waitForTimeout(2000);

  // Verify on frontend
  await page.goto('http://dev.site07.loc/advertising/context/google-ads/');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'automation/screenshots/current/frontend-final-check.png', fullPage: true });

  const content = await page.content();
  
  // Look for our widget
  if (content.includes('widget_page_siblings') || content.includes('Page Siblings')) {
    console.log('✓ SUCCESS: Page Siblings widget is now on the page!');
  } else {
    console.log('✗ Widget not visible yet - may need manual save');
    // Check if there's a sidebar with nav widgets
    const navWidget = page.locator('.widget_nav_menu');
    if (await navWidget.count() > 0) {
      console.log('Nav Menu widget found - our widget should appear near it');
    }
  }
});
