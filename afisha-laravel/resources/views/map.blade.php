@extends('layouts.app')

@section('title', 'Карта — АфишаКолыма')

@section('styles')
<style>
  .page-hero { background:var(--hero-bg); color:#fff; padding:48px 32px 40px; text-align:center; position:relative; overflow:hidden; }
  .page-hero::before { content:''; position:absolute; inset:0; background:radial-gradient(ellipse at 30% 50%,rgba(200,80,42,.45) 0%,transparent 55%),radial-gradient(ellipse at 75% 30%,rgba(42,110,90,.3) 0%,transparent 50%); }
  .page-hero-content { position:relative; z-index:1; }
  .page-hero h1 { font-family:var(--font-display); font-size:2.4rem; font-weight:700; margin-bottom:6px; }
  .page-hero p  { color:rgba(255,255,255,.6); font-size:.9rem; }
  @media(max-width:600px){ .page-hero h1{font-size:1.7rem} .page-hero{padding:32px 20px 28px} }

  /* Map tabs */
  .map-tabs { max-width:1280px; margin:0 auto; padding:20px 28px 0; display:flex; gap:8px; }
  .map-tab { padding:9px 22px; border-radius:22px; font-size:.86rem; font-weight:700; border:1.5px solid var(--border); background:var(--surface); color:var(--muted); cursor:pointer; transition:var(--transition); }
  .map-tab.active { background:var(--accent); border-color:var(--accent); color:#fff; }
  .map-tab:hover:not(.active) { border-color:var(--accent); color:var(--accent); }

  /* Layout */
  .map-wrap { max-width:1280px; margin:16px auto 40px; padding:0 28px; display:flex; gap:20px; align-items:flex-start; }
  @media(max-width:900px){ .map-wrap{flex-direction:column;padding:0 16px} }

  #map { flex:1; height:580px; border-radius:18px; overflow:hidden; box-shadow:var(--shadow-hover); min-width:0; }
  @media(max-width:900px){ #map{height:340px;width:100%} }

  /* Sidebar */
  .map-sidebar { width:272px; flex-shrink:0; display:flex; flex-direction:column; gap:12px; }
  @media(max-width:900px){ .map-sidebar{width:100%} }

  .stats-row { display:flex; gap:10px; }
  .map-stat { background:var(--surface); border-radius:14px; padding:12px 16px; box-shadow:var(--shadow-card); flex:1; text-align:center; }
  .map-stat-val { font-size:1.5rem; font-weight:800; font-family:var(--font-display); color:var(--accent); }
  .map-stat-label { font-size:.72rem; color:var(--muted); margin-top:1px; }

  .map-progress { height:3px; background:var(--border); border-radius:2px; overflow:hidden; }
  .map-progress-bar { height:100%; background:var(--accent); border-radius:2px; width:0%; transition:width .4s ease; }

  .list-card { background:var(--surface); border-radius:14px; box-shadow:var(--shadow-card); overflow:hidden; }
  .list-head { padding:11px 16px; border-bottom:1px solid var(--border); font-size:.72rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.07em; display:flex; align-items:center; justify-content:space-between; }
  .list-body { overflow-y:auto; max-height:340px; }
  @media(max-width:900px){ .list-body{max-height:180px} }

  .list-item { display:flex; align-items:flex-start; gap:9px; padding:9px 14px; border-bottom:1px solid var(--border); cursor:pointer; transition:background .14s; }
  .list-item:last-child { border-bottom:none; }
  .list-item:hover { background:var(--bg2); }
  .li-dot { width:8px; height:8px; border-radius:50%; background:var(--border); flex-shrink:0; margin-top:5px; transition:background .3s; }
  .li-dot.ok { background:var(--accent); }
  .li-dot.event-dot { background:rgba(200,80,42,.25); }
  .li-dot.event-dot.ok { background:var(--accent); }
  .li-body { flex:1; min-width:0; }
  .li-name { font-size:.84rem; font-weight:500; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  .li-sub  { font-size:.72rem; color:var(--muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; margin-top:2px; }

  .map-legend { background:var(--surface); border-radius:14px; padding:11px 15px; box-shadow:var(--shadow-card); font-size:.77rem; color:var(--muted); line-height:1.55; }
  .map-legend strong { color:var(--text); display:block; margin-bottom:2px; }

  /* Search */
  .map-search { display:flex; gap:8px; background:var(--surface); border-radius:12px; padding:6px; box-shadow:var(--shadow-card); }
  .map-search input { flex:1; border:none; outline:none; background:transparent; font-size:.86rem; color:var(--text); padding:5px 8px; }
  .map-search button { padding:6px 14px; border-radius:8px; background:var(--accent); color:#fff; border:none; font-size:.82rem; font-weight:600; cursor:pointer; white-space:nowrap; }
</style>
@endsection

@section('content')
<div class="page-hero">
  <div class="page-hero-content">
    <h1>Карта Магадана</h1>
    <p>Площадки и события на одной карте</p>
  </div>
</div>

<div class="map-tabs">
  <div class="map-tab active" id="tab-venues" onclick="switchMode('venues')">📍 Площадки</div>
  <div class="map-tab" id="tab-events" onclick="switchMode('events')">🎭 События</div>
</div>

<div class="map-wrap">
  <div id="map"></div>
  <div class="map-sidebar">

    <div class="map-search">
      <input type="text" id="map-search-input" placeholder="Поиск..." oninput="filterList(this.value)">
      <button onclick="filterList(document.getElementById('map-search-input').value)">Найти</button>
    </div>

    <div class="stats-row">
      <div class="map-stat">
        <div class="map-stat-val" id="stat-total">—</div>
        <div class="map-stat-label" id="stat-total-label">Всего</div>
      </div>
      <div class="map-stat">
        <div class="map-stat-val" id="stat-found">0</div>
        <div class="map-stat-label">На карте</div>
      </div>
    </div>

    <div class="map-progress"><div class="map-progress-bar" id="progress-bar"></div></div>

    <div class="list-card">
      <div class="list-head">
        <span id="list-title">Площадки</span>
        <span id="list-count" style="color:var(--accent);font-size:.8rem"></span>
      </div>
      <div class="list-body" id="item-list">
        <div style="padding:14px 16px;color:var(--muted);font-size:.84rem">Загрузка...</div>
      </div>
    </div>

    <div class="map-legend">
      <strong>Геокодер Яндекса</strong>
      Адреса определяются автоматически. Серая точка — в очереди, красная — найдена.
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://api-maps.yandex.ru/2.1/?apikey=da23ed3a-9052-4d2f-8ce7-e597d90a7afc&lang=ru_RU" type="text/javascript"></script>
<script>
const MAGADAN = [59.5683, 150.8084];
const BASE = window.APP_BASE || '';

let ymap = null;
let currentMode = 'venues';
let venues = [], events = [];
let coordsCache = {};       // venue_id → [lat, lng]
let placemarks = {};        // id → { pm, coords }
let geocodedCount = 0;
let geocoding = false;

function fmtPrice(p) {
  const n = parseFloat(p);
  if (!n || n === 0) return 'Бесплатно';
  return new Intl.NumberFormat('ru-RU').format(n) + ' ₽';
}
function fmtD(dt) {
  if (!dt) return '';
  const d = new Date(dt);
  return d.toLocaleDateString('ru-RU', { day:'numeric', month:'short', hour:'2-digit', minute:'2-digit' });
}

ymaps.ready(async function () {
  ymap = new ymaps.Map('map', {
    center: MAGADAN, zoom: 13,
    controls: ['zoomControl', 'fullscreenControl', 'typeSelector']
  });

  try {
    [venues, events] = await Promise.all([
      get('/venues'),
      get('/events')
    ]);
    document.getElementById('stat-total').textContent = venues.length;
    document.getElementById('stat-total-label').textContent = 'Площадок';
    renderList(venues, 'venues');
    await geocodeVenues(venues);
  } catch(e) {
    document.getElementById('item-list').innerHTML =
      '<div style="padding:14px 16px;color:var(--muted);font-size:.84rem">Ошибка загрузки</div>';
  }
});

// ── Mode switch ──
async function switchMode(mode) {
  if (mode === currentMode) return;
  currentMode = mode;

  document.getElementById('tab-venues').classList.toggle('active', mode === 'venues');
  document.getElementById('tab-events').classList.toggle('active', mode === 'events');
  document.getElementById('map-search-input').value = '';

  clearMap();

  if (mode === 'venues') {
    document.getElementById('stat-total').textContent = venues.length;
    document.getElementById('stat-total-label').textContent = 'Площадок';
    document.getElementById('list-title').textContent = 'Площадки';
    renderList(venues, 'venues');
    await geocodeVenues(venues);
  } else {
    document.getElementById('stat-total').textContent = events.length;
    document.getElementById('stat-total-label').textContent = 'Событий';
    document.getElementById('list-title').textContent = 'События';
    renderList(events, 'events');
    await geocodeEvents(events);
  }
}

function clearMap() {
  ymap.geoObjects.removeAll();
  placemarks = {};
  geocodedCount = 0;
  document.getElementById('stat-found').textContent = '0';
  document.getElementById('progress-bar').style.width = '0%';
}

// ── Geocode venues ──
async function geocodeVenues(list) {
  if (geocoding) return;
  geocoding = true;

  // Venues with stored coordinates go instantly; rest need geocoding
  const withCoords  = list.filter(v => v.latitude && v.longitude);
  const needGeocode = list.filter(v => !(v.latitude && v.longitude) && v.address);
  let done = 0;
  const total = withCoords.length + needGeocode.length;

  // Place pinned venues immediately
  for (const v of withCoords) {
    if (currentMode !== 'venues') break;
    const coords = [+v.latitude, +v.longitude];
    coordsCache[v.venue_id] = coords;
    const pm = new ymaps.Placemark(coords, {
      balloonContentHeader: `<b style="font-size:.9rem">📍 ${escHtml(v.name)}</b>`,
      balloonContentBody:   v.address ? `<span style="color:#777;font-size:.82rem">${escHtml(v.address)}</span>` : '',
      balloonContentFooter: `<a href="${BASE}/venue/${v.venue_id}" style="color:#c8502a;font-size:.82rem;font-weight:600">Открыть страницу →</a>`,
      hintContent: v.name
    }, { iconColor: '#2a6e5a', preset: 'islands#greenDotIconWithCaption' });
    ymap.geoObjects.add(pm);
    placemarks[v.venue_id] = { pm, coords };
    done++;
    geocodedCount = done;
    document.getElementById('stat-found').textContent = done;
    const dot = document.getElementById('dot-v-' + v.venue_id);
    if (dot) dot.classList.add('ok');
    document.getElementById('progress-bar').style.width = Math.round((done/Math.max(total,1))*100) + '%';
  }

  // Geocode the rest via Yandex
  for (let i = 0; i < needGeocode.length; i++) {
    if (currentMode !== 'venues') break;
    const v = needGeocode[i];
    let coords = coordsCache[v.venue_id];
    if (!coords) {
      try {
        const res = await ymaps.geocode('Магадан, ' + v.address, { results: 1 });
        const obj = res.geoObjects.get(0);
        if (obj) { coords = obj.geometry.getCoordinates(); coordsCache[v.venue_id] = coords; }
      } catch(e) {}
      if (i < needGeocode.length - 1) await delay(400);
    }
    if (coords && currentMode === 'venues') {
      const pm = new ymaps.Placemark(coords, {
        balloonContentHeader: `<b style="font-size:.9rem">📍 ${escHtml(v.name)}</b>`,
        balloonContentBody:   v.address ? `<span style="color:#777;font-size:.82rem">${escHtml(v.address)}</span>` : '',
        balloonContentFooter: `<a href="${BASE}/venue/${v.venue_id}" style="color:#c8502a;font-size:.82rem;font-weight:600">Открыть страницу →</a>`,
        hintContent: v.name
      }, { iconColor: '#c8502a', preset: 'islands#redDotIconWithCaption' });
      ymap.geoObjects.add(pm);
      placemarks[v.venue_id] = { pm, coords };
      done++;
      geocodedCount = done;
      document.getElementById('stat-found').textContent = done;
      const dot = document.getElementById('dot-v-' + v.venue_id);
      if (dot) dot.classList.add('ok');
      document.getElementById('progress-bar').style.width = Math.round((done/Math.max(total,1))*100) + '%';
    }
  }
  geocoding = false;
}

// ── Geocode events ──
async function geocodeEvents(list) {
  if (geocoding) return;
  geocoding = true;

  // Group events by venue_id
  const byVenue = {};
  list.forEach(e => {
    if (!e.venue_id) return;
    if (!byVenue[e.venue_id]) byVenue[e.venue_id] = { venue_name: e.venue_name, address: e.venue_address || e.venue_name, events: [] };
    byVenue[e.venue_id].events.push(e);
  });

  const venueIds = Object.keys(byVenue);
  let done = 0;

  for (let i = 0; i < venueIds.length; i++) {
    if (currentMode !== 'events') break;
    const vid = +venueIds[i];
    const group = byVenue[vid];

    let coords = coordsCache[vid];
    if (!coords && group.address) {
      try {
        const res = await ymaps.geocode('Магадан, ' + group.address, { results: 1 });
        const obj = res.geoObjects.get(0);
        if (obj) { coords = obj.geometry.getCoordinates(); coordsCache[vid] = coords; }
      } catch(e) {}
      if (i < venueIds.length - 1) await delay(400);
    }

    if (coords && currentMode === 'events') {
      const evList = group.events.slice(0, 5).map(e =>
        `<div style="padding:4px 0;border-bottom:1px solid #f0f0f0;font-size:.8rem">
          <div style="font-weight:600">${escHtml(e.title)}</div>
          <div style="color:#888;margin-top:1px">📅 ${fmtD(e.start_datetime)} · ${fmtPrice(e.price)}</div>
          <a href="${BASE}/event/${e.event_id}" style="color:#c8502a;font-size:.77rem;font-weight:600">Подробнее →</a>
        </div>`
      ).join('');
      const more = group.events.length > 5 ? `<div style="font-size:.78rem;color:#888;padding-top:4px">...и ещё ${group.events.length-5}</div>` : '';

      const pm = new ymaps.Placemark(coords, {
        balloonContentHeader: `<b style="font-size:.88rem">🎭 ${escHtml(group.venue_name || group.address)}</b> <span style="font-size:.75rem;color:#888">${group.events.length} ${plural(group.events.length,'событие','события','событий')}</span>`,
        balloonContentBody:   `<div style="max-height:220px;overflow-y:auto;padding:4px 0">${evList}${more}</div>`,
        hintContent: group.venue_name + ' (' + group.events.length + ' соб.)'
      }, {
        iconColor: '#2a6e5a',
        preset: 'islands#greenDotIconWithCaption'
      });
      ymap.geoObjects.add(pm);
      placemarks['e-' + vid] = { pm, coords };

      done++;
      geocodedCount = done;
      document.getElementById('stat-found').textContent = done;
      // Mark dots for all events at this venue
      group.events.forEach(e => {
        const dot = document.getElementById('dot-e-' + e.event_id);
        if (dot) dot.classList.add('ok');
      });
      document.getElementById('progress-bar').style.width = Math.round(((i+1)/venueIds.length)*100) + '%';
    }
  }
  geocoding = false;
}

// ── List render ──
function renderList(list, mode) {
  const el = document.getElementById('item-list');
  document.getElementById('list-count').textContent = list.length;
  if (!list.length) { el.innerHTML = '<div style="padding:14px 16px;color:var(--muted);font-size:.84rem">Нет данных</div>'; return; }

  if (mode === 'venues') {
    el.innerHTML = list.map(v => `
      <div class="list-item" id="li-v-${v.venue_id}" onclick="focusItem('v',${v.venue_id})">
        <div class="li-dot" id="dot-v-${v.venue_id}"></div>
        <div class="li-body">
          <div class="li-name">${escHtml(v.name)}</div>
          ${v.address ? `<div class="li-sub">📍 ${escHtml(v.address)}</div>` : ''}
        </div>
      </div>`).join('');
  } else {
    el.innerHTML = list.map(e => `
      <div class="list-item" id="li-e-${e.event_id}" onclick="focusEventItem(${e.event_id},${e.venue_id||0})">
        <div class="li-dot event-dot" id="dot-e-${e.event_id}"></div>
        <div class="li-body">
          <div class="li-name">${escHtml(e.title)}</div>
          <div class="li-sub">📅 ${fmtD(e.start_datetime)} · ${escHtml(e.venue_name||'—')}</div>
        </div>
      </div>`).join('');
  }
}

// ── Focus ──
function focusItem(type, id) {
  const key = type + '-' + id;
  const ref = placemarks[key];
  if (!ref) { toast('Адрес ещё не определён', 'error'); return; }
  ymap.setCenter(ref.coords, 16, { duration: 500 });
  setTimeout(() => ref.pm.balloon.open(), 620);
  highlightListItem('li-' + key);
}

function focusEventItem(eventId, venueId) {
  const key = 'e-' + venueId;
  const ref = placemarks[key];
  if (!ref) { toast('Адрес ещё не определён', 'error'); return; }
  ymap.setCenter(ref.coords, 16, { duration: 500 });
  setTimeout(() => ref.pm.balloon.open(), 620);
  highlightListItem('li-e-' + eventId);
}

function highlightListItem(id) {
  document.querySelectorAll('.list-item').forEach(li => li.style.background = '');
  const li = document.getElementById(id);
  if (li) { li.style.background = 'rgba(200,80,42,.07)'; li.scrollIntoView({ block:'nearest', behavior:'smooth' }); }
}

// ── Search filter ──
function filterList(q) {
  q = (q || '').toLowerCase().trim();
  const items = document.querySelectorAll('.list-item');
  let visible = 0;
  items.forEach(li => {
    const text = li.textContent.toLowerCase();
    const show = !q || text.includes(q);
    li.style.display = show ? '' : 'none';
    if (show) visible++;
  });
  document.getElementById('list-count').textContent = visible;
}

// ── Helpers ──
function plural(n, one, few, many) {
  const mod10 = n % 10, mod100 = n % 100;
  if (mod10 === 1 && mod100 !== 11) return one;
  if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) return few;
  return many;
}
function delay(ms) { return new Promise(r => setTimeout(r, ms)); }
function escHtml(s) { return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
</script>
@endsection
