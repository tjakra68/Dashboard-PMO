---
name: testing-dashboard-pmo
description: How to run and end-to-end test the DASHBOARD-PMO Laravel app (Breeze auth + Product CRUD) locally in a browser.
---

# Testing DASHBOARD-PMO locally

## Start the app
1. Start MySQL: `sudo service mysql start` (DB `dashboard_pmo`, user `pmo` / `pmo_password`, already in `.env`).
2. If DB is empty: `php artisan migrate --seed` (seeds 25 products + user `test@example.com`, password `password` — the Laravel factory default).
3. Assets: `npm run build` once (Node 22 via `source ~/.nvm/nvm.sh && nvm use 22`); no Vite dev server needed afterwards. If you see "Unable to locate file in Vite manifest", rerun `npm run build`.
4. Serve: `cd /home/ubuntu/repos/dashboard-pmo && php artisan serve` → http://127.0.0.1:8000.

## App map (UI is Indonesian)
- Auth: Breeze at `/register`, `/login`; user dropdown top-right → "Log Out".
- Products: nav link "Produk" → `/products` (auth required). "＋ Tambah Produk" → create form (fields: Nama Produk, Kategori, Harga (Rp), Stok, Deskripsi (opsional); buttons Simpan/Perbarui, Batal).
- Search form: text input `search`, category `<select name=category>` (options are DISTINCT categories from DB), button "Cari"; "Reset" link only renders when a filter is active.
- Delete ("Hapus") uses a native `confirm('Yakin ingin menghapus produk ini?')` dialog.
- Flash messages: "Produk berhasil ditambahkan." / "diperbarui." / "dihapus." in a green banner.
- List: 10/page, ordered `latest()` — newly created products appear as the FIRST row of page 1.

## Gotchas
- Form inputs have HTML `required` and `min=0`, so empty/negative submissions are blocked by browser-native tooltips ("Please fill out this field."), not server-side error text. To exercise server-side validation you'd need to strip the attributes or POST directly.
- When typing product names via computer-use, characters like `(` may be dropped by xdotool — verify the persisted value afterwards and re-edit if needed.

## Devin Secrets Needed
None — local MySQL credentials are in the repo's `.env`.
