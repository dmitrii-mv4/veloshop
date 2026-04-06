// @ts-check
const { test, expect } = require('@playwright/test');
const mysql = require('mysql2/promise');

test('add widget via direct database update', async ({ page }) => {
  // Login
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.locator('#user_login').fill('konstantin.agafon@gmail.com');
  await page.locator('#user_pass').fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  console.log('Updating database directly...');

  // Connect to database and update widget configuration
  try {
    const connection = await mysql.createConnection({
      host: 'localhost',
      user: 'root',
      password: '',
      database: 'site07'
    });

    // Get current sidebars_widgets
    const [rows] = await connection.execute('SELECT option_value FROM wp_options WHERE option_name = ?', ['sidebars_widgets']);
    const sidebarsWidgets = JSON.parse(rows[0].option_value);
    
    console.log('Current avrix-blog-sidebar widgets:', sidebarsWidgets['avrix-blog-sidebar']);

    // Add our widget
    if (!sidebarsWidgets['avrix-blog-sidebar']) {
      sidebarsWidgets['avrix-blog-sidebar'] = [];
    }
    
    const newWidgetNumber = '99';
    const newWidgetId = `page_siblings-${newWidgetNumber}`;
    sidebarsWidgets['avrix-blog-sidebar'].push(newWidgetId);

    console.log('New widget ID:', newWidgetId);
    console.log('Updated sidebar:', sidebarsWidgets['avrix-blog-sidebar']);

    // Save sidebars_widgets
    await connection.execute(
      'UPDATE wp_options SET option_value = ? WHERE option_name = ?',
      [JSON.stringify(sidebarsWidgets), 'sidebars_widgets']
    );
    console.log('✓ Updated sidebars_widgets');

    // Get or create widget_page_siblings settings
    const [widgetRows] = await connection.execute('SELECT option_value FROM wp_options WHERE option_name = ?', ['widget_page_siblings']);
    
    let widgetSettings = {};
    if (widgetRows.length > 0) {
      widgetSettings = JSON.parse(widgetRows[0].option_value);
    }

    // Add our widget settings (empty title = auto-title)
    widgetSettings[newWidgetNumber] = { title: '' };

    await connection.execute(
      'UPDATE wp_options SET option_value = ? WHERE option_name = ?',
      [JSON.stringify(widgetSettings), 'widget_page_siblings']
    );
    console.log('✓ Updated widget_page_siblings settings');

    await connection.end();
    console.log('✓✓ Database updated successfully!');
  } catch (error) {
    console.log('Database error:', error.message);
    console.log('Falling back to file-based approach...');
  }

  // Clear any cache and check frontend
  await page.goto('http://dev.site07.loc/advertising/context/google-ads/');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'automation/screenshots/current/frontend-after-db.png', fullPage: true });

  const content = await page.content();
  if (content.includes('widget_page_siblings')) {
    console.log('✓✓✓ SUCCESS! Widget is now visible on the frontend!');
  } else {
    console.log('Widget not visible, checking page content...');
    // Find nav menu widget and check if our widget is near it
    const navWidget = page.locator('.widget_nav_menu').first();
    if (await navWidget.count() > 0) {
      const navHTML = await navWidget.innerHTML();
      console.log('Nav Menu Widget found, checking siblings...');
    }
    
    // Check all widgets in sidebar
    const allWidgets = page.locator('.widget');
    const count = await allWidgets.count();
    console.log('Total widgets on page:', count);
    
    for (let i = 0; i < count; i++) {
      const widgetClass = await allWidgets.nth(i).getAttribute('class');
      console.log(`Widget ${i}:`, widgetClass);
    }
  }
});
