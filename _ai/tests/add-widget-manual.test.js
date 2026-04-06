// @ts-check
const { test, expect } = require('@playwright/test');

test('add widget using WordPress JS API', async ({ page }) => {
  // Login
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.locator('#user_login').fill('konstantin.agafonov@gmail.com');
  await page.locator('#user_pass').fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  await page.goto('http://dev.site07.loc/wp-admin/widgets.php');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(3000);

  // Examine the widgets page structure more carefully
  const pageStructure = await page.evaluate(() => {
    const widgetsLeft = document.querySelector('#widgets-left');
    const widgetsRight = document.querySelector('#widgets-right');
    
    // Get first sidebar structure
    const firstSidebar = widgetsRight?.querySelector('.widgets-holder-wrap');
    const sidebarName = firstSidebar?.querySelector('.sidebar-name')?.textContent?.trim();
    const addBtn = firstSidebar?.querySelector('.add-widget, button.add');
    
    return {
      hasWidgetsLeft: !!widgetsLeft,
      hasWidgetsRight: !!widgetsRight,
      sidebarName,
      hasAddButton: !!addBtn,
      addButtonHTML: addBtn?.outerHTML?.substring(0, 200),
      widgetCount: firstSidebar?.querySelectorAll('.widget:not(.ui-draggable)').length || 0
    };
  });

  console.log('Page structure:', pageStructure);

  // Try clicking on the widget with wait and check
  const pageSiblingsTpl = page.locator('#widgets-left .widget').filter({ hasText: 'Page Siblings' }).first();
  
  // Get widget position
  const tplBoundingBox = await pageSiblingsTpl.boundingBox();
  console.log('Widget bounding box:', tplBoundingBox);

  // Click using mouse position
  if (tplBoundingBox) {
    await page.mouse.click(
      tplBoundingBox.x + tplBoundingBox.width / 2,
      tplBoundingBox.y + tplBoundingBox.height / 2
    );
    console.log('✓ Clicked widget at position:', tplBoundingBox.x, tplBoundingBox.y);
    await page.waitForTimeout(2000);
    await page.screenshot({ path: 'automation/screenshots/current/mouse-click.png', fullPage: true });
  }

  // Check if widget appeared
  const firstSidebar = page.locator('#widgets-right .widgets-holder-wrap').first();
  const psWidget = firstSidebar.locator('[id*="page_siblings"], [class*="page_siblings"]');
  const widgetCount = await firstSidebar.locator('.widget:not([class*="ui-draggable"])').count();
  
  console.log('Widget count after click:', widgetCount);
  console.log('Page Siblings found:', await psWidget.count() > 0);

  await page.screenshot({ path: 'automation/screenshots/current/after-mouse-click.png', fullPage: true });
  
  // If still not added, let's try to manually trigger the WordPress widget addition
  if (await psWidget.count() === 0) {
    console.log('Trying manual DOM addition...');
    await page.evaluate(() => {
      const tpl = document.querySelector('#widgets-left #widget-13_page_siblings-__i__');
      if (!tpl) return false;
      
      const firstSidebar = document.querySelector('#widgets-right .widgets-holder-wrap');
      if (!firstSidebar) return false;
      
      const container = firstSidebar.querySelector('.inside, .widget-area') || firstSidebar;
      
      // Clone and modify the widget
      const clone = tpl.cloneNode(true);
      const newId = 'widget-' + (Date.now()) + '_page_siblings-2';
      clone.id = newId;
      clone.className = 'widget';
      clone.style.display = 'block';
      
      // Update all IDs inside
      const inputs = clone.querySelectorAll('[id]');
      inputs.forEach(input => {
        input.id = input.id.replace('__i__', '2');
      });
      
      container.appendChild(clone);
      return true;
    });
    
    await page.waitForTimeout(1000);
    await page.screenshot({ path: 'automation/screenshots/current/manual-dom-addition.png', fullPage: true });
  }

  // Find and click the real save button for the sidebar
  const saveBtn = firstSidebar.locator('.sidebar-name button.handlediv').first();
  if (await saveBtn.count() > 0) {
    // This toggles the sidebar - not what we want
    // Look for actual save/submit
  }

  // Submit the form
  await page.evaluate(() => {
    const form = document.querySelector('#widgets-right form, #widgets-left form, form[name="widgets"]');
    if (form) {
      console.log('Submitting form...');
      form.submit();
      return true;
    }
    return false;
  });

  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'automation/screenshots/current/after-form-submit.png', fullPage: true });

  // Check frontend
  await page.goto('http://dev.site07.loc/advertising/context/google-ads/');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'automation/screenshots/current/frontend-after-manual.png', fullPage: true });

  const content = await page.content();
  console.log('Widget on frontend:', content.includes('widget_page_siblings'));
});
