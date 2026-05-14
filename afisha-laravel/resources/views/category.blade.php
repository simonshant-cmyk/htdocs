@extends('layouts.app')

@section('title', 'Категория — АфишаКолыма')

@section('styles')
<style>
  .cat-hero { background:var(--hero-bg); color:#fff; padding:64px 32px 52px; text-align:center; position:relative; overflow:hidden; }
  .cat-hero::before { content:''; position:absolute; inset:0; background:radial-gradient(ellipse at 30% 50%,rgba(200,80,42,.45) 0%,transparent 55%),radial-gradient(ellipse at 75% 30%,rgba(42,110,90,.3) 0%,transparent 50%); }
  .cat-hero-inner { position:relative; z-index:1; }
  .cat-icon { font-size:3rem; margin-bottom:12px; display:block; }
  .cat-hero h1 { font-family:var(--font-display); font-size:2.8rem; font-weight:700; margin-bottom:8px; }
  .cat-hero p { color:rgba(255,255,255,.6); font-size:1rem; }
  @media(max-width:600px){ .cat-hero h1{ font-size:2rem; } .cat-hero{ padding:48px 20px 40px; } }

  .content { max-width:1200px; margin:36px auto; padding:0 32px; }
  @media(max-width:600px){ .content{ padding:0 16px; } }

  .content-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
  .section-title { font-family:var(--font-display); font-size:1.4rem; font-weight:700; }
  .count-badge { background:var(--bg2); border-radius:20px; padding:4px 14px; font-size:.82rem; color:var(--muted); font-weight:600; }

  .main-tabs { display:flex; gap:4px; border-bottom:1.5px solid var(--border); margin-bottom:28px; }
  .main-tab { padding:9px 22px; font-size:.92rem; font-weight:700; color:var(--muted); cursor:pointer; transition:var(--transition); border-bottom:2px solid transparent; margin-bottom:-1.5px; }
  .main-tab.active { color:var(--text); border-bottom-color:var(--accent); }

  .event-card { cursor:pointer; }
  .event-card .card-footer { padding:10px 18px; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; }
  .fav-btn { width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:1.5px solid var(--border); background:var(--surface); font-size:1.05rem; cursor:pointer; transition:var(--transition); }
  .fav-btn:hover,.fav-btn.active { border-color:var(--accent); background:rgba(200,80,42,.08); color:var(--accent); }
</style>
@endsection

@section('content')
<div class="cat-hero">
  <div class="cat-hero-inner">
    <span class="cat-icon" id="cat-icon">🎭</span>
    <h1 id="cat-name">Загрузка...</h1>
    <p id="cat-sub">События и площадки в этой категории</p>
  </div>
</div>

<div class="content">
  <div class="main-tabs">
    <div class="main-tab active" id="tab-events" onclick="switchTab('events')">🎭 События</div>
    <div class="main-tab" id="tab-venues" onclick="switchTab('venues')">📍 Площадки</div>
  </div>

  <div class="content-header">
    <div class="section-title" id="section-title">События</div>
    <div class="count-badge" id="count-badge"></div>
  </div>

  <div id="events-grid" class="grid-3"></div>
  <div id="venues-grid" class="grid-3" style="display:none"></div>
</div>
@endsection

@section('scripts')
<script>
const catId = {{ $id }};
let currentTab = 'events';
let favEventIds = new Set();
let favEventMap = {};
let favVenueIds = new Set();
let favVenueMap = {};

const CAT_ICONS = {
  'концерт':'🎵','музыка':'🎵','театр':'🎭','кино':'🎬','выставка':'🖼️',
  'спорт':'⚽','фестиваль':'🎉','лекция':'📚','клуб':'🍸','другое':'🎪'
};

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
  await loadCategoryName();
  await loadEvents();
}

async function loadCategoryName() {
  try {
    const cats = await get('/categories');
    const cat = cats.find(c => +c.id === catId);
    if (cat) {
      const name = cat.name || 'Категория';
      document.title = name + ' — АфишаКолыма';
      document.getElementById('cat-name').textContent = name;
      const lc = name.toLowerCase();
      const icon = Object.entries(CAT_ICONS).find(([k]) => lc.includes(k));
      if (icon) document.getElementById('cat-icon').textContent = icon[1];
    }
  } catch {}
}

async function loadEvents() {
  const grid = document.getElementById('events-grid');
  grid.innerHTML = skeletonGrid(6);
  try {
    const events = await get('/events?category_id=' + catId);
    document.getElementById('count-badge').textContent = events.length + ' событий';
    if (!events.length) {
      grid.innerHTML = '<div class="empty"><div class="empty-icon">🎭</div>В этой категории нет событий</div>';
      return;
    }
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
          <button class="fav-btn${favEventIds.has(+e.event_id)?' active':''}" onclick="event.stopPropagation();toggleEventFav(${e.event_id},this)">${favEventIds.has(+e.event_id)?'♥':'♡'}</button>
        </div>
      </div>
    `).join('');
  } catch(e) {
    grid.innerHTML = '<div class="empty"><div class="empty-icon">⚠️</div>' + e.message + '</div>';
  }
}

async function loadVenues() {
  const grid = document.getElementById('venues-grid');
  grid.innerHTML = skeletonGrid(6);
  try {
    const venues = await get('/venues?category_id=' + catId);
    document.getElementById('count-badge').textContent = venues.length + ' площадок';
    if (!venues.length) {
      grid.innerHTML = '<div class="empty"><div class="empty-icon">📍</div>В этой категории нет площадок</div>';
      return;
    }
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
          <button class="fav-btn${favVenueIds.has(+v.venue_id)?' active':''}" onclick="event.stopPropagation();toggleVenueFav(${v.venue_id},this)">${favVenueIds.has(+v.venue_id)?'♥':'♡'}</button>
        </div>
      </div>
    `).join('');
  } catch(e) {
    grid.innerHTML = '<div class="empty"><div class="empty-icon">⚠️</div>' + e.message + '</div>';
  }
}

function switchTab(tab) {
  currentTab = tab;
  document.getElementById('tab-events').classList.toggle('active', tab === 'events');
  document.getElementById('tab-venues').classList.toggle('active', tab === 'venues');
  document.getElementById('events-grid').style.display = tab === 'events' ? '' : 'none';
  document.getElementById('venues-grid').style.display = tab === 'venues' ? '' : 'none';
  document.getElementById('section-title').textContent = tab === 'events' ? 'События' : 'Площадки';
  if (tab === 'events') loadEvents(); else loadVenues();
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
      btn.classList.remove('active'); btn.textContent = '♡'; toast('Убрано из избранного');
    } else {
      const r = await post('/favorites', {event_id: eventId});
      favEventIds.add(+eventId); favEventMap[+eventId] = r?.favorite_id;
      btn.classList.add('active'); btn.textContent = '♥'; toast('Добавлено в избранное ♥', 'success');
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
      btn.classList.remove('active'); btn.textContent = '♡'; toast('Убрано из избранного');
    } else {
      const r = await post('/favorites', {venue_id: venueId});
      favVenueIds.add(+venueId); favVenueMap[+venueId] = r?.favorite_id;
      btn.classList.add('active'); btn.textContent = '♥'; toast('Добавлено в избранное ♥', 'success');
    }
  } catch(e) { toast(e.message, 'error'); }
  finally { btn.disabled = false; }
}

init();
</script>
@endsection
