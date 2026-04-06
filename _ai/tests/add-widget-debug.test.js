// @ts-check
const { test, expect } = require('@playwright/test');

test('debug and add widget step by step', async ({ page }) => {
  // Login
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.locator('#user_login').fill('konstantin.agafonov@gmail.com');
  await page.locator('#user_pass').fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  await page.goto('http://dev.site07.loc/wp-admin/widgets.php');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'automation/screenshots/current/step1-widgets-page.png', fullPage: true });

  // Find Page Siblings widget template and log its details
  const tplInfo = await page.evaluate(() => {
    const widgets = Array.from(document.querySelectorAll('#widgets-left .widget'));
    const pageSiblings = widgets.find(w => w.textContent.includes('Page Siblings'));
    
    if (!pageSiblings) return { found: false };
    
    return {
      found: true,
      id: pageSiblings.id,
      className: pageSiblings.className,
      innerHTML: pageSiblings.innerHTML.substring(0, 300),
      dataId: pageSiblings.getAttribute('data-id'),
      allAttrs: Array.from(pageSiblings.attributes).map(a => `${a.name}=${a.value}`)
    };
  });

  console.log('Page Siblings template:', tplInfo);

  // Find the first sidebar in right column
  const sidebarInfo = await page.evaluate(() => {
    const sidebars = document.querySelectorAll('#widgets-right .sidebar, #widgets-right .widgets-holder-wrap');
    const first = sidebars[0];
    if (!first) return { found: false };
    
    const container = first.querySelector('.inside, .widget-area') || first;
    const sortableId = container.id;
    
    return {
      found: true,
      containerId: sortableId,
      containerClass: container.className,
      currentWidgets: container.querySelectorAll('.widget').length
    };
  });

  console.log('Target sidebar:', sidebarInfo);

  // Try using the WordPress widget addition via form submission
  // WordPress widgets use a hidden form - let's try to use that
  await page.waitForTimeout(1000);

  // Get nonce for AJAX requests
  const nonce = await page.evaluate(() => {
    const wpSettings = document.querySelector('#wp-widgets-0-js-extra');
    if (wpSettings) {
      const text = wpSettings.textContent;
      const match = text.match(/widgetsNonce["']\s*:\s*["']([^"']+)["']/);
      return match ? match[1] : null;
    }
    return null;
  });

  console.log('Nonce:', nonce);

  // Let's try clicking on the widget template with proper event simulation
  const clickResult = await page.evaluate(() => {
    return new Promise((resolve) => {
      const tpl = document.querySelector('#widgets-left .widget');
      // Find Page Siblings
      const allWidgets = document.querySelectorAll('#widgets-left .widget');
      const pageSiblings = Array.from(allWidgets).find(w => w.textContent.includes('Page Siblings'));
      
      if (!pageSiblings) {
        resolve({ success: false, error: 'Not found' });
        return;
      }

      // Create and dispatch click event
      const clickEvent = new MouseEvent('click', {
        bubbles: true,
        cancelable: true,
        view: window
      });
      
      pageSiblings.dispatchEvent(clickEvent);
      
      setTimeout(() => {
        const sidebars = document.querySelectorAll('#widgets-right .sidebar');
        const firstSidebar = sidebars[0];
        const container = firstSidebar.querySelector('.inside') || firstSidebar;
        const newWidget = container.querySelector('[id*="page_siblings"], .widget:last-child');
        
        resolve({
          success: !!newWidget,
          widgetId: newWidget?.id || 'none',
          widgetCount: container.querySelectorAll('.widget').length
        });
      }, 2000);
    });
  });

  console.log('Click result:', clickResult);
  await page.screenshot({ path: 'automation/screenshots/current/step2-after-click.png', fullPage: true });

  // Wait then try to save
  await page.waitForTimeout(1500);
  await page.screenshot({ path: 'automation/screenshots/current/step3-before-save.png', fullPage: true });

  // Look for save button more carefully
  const saveButton = await page.locator('input[id*="save"], button[id*="save"]').first();
  if (await saveButton.count() > 0) {
    await saveButton.click();
    console.log('✓ Clicked save button');
    await page.waitForTimeout(3000);
  }

  await page.screenshot({ path: 'automation/screenshots/current/step4-after-save.png', fullPage: true });

  // Check frontend
  await page.goto('http://dev.site07.loc/advertising/context/google-ads/');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'automation/screenshots/current/step5-frontend.png', fullPage: true });

  const content = await page.content();
  console.log('Has widget_page_siblings:', content.includes('widget_page_siblings'));
});
