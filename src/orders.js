// Endpoint pesanan — padanan api/orders.php versi PHP.
// Harga selalu dihitung ulang dari MENU di server (edge); nilai dari browser tidak dipercaya.
import { CONFIG } from './config.js';
import { MENU } from './menu.js';

function json(data, status) {
  return new Response(JSON.stringify(data), {
    status,
    headers: { 'Content-Type': 'application/json; charset=utf-8', 'X-Content-Type-Options': 'nosniff' },
  });
}

function cleanText(value, maxLength) {
  return String(value ?? '').replace(/<[^>]*>/g, '').trim().slice(0, maxLength);
}

// yymmdd dalam timezone merchant (Asia/Jakarta) — untuk kode pesanan.
function jakartaYmd() {
  const parts = new Intl.DateTimeFormat('en-GB', {
    timeZone: CONFIG.timezone, year: '2-digit', month: '2-digit', day: '2-digit',
  }).formatToParts(new Date());
  const m = {};
  for (const p of parts) m[p.type] = p.value;
  return `${m.year}${m.month}${m.day}`;
}

// timestamp ISO-8601 (DATE_ATOM) dalam timezone merchant.
function jakartaAtom() {
  const parts = new Intl.DateTimeFormat('en-GB', {
    timeZone: CONFIG.timezone, year: 'numeric', month: '2-digit', day: '2-digit',
    hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false,
  }).formatToParts(new Date());
  const m = {};
  for (const p of parts) m[p.type] = p.value;
  const offset = new Intl.DateTimeFormat('en-US', {
    timeZone: CONFIG.timezone, timeZoneName: 'longOffset',
  }).formatToParts(new Date()).find((p) => p.type === 'timeZoneName')?.value || 'GMT+07:00';
  const offsetFixed = offset.replace(/^GMT([+-])(\d{1,2})(?::?(\d{2}))?$/, (_, s, h, m) => `${s}${h.padStart(2, '0')}:${(m || '00').padStart(2, '0')}`);
  return `${m.year}-${m.month}-${m.day}T${m.hour}:${m.minute}:${m.second}${offsetFixed}`;
}

function orderCode() {
  const bytes = new Uint8Array(2);
  crypto.getRandomValues(bytes);
  const hex = [...bytes].map((b) => b.toString(16).padStart(2, '0')).join('').toUpperCase();
  return `HRM-${jakartaYmd()}-${hex}`;
}

export async function handleOrder(request, env) {
  if (request.method !== 'POST') {
    return json({ success: false, message: 'Metode tidak diizinkan.' }, 405);
  }

  const raw = await request.text();
  if (raw.length > 100000) {
    return json({ success: false, message: 'Payload pesanan tidak valid.' }, 400);
  }

  let payload;
  try {
    payload = JSON.parse(raw);
  } catch {
    return json({ success: false, message: 'Format JSON tidak valid.' }, 400);
  }
  if (typeof payload !== 'object' || payload === null) {
    return json({ success: false, message: 'Format JSON tidak valid.' }, 400);
  }

  const customer = (typeof payload.customer === 'object' && payload.customer !== null) ? payload.customer : {};
  const name = cleanText(customer.name, 80);
  const phone = cleanText(customer.phone, 30);
  const address = cleanText(customer.address, 500);
  const note = cleanText(customer.note, 500);
  const orderType = ['Pickup', 'Delivery'].includes(payload.orderType) ? payload.orderType : 'Pickup';
  const payment = ['Cash', 'QRIS'].includes(payload.payment) ? payload.payment : 'Cash';

  if (name === '' || phone === '' || (orderType === 'Delivery' && address === '')) {
    return json({ success: false, message: 'Data pemesan belum lengkap.' }, 422);
  }

  const menuIndex = new Map(MENU.map((product) => [product.id, product]));
  const requestedItems = Array.isArray(payload.items) ? payload.items : [];
  const orderItems = [];
  let subtotal = 0;

  for (const requested of requestedItems) {
    if (typeof requested !== 'object' || requested === null) continue;
    const id = String(requested.id ?? '');
    const product = menuIndex.get(id);
    if (!product) continue;
    const size = requested.size === 'large' && product.large ? 'large' : 'regular';
    const qty = Math.min(50, Math.max(1, parseInt(requested.qty, 10) || 1));
    const price = size === 'large' ? product.large : product.regular;
    const lineSubtotal = price * qty;
    subtotal += lineSubtotal;
    orderItems.push({
      id,
      name: product.name,
      size,
      sizeLabel: product.large ? size.charAt(0).toUpperCase() + size.slice(1) : product.category,
      qty,
      price,
      subtotal: lineSubtotal,
      note: cleanText(requested.note, 200),
    });
  }

  if (!orderItems.length) {
    return json({ success: false, message: 'Keranjang pesanan kosong.' }, 422);
  }

  const deliveryFee = orderType === 'Delivery' ? CONFIG.deliveryFee : 0;
  const order = {
    code: orderCode(),
    createdAt: jakartaAtom(),
    customerName: name,
    customerPhone: phone,
    address,
    note,
    orderType,
    payment,
    items: orderItems,
    subtotal,
    deliveryFee,
    total: subtotal + deliveryFee,
    status: 'new',
  };

  await env.ORDERS.put(`order:${order.code}`, JSON.stringify(order));

  return json({ success: true, order }, 200);
}
