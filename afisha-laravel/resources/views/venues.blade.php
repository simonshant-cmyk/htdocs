@extends('layouts.app')

@section('title', 'Площадки — АфишаКолыма')

@section('styles')
<style>
  .hero { background:var(--hero-bg); color:#fff; padding:80px 32px 64px; text-align:center; position:relative; overflow:hidden; }
  .hero::before { content:''; position:absolute; inset:0; background:radial-gradient(ellipse at 20% 50%,rgba(200,80,42,.45) 0%,transparent 55%),radial-gradient(ellipse at 80% 30%,rgba(42,110,90,.3) 0%,transparent 50%); }
  .hero-content { position:relative; z-index:1; max-width:640px; margin:0 auto; }
  .hero-title { font-family:var(--font-display); font-size:3.4rem; font-weight:700; line-height:1.08; margin-bottom:14px; }
  @media(max-width:600px){ .hero-title{ font-size:2.2rem; } .hero{ padding:56px 20px 48px; } }
  .hero-title em { font-style:italic; color:rgba(255,255,255,.55); }
  .hero-sub { color:rgba(255,255,255,.58); font-size:1rem; margin-bottom:36px; }

  .search-bar { display:flex; gap:8px; background:var(--surface); border-radius:14px; padding:8px; box-shadow:0 8px 48px rgba(0,0,0,.3); max-width:580px; margin:0 auto; }
  .search-bar input { flex:1; border:none; outline:none; background:transparent; font-size:.95rem; color:var(--text); padding:6px 8px; }
  .search-bar select { border:none; outline:none; background:var(--bg2); border-radius:8px; padding:6px 12px; font-size:.85rem; color:var(--text); cursor:pointer; }

  .filters { max-width:1200px; margin:32px auto 0; padding:0 32px; display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
  .filter-chip { padding:7px 18px; border-radius:22px; font-size:.82rem; font-weight:600; border:1.5px solid var(--border); background:var(--surface); color:var(--muted); cursor:pointer; transition:var(--transition); }
  .filter-chip:hover,.filter-chip.active { border-color:var(--accent); color:var(--accent); background:rgba(200,80,42,.07); }
  .filter-label { font-size:.8rem; color:var(--muted); font-weight:600; }

  .main-tabs { max-width:1200px; margin:32px auto 0; padding:0 32px; display:flex; gap:4px; border-bottom:1.5px solid var(--border); }
  .main-tab { padding:10px 24px; font-size:.95rem; font-weight:700; color:var(--muted); cursor:pointer; transition:var(--transition); border-bottom:2px solid transparent; margin-bottom:-1.5px; }
  .main-tab.active { color:var(--text); border-bottom-color:var(--accent); }

  .content { max-width:1200px; margin:32px auto; padding:0 32px; }
  .event-card { cursor:pointer; }
  .event-card .card-footer { padding:12px 18px; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; }
  .fav-btn { width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:1.5px solid var(--border); background:var(--surface); font-size:1.05rem; cursor:pointer; transition:var(--transition); }
  .fav-btn:hover,.fav-btn.active { border-color:var(--accent); background:rgba(200,80,42,.08); color:var(--accent); }
</style>
@endsection

@section('content')
<div class="hero">
  <div class="hero-content">
    <h1 class="hero-title">Найди своё<br><em>событие</em></h1>
    <p class="hero-sub">Концерты, выставки, фестивали и лучшие места города</p>
    <div class="search-bar">
      <input id="search-input" type="text" placeholder="Поиск событий и площадок...">
      <select id="search-category">
        <option value="">Все категории</option>
      </select>
      <button class="btn btn-primary btn-sm" onclick="doSearch()">Найти</button>
    </div>
  </div>
</div>

<div class="main-tabs">
  <div class="main-tab" id="tab-events" onclick="switchTab('events')">🎭 События</div>
  <div class="main-tab active" id="tab-venues" onclick="switchTab('venues')">📍 Площадки</div>
</div>

<div class="filters" id="filters-row">
  <span class="filter-label">Категория:</span>
  <div id="category-chips" style="display:flex;gap:8px;flex-wrap:wrap"></div>
</div>

<div class="content">
  <div id="events-grid" class="grid-3" style="display:none"></div>
  <div id="venues-grid" class="grid-3"></div>
</div>
@endsection

@section('scripts')
<script>
let currentTab = 'venues';
let categories = [];
let activeCategory = null;
let favEventIds = new Set();
let favEventMap = {};
let favVenueIds = new Set();
let favVenueMap = {};

async function loadFavState() {
  if (!auth.isLoggedIn() || auth.type() !== 'user') return;
  try {
    const favs = await get('/favorites');
    favs.filter(f => f.event_id).forEach(f => { favEventIds.add(+f.event_id); favEventMap[+f.event_id] = f.favorite_id; });
    favs.filter(f => f.venue_id && !f.event_id).forEach(f => { favVenueIds.add(+f.venue_id); favVenueMap[+f.venue_id] = f.favorite_id; });
  } catch {}
}

async function init() {
  await loadFavState();
  await loadCategories();
  await loadVenues();
}

const CAT_ICONS = {
  'концерт':'🎵','музык':'🎵','театр':'🎭','кино':'🎬','выставк':'🖼️',
  'спорт':'⚽','фестивал':'🎉','лекци':'📚','мастер':'🛠️','клуб':'🍸',
  'шоу':'✨','детск':'👶','танц':'💃','цирк':'🎪','вечерин':'🎊',
};
function catIcon(name) {
  const lc = (name||'').toLowerCase();
  for (const [k,v] of Object.entries(CAT_ICONS)) if (lc.includes(k)) return v+' ';
  return '';
}

async function loadCategories() {
  try {
    categories = await get('/categories') || [];
    const chips = document.getElementById('category-chips');
    const sel   = document.getElementById('search-category');
    categories.forEach(c => {
      const chip = document.createElement('span');
      chip.className = 'filter-chip';
      chip.innerHTML = catIcon(c.name) + escHtml(c.name);
      chip.onclick = () => toggleCategory(c.id, chip);
      chips.appendChild(chip);
      const opt = document.createElement('option');
      opt.value = c.id; opt.textContent = c.name;
      sel.appendChild(opt);
    });
  } catch(e) {}
}

function toggleCategory(id, el) {
  document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
  if (activeCategory === id) { activeCategory = null; }
  else { activeCategory = id; el.classList.add('active'); }
  currentTab === 'events' ? loadEvents() : loadVenues();
}

async function loadEvents(search = '') {
  const grid = document.getElementById('events-grid');
  grid.innerHTML = skeletonGrid(6);
  try {
    let url = '/events';
    const params = [];
    if (activeCategory) params.push('category_id='+activeCategory);
    if (search) params.push('search='+encodeURIComponent(search));
    if (params.length) url += '?'+params.join('&');
    const events = await get(url);
    if (!events.length) { grid.innerHTML = '<div class="empty"><div class="empty-icon">🎭</div>События не найдены</div>'; return; }
    grid.innerHTML = events.map(e => `
      <div class="card event-card" onclick="nav('/event/${e.event_id}')">
        ${imgOrPlaceholder(e.image, '🎭')}
        <div class="card-body">
          <span class="card-tag">${escHtml(e.category_name || 'Событие')}</span>
          <div class="card-title">${escHtml(e.title)}</div>
          <div class="card-meta">📅 ${fmtDate(e.start_datetime)}</div>
          <div class="card-meta">📍 ${escHtml(e.venue_name || '—')}</div>
          <div class="card-price">${fmtPrice(e.price)}</div>
        </div>
        <div class="card-footer">
          <span class="badge badge-gray">${escHtml(e.organization_name || '')}</span>
          <button class="fav-btn${favEventIds.has(+e.event_id)?' active':''}" onclick="event.stopPropagation();toggleEventFav(${e.event_id},this)" title="${favEventIds.has(+e.event_id)?'Убрать из избранного':'В избранное'}">${favEventIds.has(+e.event_id)?'♥':'♡'}</button>
        </div>
      </div>
    `).join('');
  } catch(e) { grid.innerHTML = '<div class="empty"><div class="empty-icon">⚠️</div>'+e.message+'</div>'; }
}

async function loadVenues(search = '') {
  const grid = document.getElementById('venues-grid');
  grid.innerHTML = skeletonGrid(6);
  try {
    let url = '/venues';
    const params = [];
    if (activeCategory) params.push('category_id='+activeCategory);
    if (search) params.push('search='+encodeURIComponent(search));
    if (params.length) url += '?'+params.join('&');
    const venues = await get(url);
    if (!venues.length) { grid.innerHTML = '<div class="empty"><div class="empty-icon">📍</div>Площадки не найдены</div>'; return; }
    grid.innerHTML = venues.map(v => `
      <div class="card event-card" onclick="nav('/venue/${v.venue_id}')">
        ${imgOrPlaceholder(v.image, '🏛️')}
        <div class="card-body">
          <span class="card-tag green">${escHtml(v.category_name || 'Площадка')}</span>
          <div class="card-title">${escHtml(v.name)}</div>
          <div class="card-meta">📍 ${escHtml(v.address || 'Адрес не указан')}</div>
          ${v.age ? `<div class="card-meta">🔞 ${v.age}+</div>` : ''}
        </div>
        <div class="card-footer">
          <span></span>
          <button class="fav-btn${favVenueIds.has(+v.venue_id)?' active':''}" onclick="event.stopPropagation();toggleVenueFav(${v.venue_id},this)" title="${favVenueIds.has(+v.venue_id)?'Убрать из избранного':'В избранное'}">${favVenueIds.has(+v.venue_id)?'♥':'♡'}</button>
        </div>
      </div>
    `).join('');
  } catch(e) { grid.innerHTML = '<div class="empty"><div class="empty-icon">⚠️</div>'+e.message+'</div>'; }
}

function switchTab(tab) {
  currentTab = tab;
  document.getElementById('tab-events').classList.toggle('active', tab === 'events');
  document.getElementById('tab-venues').classList.toggle('active', tab === 'venues');
  document.getElementById('events-grid').style.display = tab === 'events' ? '' : 'none';
  document.getElementById('venues-grid').style.display = tab === 'venues' ? '' : 'none';
  activeCategory = null;
  document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
  if (tab === 'events') { loadEvents(); }
  else { loadVenues(); }
  if (tab === 'events') nav('/');
}

function doSearch() {
  const q = document.getElementById('search-input').value.trim();
  const cat = document.getElementById('search-category').value;
  if (cat) activeCategory = +cat;
  currentTab === 'events' ? loadEvents(q) : loadVenues(q);
}

async function toggleEventFav(eventId, btn) {
  if (auth.isLoggedIn() && auth.type() === 'org') { toast('Недоступно для аккаунтов организации', 'error'); return; }
  if (!auth.isLoggedIn()) { toast('Войдите чтобы добавить в избранное', 'error'); return; }
  const alreadyFav = favEventIds.has(+eventId);
  btn.disabled = true;
  try {
    if (alreadyFav) {
      await del('/favorites/' + favEventMap[+eventId]);
      favEventIds.delete(+eventId); delete favEventMap[+eventId];
      btn.classList.remove('active'); btn.textContent = '♡'; btn.title = 'В избранное';
      toast('Убрано из избранного');
    } else {
      const r = await post('/favorites', {event_id: eventId});
      favEventIds.add(+eventId); favEventMap[+eventId] = r?.favorite_id;
      btn.classList.add('active'); btn.textContent = '♥'; btn.title = 'Убрать из избранного';
      toast('Добавлено в избранное ♥', 'success');
    }
  } catch(e) { toast(e.message, 'error'); }
  finally { btn.disabled = false; }
}

async function toggleVenueFav(venueId, btn) {
  if (auth.isLoggedIn() && auth.type() === 'org') { toast('Недоступно для аккаунтов организации', 'error'); return; }
  if (!auth.isLoggedIn()) { toast('Войдите чтобы добавить в избранное', 'error'); return; }
  const alreadyFav = favVenueIds.has(+venueId);
  btn.disabled = true;
  try {
    if (alreadyFav) {
      await del('/favorites/' + favVenueMap[+venueId]);
      favVenueIds.delete(+venueId); delete favVenueMap[+venueId];
      btn.classList.remove('active'); btn.textContent = '♡'; btn.title = 'В избранное';
      toast('Убрано из избранного');
    } else {
      const r = await post('/favorites', {venue_id: venueId});
      favVenueIds.add(+venueId); favVenueMap[+venueId] = r?.favorite_id;
      btn.classList.add('active'); btn.textContent = '♥'; btn.title = 'Убрать из избранного';
      toast('Добавлено в избранное ♥', 'success');
    }
  } catch(e) { toast(e.message, 'error'); }
  finally { btn.disabled = false; }
}

document.getElementById('search-input').addEventListener('keydown', e => { if (e.key === 'Enter') doSearch(); });

init();
</script>
@endsection
