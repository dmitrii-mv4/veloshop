// @ts-check
const { test, expect } = require('@playwright/test');

test('add widget via AJAX API', async ({ page, request }) => {
  // Login and get cookies/auth
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.locator('#user_login').fill('konstantin.agafonov@gmail.com');
  await page.locator('#user_pass').fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  // Go to widgets page to get nonce
  await page.goto('http://dev.site07.loc/wp-admin/widgets.php');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);

  // Get all necessary data from page
  const widgetData = await page.evaluate(() => {
    // Find the page_siblings widget base ID
    const tpl = document.querySelector('#widget-13_page_siblings-__i__');
    if (!tpl) return { error: 'Template not found' };

    // Get nonce from scripts
    const scripts = document.querySelectorAll('script');
    let nonce = null;
    for (let script of scripts) {
      const text = script.textContent || '';
      if (text.includes('nonce')) {
        const match = text.match(/["']nonce["']\s*:\s*["']([^"']+)["']/);
        if (match) {
          nonce = match[1];
          break;
        }
      }
    }

    // Get current sidebar widgets configuration
    const firstSidebar = document.querySelector('#widgets-right .widgets-holder-wrap');
    const sidebarId = 'avrix-blog-sidebar';
    
    // Get current widget IDs in sidebar
    const currentWidgets = Array.from(firstSidebar.querySelectorAll('.widget:not([class*="ui-draggable"])'))
      .map(w => w.id)
      .filter(id => id);

    return {
      nonce,
      sidebarId,
      currentWidgets,
      pageSiblingsIdBase: 'page_siblings'
    };
  });

  console.log('Widget data:', widgetData);

  // Use REST API to update sidebar widgets
  // First, get current widget order
  const sidebarWidgetsResponse = await request.get('http://dev.site07.loc/wp-json/wp/v2/widget-types');
  console.log('Widget types status:', sidebarWidgetsResponse.status());

  // Try to use the widgets admin-ajax.php
  const newWidgetId = 'page_siblings-' + Date.now();
  
  const ajaxResult = await page.evaluate(async (data) => {
    return new Promise((resolve) => {
      // Prepare form data
      const formData = new FormData();
      formData.append('action', 'save-widget');
      formData.append('id_base', data.pageSiblingsIdBase);
      formData.append('sidebar', data.sidebarId);
      formData.append('nonce', data.nonce || '');
      formData.append('add_new', '1');

      fetch(ajaxurl, {
        method: 'POST',
        body: formData,
        credentials: 'include'
      })
      .then(response => response.json())
      .then(result => {
        resolve({ success: true, result });
      })
      .catch(error => {
        resolve({ success: false, error: error.message });
      });
    });
  }, widgetData);

  console.log('AJAX result:', ajaxResult);
  await page.waitForTimeout(2000);

  // Check if widget was added
  await page.reload();
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'automation/screenshots/current/after-ajax-add.png', fullPage: true });

  const firstSidebar = page.locator('#widgets-right .widgets-holder-wrap').first();
  const psWidget = firstSidebar.locator('[id*="page_siblings"]');
  console.log('Page Siblings in sidebar:', await psWidget.count());

  // Check frontend
  await page.goto('http://dev.site07.loc/advertising/context/google-ads/');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'automation/screenshots/current/frontend-after-ajax.png', fullPage: true });

  const content = await page.content();
  console.log('Widget on frontend:', content.includes('widget_page_siblings'));
});
