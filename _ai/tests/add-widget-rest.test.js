// @ts-check
const { test, expect } = require('@playwright/test');

test('add Page Siblings widget via REST API', async ({ page, request }) => {
  // Login
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.locator('#user_login').fill('konstantin.agafonov@gmail.com');
  await page.locator('#user_pass').fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  // Get current sidebar widgets configuration
  await page.goto('http://dev.site07.loc/wp-admin/widgets.php');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);

  // Get nonce from page
  const nonce = await page.evaluate(() => {
    // Try to find nonce in various ways
    const scripts = document.querySelectorAll('script');
    for (let script of scripts) {
      const text = script.textContent;
      if (text && text.includes('nonce')) {
        const match = text.match(/["']nonce["']\s*:\s*["']([^"']+)["']/);
        if (match) return match[1];
      }
    }
    return null;
  });

  console.log('Found nonce:', nonce);

  // Get current sidebar widgets
  const sidebarWidgets = await page.evaluate(() => {
    const sidebar = document.querySelector('#sidebar-avrix-blog-sidebar');
    if (sidebar) {
      return sidebar.innerHTML;
    }
    return 'Sidebar not found';
  });
  console.log('Current sidebar widgets:', sidebarWidgets.substring(0, 500));

  // Add widget via JavaScript simulation of WordPress widget addition
  const widgetAdded = await page.evaluate(async () => {
    return new Promise((resolve) => {
      // Find the Page Siblings widget in available widgets
      const availableWidgets = document.querySelectorAll('#widgets-left .widget');
      let pageSiblingsWidget = null;
      
      for (let widget of availableWidgets) {
        if (widget.textContent.includes('Page Siblings')) {
          pageSiblingsWidget = widget;
          break;
        }
      }

      if (!pageSiblingsWidget) {
        resolve({ success: false, error: 'Widget not found' });
        return;
      }

      // Get widget ID base
      const widgetIdBase = pageSiblingsWidget.getAttribute('id');
      console.log('Widget ID base:', widgetIdBase);

      // Find the Blog Sidebar
      const blogSidebar = document.querySelector('#sidebar-avrix-blog-sidebar');
      if (!blogSidebar) {
        resolve({ success: false, error: 'Blog Sidebar not found' });
        return;
      }

      // Click on the widget to add it (simulate WordPress widget click-to-add)
      pageSiblingsWidget.click();
      
      setTimeout(() => {
        const widgetAdded = blogSidebar.querySelector('.widget_page_siblings, [id*="page_siblings"]');
        resolve({ 
          success: !!widgetAdded,
          widgetHTML: widgetAdded ? widgetAdded.innerHTML.substring(0, 200) : 'Not added'
        });
      }, 2000);
    });
  });

  console.log('Widget added result:', widgetAdded);
  await page.screenshot({ path: 'automation/screenshots/current/after-widget-click.png', fullPage: true });

  // Check if widget was added to sidebar
  const updatedSidebar = await page.evaluate(() => {
    const sidebar = document.querySelector('#sidebar-avrix-blog-sidebar');
    return sidebar ? sidebar.innerHTML : 'Not found';
  });
  console.log('Updated sidebar:', updatedSidebar.substring(0, 500));

  // Save/Publish
  const saveButton = page.locator('#savewidget');
  if (await saveButton.count() > 0) {
    await saveButton.click();
    await page.waitForTimeout(2000);
    console.log('Widgets saved!');
  }

  // Verify on frontend
  await page.goto('http://dev.site07.loc/advertising/context/google-ads/');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'automation/screenshots/current/frontend-verify.png', fullPage: true });

  const pageContent = await page.content();
  const hasWidget = pageContent.includes('widget_page_siblings') || 
                    (pageContent.includes('Google Ads') && pageContent.includes('siblings'));
  console.log('Widget found on frontend:', hasWidget);
});
