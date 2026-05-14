@extends('layouts.app')

@section('title', 'Мои билеты — АфишаКолыма')

@section('styles')
<style>
  .tickets-layout { max-width: 860px; margin: 0 auto; padding: 40px 32px; }
  @media(max-width:600px){ .tickets-layout{ padding: 24px 16px; } }

  .page-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; flex-wrap: wrap; gap: 12px; }
  .page-title { font-family: var(--font-display); font-size: 2rem; font-weight: 700; }

  .ticket-card {
    display: flex; background: var(--surface);
    border: 1.5px solid var(--border); border-radius: var(--radius);
    overflow: hidden; margin-bottom: 16px;
    transition: box-shadow .3s cubic-bezier(.4,0,.2,1), border-color .3s, transform .3s cubic-bezier(.4,0,.2,1);
    box-shadow: var(--shadow-card);
  }
  .ticket-card:hover { box-shadow: var(--shadow-hover); border-color: rgba(200,80,42,.3); transform: translateY(-2px); }
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
</style>
@endsection

@section('content')
<div class="tickets-layout page-enter">
  <div class="page-head">
    <h1 class="page-title">🎫 Мои билеты</h1>
    <a href="{{ url('/cart') }}" class="btn btn-secondary btn-sm">Корзина</a>
  </div>
  <div id="tickets-list"><div class="loader"><div class="spinner"></div></div></div>
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

function plural(n, one, two, five) {
  const m = Math.abs(n) % 100;
  if (m >= 11 && m <= 14) return five;
  const m1 = m % 10;
  if (m1 === 1) return one;
  if (m1 >= 2 && m1 <= 4) return two;
  return five;
}

function payLabel(m) {
  const map = { sbp:'СБП', card:'Банковская карта', sber:'СберПей', ymoney:'ЮMoney', tpay:'T-Pay', free:'Бесплатно' };
  return map[m] || m || '';
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

async function loadTickets() {
  const list = document.getElementById('tickets-list');
  try {
    const items = await get('/tickets/paid');
    if (!Array.isArray(items) || !items.length) {
      list.innerHTML = `<div class="empty">
        <div class="empty-icon">🎫</div>
        <div>У вас пока нет оплаченных билетов</div>
        <div style="font-size:.82rem;color:var(--muted);margin-top:8px">
          <a href="/" style="color:var(--accent);font-weight:600">Найти события</a>
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
            <button class="ticket-return-btn" onclick="event.stopPropagation();returnTicket(${t.ticket_id})">Возврат</button>
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
    list.innerHTML = `<div class="empty" style="padding:40px 24px">
      <div class="empty-icon">⚠️</div>
      <div style="font-weight:600;margin-bottom:6px">Ошибка загрузки</div>
      <div style="font-size:.82rem;color:var(--muted);margin-bottom:16px">${escHtml(e.message)}</div>
      <button class="btn btn-primary" onclick="loadTickets()">Повторить</button>
    </div>`;
  }
}

async function returnTicket(ticketId) {
  if (!confirm('Запросить возврат этого билета? Средства вернутся в течение 3–5 рабочих дней.')) return;
  try {
    await post('/tickets/' + ticketId + '/return', {});
    toast('Заявка на возврат принята', 'success');
    loadTickets();
  } catch(e) {
    toast(e.message || 'Возврат временно недоступен', 'error');
  }
}

loadTickets();
</script>
@endsection
