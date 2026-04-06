// @ts-check
const { test, expect } = require('@playwright/test');

test('add Page Siblings widget to Blog Sidebar', async ({ page }) => {
  // Login
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.locator('#user_login').fill('konstantin.agafonov@gmail.com');
  await page.locator('#user_pass').fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  // Navigate to customizer
  await page.goto('http://dev.site07.loc/wp-admin/customize.php?url=' + encodeURIComponent('http://dev.site07.loc/advertising/context/google-ads/'));
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(3000);

  // Use JavaScript to expand the Blog Sidebar section
  await page.evaluate(() => {
    const section = document.querySelector('#accordion-section-sidebar-widgets-avrix-blog-sidebar');
    if (section) {
      section.classList.add('open', 'control-section');
      section.setAttribute('aria-expanded', 'true');
      const content = document.querySelector('#sub-accordion-section-sidebar-widgets-avrix-blog-sidebar');
      if (content) {
        content.style.display = 'block';
      }
    }
  });
  await page.waitForTimeout(1000);
  await page.screenshot({ path: 'automation/screenshots/current/customizer-blog-sidebar-expanded.png', fullPage: true });

  // Now click the "Add a widget" button
  const addWidgetButton = page.locator('#sub-accordion-section-sidebar-widgets-avrix-blog-sidebar .add-new-widget');
  await addWidgetButton.click({ force: true });
  await page.waitForTimeout(1500);
  await page.screenshot({ path: 'automation/screenshots/current/customizer-widget-modal.png', fullPage: true });

  // Wait for widget modal to appear
  const widgetModal = page.locator('.add-new-widget-modal');
  await expect(widgetModal.first()).toBeVisible();

  // Search for "Page Siblings"
  const searchInput = page.locator('.add-new-widget-modal input.search');
  await searchInput.fill('Page Siblings');
  await page.waitForTimeout(1500);
  await page.screenshot({ path: 'automation/screenshots/current/customizer-search-results.png', fullPage: true });

  // Click on Page Siblings widget
  const pageSiblingsItem = page.locator('.add-new-widget-modal .widget button').filter({ hasText: 'Page Siblings' }).first();
  
  if (await pageSiblingsItem.count() > 0) {
    await pageSiblingsItem.click();
    await page.waitForTimeout(2000);
    await page.screenshot({ path: 'automation/screenshots/current/customizer-widget-form.png', fullPage: true });
    
    // Don't set title - leave it blank for auto-title
    // Look for title input and clear it if exists
    const titleInput = page.locator('.add-new-widget-modal input[name*="title"], .widget-inside input[name*="title"]');
    if (await titleInput.count() > 0) {
      await titleInput.first().fill('');
      console.log('Title cleared for auto-title');
    }

    // Click Publish button
    const publishButton = page.locator('#save');
    await publishButton.click({ force: true });
    await page.waitForTimeout(3000);
    await page.screenshot({ path: 'automation/screenshots/current/customizer-published.png', fullPage: true });

    console.log('✓ Widget added and published!');
    
    // Verify by visiting the page
    await page.goto('http://dev.site07.loc/advertising/context/google-ads/');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    await page.screenshot({ path: 'automation/screenshots/current/final-page.png', fullPage: true });
    
    // Check for Page Siblings widget
    const pageContent = await page.content();
    if (pageContent.includes('widget_page_siblings') || pageContent.includes('Page Siblings')) {
      console.log('✓ Page Siblings widget found on frontend!');
    } else {
      console.log('✗ Widget not found on frontend - checking sidebar content...');
      const sidebarContent = page.locator('.sidebar, .widget-area');
      if (await sidebarContent.count() > 0) {
        const text = await sidebarContent.first().textContent();
        console.log('Sidebar content (first 1000 chars):', text?.substring(0, 1000));
      }
    }
  } else {
    console.log('✗ Page Siblings widget not found in widget list');
    // List available widgets for debugging
    const widgetButtons = page.locator('.add-new-widget-modal .widget button');
    const count = await widgetButtons.count();
    console.log('Total widget buttons found:', count);
    if (count > 0) {
      const firstFew = await widgetButtons.allTextContents();
      console.log('First 10 widgets:', firstFew.slice(0, 10));
    }
  }
});
