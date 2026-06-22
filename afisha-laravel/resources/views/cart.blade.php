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

  @keyframes cartItemIn {
    from { opacity:0; transform:translateY(10px); }
    to   { opacity:1; transform:translateY(0); }
  }
  @keyframes numPop {
    0%   { transform:scale(1); }
    40%  { transform:scale(1.22); color:var(--accent); }
    100% { transform:scale(1); }
  }
  .num-pop { animation: numPop .22s ease; }

  .cart-item {
    display:flex; gap:16px; align-items:flex-start;
    background:var(--surface); border:1px solid var(--border);
    border-radius:var(--radius); padding:18px; margin-bottom:12px;
    overflow:hidden;
    animation: cartItemIn .28s ease backwards;
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

  /* ── Pager ── */
  .pager { display:flex; gap:4px; align-items:center; justify-content:center; margin-top:24px; flex-wrap:wrap; padding-bottom:4px; }
  .pager-btn { min-width:36px; height:36px; padding:0 10px; border-radius:8px; border:1.5px solid var(--border); background:var(--surface); color:var(--text); cursor:pointer; font-size:.85rem; font-weight:600; transition:var(--transition); display:inline-flex; align-items:center; justify-content:center; }
  .pager-btn:hover:not([disabled]) { border-color:var(--accent); color:var(--accent); }
  .pager-btn.pager-active { background:var(--accent); border-color:var(--accent); color:#fff; pointer-events:none; }
  .pager-btn[disabled] { opacity:.35; cursor:not-allowed; pointer-events:none; }
  .pager-gap { color:var(--muted); padding:0 4px; line-height:36px; }
  .pager-info { font-size:.78rem; color:var(--muted); padding:0 6px; white-space:nowrap; }

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

<!-- Простое модальное окно подтверждения оплаты -->
<div class="modal-overlay" id="modal-pay" onclick="if(event.target===this)closeModal('modal-pay')">
  <div class="modal" style="max-width:400px">
    <div class="modal-title">Подтверждение заказа</div>
    <div id="modal-pay-body" style="margin-bottom:20px;color:var(--muted);font-size:.9rem"></div>
    <div style="display:flex;gap:10px">
      <button class="btn btn-primary" id="pay-confirm-btn" onclick="doConfirmPay()" style="flex:1">Оплатить</button>
      <button class="btn btn-secondary" onclick="closeModal('modal-pay')">Отмена</button>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/qrcode.min.js') }}"></script>
<script>
(function() {
  try {
    const token = localStorage.getItem('token');
    const type  = localStorage.getItem('auth_type');
    if (!token || JSON.parse(type) !== 'user') { nav('/login'); }
  } catch(e) { nav('/login'); }
})();

let cartItems = [];

/* ── Pagination ── */
const CART_PER_PAGE = 8;
const PAID_PER_PAGE = 6;
let _cartPage  = 1;
let _paidItems = [];
let _paidPage  = 1;

function paginate(arr, page, perPage) {
  return arr.slice((page - 1) * perPage, page * perPage);
}
function renderPager(total, page, perPage, goCb) {
  const pages = Math.ceil(total / perPage);
  if (pages <= 1) return '';
  const range = [];
  for (let i = 1; i <= pages; i++) {
    if (i === 1 || i === pages || (i >= page - 1 && i <= page + 1)) range.push(i);
    else if (range[range.length - 1] !== '…') range.push('…');
  }
  const from = (page - 1) * perPage + 1, to = Math.min(page * perPage, total);
  return `<div class="pager">
    <button class="pager-btn" onclick="${goCb}(${page-1})" ${page===1?'disabled':''}>‹</button>
    ${range.map(v => v==='…'
      ? `<span class="pager-gap">…</span>`
      : `<button class="pager-btn${v===page?' pager-active':''}" onclick="${goCb}(${v})">${v}</button>`
    ).join('')}
    <button class="pager-btn" onclick="${goCb}(${page+1})" ${page===pages?'disabled':''}>›</button>
    <span class="pager-info">${from}–${to} из ${total}</span>
  </div>`;
}
function goCartPage(p) { _cartPage = p; renderCart(); document.getElementById('tab-cart').scrollIntoView({behavior:'smooth',block:'start'}); }
function goPaidPage(p) { _paidPage = p; renderPaidList(); document.getElementById('tab-paid').scrollIntoView({behavior:'smooth',block:'start'}); }

async function loadCart() {
  const list = document.getElementById('cart-list');
  list.innerHTML = '<div class="loader"><div class="spinner"></div></div>';
  _cartPage = 1;
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
  _paidPage = 1;
  try {
    const data = await get('/tickets/paid');
    _paidItems = Array.isArray(data) ? data : [];
    renderPaidList();
  } catch(e) {
    list.innerHTML = errBlock(e.message, 'loadPaid()');
  }
}

function renderPaidList() {
  const list = document.getElementById('paid-list');
  if (!list) return;
  if (!_paidItems.length) {
    list.innerHTML = `<div class="empty"><div class="empty-icon">🎫</div>
      <div>У вас пока нет оплаченных билетов</div>
      <div style="font-size:.82rem;color:var(--muted);margin-top:6px">
        <a href="${window.APP_BASE||''}" style="color:var(--accent);font-weight:600">Найти события</a>
      </div>
    </div>`;
    return;
  }
  const page = paginate(_paidItems, _paidPage, PAID_PER_PAGE);
  list.innerHTML = page.map((t, i) => `
    <div class="ticket-card mod-item-enter" onclick="nav('/event/${t.event_id}')" style="cursor:pointer;animation-delay:${i*50}ms">
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
        <div class="qr-canvas" data-qr="TICKET:${t.ticket_id}:${t.event_id}:${t.quantity}" style="width:64px;height:64px"></div>
        <div class="paid-badge">✓ ОПЛАЧЕНО</div>
        <div class="ticket-pay-lbl">${payLabel(t.payment_method)}</div>
      </div>
    </div>
  `).join('') + renderPager(_paidItems.length, _paidPage, PAID_PER_PAGE, 'goPaidPage');
  renderQrCodes();
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
  document.querySelectorAll('[data-qr]').forEach(el => {
    const data = el.dataset.qr;
    if (!data) return;
    if (window.QRCode) {
      el.innerHTML = '';
      new QRCode(el, { text: data, width: 64, height: 64, colorDark: '#000000', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.H });
    } else {
      setTimeout(renderQrCodes, 150);
    }
  });
}

function payLabel(m) {
  const map = { online:'Онлайн-оплата', free:'Бесплатно', sbp:'СБП', card:'Банковская карта' };
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
  if (_cartPage > 1 && paginate(cartItems, _cartPage, CART_PER_PAGE).length === 0) _cartPage--;
  const page = paginate(cartItems, _cartPage, CART_PER_PAGE);
  list.innerHTML = page.map((t, i) => `
    <div class="cart-item" id="ci-${t.ticket_id}" style="animation-delay:${i * 55}ms">
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
  `).join('') + renderPager(cartItems.length, _cartPage, CART_PER_PAGE, 'goCartPage');
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
      <button class="btn btn-primary btn-full" style="margin-top:20px" onclick="doCheckout()">
        ${allFree ? '✓ Оформить бесплатно' : '✓ Оплатить'}
      </button>
    </div>`;
}

function popEl(el) {
  if (!el) return;
  el.classList.remove('num-pop');
  void el.offsetWidth;
  el.classList.add('num-pop');
}

async function changeQty(id, qty) {
  if (qty < 1) return removeItem(id);
  const item = cartItems.find(t => +t.ticket_id === id);
  if (!item) return;
  const prevQty = item.quantity;
  item.quantity = qty;

  const el = document.getElementById('ci-' + id);
  if (el) {
    const qtyEl   = el.querySelector('.qty-val');
    const priceEl = el.querySelector('.cart-item-price');
    if (qtyEl)   { qtyEl.textContent   = qty; popEl(qtyEl); }
    if (priceEl) { priceEl.textContent = fmtPrice(+item.price * qty); popEl(priceEl); }
    const btns = el.querySelectorAll('.qty-btn');
    if (btns[0]) btns[0].setAttribute('onclick', `changeQty(${id},${qty-1})`);
    if (btns[1]) btns[1].setAttribute('onclick', `changeQty(${id},${qty+1})`);
  }
  renderSidebar();

  try {
    await put('/tickets/' + id, { quantity: qty });
    updateCartBadge();
  } catch(e) {
    item.quantity = prevQty;
    renderCart();
    toast(e.message, 'error');
  }
}

async function removeItem(id) {
  const el = document.getElementById('ci-' + id);
  if (el) {
    el.style.maxHeight = el.offsetHeight + 'px';
    el.style.transition = 'opacity .22s, transform .22s, max-height .28s ease, margin-bottom .28s, padding .22s';
    requestAnimationFrame(() => {
      el.style.opacity = '0';
      el.style.transform = 'translateX(18px)';
      el.style.maxHeight = '0';
      el.style.marginBottom = '0';
      el.style.paddingTop = '0';
      el.style.paddingBottom = '0';
    });
    await new Promise(r => setTimeout(r, 300));
  }
  try {
    await del('/tickets/' + id);
    cartItems = cartItems.filter(t => +t.ticket_id !== id);
    renderCart();
    updateCartBadge();
    toast('Удалено из корзины');
  } catch(e) {
    if (el) el.removeAttribute('style');
    toast(e.message, 'error');
  }
}

function doCheckout() {
  const total = cartItems.reduce((s, t) => s + +t.price * +t.quantity, 0);
  const count = cartItems.reduce((s, t) => s + +t.quantity, 0);
  if (total === 0) { confirmPayMethod('free'); return; }
  const body = document.getElementById('modal-pay-body');
  if (body) {
    body.innerHTML = `
      <div style="margin-bottom:12px">${count} ${plural(count,'билет','билета','билетов')} на сумму <strong style="color:var(--accent)">${fmtPrice(total)}</strong></div>
      <div style="font-size:.8rem;opacity:.7">Нажмите «Оплатить» для завершения заказа</div>`;
  }
  const btn = document.getElementById('pay-confirm-btn');
  if (btn) { btn.disabled = false; btn.textContent = 'Оплатить ' + fmtPrice(total); }
  openModal('modal-pay');
}

function doConfirmPay() {
  const btn = document.getElementById('pay-confirm-btn');
  if (btn) { btn.disabled = true; btn.textContent = 'Оформляем…'; }
  closeModal('modal-pay');
  confirmPayMethod('online');
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

async function confirmPayMethod(method) {
  try {
    const result = await post('/tickets/checkout', { payment_method: method, promo_code: appliedPromo?.code ?? null });
    const paid = (result && result.paid != null) ? result.paid : 0;
    cartItems = [];
    appliedPromo = null;
    updateCartBadge();
    const tabCart = document.getElementById('tab-cart');
    if (tabCart) tabCart.innerHTML = `
      <div style="text-align:center;padding:60px 24px">
        <div style="font-size:3.5rem;margin-bottom:16px">🎉</div>
        <div style="font-family:var(--font-display);font-size:1.8rem;font-weight:700;margin-bottom:8px">Готово!</div>
        <div style="color:var(--muted);margin-bottom:24px">
          ${paid} ${plural(paid,'билет','билета','билетов')} успешно оформлен${paid===1?'':'о'}.
        </div>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
          <button class="btn btn-secondary" onclick="showTab('paid',null)">Мои билеты</button>
          <a href="${window.APP_BASE||''}" class="btn btn-primary">Найти ещё события</a>
        </div>
      </div>`;
    const sb = document.getElementById('order-sidebar');
    if (sb) sb.innerHTML = '';
  } catch(e) {
    const btn = document.getElementById('pay-confirm-btn');
    if (btn) { btn.disabled = false; btn.textContent = 'Оплатить'; }
    toast('Ошибка: ' + e.message, 'error');
  }
}

async function showTab(tab, el) {
  const fromId = tab === 'cart' ? 'tab-paid' : 'tab-cart';
  const toId   = 'tab-' + tab;
  const fromEl = document.getElementById(fromId);
  const toEl   = document.getElementById(toId);

  fromEl.style.transition = 'opacity .15s';
  fromEl.style.opacity = '0';
  await new Promise(r => setTimeout(r, 160));
  fromEl.style.display = 'none';
  fromEl.style.opacity = '';
  fromEl.style.transition = '';

  toEl.style.display = '';
  toEl.style.opacity = '0';
  toEl.style.transition = 'opacity .2s';
  requestAnimationFrame(() => requestAnimationFrame(() => {
    toEl.style.opacity = '1';
    setTimeout(() => { toEl.style.opacity = ''; toEl.style.transition = ''; }, 220);
  }));

  document.querySelectorAll('.cart-tab').forEach(t => t.classList.remove('active'));
  if (el) el.classList.add('active');
  else document.querySelectorAll('.cart-tab')[tab === 'paid' ? 1 : 0]?.classList.add('active');

  if (tab === 'paid') { loadPaid(); document.getElementById('order-sidebar').innerHTML = ''; }
  else renderSidebar();
}

loadCart();
</script>
@endsection
