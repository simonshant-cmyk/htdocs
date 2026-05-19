@extends('layouts.app')

@section('title', 'Корзина — АфишаКолыма')

@section('styles')
<style>
  .cart-layout {
    max-width: 1000px; margin: 0 auto; padding: 40px 32px;
    display: grid; grid-template-columns: 1fr 340px; gap: 32px;
  }
  @media(max-width:800px){ .cart-layout{grid-template-columns:1fr;} }

  .page-title { font-family:var(--font-display); font-size:2rem; font-weight:700; margin-bottom:32px; }

  .cart-item {
    display:flex; gap:16px; align-items:flex-start;
    background:var(--surface); border:1px solid var(--border);
    border-radius:var(--radius); padding:18px; margin-bottom:12px;
    transition:var(--transition);
  }
  .cart-item-img {
    width:80px; height:80px; border-radius:10px; flex-shrink:0;
    object-fit:cover; background:var(--bg2);
    display:flex; align-items:center; justify-content:center; font-size:1.8rem;
  }
  .cart-item-img img { width:100%; height:100%; object-fit:cover; border-radius:10px; }
  .cart-item-info { flex:1; }
  .cart-item-title { font-weight:700; font-size:1rem; margin-bottom:4px; }
  .cart-item-meta { font-size:.8rem; color:var(--muted); margin-bottom:10px; }
  .cart-item-price { font-weight:700; color:var(--accent); font-size:1rem; }

  .qty-control { display:flex; align-items:center; border:1.5px solid var(--border); border-radius:8px; overflow:hidden; width:fit-content; }
  .qty-btn {
    width:32px; height:32px; display:flex; align-items:center; justify-content:center;
    background:var(--bg2); border:none; cursor:pointer; font-size:1.1rem; font-weight:700;
    color:var(--text); transition:var(--transition);
  }
  .qty-btn:hover { background:var(--border); }
  .qty-val { width:36px; text-align:center; font-weight:700; font-size:.9rem; line-height:32px; }

  .cart-item-remove {
    color:var(--muted); background:none; border:none; cursor:pointer;
    font-size:1.1rem; padding:4px; transition:var(--transition); align-self:flex-start;
  }
  .cart-item-remove:hover { color:var(--accent); }

  .order-card {
    background:var(--surface); border:1px solid var(--border);
    border-radius:var(--radius); padding:24px; position:sticky; top:84px;
  }
  .order-title { font-weight:700; font-size:1.1rem; margin-bottom:20px; }
  .order-row { display:flex; justify-content:space-between; font-size:.9rem; margin-bottom:10px; color:var(--muted); }
  .order-total { display:flex; justify-content:space-between; font-weight:700; font-size:1.1rem; padding-top:14px; border-top:1.5px solid var(--border); margin-top:4px; }

  .cart-tabs { display:flex; gap:4px; margin-bottom:28px; border-bottom:1.5px solid var(--border); }
  .cart-tab { padding:10px 20px; font-size:.9rem; font-weight:600; color:var(--muted); cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-1.5px; transition:var(--transition); }
  .cart-tab.active { color:var(--accent); border-bottom-color:var(--accent); }

  .ticket-card {
    display: flex; background: var(--surface);
    border: 1.5px solid var(--border); border-radius: var(--radius);
    overflow: hidden; margin-bottom: 16px; transition: var(--transition);
  }
  .ticket-card:hover { box-shadow: var(--shadow-lg); border-color: var(--accent); }
  .ticket-img-col {
    width: 160px; flex-shrink: 0;
    background: var(--bg2); display: flex; align-items: center; justify-content: center;
    font-size: 3rem; min-height: 150px;
  }
  .ticket-img-col img { width: 100%; height: 100%; object-fit: cover; }
  .ticket-body { flex: 1; padding: 18px 20px; }
  .ticket-tag { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--accent); margin-bottom: 6px; }
  .ticket-title { font-family: var(--font-display); font-size: 1.05rem; font-weight: 700; line-height: 1.3; margin-bottom: 8px; }
  .ticket-meta { font-size: .8rem; color: var(--muted); margin-bottom: 4px; }
  .ticket-sep { border: none; border-top: 1.5px dashed var(--border); margin: 12px 0; }
  .ticket-footer { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
  .ticket-qty { font-size: .82rem; color: var(--muted); }
  .ticket-price { font-weight: 700; color: var(--accent); font-size: 1rem; margin-left: auto; }
  .ticket-side {
    width: 110px; flex-shrink: 0;
    border-left: 1.5px dashed var(--border);
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; gap: 10px; padding: 16px 12px;
  }
  .qr-canvas { border-radius: 4px; opacity: .85; }
  .paid-badge {
    background: rgba(42,110,90,.12); color: var(--accent2);
    padding: 4px 10px; border-radius: 20px;
    font-size: .68rem; font-weight: 700; letter-spacing: .06em; white-space: nowrap;
  }
  .ticket-pay-lbl { font-size: .68rem; color: var(--muted); text-align: center; }
  @media(max-width:600px){
    .ticket-card{ flex-direction:column; }
    .ticket-img-col{ width:100%; height:140px; min-height:unset; }
    .ticket-side{ width:100%; border-left:none; border-top:1.5px dashed var(--border); flex-direction:row; padding:12px 16px; }
  }

  /* Pay methods */
  .pay-methods { display:flex; flex-direction:column; gap:10px; margin-bottom:16px; }
  .pay-method {
    display:flex; align-items:center; gap:14px; padding:14px 16px;
    border:1.5px solid var(--border); border-radius:var(--radius-sm);
    cursor:pointer; transition:var(--transition); background:var(--surface);
  }
  .pay-method:hover { border-color:var(--accent2); background:rgba(42,110,90,.04); }
  .pay-method.selected { border-color:var(--accent2); background:rgba(42,110,90,.06); }
  .pay-method-icon { width:40px; height:32px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; flex-shrink:0; }
  .pay-method-info { flex:1; }
  .pay-method-name { font-weight:600; font-size:.88rem; }
  .pay-method-desc { font-size:.75rem; color:var(--muted); margin-top:1px; }
  .pay-method-badge { font-size:.65rem; font-weight:700; padding:2px 8px; border-radius:20px; background:rgba(42,110,90,.1); color:var(--accent2); white-space:nowrap; }
</style>
@endsection

@section('content')
<div class="cart-layout page-enter">
  <div>
    <h1 class="page-title">Корзина</h1>

    <div class="cart-tabs">
      <div class="cart-tab active" onclick="showTab('cart',this)">Корзина</div>
      <div class="cart-tab" onclick="showTab('paid',this)">Мои билеты</div>
    </div>

    <div id="tab-cart">
      <div id="cart-list"><div class="loader"><div class="spinner"></div></div></div>
    </div>

    <div id="tab-paid" style="display:none">
      <div id="paid-list"><div class="loader"><div class="spinner"></div></div></div>
    </div>
  </div>

  <div id="order-sidebar"></div>
</div>

<!-- МОДАЛЬНОЕ ОКНО ОПЛАТЫ -->
<div class="modal-overlay" id="modal-pay" onclick="if(event.target===this)closeModal('modal-pay')">
  <div class="modal" style="max-width:480px">
    <div class="modal-title">Выберите способ оплаты</div>
    <div class="pay-methods">
      <div class="pay-method" onclick="selectPay('sbp',this)">
        <div class="pay-method-icon">🏦</div>
        <div class="pay-method-info">
          <div class="pay-method-name">СБП — Система быстрых платежей</div>
          <div class="pay-method-desc">Перевод по QR-коду между банками</div>
        </div>
        <span class="pay-method-badge">Быстро</span>
      </div>
      <div class="pay-method" onclick="selectPay('card',this)">
        <div class="pay-method-icon">💳</div>
        <div class="pay-method-info">
          <div class="pay-method-name">Банковская карта</div>
          <div class="pay-method-desc">Visa, Mastercard, МИР</div>
        </div>
      </div>
      <div class="pay-method" onclick="selectPay('sber',this)">
        <div class="pay-method-icon">🟢</div>
        <div class="pay-method-info">
          <div class="pay-method-name">СберПей</div>
          <div class="pay-method-desc">Оплата через приложение Сбербанка</div>
        </div>
      </div>
      <div class="pay-method" onclick="selectPay('ymoney',this)">
        <div class="pay-method-icon">💜</div>
        <div class="pay-method-info">
          <div class="pay-method-name">ЮMoney</div>
          <div class="pay-method-desc">Бывшие Яндекс.Деньги</div>
        </div>
      </div>
      <div class="pay-method" onclick="selectPay('tpay',this)">
        <div class="pay-method-icon">🟡</div>
        <div class="pay-method-info">
          <div class="pay-method-name">T‑Pay</div>
          <div class="pay-method-desc">Оплата через приложение Т‑Банка</div>
        </div>
      </div>
    </div>
    <div style="display:flex;gap:10px;margin-top:8px">
      <button class="btn btn-primary" id="pay-confirm-btn" onclick="confirmPay()" disabled style="flex:1">Оплатить</button>
      <button class="btn btn-secondary" onclick="closeModal('modal-pay')">Отмена</button>
    </div>
    <div style="margin-top:12px;font-size:.75rem;color:var(--muted);text-align:center">
      🔒 Платёж защищён. Данные карты не хранятся на сайте.
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
(function() {
  try {
    const token = localStorage.getItem('token');
    const type  = localStorage.getItem('auth_type');
    if (!token || JSON.parse(type) !== 'user') { nav('/login'); }
  } catch(e) { nav('/login'); }
})();

let cartItems = [];
let selectedPayMethod = null;

async function loadCart() {
  const list = document.getElementById('cart-list');
  list.innerHTML = '<div class="loader"><div class="spinner"></div></div>';
  try {
    const data = await get('/tickets');
    cartItems = Array.isArray(data) ? data : [];
    renderCart();
  } catch(e) {
    list.innerHTML = errBlock(e.message, 'loadCart()');
  }
}

async function loadPaid() {
  const list = document.getElementById('paid-list');
  list.innerHTML = '<div class="loader"><div class="spinner"></div></div>';
  try {
    const data = await get('/tickets/paid');
    const items = Array.isArray(data) ? data : [];
    if (!items.length) {
      list.innerHTML = `<div class="empty"><div class="empty-icon">🎫</div>
        <div>У вас пока нет оплаченных билетов</div>
        <div style="font-size:.82rem;color:var(--muted);margin-top:6px">
          <a href="${window.APP_BASE||''}" style="color:var(--accent);font-weight:600">Найти события</a>
        </div>
      </div>`;
      return;
    }
    list.innerHTML = items.map(t => `
      <div class="ticket-card" onclick="nav('/event/${t.event_id}')" style="cursor:pointer">
        <div class="ticket-img-col">
          ${t.image ? `<img src="${escHtml(t.image)}" alt="">` : '🎭'}
        </div>
        <div class="ticket-body">
          <div class="ticket-tag">${escHtml(t.category_name || 'Событие')}</div>
          <div class="ticket-title">${escHtml(t.title)}</div>
          <div class="ticket-meta">📅 ${fmtDate(t.start_datetime)}</div>
          ${t.venue_name ? `<div class="ticket-meta">📍 ${escHtml(t.venue_name)}</div>` : ''}
          <hr class="ticket-sep">
          <div class="ticket-footer">
            <span class="ticket-qty">🎫 ${t.quantity} ${plural(t.quantity,'билет','билета','билетов')}</span>
            <span class="ticket-price">${fmtPrice(+t.price * +t.quantity)}</span>
          </div>
        </div>
        <div class="ticket-side">
          <canvas class="qr-canvas" data-qr="TICKET:${t.ticket_id}:${t.event_id}:${t.quantity}" width="64" height="64"></canvas>
          <div class="paid-badge">✓ ОПЛАЧЕНО</div>
          <div class="ticket-pay-lbl">${payLabel(t.payment_method)}</div>
        </div>
      </div>
    `).join('');
    renderQrCodes();
  } catch(e) {
    list.innerHTML = errBlock(e.message, 'loadPaid()');
  }
}

function errBlock(msg, ctx) {
  return `<div class="empty" style="padding:40px 24px">
    <div class="empty-icon">⚠️</div>
    <div style="font-weight:600;margin-bottom:6px">Ошибка загрузки</div>
    <div style="font-size:.82rem;color:var(--muted);margin-bottom:16px">${escHtml(msg)}</div>
    <button class="btn btn-primary" onclick="${ctx === 'loadCart()' ? 'loadCart' : 'loadPaid'}()">Повторить</button>
  </div>`;
}

function renderQrCodes() {
  document.querySelectorAll('canvas.qr-canvas').forEach(canvas => {
    const data = canvas.dataset.qr;
    if (!data || !window.QRCode) return;
    QRCode.toCanvas(canvas, data, { width: 64, margin: 1, color: {
      dark: getComputedStyle(document.documentElement).getPropertyValue('--text').trim() || '#000',
      light: '#00000000',
    }}, () => {});
  });
}

function payLabel(m) {
  const map = { sbp:'СБП', card:'Банковская карта', sber:'СберПей', ymoney:'ЮMoney', tpay:'T-Pay', free:'Бесплатно' };
  return map[m] || m || '';
}

function plural(n, one, two, five) {
  const m = Math.abs(n) % 100;
  if (m >= 11 && m <= 14) return five;
  const m1 = m % 10;
  if (m1 === 1) return one;
  if (m1 >= 2 && m1 <= 4) return two;
  return five;
}

function renderCart() {
  const list = document.getElementById('cart-list');
  if (!list) return;
  if (!cartItems.length) {
    list.innerHTML = `<div class="empty" style="padding:60px 24px">
      <div class="empty-icon">🛒</div>
      <div style="font-size:1.1rem;font-weight:600;margin-bottom:6px">Корзина пуста</div>
      <div style="font-size:.85rem;color:var(--muted);margin-bottom:20px">Добавьте события, которые хотите посетить</div>
      <a href="${window.APP_BASE||''}" class="btn btn-primary">Найти события</a>
    </div>`;
    renderSidebar();
    return;
  }
  list.innerHTML = cartItems.map(t => `
    <div class="cart-item" id="ci-${t.ticket_id}">
      <div class="cart-item-img">${t.image ? `<img src="${escHtml(t.image)}">` : '🎭'}</div>
      <div class="cart-item-info">
        <div class="cart-item-title">
          <a href="${(window.APP_BASE||'')}/event/${t.event_id}" style="color:inherit">${escHtml(t.title)}</a>
        </div>
        <div class="cart-item-meta">
          📅 ${fmtDate(t.start_datetime)}${t.venue_name ? ' · 📍 ' + escHtml(t.venue_name) : ''}
        </div>
        <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
          <div class="qty-control">
            <button class="qty-btn" onclick="changeQty(${+t.ticket_id},${+t.quantity-1})">−</button>
            <span class="qty-val">${t.quantity}</span>
            <button class="qty-btn" onclick="changeQty(${+t.ticket_id},${+t.quantity+1})">+</button>
          </div>
          <div class="cart-item-price">${fmtPrice(+t.price * +t.quantity)}</div>
          ${+t.price === 0
            ? '<span class="badge badge-green">Бесплатно</span>'
            : `<span style="font-size:.78rem;color:var(--muted)">${fmtPrice(+t.price)} × ${t.quantity}</span>`}
        </div>
      </div>
      <button class="cart-item-remove" onclick="removeItem(${+t.ticket_id})" title="Удалить">✕</button>
    </div>
  `).join('');
  renderSidebar();
}

function renderSidebar() {
  const sb = document.getElementById('order-sidebar');
  if (!sb) return;
  if (!cartItems.length) { sb.innerHTML = ''; return; }
  const total = cartItems.reduce((s, t) => s + +t.price * +t.quantity, 0);
  const count = cartItems.reduce((s, t) => s + +t.quantity, 0);
  const allFree = cartItems.every(t => +t.price === 0);
  sb.innerHTML = `
    <div class="order-card">
      <div class="order-title">Ваш заказ</div>
      ${cartItems.map(t => `
        <div class="order-row">
          <span style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escHtml(t.title)}</span>
          <span>${fmtPrice(+t.price * +t.quantity)}</span>
        </div>
      `).join('')}
      ${!allFree ? `
      <div style="margin:14px 0 0;display:flex;gap:8px">
        <input id="promo-input" type="text" placeholder="Промокод" style="flex:1;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:.85rem;background:var(--surface);color:var(--text);font-family:var(--font);outline:none;text-transform:uppercase"
          oninput="this.value=this.value.toUpperCase()" onkeydown="if(event.key==='Enter')applyPromo()">
        <button class="btn btn-secondary" style="padding:9px 14px;font-size:.82rem;white-space:nowrap" onclick="applyPromo()">Применить</button>
      </div>
      <div id="promo-result" style="font-size:.8rem;margin-top:6px"></div>` : ''}
      <div class="order-total" id="order-total-row">
        <span>Итого (${count} ${plural(count,'билет','билета','билетов')})</span>
        <span style="color:var(--accent)" id="order-total-val">${fmtPrice(total)}</span>
      </div>
      <button class="btn btn-primary btn-full" style="margin-top:20px" onclick="openPayModal()">
        ${allFree ? '✓ Оформить бесплатно' : '💳 Перейти к оплате'}
      </button>
      <div style="margin-top:10px;font-size:.75rem;color:var(--muted);text-align:center">🔒 Безопасная оплата</div>
    </div>`;
}

async function changeQty(id, qty) {
  if (qty < 1) return removeItem(id);
  try {
    await put('/tickets/' + id, { quantity: qty });
    const item = cartItems.find(t => +t.ticket_id === id);
    if (item) item.quantity = qty;
    renderCart();
    updateCartBadge();
  } catch(e) { toast(e.message, 'error'); }
}

async function removeItem(id) {
  try {
    await del('/tickets/' + id);
    cartItems = cartItems.filter(t => +t.ticket_id !== id);
    renderCart();
    updateCartBadge();
    toast('Удалено из корзины');
  } catch(e) { toast(e.message, 'error'); }
}

function openPayModal() {
  const total = cartItems.reduce((s, t) => s + +t.price * +t.quantity, 0);
  if (total === 0) { confirmPayMethod('free'); return; }
  selectedPayMethod = null;
  document.querySelectorAll('.pay-method').forEach(el => el.classList.remove('selected'));
  document.getElementById('pay-confirm-btn').disabled = true;
  openModal('modal-pay');
}

function selectPay(method, el) {
  selectedPayMethod = method;
  document.querySelectorAll('.pay-method').forEach(e => e.classList.remove('selected'));
  el.classList.add('selected');
  const btn = document.getElementById('pay-confirm-btn');
  btn.disabled = false;
  btn.textContent = 'Оплатить ' + fmtPrice(cartItems.reduce((s,t) => s + +t.price * +t.quantity, 0));
}

let appliedPromo = null;

async function applyPromo() {
  const code = document.getElementById('promo-input')?.value.trim();
  const res = document.getElementById('promo-result');
  if (!code) { if (res) res.innerHTML = ''; appliedPromo = null; return; }
  try {
    const data = await post('/promo/validate', { code });
    appliedPromo = data;
    const discountStr = data.discount_type === 'percent'
      ? `−${data.discount_value}%`
      : `−${fmtPrice(data.discount_value)}`;
    if (res) res.innerHTML = `<span style="color:var(--accent2)">✓ Промокод применён: ${escHtml(data.code)} (${discountStr})</span>`;
    const totalEl = document.getElementById('order-total-val');
    if (totalEl) totalEl.innerHTML = `<span style="text-decoration:line-through;opacity:.5;font-size:.85em;margin-right:6px">${fmtPrice(cartItems.reduce((s,t)=>s+ +t.price* +t.quantity,0))}</span>${fmtPrice(data.final_total)}`;
  } catch(e) {
    appliedPromo = null;
    if (res) res.innerHTML = `<span style="color:var(--accent)">${escHtml(e.message)}</span>`;
  }
}

async function confirmPay() {
  if (!selectedPayMethod) return;
  closeModal('modal-pay');
  await confirmPayMethod(selectedPayMethod);
}

async function confirmPayMethod(method) {
  try {
    const result = await post('/tickets/checkout', { payment_method: method, promo_code: appliedPromo?.code ?? null });
    const paid = result.paid ?? result.data?.paid ?? 0;
    cartItems = [];
    updateCartBadge();
    document.getElementById('tab-cart').innerHTML = `
      <div style="text-align:center;padding:60px 24px">
        <div style="font-size:3.5rem;margin-bottom:16px">🎉</div>
        <div style="font-family:var(--font-display);font-size:1.8rem;font-weight:700;margin-bottom:8px">Готово!</div>
        <div style="color:var(--muted);margin-bottom:24px">
          ${paid} ${plural(paid,'билет','билета','билетов')} успешно оформлен${paid===1?'':'о'}.<br>
          Способ оплаты: <strong>${payLabel(method)}</strong>
        </div>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
          <button class="btn btn-secondary" onclick="showTab('paid',null)">Мои билеты</button>
          <a href="${window.APP_BASE||''}" class="btn btn-primary">Найти ещё события</a>
        </div>
      </div>`;
    document.getElementById('order-sidebar').innerHTML = '';
  } catch(e) { toast(e.message, 'error'); }
}

function showTab(tab, el) {
  document.getElementById('tab-cart').style.display = tab === 'cart' ? '' : 'none';
  document.getElementById('tab-paid').style.display = tab === 'paid' ? '' : 'none';
  document.querySelectorAll('.cart-tab').forEach(t => t.classList.remove('active'));
  if (el) el.classList.add('active');
  else document.querySelectorAll('.cart-tab')[tab === 'paid' ? 1 : 0]?.classList.add('active');
  if (tab === 'paid') { loadPaid(); document.getElementById('order-sidebar').innerHTML = ''; }
  else renderSidebar();
}

loadCart();
</script>
@endsection
