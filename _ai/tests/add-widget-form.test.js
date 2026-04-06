// @ts-check
const { test, expect } = require('playwright/test');

test('add widget via form manipulation', async ({ page }) => {
  // Login
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.locator('#user_login').fill('konstantin.agafonov@gmail.com');
  await page.locator('#user_pass').fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  await page.goto('http://dev.site07.loc/wp-admin/widgets.php');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);

  // Get the form and manually add widget data
  const formData = await page.evaluate(() => {
    const tpl = document.querySelector('#widget-13_page_siblings-__i__');
    if (!tpl) return { error: 'Template not found' };

    const firstSidebar = document.querySelector('#widgets-right .widgets-holder-wrap');
    const container = firstSidebar.querySelector('.inside') || firstSidebar;
    
    // Clone widget
    const clone = tpl.cloneNode(true);
    clone.id = 'widget-page_siblings-99';
    clone.className = 'widget';
    clone.removeAttribute('style');
    
    // Update all internal IDs
    const allElements = clone.querySelectorAll('*');
    allElements.forEach(el => {
      if (el.id) el.id = el.id.replace('__i__', '99');
      if (el.name) el.name = el.name.replace('__i__', '99');
    });
    
    container.appendChild(clone);
    
    // Get form data
    const form = document.querySelector('form');
    if (!form) return { error: 'Form not found' };
    
    const formData = new FormData(form);
    const data = {};
    for (let [key, value] of formData.entries()) {
      data[key] = value;
    }
    
    return {
      success: true,
      formData: data,
      action: form.action,
      method: form.method
    };
  });

  console.log('Form data prepared:', formData);
  await page.screenshot({ path: 'automation/screenshots/current/form-prepared.png', fullPage: true });

  // Submit the form
  const submitResult = await page.evaluate(() => {
    const form = document.querySelector('form');
    if (!form) return { error: 'Form not found' };
    
    form.submit();
    return { success: true };
  });

  console.log('Submit result:', submitResult);
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(3000);
  await page.screenshot({ path: 'automation/screenshots/current/after-form-submit2.png', fullPage: true });

  // Verify
  await page.goto('http://dev.site07.loc/advertising/context/google-ads/');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'automation/screenshots/current/frontend-after-form.png', fullPage: true });

  const content = await page.content();
  console.log('widget_page_siblings in content:', content.includes('widget_page_siblings'));
  
  if (content.includes('widget_page_siblings')) {
    console.log('✓✓ SUCCESS!');
  }
});
