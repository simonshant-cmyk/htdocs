@extends('layouts.app')

@section('title', 'Организация — АфишаКолыма')

@section('styles')
<style>
  .org-hero { background:var(--hero-bg); color:#fff; padding:64px 32px 52px; position:relative; overflow:hidden; }
  .org-hero::before { content:''; position:absolute; inset:0; background:radial-gradient(ellipse at 25% 60%,rgba(200,80,42,.45) 0%,transparent 55%),radial-gradient(ellipse at 80% 20%,rgba(42,110,90,.3) 0%,transparent 50%); }
  .org-hero-inner { position:relative; z-index:1; max-width:1200px; margin:0 auto; display:flex; align-items:center; gap:32px; }
  @media(max-width:700px){ .org-hero-inner{ flex-direction:column; text-align:center; } .org-hero{ padding:48px 20px 40px; } }

  .org-avatar { width:100px; height:100px; border-radius:20px; background:rgba(255,255,255,.15); border:3px solid rgba(255,255,255,.3); flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:2.8rem; overflow:hidden; }
  .org-avatar img { width:100%; height:100%; object-fit:cover; }

  .org-info h1 { font-family:var(--font-display); font-size:2.4rem; font-weight:700; margin-bottom:6px; }
  .org-badge { display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,.12); border-radius:20px; padding:4px 14px; font-size:.8rem; font-weight:600; color:rgba(255,255,255,.8); margin-bottom:8px; }
  .org-contacts { display:flex; flex-wrap:wrap; gap:14px; margin-top:12px; }
  .org-contact-item { display:flex; align-items:center; gap:6px; color:rgba(255,255,255,.7); font-size:.88rem; }
  .org-contact-item a { color:rgba(255,255,255,.9); text-decoration:none; }
  .org-contact-item a:hover { color:#fff; text-decoration:underline; }

  .content { max-width:1200px; margin:36px auto; padding:0 32px; }
  @media(max-width:600px){ .content{ padding:0 16px; } }

  .stats-row { display:flex; gap:16px; flex-wrap:wrap; margin-bottom:28px; }
  .stat-card { background:var(--surface); border-radius:14px; padding:16px 24px; box-shadow:var(--shadow-card); flex:1; min-width:120px; }
  .stat-val { font-size:1.8rem; font-weight:800; font-family:var(--font-display); color:var(--accent); }
  .stat-label { font-size:.8rem; color:var(--muted); margin-top:2px; }

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
<div class="org-hero" id="org-hero">
  <div class="org-hero-inner">
    <div class="org-avatar" id="org-avatar">🏢</div>
    <div class="org-info">
      <div class="org-badge">🏢 Организация</div>
      <h1 id="org-name">Загрузка...</h1>
      <div class="org-contacts" id="org-contacts"></div>
      <button id="sub-btn" onclick="toggleSubscription()" style="display:none;margin-top:14px;padding:10px 24px;border-radius:10px;font-weight:700;font-size:.9rem;cursor:pointer;transition:var(--transition);border:2px solid rgba(255,255,255,.5);background:rgba(255,255,255,.15);color:#fff;backdrop-filter:blur(4px)">🔔 Подписаться</button>
    </div>
  </div>
</div>

<div class="content">
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-val" id="stat-events">—</div>
      <div class="stat-label">Событий</div>
    </div>
    <div class="stat-card">
      <div class="stat-val" id="stat-venues">—</div>
      <div class="stat-label">Площадок</div>
    </div>
    <div class="stat-card" style="cursor:pointer" onclick="toggleSubscription()" id="sub-card">
      <div class="stat-val" id="stat-subs">—</div>
      <div class="stat-label" id="sub-label">Подписчиков</div>
    </div>
  </div>

  <div class="main-tabs">
    <div class="main-tab active" id="tab-events" onclick="switchTab('events')">🎭 События</div>
    <div class="main-tab" id="tab-venues" onclick="switchTab('venues')">📍 Площадки</div>
  </div>

  <div id="events-grid" class="grid-3"></div>
  <div id="venues-grid" class="grid-3" style="display:none"></div>
</div>
@endsection

@section('scripts')
<script>
const orgId = {{ $id }};
let currentTab = 'events';
let favEventIds = new Set();
let favEventMap = {};
let favVenueIds = new Set();
let favVenueMap = {};
let isSubscribed = false;
let subCount = 0;

async function loadFavState() {
  if (!auth.isLoggedIn() || auth.type() !== 'user') return;
  try {
    const favs = await get('/favorites');
    favs.filter(f => f.event_id).forEach(f => { favEventIds.add(+f.event_id); favEventMap[+f.event_id] = f.favorite_id; });
    favs.filter(f => f.venue_id && !f.event_id).forEach(f => { favVenueIds.add(+f.venue_id); favVenueMap[+f.venue_id] = f.favorite_id; });
  } catch {}
}

async function loadOrg() {
  try {
    const org = await get('/orgs/' + orgId);
    document.title = (org.full_name || 'Организация') + ' — АфишаКолыма';
    document.getElementById('org-name').textContent = org.full_name || 'Организация';

    const avatarEl = document.getElementById('org-avatar');
    if (org.image) avatarEl.innerHTML = `<img src="${escHtml(org.image)}" alt="">`;

    const contacts = document.getElementById('org-contacts');
    const parts = [];
    if (org.address) parts.push(`<span class="org-contact-item">📍 ${escHtml(org.address)}</span>`);
    if (org.phone) parts.push(`<span class="org-contact-item">📞 <a href="tel:${escHtml(org.phone)}">${escHtml(org.phone)}</a></span>`);
    if (org.website) {
      const url = /^https?:\/\//i.test(org.website) ? org.website : 'https://' + org.website;
      parts.push(`<span class="org-contact-item">🌐 <a href="${escHtml(url)}" target="_blank" rel="noopener">${escHtml(org.website)}</a></span>`);
    }
    if (org.avg_rating) parts.push(`<span class="org-contact-item" style="color:#f0b429">★ ${org.avg_rating} <span style="opacity:.7;font-size:.85em">(${org.review_count} отзывов)</span></span>`);
    contacts.innerHTML = parts.join('');
  } catch {}
}

async function loadEvents() {
  const grid = document.getElementById('events-grid');
  grid.innerHTML = skeletonGrid(6);
  try {
    const events = await get('/events?organization_id=' + orgId);
    document.getElementById('stat-events').textContent = events.length;
    if (!events.length) {
      grid.innerHTML = '<div class="empty"><div class="empty-icon">🎭</div>У этой организации нет событий</div>';
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
          <span class="badge badge-gray">${escHtml(e.venue_address || '')}</span>
          <button class="fav-btn${favEventIds.has(+e.event_id)?' active':''}" onclick="event.stopPropagation();toggleEventFav(${e.event_id},this)">${favEventIds.has(+e.event_id)?'♥':'♡'}</button>
        </div>
      </div>
    `).join('');
  } catch(e) {
    grid.innerHTML = '<div class="empty"><div class="empty-icon">⚠️</div>' + escHtml(e.message) + '</div>';
  }
}

async function loadVenues() {
  const grid = document.getElementById('venues-grid');
  grid.innerHTML = skeletonGrid(6);
  try {
    const venues = await get('/venues?organization_id=' + orgId);
    document.getElementById('stat-venues').textContent = venues.length;
    if (!venues.length) {
      grid.innerHTML = '<div class="empty"><div class="empty-icon">📍</div>У этой организации нет площадок</div>';
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
    grid.innerHTML = '<div class="empty"><div class="empty-icon">⚠️</div>' + escHtml(e.message) + '</div>';
  }
}

function switchTab(tab) {
  currentTab = tab;
  document.getElementById('tab-events').classList.toggle('active', tab === 'events');
  document.getElementById('tab-venues').classList.toggle('active', tab === 'venues');
  document.getElementById('events-grid').style.display = tab === 'events' ? '' : 'none';
  document.getElementById('venues-grid').style.display = tab === 'venues' ? '' : 'none';
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

async function loadSubscription() {
  try {
    const d = await get('/orgs/' + orgId + '/subscription');
    isSubscribed = d.subscribed;
    subCount = d.count ?? 0;
    document.getElementById('stat-subs').textContent = subCount;
    const btn = document.getElementById('sub-btn');
    if (btn && auth.isLoggedIn() && auth.type() === 'user') {
      btn.style.display = '';
      btn.textContent = isSubscribed ? '🔕 Отписаться' : '🔔 Подписаться';
      btn.style.background = isSubscribed ? 'rgba(255,255,255,.35)' : 'rgba(255,255,255,.15)';
    }
  } catch {}
}

async function toggleSubscription() {
  if (!auth.isLoggedIn() || auth.type() !== 'user') { toast('Войдите чтобы подписаться', 'error'); return; }
  const btn = document.getElementById('sub-btn');
  if (btn) btn.disabled = true;
  try {
    if (isSubscribed) {
      await del('/orgs/' + orgId + '/subscribe');
      isSubscribed = false; subCount = Math.max(0, subCount - 1);
      toast('Вы отписались от организации');
    } else {
      await post('/orgs/' + orgId + '/subscribe', {});
      isSubscribed = true; subCount++;
      toast('Вы подписались на организацию 🔔', 'success');
    }
    document.getElementById('stat-subs').textContent = subCount;
    if (btn) {
      btn.textContent = isSubscribed ? '🔕 Отписаться' : '🔔 Подписаться';
      btn.style.background = isSubscribed ? 'rgba(255,255,255,.35)' : 'rgba(255,255,255,.15)';
    }
  } catch(e) { toast(e.message, 'error'); }
  finally { if (btn) btn.disabled = false; }
}

async function init() {
  await loadFavState();
  await Promise.all([loadOrg(), loadEvents(), loadSubscription()]);
  loadVenues();
}

init();
</script>
@endsection
