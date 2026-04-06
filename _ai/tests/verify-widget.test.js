// @ts-check
const { test, expect } = require('@playwright/test');

test('verify widget on frontend and compare with Nav Menu Widget', async ({ page }) => {
  // Login
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.locator('#user_login').fill('konstantin.agafonov@gmail.com');
  await page.locator('#user_pass').fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  // Visit the target page
  await page.goto('http://dev.site07.loc/advertising/context/google-ads/');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);

  // Take full page screenshot
  await page.screenshot({ path: 'automation/screenshots/current/widget-verification.png', fullPage: true });

  const content = await page.content();
  
  // Check for our widget
  if (content.includes('widget_page_siblings')) {
    console.log('✓✓✓ SUCCESS! Page Siblings widget is now on the frontend!');
    
    // Get widget HTML for comparison
    const pageSiblingsWidget = page.locator('.widget_page_siblings').first();
    if (await pageSiblingsWidget.count() > 0) {
      const widgetHTML = await pageSiblingsWidget.innerHTML();
      console.log('Page Siblings Widget HTML:');
      console.log(widgetHTML);
      
      // Get the widget title
      const title = await pageSiblingsWidget.locator('.widget-title').first().textContent();
      console.log('Widget title:', title);
      
      // Get all links in the widget
      const links = await pageSiblingsWidget.locator('a').allTextContents();
      console.log('Sibling page links:', links);
    }
    
    // Compare with Nav Menu Widget
    const navMenuWidget = page.locator('.widget_nav_menu').first();
    if (await navMenuWidget.count() > 0) {
      const navHTML = await navMenuWidget.innerHTML();
      console.log('\nNav Menu Widget HTML:');
      console.log(navHTML);
    }
    
    // Take comparison screenshots
    await page.screenshot({ 
      path: 'automation/screenshots/current/widget-comparison.png',
      fullPage: true
    });
    
    console.log('\n✓ Both widgets are now on the page for visual comparison!');
    console.log('Nav Menu Widget: "Другие услуги"');
    console.log('Page Siblings Widget: Should show sibling pages');
  } else {
    console.log('✗ Widget NOT found on frontend');
    console.log('Checking what widgets are present...');
    
    const widgets = page.locator('.widget');
    const count = await widgets.count();
    console.log('Total widgets found:', count);
    
    for (let i = 0; i < count; i++) {
      const widgetClass = await widgets.nth(i).getAttribute('class');
      const title = await widgets.nth(i).locator('.widget-title').first().textContent().catch(() => 'No title');
      console.log(`Widget ${i}: ${widgetClass} - ${title}`);
    }
  }
});
