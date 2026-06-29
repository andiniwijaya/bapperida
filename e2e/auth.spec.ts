import { test, expect } from '@playwright/test';
import { e2eCredentials } from './helpers';

test('pengguna dapat masuk ke dashboard', async ({ page }) => {
  await page.goto('/login');

  await page.locator('#login').fill(e2eCredentials.login);
  await page.locator('#password').fill(e2eCredentials.password);
  await page.getByTestId('login-button').click();

  await expect(page).toHaveURL(/\/dashboard$/);
});

test('login gagal menampilkan pesan error', async ({ page }) => {
  await page.goto('/login');

  await page.locator('#login').fill('pengguna-tidak-ada');
  await page.locator('#password').fill('salah-sandi');
  await page.getByTestId('login-button').click();

  await expect(page).toHaveURL(/\/login$/);
  await expect(page.locator('#login-error')).toBeVisible();
});
