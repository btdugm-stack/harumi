(() => {
  'use strict';

  const config = window.HARUMI;
  const menu = config.menu;
  const menuMap = new Map(menu.map((item) => [item.id, item]));
  const rupiah = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
  const escapeHtml = (value = '') => String(value).replace(/[&<>'"]/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[char]));

  const state = {
    cart: loadCart(),
    selected: null,
    productQty: 1,
    selectedSize: 'regular',
    checkout: false,
    orderType: 'Pickup',
    payment: 'Cash',
  };

  const el = {
    productOverlay: document.getElementById('productOverlay'),
    cartOverlay: document.getElementById('cartOverlay'),
    cartContent: document.getElementById('cartContent'),
    drawerTitle: document.getElementById('drawerTitle'),
    cartCount: document.getElementById('cartCount'),
    floatingCart: document.getElementById('floatingCart'),
    floatingCount: document.getElementById('floatingCount'),
    floatingTotal: document.getElementById('floatingTotal'),
    toast: document.getElementById('toast'),
    search: document.getElementById('searchInput'),
    noResult: document.getElementById('noResult'),
  };

  function loadCart() {
    try {
      const saved = JSON.parse(localStorage.getItem('harumi-laragon-cart') || '[]');
      return Array.isArray(saved) ? saved : [];
    } catch (_) {
      return [];
    }
  }

  function saveCart() {
    localStorage.setItem('harumi-laragon-cart', JSON.stringify(state.cart));
    updateCartBadge();
  }

  function cartTotal() {
    return state.cart.reduce((sum, line) => sum + line.price * line.qty, 0);
  }

  function updateCartBadge() {
    const count = state.cart.reduce((sum, line) => sum + line.qty, 0);
    const total = cartTotal();
    el.cartCount.textContent = count;
    el.floatingCount.textContent = `${count} item`;
    el.floatingTotal.textContent = `${rupiah(total)} →`;
    el.floatingCart.hidden = count === 0;
  }

  let toastTimer;
  function showToast(message, error = false) {
    clearTimeout(toastTimer);
    el.toast.textContent = `${error ? '!' : '✓'} ${message}`;
    el.toast.style.background = error ? '#8f493f' : '#263b2c';
    el.toast.hidden = false;
    toastTimer = setTimeout(() => { el.toast.hidden = true; }, 2400);
  }

  function setPageLock(locked) {
    document.body.classList.toggle('locked', locked);
  }

  function filterProducts() {
    const active = document.querySelector('.category-tabs button.active')?.dataset.category || 'Semua';
    const query = el.search.value.trim().toLowerCase();
    let visible = 0;
    document.querySelectorAll('.menu-card').forEach((card) => {
      const categoryMatch = active === 'Semua' || card.dataset.category === active;
      const queryMatch = !query || card.dataset.name.includes(query);
      card.hidden = !(categoryMatch && queryMatch);
      if (!card.hidden) visible += 1;
    });
    el.noResult.hidden = visible > 0;
  }

  document.querySelectorAll('.category-tabs button').forEach((button) => {
    button.addEventListener('click', () => {
      document.querySelectorAll('.category-tabs button').forEach((item) => item.classList.remove('active'));
      button.classList.add('active');
      filterProducts();
    });
  });
  el.search.addEventListener('input', filterProducts);

  document.querySelectorAll('.add-product').forEach((button) => {
    button.addEventListener('click', () => openProduct(button.dataset.id));
  });

  function openProduct(id) {
    const product = menuMap.get(id);
    if (!product) return;
    state.selected = product;
    state.productQty = 1;
    state.selectedSize = 'regular';
    document.getElementById('modalProductImage').src = product.image;
    document.getElementById('modalProductImage').alt = `Foto ${product.name}`;
    document.getElementById('modalCategory').textContent = product.category;
    document.getElementById('modalProductName').textContent = product.name;
    document.getElementById('modalNote').textContent = product.note;
    document.getElementById('itemNote').value = '';
    document.getElementById('productQty').textContent = '1';
    renderSizeOptions();
    updateAddButton();
    el.productOverlay.hidden = false;
    setPageLock(true);
  }

  function renderSizeOptions() {
    const product = state.selected;
    const options = [{ key: 'regular', label: product.large ? 'Regular' : 'Harga', price: product.regular }];
    if (product.large) options.push({ key: 'large', label: 'Large', price: product.large });
    document.getElementById('sizeOptions').innerHTML = options.map((option) => `<button type="button" class="${state.selectedSize === option.key ? 'active' : ''}" data-size="${option.key}"><span>${option.label}</span><b>${rupiah(option.price)}</b></button>`).join('');
    document.querySelectorAll('#sizeOptions button').forEach((button) => button.addEventListener('click', () => {
      state.selectedSize = button.dataset.size;
      renderSizeOptions();
      updateAddButton();
    }));
  }

  function selectedPrice() {
    if (!state.selected) return 0;
    return state.selectedSize === 'large' && state.selected.large ? state.selected.large : state.selected.regular;
  }

  function updateAddButton() {
    document.getElementById('confirmAdd').textContent = `Tambah • ${rupiah(selectedPrice() * state.productQty)}`;
  }

  document.getElementById('qtyMinus').addEventListener('click', () => {
    state.productQty = Math.max(1, state.productQty - 1);
    document.getElementById('productQty').textContent = state.productQty;
    updateAddButton();
  });
  document.getElementById('qtyPlus').addEventListener('click', () => {
    state.productQty += 1;
    document.getElementById('productQty').textContent = state.productQty;
    updateAddButton();
  });

  document.getElementById('confirmAdd').addEventListener('click', () => {
    const product = state.selected;
    if (!product) return;
    const note = document.getElementById('itemNote').value.trim();
    const size = product.large ? state.selectedSize : 'regular';
    const key = `${product.id}|${size}|${note}`;
    const existing = state.cart.find((line) => line.key === key);
    if (existing) existing.qty += state.productQty;
    else state.cart.push({ key, id: product.id, size, note, price: selectedPrice(), qty: state.productQty });
    saveCart();
    closeProduct();
    showToast(`${product.name} masuk keranjang`);
  });

  function closeProduct() {
    el.productOverlay.hidden = true;
    if (el.cartOverlay.hidden) setPageLock(false);
  }

  function openCart() {
    state.checkout = false;
    renderCart();
    el.cartOverlay.hidden = false;
    setPageLock(true);
  }

  function closeCart() {
    el.cartOverlay.hidden = true;
    if (el.productOverlay.hidden) setPageLock(false);
  }

  document.getElementById('openCart').addEventListener('click', openCart);
  el.floatingCart.addEventListener('click', openCart);
  document.querySelector('[data-close="product"]').addEventListener('click', closeProduct);
  document.querySelector('[data-close="cart"]').addEventListener('click', closeCart);
  el.productOverlay.addEventListener('mousedown', (event) => { if (event.target === el.productOverlay) closeProduct(); });
  el.cartOverlay.addEventListener('mousedown', (event) => { if (event.target === el.cartOverlay) closeCart(); });
  document.addEventListener('keydown', (event) => { if (event.key === 'Escape') { closeProduct(); closeCart(); } });

  function renderCart() {
    el.drawerTitle.textContent = state.checkout ? 'Checkout' : 'Keranjang';
    if (!state.cart.length) {
      el.cartContent.innerHTML = `<div class="empty-cart"><span>茶</span><h3>Keranjang masih kosong</h3><p>Waktunya pilih teman untuk harimu.</p><button type="button" class="primary-button" id="emptyBrowse">Lihat menu</button></div>`;
      document.getElementById('emptyBrowse').addEventListener('click', closeCart);
      return;
    }
    if (state.checkout) renderCheckout();
    else renderCartLines();
  }

  function renderCartLines() {
    const lines = state.cart.map((line) => {
      const product = menuMap.get(line.id);
      return `<div class="cart-line"><img class="line-image" src="${product.image}" alt=""><div class="line-copy"><strong>${escapeHtml(product.name)}</strong><span>${product.large ? escapeHtml(line.size) : escapeHtml(product.category)}${line.note ? ` • ${escapeHtml(line.note)}` : ''}</span><div class="mini-qty"><button type="button" data-qty="-1" data-key="${escapeHtml(line.key)}">−</button><span>${line.qty}</span><button type="button" data-qty="1" data-key="${escapeHtml(line.key)}">+</button></div></div><b>${rupiah(line.price * line.qty)}</b></div>`;
    }).join('');
    el.cartContent.innerHTML = `${lines}<div class="cart-summary"><div><span>Subtotal</span><strong>${rupiah(cartTotal())}</strong></div><p>Biaya pengantaran dihitung saat checkout.</p><button type="button" class="primary-button" id="checkoutButton">Lanjut checkout →</button></div>`;
    document.querySelectorAll('[data-qty]').forEach((button) => button.addEventListener('click', () => changeCartQty(button.dataset.key, Number(button.dataset.qty))));
    document.getElementById('checkoutButton').addEventListener('click', () => { state.checkout = true; renderCart(); });
  }

  function changeCartQty(key, delta) {
    const line = state.cart.find((item) => item.key === key);
    if (!line) return;
    line.qty += delta;
    state.cart = state.cart.filter((item) => item.qty > 0);
    saveCart();
    renderCart();
  }

  function renderCheckout() {
    const delivery = state.orderType === 'Delivery';
    el.cartContent.innerHTML = `<form class="checkout" id="checkoutForm">
      <div class="segmented"><button type="button" data-order="Pickup" class="${!delivery ? 'active' : ''}">Pickup</button><button type="button" data-order="Delivery" class="${delivery ? 'active' : ''}">Delivery</button></div>
      <label>Nama pemesan<input name="name" required maxlength="80" placeholder="Nama kamu"></label>
      <label>Nomor WhatsApp<input name="phone" required maxlength="30" inputmode="tel" placeholder="08xxxxxxxxxx"></label>
      ${delivery ? '<label>Alamat pengantaran<textarea name="address" required maxlength="500" placeholder="Alamat lengkap dan patokan"></textarea></label>' : ''}
      <label>Catatan pesanan<textarea name="note" maxlength="500" placeholder="Catatan untuk seluruh pesanan"></textarea></label>
      <fieldset><legend>Pembayaran</legend><div class="payments"><button type="button" data-payment="Cash" class="${state.payment === 'Cash' ? 'active' : ''}">Cash</button><button type="button" data-payment="QRIS" class="${state.payment === 'QRIS' ? 'active' : ''}">QRIS</button></div></fieldset>
      <div class="checkout-total"><span>Subtotal <b>${rupiah(cartTotal())}</b></span><span>Ongkir <b>${rupiah(delivery ? config.deliveryFee : 0)}</b></span><strong>Total <b>${rupiah(cartTotal() + (delivery ? config.deliveryFee : 0))}</b></strong></div>
      <p class="error-message" id="checkoutError" hidden></p>
      <button class="primary-button" type="submit">Buat pesanan →</button><button class="back-button" type="button" id="backToCart">← Kembali ke keranjang</button>
    </form>`;
    document.querySelectorAll('[data-order]').forEach((button) => button.addEventListener('click', () => { state.orderType = button.dataset.order; renderCheckout(); }));
    document.querySelectorAll('[data-payment]').forEach((button) => button.addEventListener('click', () => { state.payment = button.dataset.payment; renderCheckout(); }));
    document.getElementById('backToCart').addEventListener('click', () => { state.checkout = false; renderCart(); });
    document.getElementById('checkoutForm').addEventListener('submit', submitOrder);
  }

  async function submitOrder(event) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const submit = event.currentTarget.querySelector('[type="submit"]');
    const payload = {
      customer: { name: form.get('name'), phone: form.get('phone'), address: form.get('address') || '', note: form.get('note') || '' },
      orderType: state.orderType,
      payment: state.payment,
      items: state.cart.map(({ id, size, qty, note }) => ({ id, size, qty, note })),
    };
    submit.disabled = true;
    submit.textContent = 'Menyimpan pesanan...';
    try {
      const response = await fetch('/api/orders', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.message || 'Pesanan gagal disimpan.');
      renderSuccess(result.order);
    } catch (error) {
      const errorBox = document.getElementById('checkoutError');
      errorBox.textContent = error.message || 'Terjadi kesalahan. Pastikan Laragon dan PHP aktif.';
      errorBox.hidden = false;
      submit.disabled = false;
      submit.textContent = 'Buat pesanan →';
    }
  }

  function renderSuccess(order) {
    el.drawerTitle.textContent = 'Pesanan dibuat!';
    const message = [
      `Halo Harumi! Saya ingin konfirmasi pesanan ${order.code}.`, '',
      ...order.items.map((line) => `• ${line.name} (${line.sizeLabel}) x${line.qty} — ${rupiah(line.subtotal)}`), '',
      `Metode: ${order.orderType}`,
      order.address ? `Alamat: ${order.address}` : 'Ambil di outlet Harumi',
      `Pembayaran: ${order.payment}`,
      `Total: ${rupiah(order.total)}`,
      `Nama: ${order.customerName}`,
      `No. WhatsApp: ${order.customerPhone}`,
      order.note ? `Catatan: ${order.note}` : '',
    ].filter(Boolean).join('\n');
    const target = config.whatsappNumber ? `https://wa.me/${config.whatsappNumber}?text=${encodeURIComponent(message)}` : `https://wa.me/?text=${encodeURIComponent(message)}`;
    el.cartContent.innerHTML = `<div class="success"><div class="success-icon">✓</div><h3>Arigatou, ${escapeHtml(order.customerName)}!</h3><p>Pesanan <b>${escapeHtml(order.code)}</b> tersimpan secara lokal. Kirim ringkasannya untuk konfirmasi.</p><div class="success-card"><span>Total pembayaran</span><strong>${rupiah(order.total)}</strong><small>${escapeHtml(order.orderType)} • ${escapeHtml(order.payment)}</small></div><a class="primary-button" target="_blank" rel="noreferrer" href="${target}">Kirim via WhatsApp ↗</a><button class="back-button" type="button" id="finishOrder">Selesai</button></div>`;
    state.cart = [];
    saveCart();
    document.getElementById('finishOrder').addEventListener('click', () => { state.checkout = false; closeCart(); });
  }

  updateCartBadge();
})();
