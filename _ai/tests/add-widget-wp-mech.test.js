// @ts-check
const { test, expect } = require('@playwright/test');

test('add widget via WordPress mechanism', async ({ page }) => {
  // Login
  await page.goto('http://dev.site07.loc/wp-login.php');
  await page.locator('#user_login').fill('konstantin.agafonov@gmail.com');
  await page.locator('#user_pass').fill('q3AF52*!bV3N5@yvSu(LbKia');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  await page.goto('http://dev.site07.loc/wp-admin/widgets.php');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(3000);
  await page.screenshot({ path: 'automation/screenshots/current/widgets-page-ready.png', fullPage: true });

  // Use WordPress's own widget addition mechanism
  const result = await page.evaluate(() => {
    return new Promise((resolve) => {
      try {
        // Find the Page Siblings widget template
        const allWidgets = document.querySelectorAll('#widgets-left .widget, .widget-tpl');
        let pageSiblingsTpl = null;
        
        for (let widget of allWidgets) {
          const text = widget.textContent || '';
          if (text.includes('Page Siblings')) {
            pageSiblingsTpl = widget;
            break;
          }
        }

        if (!pageSiblingsTpl) {
          resolve({ success: false, error: 'Template not found' });
          return;
        }

        // Get the ID of the widget template
        const tplId = pageSiblingsTpl.id;
        console.log('Template ID:', tplId);

        // Find the Blog Sidebar - first sidebar in right column
        const sidebars = document.querySelectorAll('#widgets-right .sidebar, #widgets-right .widgets-holder-wrap');
        const blogSidebar = sidebars[0];
        
        if (!blogSidebar) {
          resolve({ success: false, error: 'Blog Sidebar not found' });
          return;
        }

        // Find the widgets container inside the sidebar
        const widgetsContainer = blogSidebar.querySelector('.inside, .widget-area, .sidebar-widgets');
        const targetContainer = widgetsContainer || blogSidebar;

        // Trigger WordPress's widget addition
        // WordPress uses jQuery UI sortable and widget addition
        if (typeof jQuery !== 'undefined') {
          // Simulate the widget addition
          const $tpl = jQuery(pageSiblingsTpl);
          const id_base = $tpl.attr('id') || 'page_siblings';
          
          // Add widget via WordPress's addWidget function if available
          if (typeof wpWidgets !== 'undefined' && wpWidgets.addWidget) {
            wpWidgets.addWidget(id_base.replace('tpl-', ''));
            resolve({ success: true, method: 'wpWidgets' });
          } else {
            // Try alternative - click on the widget
            $tpl.click();
            setTimeout(() => {
              const added = targetContainer.querySelector('[id*="page_siblings"]');
              resolve({ 
                success: !!added, 
                method: 'click',
                containerHTML: targetContainer.innerHTML.substring(0, 300)
              });
            }, 1500);
          }
        } else {
          resolve({ success: false, error: 'jQuery not found' });
        }
      } catch (e) {
        resolve({ success: false, error: e.message });
      }
    });
  });

  console.log('Result:', result);
  await page.screenshot({ path: 'automation/screenshots/current/after-add-attempt.png', fullPage: true });

  // Save
  const saveBtn = page.locator('#savewidget');
  if (await saveBtn.count() > 0) {
    await saveBtn.click();
    await page.waitForTimeout(2000);
  }

  // Check frontend
  await page.goto('http://dev.site07.loc/advertising/context/google-ads/');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'automation/screenshots/current/frontend-final.png', fullPage: true });
});
