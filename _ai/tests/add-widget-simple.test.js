// @ts-click
const { test, expect } = require('@playwright/test');

test('simple widget addition', async ({ page }) => {
  // Login
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.locator('#user_login').fill('konstantin.agafonov@gmail.com');
  await page.locator('#user_pass').fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  await page.goto('http://dev.site07.loc/wp-admin/widgets.php');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);

  // Find and click Page Siblings widget
  const pageSiblingsTpl = page.locator('#widgets-left .widget').filter({ hasText: 'Page Siblings' }).first();
  await expect(pageSiblingsTpl).toBeVisible();
  
  console.log('Clicking Page Siblings widget...');
  await pageSiblingsTpl.click();
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'automation/screenshots/current/after-click-tpl.png', fullPage: true });

  // Check if widget appeared in first sidebar
  const firstSidebar = page.locator('#widgets-right .widgets-holder-wrap').first();
  const widgetsInSidebar = firstSidebar.locator('.widget:not([class*="ui-draggable"])');
  const count = await widgetsInSidebar.count();
  console.log('Widgets in sidebar after click:', count);

  // Look for Page Siblings specifically  
  const pageSiblingsInSidebar = firstSidebar.locator('[id*="page_siblings"]');
  if (await pageSiblingsInSidebar.count() > 0) {
    console.log('✓ Found Page Siblings in sidebar!');
    const widgetId = await pageSiblingsInSidebar.first().getAttribute('id');
    console.log('Widget ID:', widgetId);
  } else {
    console.log('✗ Page Siblings not in sidebar yet');
  }

  await page.screenshot({ path: 'automation/screenshots/current/sidebar-state.png', fullPage: true });

  // Look for Save button
  const saveBtn = page.locator('input[value*="Сохранить"], input[value*="Save"], button[value*="Save"], #savewidget');
  if (await saveBtn.count() > 0) {
    await saveBtn.first().click();
    console.log('✓ Clicked save');
    await page.waitForTimeout(3000);
  } else {
    // Try finding by partial text
    const submitBtn = page.locator('input[type="submit"]').first();
    if (await submitBtn.count() > 0) {
      await submitBtn.click();
      console.log('✓ Clicked submit');
      await page.waitForTimeout(3000);
    }
  }

  await page.screenshot({ path: 'automation/screenshots/current/after-save-attempt.png', fullPage: true });

  // Check frontend
  await page.goto('http://dev.site07.loc/advertising/context/google-ads/');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'automation/screenshots/current/frontend-check-widget.png', fullPage: true });

  const content = await page.content();
  console.log('widget_page_siblings in content:', content.includes('widget_page_siblings'));
});
