@extends('layouts.app')

@section('title', 'История просмотров — АфишаКолыма')

@section('styles')
<style>
  .page-hero { background:var(--hero-bg); color:#fff; padding:56px 32px 48px; text-align:center; position:relative; overflow:hidden; }
  .page-hero::before { content:''; position:absolute; inset:0; background:radial-gradient(ellipse at 30% 50%,rgba(200,80,42,.4) 0%,transparent 55%),radial-gradient(ellipse at 75% 30%,rgba(42,110,90,.25) 0%,transparent 50%); }
  .page-hero-content { position:relative; z-index:1; }
  .page-hero h1 { font-family:var(--font-display); font-size:2.8rem; font-weight:700; margin-bottom:10px; }
  .page-hero p  { color:rgba(255,255,255,.6); font-size:1rem; }
  @media(max-width:600px){ .page-hero h1{ font-size:2rem; } .page-hero{ padding:40px 20px 36px; } }

  .content { max-width:1200px; margin:32px auto; padding:0 32px; }
  @media(max-width:600px){ .content{ padding:0 16px; } }

  .section-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
  .section-title { font-family:var(--font-display); font-size:1.4rem; font-weight:700; }
  .clear-btn { font-size:.82rem; color:var(--muted); cursor:pointer; padding:6px 12px; border-radius:8px; border:1.5px solid var(--border); background:var(--surface); transition:var(--transition); }
  .clear-btn:hover { border-color:var(--accent); color:var(--accent); }

  .history-tabs { display:flex; gap:4px; border-bottom:1.5px solid var(--border); margin-bottom:28px; }
  .history-tab { padding:9px 22px; font-size:.92rem; font-weight:700; color:var(--muted); cursor:pointer; transition:var(--transition); border-bottom:2px solid transparent; margin-bottom:-1.5px; }
  .history-tab.active { color:var(--text); border-bottom-color:var(--accent); }

  .history-meta { font-size:.78rem; color:var(--muted); margin-top:4px; }
  .event-card { cursor:pointer; }
  .event-card .card-footer { padding:10px 18px; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; }
</style>
@endsection

@section('content')
<div class="page-hero">
  <div class="page-hero-content">
    <h1>История просмотров</h1>
    <p>Недавно просмотренные события и площадки</p>
  </div>
</div>

<div class="content">
  <div class="section-header">
    <div class="section-title" id="section-title">История</div>
    <button class="clear-btn" onclick="clearHistory()">Очистить историю</button>
  </div>

  <div class="history-tabs">
    <div class="history-tab active" id="tab-events" onclick="switchTab('events')">🎭 События</div>
    <div class="history-tab" id="tab-venues" onclick="switchTab('venues')">📍 Площадки</div>
  </div>

  <div id="events-grid" class="grid-3"></div>
  <div id="venues-grid" class="grid-3" style="display:none"></div>
</div>
@endsection

@section('scripts')
<script>
let currentTab = 'events';
let history_data = { events: [], venues: [] };

function loadHistory() {
  try {
    const raw = JSON.parse(localStorage.getItem('view_history') || '{"events":[],"venues":[]}');
    history_data.events = (raw.events || []).slice().reverse();
    history_data.venues = (raw.venues || []).slice().reverse();
  } catch { history_data = { events: [], venues: [] }; }
}

function renderEvents() {
  const grid = document.getElementById('events-grid');
  const list = history_data.events;
  if (!list.length) {
    grid.innerHTML = '<div class="empty"><div class="empty-icon">🎭</div>Вы ещё не смотрели события</div>';
    return;
  }
  grid.innerHTML = list.map(e => `
    <div class="card event-card" onclick="nav('/event/${e.id}')">
      ${imgOrPlaceholder(e.image, '🎭')}
      <div class="card-body">
        <span class="card-tag">${escHtml(e.category || 'Событие')}</span>
        <div class="card-title">${escHtml(e.title || 'Без названия')}</div>
        ${e.date ? `<div class="card-meta">📅 ${fmtDate(e.date)}</div>` : ''}
        ${e.venue ? `<div class="card-meta">📍 ${escHtml(e.venue)}</div>` : ''}
        ${e.price != null ? `<div class="card-price">${fmtPrice(e.price)}</div>` : ''}
        <div class="history-meta">Просмотрено ${fmtDate(e.viewed_at)}</div>
      </div>
    </div>
  `).join('');
}

function renderVenues() {
  const grid = document.getElementById('venues-grid');
  const list = history_data.venues;
  if (!list.length) {
    grid.innerHTML = '<div class="empty"><div class="empty-icon">📍</div>Вы ещё не смотрели площадки</div>';
    return;
  }
  grid.innerHTML = list.map(v => `
    <div class="card event-card" onclick="nav('/venue/${v.id}')">
      ${imgOrPlaceholder(v.image, '🏛️')}
      <div class="card-body">
        <span class="card-tag green">${escHtml(v.category || 'Площадка')}</span>
        <div class="card-title">${escHtml(v.name || 'Без названия')}</div>
        ${v.address ? `<div class="card-meta">📍 ${escHtml(v.address)}</div>` : ''}
        <div class="history-meta">Просмотрено ${fmtDate(v.viewed_at)}</div>
      </div>
    </div>
  `).join('');
}

function switchTab(tab) {
  currentTab = tab;
  document.getElementById('tab-events').classList.toggle('active', tab === 'events');
  document.getElementById('tab-venues').classList.toggle('active', tab === 'venues');
  document.getElementById('events-grid').style.display = tab === 'events' ? '' : 'none';
  document.getElementById('venues-grid').style.display = tab === 'venues' ? '' : 'none';
}

function clearHistory() {
  if (!confirm('Очистить всю историю просмотров?')) return;
  localStorage.removeItem('view_history');
  history_data = { events: [], venues: [] };
  renderEvents(); renderVenues();
  toast('История очищена');
}

loadHistory();
renderEvents();
renderVenues();
</script>
@endsection
