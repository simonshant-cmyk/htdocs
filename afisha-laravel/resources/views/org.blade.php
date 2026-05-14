@extends('layouts.app')

@section('title', 'Организация — АфишаКолыма')

@section('styles')
<style>
  .org-hero { background:var(--hero-bg); color:#fff; padding:64px 32px 52px; position:relative; overflow:hidden; }
  .org-hero::before { content:''; position:absolute; inset:0; background:radial-gradient(ellipse at 25% 60%,rgba(200,80,42,.45) 0%,transparent 55%),radial-gradient(ellipse at 80% 20%,rgba(42,110,90,.3) 0%,transparent 50%); }
  .org-hero-inner { position:relative; z-index:1; max-width:1200px; margin:0 auto; display:flex; align-items:center; gap:32px; }
  @media(max-width:700px){ .org-hero-inner{ flex-direction:column; text-align:center; } .org-hero{ padding:48px 20px 40px; } }

  .org-avatar { width:100px; height:100px; border-radius:20px; object-fit:cover; background:rgba(255,255,255,.15); border:3px solid rgba(255,255,255,.3); flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:2.8rem; }
  .org-avatar img { width:100%; height:100%; object-fit:cover; border-radius:17px; }

  .org-info h1 { font-family:var(--font-display); font-size:2.4rem; font-weight:700; margin-bottom:6px; }
  .org-info .org-desc { color:rgba(255,255,255,.65); font-size:.95rem; line-height:1.55; max-width:560px; margin-top:8px; }
  .org-badge { display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,.12); border-radius:20px; padding:4px 14px; font-size:.8rem; font-weight:600; color:rgba(255,255,255,.8); margin-bottom:8px; }

  .content { max-width:1200px; margin:36px auto; padding:0 32px; }
  @media(max-width:600px){ .content{ padding:0 16px; } }

  .section-title { font-family:var(--font-display); font-size:1.5rem; font-weight:700; margin-bottom:24px; }
  .event-card { cursor:pointer; }
  .event-card .card-footer { padding:10px 18px; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; }
  .fav-btn { width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:1.5px solid var(--border); background:var(--surface); font-size:1.05rem; cursor:pointer; transition:var(--transition); }
  .fav-btn:hover,.fav-btn.active { border-color:var(--accent); background:rgba(200,80,42,.08); color:var(--accent); }

  .stats-row { display:flex; gap:16px; flex-wrap:wrap; margin-bottom:32px; }
  .stat-card { background:var(--surface); border-radius:14px; padding:16px 24px; box-shadow:var(--shadow-card); flex:1; min-width:120px; }
  .stat-val { font-size:1.8rem; font-weight:800; font-family:var(--font-display); color:var(--accent); }
  .stat-label { font-size:.8rem; color:var(--muted); margin-top:2px; }
</style>
@endsection

@section('content')
<div class="org-hero" id="org-hero">
  <div class="org-hero-inner">
    <div class="org-avatar" id="org-avatar">🏢</div>
    <div class="org-info">
      <div class="org-badge" id="org-badge" style="display:none">🏢 Организация</div>
      <h1 id="org-name">Загрузка...</h1>
      <div class="org-desc" id="org-desc"></div>
    </div>
  </div>
</div>

<div class="content">
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-val" id="stat-events">—</div>
      <div class="stat-label">Событий</div>
    </div>
  </div>
  <div class="section-title">События организации</div>
  <div id="events-grid" class="grid-3"></div>
</div>
@endsection

@section('scripts')
<script>
const orgId = {{ $id }};
let favEventIds = new Set();
let favEventMap = {};

async function loadFavState() {
  if (!auth.isLoggedIn() || auth.type() !== 'user') return;
  try {
    const favs = await get('/favorites');
    favs.filter(f => f.event_id).forEach(f => { favEventIds.add(+f.event_id); favEventMap[+f.event_id] = f.favorite_id; });
  } catch {}
}

async function init() {
  await loadFavState();
  await loadOrg();
  await loadEvents();
}

async function loadOrg() {
  try {
    const events = await get('/events?organization_id=' + orgId);
    if (!events.length) return;
    const first = events[0];
    document.title = (first.organization_name || 'Организация') + ' — АфишаКолыма';
    document.getElementById('org-name').textContent = first.organization_name || 'Организация';
    document.getElementById('org-badge').style.display = '';
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
          <button class="fav-btn${favEventIds.has(+e.event_id)?' active':''}" onclick="event.stopPropagation();toggleFav(${e.event_id},this)">${favEventIds.has(+e.event_id)?'♥':'♡'}</button>
        </div>
      </div>
    `).join('');
  } catch(e) {
    grid.innerHTML = '<div class="empty"><div class="empty-icon">⚠️</div>' + e.message + '</div>';
  }
}

async function toggleFav(eventId, btn) {
  if (auth.isLoggedIn() && auth.type() === 'org') { toast('Недоступно для аккаунтов организации', 'error'); return; }
  if (!auth.isLoggedIn()) { toast('Войдите чтобы добавить в избранное', 'error'); return; }
  const alreadyFav = favEventIds.has(+eventId);
  btn.disabled = true;
  try {
    if (alreadyFav) {
      await del('/favorites/' + favEventMap[+eventId]);
      favEventIds.delete(+eventId); delete favEventMap[+eventId];
      btn.classList.remove('active'); btn.textContent = '♡';
      toast('Убрано из избранного');
    } else {
      const r = await post('/favorites', {event_id: eventId});
      favEventIds.add(+eventId); favEventMap[+eventId] = r?.favorite_id;
      btn.classList.add('active'); btn.textContent = '♥';
      toast('Добавлено в избранное ♥', 'success');
    }
  } catch(e) { toast(e.message, 'error'); }
  finally { btn.disabled = false; }
}

init();
</script>
@endsection
