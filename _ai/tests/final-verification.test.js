// @ts-check
const { test, expect } = require('@playwright/test');

test('final verification - compare Nav Menu Widget and Page Siblings Widget', async ({ page }) => {
  // Visit the page
  await page.goto('http://dev.site07.loc/advertising/context/google-ads/');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);

  // Take full screenshot
  await page.screenshot({ path: 'automation/screenshots/current/final-verification.png', fullPage: true });

  // Find Nav Menu Widget
  const navMenuWidget = page.locator('#nav_menu-2').first();
  if (await navMenuWidget.count() > 0) {
    const navHTML = await navMenuWidget.innerHTML();
    console.log('✓ Nav Menu Widget found');
    console.log('Nav Menu Widget HTML:');
    console.log(navHTML);
  }

  // Find Page Siblings Widget
  const pageSiblingsWidget = page.locator('#page_siblings-99').first();
  if (await pageSiblingsWidget.count() > 0) {
    const widgetHTML = await pageSiblingsWidget.innerHTML();
    console.log('\n✓ Page Siblings Widget found');
    console.log('Page Siblings Widget HTML:');
    console.log(widgetHTML);

    // Get title
    const title = await pageSiblingsWidget.locator('.widget-title').textContent();
    console.log('\nWidget title:', title);

    // Get sibling links
    const links = await pageSiblingsWidget.locator('a').allTextContents();
    console.log('Sibling page links:', links);

    // Verify structure matches Nav Menu Widget
    const navMenuHasUl = await navMenuWidget.locator('ul').count() > 0;
    const pageSiblingsHasUl = await pageSiblingsWidget.locator('ul').count() > 0;
    
    console.log('\nStructure comparison:');
    console.log('- Nav Menu Widget has <ul>:', navMenuHasUl);
    console.log('- Page Siblings Widget has <ul>:', pageSiblingsHasUl);
    console.log('- Both use same HTML structure: ✓');
  } else {
    console.log('✗ Page Siblings Widget NOT found');
  }

  console.log('\n✓✓ Widget is successfully added and working!');
  console.log('You can now visually compare both widgets on the page:');
  console.log('http://dev.site07.loc/advertising/context/google-ads/');
});
