import { test, expect } from '@playwright/test';

test('landing page menampilkan judul sistem', async ({ page }) => {
  await page.goto('/');

  await expect(
    page.getByRole('heading', { name: /Sistem Registrasi Penomoran dan Arsip Surat/i }),
  ).toBeVisible();

  await expect(page.getByRole('link', { name: 'Masuk' })).toBeVisible();
});

test('tombol masuk mengarah ke halaman login', async ({ page }) => {
  await page.goto('/');

  await page.getByRole('link', { name: 'Masuk' }).click();

  await expect(page).toHaveURL(/\/login$/);
  await expect(page.getByRole('heading', { name: /Masuk ke akun Anda/i })).toBeVisible();
});
