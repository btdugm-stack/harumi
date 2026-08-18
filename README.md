# Harumi Digital Ordering — Cloudflare Workers

Website pemasaran dan pemesanan Harumi. Versi Cloudflare Workers: halaman di-render di edge, aset statis disajikan langsung, dan pesanan disimpan di Workers KV. Tidak ada server, tidak ada PHP, tidak ada database tradisional.

## Struktur proyek

```text
harumi/
├── src/index.js        Entry Worker: routing / , /api/orders, fallback aset
├── src/template.js     Render halaman utama (padanan index.php)
├── src/orders.js       Endpoint pesanan + validasi harga server-side (padanan api/orders.php)
├── src/menu.js         Master menu dan harga (57 item)
├── src/config.js       Konfigurasi merchant (WhatsApp, ongkir, jam, alamat)
├── public/assets/      Aset statis: css, js (frontend), img
├── package.json        Script: npm run dev / npm run deploy
└── wrangler.toml       Konfigurasi Worker + binding KV
```

## Menjalankan lokal

```bash
npm install
npm run dev        # http://127.0.0.1:8787 — KV diemulasikan lokal
```

## Deploy ke Cloudflare

1. `npx wrangler login` (browser → pilih akun → Allow).
2. Buat KV namespace: `npx wrangler kv namespace create ORDERS`.
3. Tempel `id` hasil perintah itu ke `wrangler.toml` (ganti `REPLACE_WITH_KV_NAMESPACE_ID`).
4. `npm run deploy` → URL: `https://harumi.<subdomain-kamu>.workers.dev`.

Detail lengkap di [DEPLOY.md](DEPLOY.md).

## Konfigurasi

- WhatsApp merchant, ongkos delivery, jam operasional, alamat → `src/config.js`.
- Menu dan harga → `src/menu.js` (harga dihitung ulang di edge; nilai dari browser tidak dipercaya).

## Penyimpanan pesanan

Pesanan disimpan ke Workers KV dengan key `order:<kode>` (mis. `order:HRM-260818-D4FD`). KV tidak punya file system — karenanya storage/orders.json versi PHP digantikan KV.

## Catatan migrasi dari versi Laragon (PHP)

- `index.php` → `src/template.js` (render server-side di edge).
- `api/orders.php` → `src/orders.js` (validasi + simpan ke KV).
- `data/menu.php` → `src/menu.js` (di-generate dari data PHP asli).
- `config/app.php` → `src/config.js`.
- Frontend (`public/assets/js/app.js`) tidak berubah kecuali URL fetch: `api/orders.php` → `/api/orders`.
- Bug `$notes` tanpa kategori Dessert (versi PHP) sudah diperbaiki di port ini.
- Riwayat versi PHP tetap ada di git history (commit `237e571`).
