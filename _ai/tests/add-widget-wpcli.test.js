// @ts-check
const { test, expect } = require('@playwright/test');
const { execSync } = require('child_process');

test('add widget via WP-CLI', async ({ page }) => {
  // Login first to ensure we have proper session
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.locator('#user_login').fill('konstantin.agafonov@gmail.com');
  await page.locator('#user_pass').fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  console.log('Getting current sidebar widgets...');
  
  // Get current sidebar widgets
  const getCurrentWidgets = `cd /var/www/dev.site07.loc && wp option get sidebars_widgets --format=json 2>&1`;
  const currentWidgetsJson = execSync(getCurrentWidgets, { encoding: 'utf-8' });
  console.log('Current sidebars_widgets:', currentWidgetsJson);

  // Parse and add our widget
  const sidebarsWidgets = JSON.parse(currentWidgetsJson);
  console.log('Blog sidebar current widgets:', sidebarsWidgets['avrix-blog-sidebar']);

  // Add our widget to the array
  if (!sidebarsWidgets['avrix-blog-sidebar']) {
    sidebarsWidgets['avrix-blog-sidebar'] = [];
  }
  
  const newWidgetId = 'page_siblings-' + (Date.now() % 10000);
  sidebarsWidgets['avrix-blog-sidebar'].push(newWidgetId);
  
  console.log('New widget ID:', newWidgetId);
  console.log('Updated sidebar:', sidebarsWidgets['avrix-blog-sidebar']);

  // Save back
  const updatedJson = JSON.stringify(sidebarsWidgets);
  const saveCommand = `cd /var/www/dev.site07.loc && wp option set sidebars_widgets '${updatedJson}' --format=json 2>&1`;
  const saveResult = execSync(saveCommand, { encoding: 'utf-8' });
  console.log('Save result:', saveResult);

  // Now we also need to add the widget instance settings
  const widgetSettingsCommand = `cd /var/www/dev.site07.loc && wp option get widget_page_siblings --format=json 2>&1`;
  
  try {
    const currentSettingsJson = execSync(widgetSettingsCommand, { encoding: 'utf-8' });
    const widgetSettings = JSON.parse(currentSettingsJson);
    
    // Add new widget instance (empty settings = auto title)
    const widgetNumber = newWidgetId.split('-')[1];
    widgetSettings[widgetNumber] = { title: '' };
    
    const saveSettingsCommand = `cd /var/www/dev.site07.loc && wp option set widget_page_siblings '${JSON.stringify(widgetSettings)}' --format=json 2>&1`;
    const settingsResult = execSync(saveSettingsCommand, { encoding: 'utf-8' });
    console.log('Widget settings saved:', settingsResult);
  } catch (e) {
    console.log('No existing widget settings, creating new...');
    const widgetNumber = newWidgetId.split('-')[1];
    const newSettings = { [widgetNumber]: { title: '' } };
    const createSettingsCommand = `cd /var/www/dev.site07.loc && wp option set widget_page_siblings '${JSON.stringify(newSettings)}' --format=json 2>&1`;
    const result = execSync(createSettingsCommand, { encoding: 'utf-8' });
    console.log('Widget settings created:', result);
  }

  console.log('✓ Widget added via WP-CLI!');

  // Verify on frontend
  await page.goto('http://dev.site07.loc/advertising/context/google-ads/');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'automation/screenshots/current/frontend-after-wpcli.png', fullPage: true });

  const content = await page.content();
  if (content.includes('widget_page_siblings')) {
    console.log('✓✓ SUCCESS! Widget is now on the frontend!');
  } else {
    console.log('Widget not visible yet, checking sidebar HTML...');
    // Check if sidebar has our widget
    const sidebarHTML = await page.locator('aside, .sidebar, .widget-area').first().innerHTML();
    console.log('Sidebar HTML preview:', sidebarHTML.substring(0, 500));
  }
});
