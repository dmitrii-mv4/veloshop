// @ts-check
const { test, expect } = require('@playwright/test');

test('find correct sidebar ID', async ({ page }) => {
  // Login
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.locator('#user_login').fill('konstantin.agafonov@gmail.com');
  await page.locator('#user_pass').fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  await page.goto('http://dev.site07.loc/wp-admin/widgets.php');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);

  // Get all sidebar IDs and structure
  const sidebarsInfo = await page.evaluate(() => {
    const sidebars = [];
    const sidebarElements = document.querySelectorAll('#widgets-right .sidebar, #widgets-right .widgets-holder-wrap');
    
    for (let el of sidebarElements) {
      const id = el.id;
      const className = el.className;
      const title = el.querySelector('h2, h3, .sidebar-name, .widget-title')?.textContent?.trim();
      const hasWidgets = el.querySelectorAll('.widget:not(.ui-draggable)').length;
      
      sidebars.push({ id, className, title, hasWidgets });
    }
    
    return sidebars;
  });

  console.log('Sidebars info:');
  sidebarsInfo.forEach((sb, i) => {
    console.log(`${i}. ID: ${sb.id}, Title: ${sb.title}, Widgets: ${sb.hasWidgets}`);
  });

  await page.screenshot({ path: 'automation/screenshots/current/sidebars-structure.png', fullPage: true });
});
