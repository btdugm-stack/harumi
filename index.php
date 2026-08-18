<?php
$app = require __DIR__ . '/config/app.php';
$menu = require __DIR__ . '/data/menu.php';
date_default_timezone_set($app['timezone']);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function product_image(array $item): string
{
    $images = [
        'matcha' => 'https://images.unsplash.com/photo-1774797405168-02b8df73fe6b?auto=format&fit=crop&w=900&q=78',
        'coffee' => 'https://images.unsplash.com/photo-1637837600070-13973f4fb61f?auto=format&fit=crop&w=900&q=78',
        'thai' => 'https://images.unsplash.com/photo-1482349274213-19ca6126f02f?auto=format&fit=crop&w=900&q=78',
        'chocolate' => 'https://images.unsplash.com/photo-1553787499-6f9133860278?auto=format&fit=crop&w=900&q=78',
        'fruit' => 'https://images.unsplash.com/photo-1648178628363-c8058d7d3423?auto=format&fit=crop&w=900&q=78',
        'rice' => 'https://images.unsplash.com/photo-1704793027864-ac1b45376b6e?auto=format&fit=crop&w=900&q=78',
        'noodles' => 'https://images.unsplash.com/photo-1697652974700-6851a3b0fe06?auto=format&fit=crop&w=900&q=78',
        'fries' => 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?auto=format&fit=crop&w=900&q=78',
        'dimsum' => 'https://images.unsplash.com/photo-1767324672653-84c017d85d8e?auto=format&fit=crop&w=900&q=78',
        'cake' => 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?auto=format&fit=crop&w=900&q=78',
    ];

    if ($item['category'] === 'Premium Matcha') return $images['matcha'];
    if ($item['category'] === 'Coffee') return $images['coffee'];
    if ($item['category'] === 'Non Coffee') {
        if (preg_match('/choco|velvet/i', $item['name'])) return $images['chocolate'];
        if (preg_match('/strawberry|blueberry|mango|kiwi|lychee/i', $item['name'])) return $images['fruit'];
        return $images['thai'];
    }
    if ($item['category'] === 'Food') return preg_match('/indomie/i', $item['name']) ? $images['noodles'] : $images['rice'];
    if ($item['category'] === 'Snacks') return preg_match('/fries/i', $item['name']) ? $images['fries'] : $images['dimsum'];
    return $images['cake'];
}

$notes = [
    'Premium Matcha' => 'Matcha premium yang lembut dan earthy.',
    'Coffee' => 'Espresso-based, nyaman untuk setiap hari.',
    'Non Coffee' => 'Creamy, segar, dan ramah semua usia.',
    'Food' => 'Comfort food hangat dan mengenyangkan.',
    'Snacks' => 'Camilan gurih untuk teman ngobrol.',
    'Dessert' => 'Manis lembut untuk penutup yang proper.',
];
$categories = array_values(array_unique(array_column($menu, 'category')));
$frontendMenu = array_map(function (array $item) use ($notes): array {
    $item['image'] = product_image($item);
    $item['note'] = $notes[$item['category']];
    return $item;
}, $menu);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Pesan matcha, kopi, makanan, snack, dan dessert Harumi.">
    <title><?= e($app['name']) ?> - <?= e($app['tagline']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css?v=1.0.0">
</head>
<body>
<header class="site-header">
    <a class="brand" href="#home"><span>Harumi</span><small>はるみ</small></a>
    <nav aria-label="Navigasi utama">
        <a href="#menu">Menu</a><a href="#about">Tentang</a><a href="#location">Lokasi</a>
    </nav>
    <button class="cart-button" id="openCart" type="button"><span>Keranjang</span><b id="cartCount">0</b></button>
</header>

<main>
    <section class="hero" id="home">
        <div class="hero-copy">
            <p class="eyebrow"><span></span> MATCHA • COFFEE • COMFORT FOOD</p>
            <h1>Little joys,<br><em>served daily.</em></h1>
            <p>Temukan ritual nyamanmu—matcha premium, kopi yang ramah, dan comfort food yang dibuat untuk menemani hari.</p>
            <div class="hero-actions"><a class="primary-button" href="#menu">Mulai pesan <span>→</span></a><a class="text-link" href="#about">Kenalan dengan Harumi</a></div>
            <div class="stats"><div><strong>57</strong><span>Pilihan menu</span></div><div><strong>12</strong><span>Menu favorit</span></div><div><strong>10k</strong><span>Mulai dari</span></div></div>
        </div>
        <div class="hero-visual">
            <div class="hero-photo"><img src="assets/img/hero-matcha.jpg" alt="Menu matcha Harumi"></div>
            <div class="hero-badge"><span>★★★★★</span><strong>Customer favorite</strong><small>Signature Matcha Latte</small></div>
            <div class="fresh"><b>今日</b><small>Freshly made</small></div>
        </div>
    </section>

    <section class="about" id="about">
        <p class="eyebrow centered"><span></span> SMALL MOMENTS, BETTER DAYS <span></span></p>
        <h2>Diracik pelan.<br><em>Dinikmati sepenuh hati.</em></h2>
        <p>Harumi menghadirkan rasa yang familiar dalam sentuhan Jepang yang lembut—hangat, jujur, dan tetap ramah di kantong.</p>
    </section>

    <section class="catalog" id="menu">
        <div class="section-title"><p class="eyebrow centered"><span></span> PILIH YANG KAMU SUKA <span></span></p><h2>Our complete <em>menu.</em></h2><p>Semua menu Harumi, siap dipesan secara digital.</p></div>
        <div class="catalog-tools">
            <div class="category-tabs" role="tablist">
                <button type="button" class="active" data-category="Semua">Semua</button>
                <?php foreach ($categories as $category): ?>
                    <button type="button" data-category="<?= e($category) ?>"><?= e($category) ?></button>
                <?php endforeach; ?>
            </div>
            <label class="search"><span>⌕</span><input id="searchInput" type="search" placeholder="Cari menu favoritmu..." aria-label="Cari menu"></label>
        </div>

        <div class="menu-grid" id="menuGrid">
            <?php foreach ($frontendMenu as $item): ?>
                <article class="menu-card" data-id="<?= e($item['id']) ?>" data-category="<?= e($item['category']) ?>" data-name="<?= e(strtolower($item['name'])) ?>">
                    <div class="menu-image">
                        <img src="<?= e($item['image']) ?>" alt="Foto <?= e($item['name']) ?>" loading="lazy">
                        <?php if ($item['favorite']): ?><span class="favorite">★ Favorit</span><?php endif; ?>
                        <small><?= e($item['category']) ?> • Harumi</small>
                    </div>
                    <div class="menu-copy">
                        <span><?= e($item['category']) ?></span>
                        <h3><?= e($item['name']) ?></h3>
                        <p><?= e($item['note']) ?></p>
                        <div><strong><?= $item['large'] ? 'Mulai ' : '' ?>Rp <?= number_format($item['regular'], 0, ',', '.') ?></strong><button type="button" class="add-product" data-id="<?= e($item['id']) ?>" aria-label="Tambah <?= e($item['name']) ?>">+</button></div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="no-result" id="noResult" hidden><strong>Menu belum ketemu.</strong><span>Coba kata kunci lain—jangan menyerah sebelum matcha.</span></div>
    </section>

    <section class="how-order">
        <div><p class="eyebrow"><span></span> EASY ORDERING</p><h2>Dari craving<br>ke <em>checkout.</em></h2><p>Tiga langkah ringan. Sisanya biar Harumi yang meracik.</p></div>
        <div class="steps"><article><b>01</b><h3>Choose</h3><p>Pilih menu dan ukuran favoritmu.</p></article><article><b>02</b><h3>Checkout</h3><p>Isi detail pickup atau delivery.</p></article><article><b>03</b><h3>Enjoy</h3><p>Konfirmasi lewat WhatsApp. Beres.</p></article></div>
    </section>

    <section class="location" id="location">
        <div><span>01</span><strong>Freshly made</strong><small>Dibuat setelah kamu pesan</small></div>
        <div><span>02</span><strong>Friendly price</strong><small>Enak tanpa bikin dompet panik</small></div>
        <div><span>03</span><strong>Everyday comfort</strong><small><?= e($app['open_hours']) ?></small></div>
    </section>
</main>

<footer><a class="brand" href="#home"><span>Harumi</span><small>はるみ</small></a><div><strong>Outlet</strong><p><?= e($app['address']) ?></p></div><div><strong>PoC Laragon</strong><p>PHP native • Tanpa framework • Git ready</p></div></footer>

<button type="button" class="floating-cart" id="floatingCart" hidden><span><b id="floatingCount">0 item</b><small>Lihat keranjang</small></span><strong id="floatingTotal">Rp 0 →</strong></button>

<div class="overlay" id="productOverlay" hidden>
    <section class="product-modal" role="dialog" aria-modal="true" aria-labelledby="modalProductName">
        <button type="button" class="close" data-close="product">×</button>
        <div class="modal-image"><img id="modalProductImage" alt=""><small>HARUMI • FRESHLY MADE</small></div>
        <div class="modal-copy"><span id="modalCategory"></span><h2 id="modalProductName"></h2><p id="modalNote"></p><div class="size-options" id="sizeOptions"></div><label>Catatan item<input id="itemNote" placeholder="Contoh: less ice, tanpa sambal..."></label><div class="add-row"><div class="qty"><button type="button" id="qtyMinus">−</button><span id="productQty">1</span><button type="button" id="qtyPlus">+</button></div><button type="button" class="primary-button" id="confirmAdd">Tambah</button></div></div>
    </section>
</div>

<div class="drawer-overlay" id="cartOverlay" hidden>
    <aside class="cart-drawer" aria-label="Keranjang pesanan">
        <header><div><p class="eyebrow">YOUR ORDER</p><h2 id="drawerTitle">Keranjang</h2></div><button type="button" class="close" data-close="cart">×</button></header>
        <div id="cartContent" class="drawer-content"></div>
    </aside>
</div>

<div class="toast" id="toast" hidden></div>

<script>
window.HARUMI = <?= json_encode([
    'menu' => $frontendMenu,
    'deliveryFee' => $app['delivery_fee'],
    'whatsappNumber' => $app['whatsapp_number'],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="assets/js/app.js?v=1.0.0"></script>
</body>
</html>
