# Deploy Harumi ke Cloudflare Workers

Panduan langkah demi langkah. Total ~5 menit, hanya perlu akun Cloudflare gratis.

## 1. Login wrangler

```bash
npx wrangler login
```

Browser terbuka → pilih akun Cloudflare → klik **Allow**. Selesai bila muncul "Successfully logged in".

## 2. Buat KV namespace untuk simpanan pesanan

```bash
npx wrangler kv namespace create ORDERS
```

Output-nya seperti ini:

```
🌀 Creating namespace with title "harumi-ORDERS"
✨ Success!
Add the following to your configuration file in your kv_namespaces array:
[[kv_namespaces]]
binding = "ORDERS"
id = "8f2d3a1b4c5d6e7f8a9b0c1d2e3f4a5b"
```

Salin `id` itu, lalu edit `wrangler.toml`:

```toml
[[kv_namespaces]]
binding = "ORDERS"
id = "8f2d3a1b4c5d6e7f8a9b0c1d2e3f4a5b"   # ← id asli dari perintah di atas
```

## 3. Deploy

```bash
npm run deploy
```

Selesai — URL yang muncul (biasanya `https://harumi.<nama-subdomain>.workers.dev`) bisa langsung dibuka siapa pun.

## 4. Verifikasi

```bash
curl -s https://harumi.<subdomain>.workers.dev/ | grep -c 'menu-card'   # → 57
curl -s -X POST https://harumi.<subdomain>.workers.dev/api/orders \
  -H 'Content-Type: application/json' \
  -d '{"customer":{"name":"Tes","phone":"0812"},"items":[{"id":"usucha","qty":1}]}'
# → {"success":true,"order":{...}}
```

## Jika build di dashboard gagal

Workers Builds (integrasi Git) mengharapkan proyek JS dengan `package.json` — repositori ini sudah punya. Pastikan:
- Repo yang terhubung adalah `btdugm-stack/harumi` branch `main`.
- KV namespace sudah dibuat (langkah 2) dan `id`-nya sudah ada di `wrangler.toml` — build akan gagal saat deploy jika binding mengarah ke id yang tidak ada.

## Update WhatsApp merchant

Edit `src/config.js` → `whatsappNumber`, commit, push. Build otomatis (kalau pakai Workers Builds) atau `npm run deploy` manual.

## Catatan produksi

- KV bersifat eventually-consistent: order yang baru ditulis mungkin butuh beberapa detik sebelum konsisten di seluruh edge. Untuk PoC tidak masalah.
- Belum ada admin panel; lihat pesanan via KV (dashboard Cloudflare → Workers → KV → namespace ORDERS) atau API `list`.
- Untuk rate limiting / CSRF token, tambahkan di `src/orders.js`.
