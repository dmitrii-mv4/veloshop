// @ts-check
const { test, expect } = require('@playwright/test');

test('add Page Siblings widget below WP_Nav_Menu_Widget', async ({ page }) => {
  // Login to WP admin
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.getByLabel('Username or Email Address').fill('konstantin.agafonov@gmail.com');
  await page.getByLabel('Password', { exact: true }).fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.getByRole('button', { name: 'Log In' }).click();
  await page.waitForURL('**/wp-admin/**');

  // Check if already logged in (redirected)
  if (page.url().includes('wp-login')) {
    await expect(page).not.toHaveURL(/wp-login/);
  }

  // Navigate to the page editor (widgets editor or customizer)
  // First, let's check what sidebar the page uses by visiting it
  await page.goto('http://dev.site07.loc/advertising/context/google-ads/');
  await page.waitForLoadState('networkidle');

  // Navigate to widgets admin
  await page.goto('http://dev.site07.loc/wp-admin/widgets.php');
  await page.waitForLoadState('networkidle');

  // Check if we're in block widgets or classic widgets
  const isBlockEditor = await page.locator('.block-editor-widget-area').count() > 0;

  if (isBlockEditor) {
    console.log('Block widget editor detected');
    // For block editor, we need to find the sidebar used by this specific page
    // This is more complex - we may need to edit the page itself
  } else {
    console.log('Classic widget editor detected');
    // Classic widgets - find the sidebar that contains the WP_Nav_Menu_Widget
  }

  // Let's try editing the page directly to see which sidebar is used
  await page.goto('http://dev.site07.loc/wp-admin/post.php?post=');

  // First, find the page ID for google-ads
  await page.goto('http://dev.site07.loc/wp-admin/edit.php?post_type=page');
  await page.waitForLoadState('networkidle');

  // Find the Google Ads page and get its edit link
  const googleAdsLink = page.locator('a.row-title:has-text("Google Ads"), td.title a:has-text("Google Ads")').first();
  await expect(googleAdsLink).toBeVisible();
  const editUrl = await googleAdsLink.getAttribute('href');
  
  console.log('Edit URL:', editUrl);

  // Instead of editing page, let's use the customizer or check widgets
  // Navigate to Appearance > Widgets
  await page.goto('http://dev.site07.loc/wp-admin/widgets.php');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);

  // Take a screenshot to see what we're working with
  await page.screenshot({ path: 'automation/screenshots/current/widgets-before.png', fullPage: true });

  // Try to find sidebar that has nav menu widget
  // Look for any existing widget areas
  const widgetAreas = page.locator('.widgets-holder-wrap, .widget-area');
  const widgetAreasCount = await widgetAreas.count();
  console.log('Widget areas found:', widgetAreasCount);

  // Let's check all widget areas for the nav menu widget
  let targetSidebar = null;
  const sidebarSelector = '.widgets-holder-wrap .widget, .sidebar .widget';
  const widgets = page.locator(sidebarSelector);
  const widgetsCount = await widgets.count();
  
  for (let i = 0; i < widgetsCount; i++) {
    const widgetText = await widgets.nth(i).textContent();
    if (widgetText && widgetText.includes('Navigation Menu')) {
      console.log('Found Navigation Menu widget');
      // Get the sidebar name
      const sidebar = widgets.nth(i).locator('.widget-inside, .widget-content').first();
      targetSidebar = await widgets.nth(i).getAttribute('id');
      console.log('Target sidebar/widget:', targetSidebar);
      break;
    }
  }

  // Since WordPress 5.8+ uses block widgets, let's try a different approach
  // We'll edit the page and add the widget using the block editor
  
  // Go to the target page in frontend to check its structure
  await page.goto('http://dev.site07.loc/advertising/context/google-ads/');
  await page.waitForLoadState('networkidle');
  await page.screenshot({ path: 'automation/screenshots/current/page-before.png', fullPage: true });

  // Check if there's a sidebar with widgets
  const sidebarContent = await page.content();
  const hasNavMenuWidget = sidebarContent.includes('widget_nav_menu') || 
                           sidebarContent.includes('wp-block-navigation');
  console.log('Has nav menu widget on page:', hasNavMenuWidget);

  // Find the edit link in admin bar
  const editLink = page.locator('#wp-admin-bar-edit a, .edit-link a');
  if (await editLink.count() > 0) {
    await editLink.first().click();
    await page.waitForLoadState('networkidle');
  } else {
    // Navigate to page edit screen manually
    await page.goto('http://dev.site07.loc/wp-admin/edit.php?post_type=page');
    await page.waitForLoadState('networkidle');
    await page.locator('a.row-title:has-text("Google Ads")').first().click();
    await page.waitForLoadState('networkidle');
  }

  // Save current state
  await page.screenshot({ path: 'automation/screenshots/current/page-editor.png', fullPage: true });
});
