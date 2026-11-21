import { test, expect } from '@playwright/test';

/** REQ-DEMO-013: the home page renders on the UiKit shell, worker-safe, without console errors. */
test.describe('PhpStanFrankenPhp demo', () => {
  test('home page renders the runtime and the PHPStan levels', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (message) => {
      if (message.type() === 'error') {
        errors.push(message.text());
      }
    });
    page.on('pageerror', (error) => errors.push(error.message));

    const response = await page.goto('/');
    expect(response?.status()).toBe(200);
    await expect(page.getByTestId('demo-platform')).toBeVisible();
    await expect(page.getByRole('heading', { level: 1 })).toContainText('PhpStanFrankenPhp');
    await expect(page.getByTestId('demo-mode')).not.toBeEmpty();
    await page.waitForLoadState('networkidle');
    expect(errors).toEqual([]);
  });

  test('the request counter is request-scoped: two requests in a row both start at 1', async ({ page }) => {
    for (let i = 0; i < 2; i++) {
      await page.goto('/');
      await expect(page.getByTestId('demo-hits')).toHaveText('1');
    }
  });
});
