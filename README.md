# Harumi Digital Ordering - Laragon Edition

Proof of concept website pemasaran dan pemesanan Harumi yang dibuat dengan PHP native, CSS, dan JavaScript. Tidak memerlukan Composer, npm, framework, atau database.

## Persyaratan

- Laragon dengan PHP 8.1 atau lebih baru
- Ekstensi PHP `mbstring` (aktif secara default pada Laragon)
- Koneksi internet untuk memuat foto produk representatif dari Unsplash

## Instalasi di Laragon

1. Salin folder `harumi-laragon` ke `C:\laragon\www\`.
2. Jalankan Laragon dan klik **Start All**.
3. Klik **Menu > www > harumi-laragon**, atau buka:
   - `http://harumi-laragon.test` jika Auto Virtual Hosts aktif; atau
   - `http://localhost/harumi-laragon`.
4. Aplikasi langsung dapat digunakan tanpa proses instalasi tambahan.

## Konfigurasi

Edit `config/app.php` untuk mengubah:

- nomor WhatsApp merchant;
- ongkos delivery;
- jam operasional;
- alamat outlet.

Data menu dan harga berada di `data/menu.php`.

## Penyimpanan pesanan

Pesanan disimpan oleh PHP ke `storage/orders.json`. File tersebut sengaja diabaikan Git agar data pelanggan tidak ikut ter-commit.

Harga dan total dihitung ulang pada server agar nilai dari browser tidak dapat dimanipulasi langsung.

## Struktur proyek

```text
harumi-laragon/
├── api/orders.php       Endpoint penyimpanan pesanan
├── assets/css/style.css Tampilan responsif
├── assets/img/          Aset lokal
├── assets/js/app.js     Katalog, cart, dan checkout
├── config/app.php       Konfigurasi merchant
├── data/menu.php        Master menu dan harga
├── storage/             Pesanan lokal
└── index.php            Halaman utama
```

## Git

Repository sudah diinisialisasi dan memiliki initial commit. Untuk menghubungkan ke GitHub/GitLab:

```bash
git remote add origin URL_REPOSITORY_ANDA
git branch -M main
git push -u origin main
```

## Catatan produksi

Versi ini cocok untuk demo dan operasional sederhana di satu mesin. Untuk penggunaan multi-outlet atau banyak pengguna, lanjutkan dengan database MySQL, autentikasi admin, manajemen stok, payment gateway, dan HTTPS.
