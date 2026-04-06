// @ts-check
const { test, expect } = require('@playwright/test');

test('add widget with force clicks', async ({ page }) => {
  // Login
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.locator('#user_login').fill('konstantin.agafonov@gmail.com');
  await page.locator('#user_pass').fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  await page.goto('http://dev.site07.loc/wp-admin/widgets.php');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);

  // Click Page Siblings with force option
  const pageSiblingsTpl = page.locator('#widgets-left .widget').filter({ hasText: 'Page Siblings' }).first();
  await pageSiblingsTpl.click({ force: true });
  console.log('✓ Clicked Page Siblings template');
  await page.waitForTimeout(1500);
  await page.screenshot({ path: 'automation/screenshots/current/clicked-tpl.png', fullPage: true });

  // Check sidebar again
  const firstSidebar = page.locator('#widgets-right .widgets-holder-wrap').first();
  const psWidget = firstSidebar.locator('[id*="page_siblings"]');
  if (await psWidget.count() > 0) {
    console.log('✓ Widget appeared in sidebar!');
  } else {
    console.log('✗ Widget not in sidebar - trying alternative');
    
    // Maybe WordPress uses a different mechanism - let's try double-clicking
    await pageSiblingsTpl.dblclick({ force: true });
    await page.waitForTimeout(1500);
    await page.screenshot({ path: 'automation/screenshots/current/double-clicked.png', fullPage: true });
  }

  // Try the save button with force click
  const saveBtn = page.locator('#widget-avrix_aboutus_custom_widget-__i__-savewidget');
  if (await saveBtn.count() > 0) {
    await saveBtn.click({ force: true });
    console.log('✓ Clicked save button with force');
    await page.waitForTimeout(3000);
    await page.screenshot({ path: 'automation/screenshots/current/after-force-save.png', fullPage: true });
  }

  // Check frontend
  await page.goto('http://dev.site07.loc/advertising/context/google-ads/');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'automation/screenshots/current/frontend-after-force.png', fullPage: true });

  const content = await page.content();
  console.log('Has widget:', content.includes('widget_page_siblings'));
});
