@extends('layouts.app')

@section('title', 'Мои билеты — АфишаКолыма')

@section('styles')
<style>
  .tickets-layout { max-width: 860px; margin: 0 auto; padding: 40px 32px; }
  @media(max-width:600px){ .tickets-layout{ padding: 24px 16px; } }

  .page-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; flex-wrap: wrap; gap: 12px; }
  .page-title { font-family: var(--font-display); font-size: 2rem; font-weight: 700; }

  /* ── Ticket card ── */
  .ticket-card {
    display: flex; background: var(--surface);
    border: 1.5px solid var(--border); border-radius: var(--radius);
    overflow: hidden; margin-bottom: 16px;
    transition: box-shadow .3s, border-color .3s, transform .3s;
    box-shadow: var(--shadow-card);
  }
  .ticket-card:hover { box-shadow: var(--shadow-hover); border-color: rgba(200,80,42,.3); transform: translateY(-2px); }
  .ticket-img-col {
    width: 160px; flex-shrink: 0;
    background: var(--bg2); display: flex; align-items: center; justify-content: center;
    font-size: 3rem; min-height: 150px; cursor: pointer;
  }
  .ticket-img-col img { width: 100%; height: 100%; object-fit: cover; }
  .ticket-body { flex: 1; padding: 18px 20px; cursor: pointer; }
  .ticket-tag { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--accent); margin-bottom: 6px; }
  .ticket-title { font-family: var(--font-display); font-size: 1.05rem; font-weight: 700; line-height: 1.3; margin-bottom: 8px; }
  .ticket-meta { font-size: .8rem; color: var(--muted); margin-bottom: 4px; }
  .ticket-sep { border: none; border-top: 1.5px dashed var(--border); margin: 12px 0; }
  .ticket-footer { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
  .ticket-qty { font-size: .82rem; color: var(--muted); }
  .ticket-price { font-weight: 700; color: var(--accent); font-size: 1rem; margin-left: auto; }
  .ticket-return-btn {
    font-size: .72rem; color: var(--muted); background: none;
    border: 1px solid var(--border); border-radius: 20px; padding: 3px 10px;
    cursor: pointer; transition: var(--transition);
  }
  .ticket-return-btn:hover { border-color: var(--accent); color: var(--accent); }

  /* ── QR side ── */
  .ticket-side {
    width: 120px; flex-shrink: 0;
    border-left: 1.5px dashed var(--border);
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; gap: 8px; padding: 16px 12px;
    cursor: pointer; transition: background .2s;
  }
  .ticket-side:hover { background: rgba(200,80,42,.04); }
  .qr-wrap {
    background: #fff; border-radius: 6px; padding: 5px;
    display: inline-flex; position: relative;
  }
  .qr-canvas { display: block; border-radius: 2px; }
  .qr-zoom-hint { font-size: .6rem; color: var(--muted); margin-top: 2px; }
  .paid-badge {
    background: rgba(42,110,90,.12); color: var(--accent2);
    padding: 4px 8px; border-radius: 20px;
    font-size: .65rem; font-weight: 700; letter-spacing: .05em; white-space: nowrap;
  }
  .ticket-pay-lbl { font-size: .65rem; color: var(--muted); text-align: center; }

  @media(max-width:600px){
    .ticket-card{ flex-direction:column; }
    .ticket-img-col{ width:100%; height:130px; min-height:unset; }
    .ticket-side{ width:100%; border-left:none; border-top:1.5px dashed var(--border); flex-direction:row; padding:12px 16px; justify-content:flex-start; gap:16px; }
  }

  /* ── Ticket detail modal ── */
  .tmodal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.6); z-index: 600;
    display: flex; align-items: center; justify-content: center; padding: 20px;
    animation: fadeInBg .18s ease;
  }
  @keyframes fadeInBg { from{opacity:0} to{opacity:1} }
  .tmodal {
    background: var(--surface); border: 1.5px solid var(--border);
    border-radius: 20px; width: 100%; max-width: 520px;
    max-height: 90vh; overflow-y: auto;
    box-shadow: 0 24px 64px rgba(0,0,0,.4);
    animation: slideUp .22s ease;
  }
  @keyframes slideUp { from{transform:translateY(20px);opacity:0} to{transform:translateY(0);opacity:1} }
  .tmodal-head {
    display: flex; align-items: flex-start; justify-content: space-between;
    padding: 24px 24px 0;
  }
  .tmodal-title { font-family: var(--font-display); font-size: 1.2rem; font-weight: 800; line-height: 1.3; }
  .tmodal-close { background: none; border: none; color: var(--muted); cursor: pointer; font-size: 1.3rem; line-height: 1; padding: 0; margin-left: 12px; flex-shrink: 0; transition: color .15s; }
  .tmodal-close:hover { color: var(--text); }
  .tmodal-body { padding: 20px 24px; }
  .tmodal-qr-row {
    display: flex; gap: 20px; align-items: flex-start;
    background: var(--bg2); border-radius: 12px; padding: 16px; margin-bottom: 20px;
  }
  .tmodal-qr-block { flex-shrink: 0; }
  .tmodal-qr-wrap { background: #fff; border-radius: 8px; padding: 8px; display: inline-flex; }
  .tmodal-qr-info { flex: 1; min-width: 0; }
  .tmodal-ticket-id { font-size: .72rem; color: var(--muted); font-weight: 600; letter-spacing: .05em; text-transform: uppercase; margin-bottom: 6px; }
  .tmodal-paid-badge { display: inline-block; background: rgba(42,110,90,.15); color: var(--accent2); padding: 4px 12px; border-radius: 20px; font-size: .72rem; font-weight: 700; letter-spacing: .06em; margin-bottom: 10px; }
  .tmodal-row { display: flex; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--border); font-size: .86rem; }
  .tmodal-row:last-child { border-bottom: none; }
  .tmodal-lbl { color: var(--muted); width: 100px; flex-shrink: 0; font-size: .8rem; }
  .tmodal-val { flex: 1; font-weight: 500; }
  .tmodal-actions { display: flex; gap: 10px; padding: 0 24px 24px; flex-wrap: wrap; }
  .tmodal-actions .btn { flex: 1; min-width: 120px; }

  /* ── QR zoom modal ── */
  .qrzoom-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.8); z-index: 700;
    display: flex; align-items: center; justify-content: center;
    animation: fadeInBg .15s ease; cursor: pointer;
  }
  .qrzoom-box {
    background: #fff; border-radius: 16px; padding: 20px;
    display: flex; flex-direction: column; align-items: center; gap: 12px;
    animation: slideUp .2s ease; cursor: default;
  }
  .qrzoom-label { font-size: .82rem; color: #333; font-weight: 600; text-align: center; }
  .qrzoom-id { font-size: .72rem; color: #888; }
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

<!-- Ticket detail modal -->
<div class="tmodal-overlay" id="tmodal" style="display:none" onclick="if(event.target===this)closeTModal()">
  <div class="tmodal">
    <div class="tmodal-head">
      <div class="tmodal-title" id="tmodal-title">Билет</div>
      <button class="tmodal-close" onclick="closeTModal()">✕</button>
    </div>
    <div class="tmodal-body">
      <div class="tmodal-qr-row">
        <div class="tmodal-qr-block">
          <div class="tmodal-qr-wrap">
            <div id="tmodal-qr" style="width:140px;height:140px"></div>
          </div>
          <div style="text-align:center;margin-top:6px;font-size:.65rem;color:var(--muted)">Нажмите для увеличения</div>
        </div>
        <div class="tmodal-qr-info">
          <div class="tmodal-ticket-id" id="tmodal-ticket-id"></div>
          <div class="tmodal-paid-badge">✓ ОПЛАЧЕНО</div>
          <div style="font-size:.82rem;color:var(--muted)" id="tmodal-payment"></div>
        </div>
      </div>
      <div id="tmodal-rows"></div>
    </div>
    <div class="tmodal-actions">
      <button class="btn btn-secondary" onclick="openQrZoom()">🔍 QR на весь экран</button>
      <button class="btn btn-primary" onclick="downloadPdf()">⬇ Скачать PDF</button>
      <button class="ticket-return-btn" style="border-radius:8px;padding:10px 16px;font-size:.83rem" id="tmodal-return-btn" onclick="doReturn()">Возврат</button>
    </div>
  </div>
</div>

<!-- QR zoom modal -->
<div class="qrzoom-overlay" id="qrzoom" style="display:none" onclick="closeQrZoom()">
  <div class="qrzoom-box" onclick="event.stopPropagation()">
    <div id="qrzoom-canvas" style="width:256px;height:256px"></div>
    <div class="qrzoom-label" id="qrzoom-label"></div>
    <div class="qrzoom-id" id="qrzoom-id"></div>
    <button class="btn btn-secondary btn-sm" onclick="closeQrZoom()">Закрыть</button>
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
    if (!token || JSON.parse(type) !== 'user') nav('/login');
  } catch(e) { nav('/login'); }
})();

let _tickets = [];
let _activeTicket = null;

function plural(n, one, two, five) {
  const m = Math.abs(n) % 100;
  if (m >= 11 && m <= 14) return five;
  const m1 = m % 10;
  if (m1 === 1) return one; if (m1 >= 2 && m1 <= 4) return two;
  return five;
}
function payLabel(m) {
  const map = { sbp:'СБП', card:'Банковская карта', sber:'СберПей', ymoney:'ЮMoney', tpay:'T-Pay', free:'Бесплатно', cash:'Наличные' };
  return map[m] || m || '—';
}

/* ── QR rendering ── */
function renderQr(el, data, size) {
  if (!el || !data) return;
  if (window.QRCode) {
    el.innerHTML = '';
    new QRCode(el, {
      text: data, width: size, height: size,
      colorDark: '#000000', colorLight: '#ffffff',
      correctLevel: QRCode.CorrectLevel.H,
    });
  } else {
    setTimeout(() => renderQr(el, data, size), 150);
  }
}

/* ── Load tickets ── */
async function loadTickets() {
  const list = document.getElementById('tickets-list');
  try {
    _tickets = await get('/tickets/paid');
    if (!Array.isArray(_tickets) || !_tickets.length) {
      list.innerHTML = `<div class="empty">
        <div class="empty-icon">🎫</div>
        <div>У вас пока нет оплаченных билетов</div>
        <div style="font-size:.82rem;color:var(--muted);margin-top:8px">
          <a href="${window.APP_BASE||''}" style="color:var(--accent);font-weight:600">Найти события</a>
        </div>
      </div>`;
      return;
    }
    list.innerHTML = _tickets.map(t => {
      const isPending = t.status === 'return_pending';
      return `
      <div class="ticket-card" id="tcard-${t.ticket_id}">
        <div class="ticket-img-col" onclick="nav('/event/${t.event_id}')">
          ${t.image ? `<img src="${escHtml(t.image)}" alt="">` : '🎭'}
        </div>
        <div class="ticket-body" onclick="openTicket(${t.ticket_id})">
          <div class="ticket-tag">${escHtml(t.category_name || 'Событие')}</div>
          <div class="ticket-title">${escHtml(t.title)}</div>
          <div class="ticket-meta">📅 ${fmtDate(t.start_datetime)}</div>
          ${t.venue_name ? `<div class="ticket-meta">📍 ${escHtml(t.venue_name)}</div>` : ''}
          <hr class="ticket-sep">
          <div class="ticket-footer">
            <span class="ticket-qty">🎫 ${t.quantity} ${plural(t.quantity,'билет','билета','билетов')}</span>
            ${isPending
              ? `<span class="ticket-return-btn" style="cursor:default;color:#d97706;border-color:rgba(245,158,11,.4)">⏳ Ожидает возврата</span>`
              : `<button class="ticket-return-btn" onclick="event.stopPropagation();returnTicket(${t.ticket_id})">Возврат</button>`
            }
            <span class="ticket-price">${fmtPrice(+t.price * +t.quantity)}</span>
          </div>
        </div>
        <div class="ticket-side" onclick="openTicket(${t.ticket_id})" style="${isPending ? 'opacity:.55' : ''}">
          <div class="qr-wrap">
            <div class="qr-canvas" id="qr-${t.ticket_id}" style="width:72px;height:72px"></div>
          </div>
          <div class="qr-zoom-hint">нажмите</div>
          ${isPending
            ? `<div class="paid-badge" style="background:rgba(245,158,11,.15);color:#d97706">⏳ ВОЗВРАТ</div>`
            : `<div class="paid-badge">✓ ОПЛАЧЕНО</div>`
          }
          <div class="ticket-pay-lbl">${payLabel(t.payment_method)}</div>
        </div>
      </div>
    `}).join('');

    /* Render small QR codes after DOM inserted */
    _tickets.forEach(t => {
      renderQr(document.getElementById('qr-' + t.ticket_id), `TICKET:${t.ticket_id}:${t.event_id}:${t.quantity}`, 72);
    });

  } catch(e) {
    list.innerHTML = `<div class="empty" style="padding:40px 24px">
      <div class="empty-icon">⚠️</div>
      <div style="font-weight:600;margin-bottom:6px">Ошибка загрузки</div>
      <div style="font-size:.82rem;color:var(--muted);margin-bottom:16px">${escHtml(e.message)}</div>
      <button class="btn btn-primary" onclick="loadTickets()">Повторить</button>
    </div>`;
  }
}

/* ── Ticket detail modal ── */
function openTicket(ticketId) {
  const t = _tickets.find(x => x.ticket_id == ticketId);
  if (!t) return;
  _activeTicket = t;
  const isPending = t.status === 'return_pending';

  document.getElementById('tmodal-title').textContent = t.title;
  document.getElementById('tmodal-ticket-id').textContent = `Билет №${t.ticket_id}`;
  document.getElementById('tmodal-payment').textContent = payLabel(t.payment_method);

  const badge = document.querySelector('#tmodal .tmodal-paid-badge');
  if (badge) {
    badge.textContent = isPending ? '⏳ ОЖИДАЕТ ВОЗВРАТА' : '✓ ОПЛАЧЕНО';
    badge.style.background = isPending ? 'rgba(245,158,11,.15)' : '';
    badge.style.color = isPending ? '#d97706' : '';
  }
  const returnBtn = document.getElementById('tmodal-return-btn');
  if (returnBtn) returnBtn.style.display = isPending ? 'none' : '';

  const rows = [
    ['Категория',   t.category_name  || '—'],
    ['Дата',        fmtDate(t.start_datetime)],
    ['Площадка',    t.venue_name     || '—'],
    ['Количество',  t.quantity + ' ' + plural(t.quantity,'билет','билета','билетов')],
    ['Сумма',       fmtPrice(+t.price * +t.quantity)],
    ['Оплачено',    t.paid_at ? fmtDate(t.paid_at) : '—'],
  ];
  document.getElementById('tmodal-rows').innerHTML = rows.map(([l, v]) =>
    `<div class="tmodal-row"><div class="tmodal-lbl">${l}</div><div class="tmodal-val">${escHtml(String(v))}</div></div>`
  ).join('');

  const qrData = `TICKET:${t.ticket_id}:${t.event_id}:${t.quantity}`;
  const qrEl   = document.getElementById('tmodal-qr');
  qrEl.dataset.qr = qrData;
  renderQr(qrEl, qrData, 140);

  document.getElementById('tmodal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function closeTModal() {
  document.getElementById('tmodal').style.display = 'none';
  document.body.style.overflow = '';
  _activeTicket = null;
}

/* ── QR zoom ── */
function openQrZoom() {
  if (!_activeTicket) return;
  const t = _activeTicket;
  const qrData = `TICKET:${t.ticket_id}:${t.event_id}:${t.quantity}`;
  document.getElementById('qrzoom-label').textContent = t.title;
  document.getElementById('qrzoom-id').textContent    = `Билет №${t.ticket_id}`;
  renderQr(document.getElementById('qrzoom-canvas'), qrData, 256);
  document.getElementById('qrzoom').style.display = 'flex';
}
function closeQrZoom() { document.getElementById('qrzoom').style.display = 'none'; }

/* ── Return ticket ── */
async function returnTicket(ticketId) {
  if (!confirm('Запросить возврат этого билета? Средства вернутся в течение 3–5 рабочих дней.')) return;
  try {
    await post('/tickets/' + ticketId + '/return', {});
    toast('Заявка на возврат принята', 'success');
    closeTModal();
    loadTickets();
  } catch(e) {
    toast(e.message || 'Возврат временно недоступен', 'error');
  }
}
function doReturn() {
  if (_activeTicket) returnTicket(_activeTicket.ticket_id);
}

/* ── Download PDF ── */
function downloadPdf() {
  const t = _activeTicket;
  if (!t) return;

  const qrDiv    = document.getElementById('tmodal-qr');
  const qrCanvas = qrDiv ? qrDiv.querySelector('canvas') : null;
  const qrImg    = qrCanvas ? qrCanvas.toDataURL('image/png') : '';

  const html = `<!DOCTYPE html><html lang="ru"><head>
  <meta charset="UTF-8">
  <title>Билет №${t.ticket_id}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; background: #fff; color: #111; padding: 32px; }
    .ticket {
      border: 2px solid #e5e7eb; border-radius: 16px; overflow: hidden;
      max-width: 500px; margin: 0 auto;
      box-shadow: 0 4px 24px rgba(0,0,0,.1);
    }
    .ticket-header {
      background: #c8502a; color: #fff; padding: 20px 24px;
      display: flex; justify-content: space-between; align-items: center;
    }
    .ticket-org { font-size: 13px; opacity: .85; }
    .ticket-name { font-size: 20px; font-weight: 800; margin: 4px 0; }
    .ticket-badge { background: rgba(255,255,255,.2); padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: .06em; }
    .ticket-body { padding: 24px; display: flex; gap: 20px; align-items: flex-start; }
    .ticket-info { flex: 1; }
    .info-row { display: flex; gap: 8px; padding: 7px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
    .info-row:last-child { border-bottom: none; }
    .info-lbl { color: #9ca3af; width: 90px; flex-shrink: 0; }
    .info-val { font-weight: 600; }
    .ticket-qr { flex-shrink: 0; text-align: center; }
    .ticket-qr img { width: 120px; height: 120px; border-radius: 6px; border: 1px solid #e5e7eb; }
    .ticket-qr-lbl { font-size: 10px; color: #9ca3af; margin-top: 4px; }
    .ticket-footer { background: #f9fafb; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; border-top: 2px dashed #e5e7eb; }
    .ticket-price { font-size: 22px; font-weight: 800; color: #c8502a; }
    .ticket-id { font-size: 11px; color: #9ca3af; letter-spacing: .05em; }
    @media print {
      body { padding: 0; }
      .no-print { display: none; }
    }
  </style>
</head><body>
  <div class="ticket">
    <div class="ticket-header">
      <div>
        <div class="ticket-org">АфишаКолыма</div>
        <div class="ticket-name">${escHtmlPdf(t.title)}</div>
        <div style="margin-top:6px"><span class="ticket-badge">✓ ОПЛАЧЕНО</span></div>
      </div>
    </div>
    <div class="ticket-body">
      <div class="ticket-info">
        ${t.category_name ? infoRow('Категория', t.category_name) : ''}
        ${infoRow('Дата', fmtDate(t.start_datetime))}
        ${t.venue_name ? infoRow('Площадка', t.venue_name) : ''}
        ${infoRow('Количество', t.quantity + ' ' + pluralPdf(t.quantity,'билет','билета','билетов'))}
        ${infoRow('Способ оплаты', payLabel(t.payment_method))}
      </div>
      <div class="ticket-qr">
        ${qrImg ? `<img src="${qrImg}" alt="QR">` : ''}
        <div class="ticket-qr-lbl">Предъявить на входе</div>
      </div>
    </div>
    <div class="ticket-footer">
      <div class="ticket-id">БИЛЕТ №${t.ticket_id}</div>
      <div class="ticket-price">${fmtPrice(+t.price * +t.quantity)}</div>
    </div>
  </div>
  <div class="no-print" style="text-align:center;margin-top:20px">
    <button onclick="window.print()" style="padding:10px 28px;background:#c8502a;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer">Сохранить / Печать</button>
  </div>
</body></html>`;

  const win = window.open('', '_blank', 'width=620,height=760');
  if (!win) { toast('Разрешите всплывающие окна в браузере', 'error'); return; }
  win.document.write(html);
  win.document.close();
  setTimeout(() => win.print(), 600);
}

function escHtmlPdf(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function infoRow(lbl, val) {
  return `<div class="info-row"><div class="info-lbl">${lbl}</div><div class="info-val">${escHtmlPdf(String(val||'—'))}</div></div>`;
}
function pluralPdf(n, one, two, five) {
  const m = Math.abs(n) % 100;
  if (m >= 11 && m <= 14) return five;
  const m1 = m % 10;
  if (m1 === 1) return one; if (m1 >= 2 && m1 <= 4) return two; return five;
}

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') { closeQrZoom(); closeTModal(); }
});

loadTickets();
</script>
@endsection
