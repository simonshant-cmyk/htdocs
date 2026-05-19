@extends('layouts.app')

@section('title', 'Организации — АфишаКолыма')

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

  /* ── Search ── */
  .search-bar { display:flex; gap:10px; margin-bottom:16px; }
  .search-input { flex:1; padding:11px 16px; border:1.5px solid var(--border); border-radius:10px; font-size:.95rem; background:var(--surface); color:var(--text); font-family:var(--font); outline:none; transition:border-color .2s; }
  .search-input:focus { border-color:var(--accent); }

  /* ── Alphabet bar ── */
  .alpha-bar { display:flex; flex-wrap:wrap; gap:4px; margin-bottom:24px; align-items:center; }
  .alpha-btn { min-width:32px; height:32px; padding:0 6px; border-radius:8px; border:1.5px solid var(--border); background:var(--surface); color:var(--muted); font-size:.82rem; font-weight:700; cursor:pointer; transition:var(--transition); display:flex; align-items:center; justify-content:center; }
  .alpha-btn:hover:not(:disabled) { border-color:var(--accent); color:var(--accent); }
  .alpha-btn.active { background:var(--accent); border-color:var(--accent); color:#fff; }
  .alpha-btn:disabled { opacity:.3; cursor:default; }
  .alpha-sep { width:1px; height:22px; background:var(--border); margin:0 4px; flex-shrink:0; }

  /* ── Cards ── */
  .org-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:20px; }

  .org-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); overflow:hidden; transition:var(--transition); cursor:pointer; display:flex; flex-direction:column; }
  .org-card:hover { box-shadow:var(--shadow-lg); transform:translateY(-2px); border-color:var(--accent); }

  .org-card-top { display:flex; align-items:center; gap:16px; padding:20px 20px 0; }
  .org-avatar { width:64px; height:64px; border-radius:14px; background:var(--bg2); display:flex; align-items:center; justify-content:center; font-size:1.6rem; overflow:hidden; flex-shrink:0; border:1.5px solid var(--border); }
  .org-avatar img { width:100%; height:100%; object-fit:cover; }
  .org-card-name { font-weight:700; font-size:1rem; color:var(--text); line-height:1.3; margin-bottom:3px; }
  .org-card-addr { font-size:.8rem; color:var(--muted); }

  .org-card-body { padding:12px 20px 16px; flex:1; }
  .org-stats { display:flex; gap:16px; margin-bottom:10px; }
  .org-stat { font-size:.82rem; color:var(--muted); }
  .org-stat strong { color:var(--text); font-weight:700; display:block; font-size:1rem; }

  .org-card-footer { padding:12px 20px; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; gap:10px; }
  .btn-sub { padding:7px 16px; border-radius:8px; font-size:.82rem; font-weight:700; cursor:pointer; transition:var(--transition); border:1.5px solid var(--border); background:var(--surface); color:var(--muted); }
  .btn-sub:hover { border-color:var(--accent); color:var(--accent); }
  .btn-sub.subbed { border-color:var(--accent2); color:var(--accent2); background:rgba(42,110,90,.07); }
  .btn-open { font-size:.82rem; font-weight:600; color:var(--accent); text-decoration:none; }
  .btn-open:hover { text-decoration:underline; }

  .count-hint { font-size:.78rem; color:var(--muted); margin-bottom:20px; }
</style>
@endsection

@section('content')
<div class="page-hero">
  <div class="page-hero-content">
    <h1>Организации</h1>
    <p>Организаторы событий и мероприятий в Магадане</p>
  </div>
</div>

<div class="content">
  <div class="search-bar">
    <input class="search-input" id="search-input" type="text" placeholder="🔍 Поиск по названию…" oninput="onSearch(this.value)">
  </div>

  <div class="alpha-bar" id="alpha-bar"></div>
  <div class="count-hint" id="count-hint"></div>
  <div class="org-grid" id="org-grid"></div>
</div>
@endsection

@section('scripts')
<script>
const RU_ALPHA = 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ'.split('');
let allOrgs = [];
let subsSet = new Set();
let searchTimer = null;
let activeLetter = null;

async function loadOrgs() {
  const grid = document.getElementById('org-grid');
  grid.innerHTML = skeletonGrid(6);
  try {
    allOrgs = await get('/orgs');
    buildAlphaBar();
    applyFilter();
  } catch(e) {
    grid.innerHTML = `<div class="empty"><div class="empty-icon">⚠️</div>${escHtml(e.message)}</div>`;
  }
}

function buildAlphaBar() {
  const bar = document.getElementById('alpha-bar');
  const presentLetters = new Set(allOrgs.map(o => o.full_name.trim().toUpperCase()[0]));

  const allBtn = document.createElement('button');
  allBtn.className = 'alpha-btn active';
  allBtn.id = 'alpha-all';
  allBtn.textContent = 'Все';
  allBtn.onclick = () => setLetter(null);
  bar.appendChild(allBtn);

  const sep = document.createElement('div');
  sep.className = 'alpha-sep';
  bar.appendChild(sep);

  RU_ALPHA.forEach(letter => {
    const btn = document.createElement('button');
    btn.className = 'alpha-btn';
    btn.id = 'alpha-' + letter;
    btn.textContent = letter;
    btn.disabled = !presentLetters.has(letter);
    btn.onclick = () => setLetter(letter);
    bar.appendChild(btn);
  });
}

function setLetter(letter) {
  activeLetter = letter;
  document.getElementById('search-input').value = '';

  document.querySelectorAll('.alpha-btn').forEach(b => b.classList.remove('active'));
  const activeId = letter ? 'alpha-' + letter : 'alpha-all';
  document.getElementById(activeId)?.classList.add('active');

  applyFilter();
}

function applyFilter() {
  const search = document.getElementById('search-input').value.trim().toLowerCase();
  let list = allOrgs;

  if (activeLetter) {
    list = list.filter(o => o.full_name.trim().toUpperCase().startsWith(activeLetter));
  }
  if (search) {
    list = list.filter(o => o.full_name.toLowerCase().includes(search));
  }

  renderOrgs(list);
}

function renderOrgs(list) {
  const grid = document.getElementById('org-grid');
  const hint = document.getElementById('count-hint');

  if (!list.length) {
    grid.innerHTML = '<div class="empty" style="grid-column:1/-1"><div class="empty-icon">🏢</div>Организации не найдены</div>';
    hint.textContent = '';
    return;
  }

  hint.textContent = `Найдено: ${list.length}`;
  grid.innerHTML = list.map(org => `
    <div class="org-card" onclick="nav('/org/${org.organization_id}')">
      <div class="org-card-top">
        <div class="org-avatar">
          ${org.image ? `<img src="${escHtml(org.image)}" alt="">` : '🏢'}
        </div>
        <div>
          <div class="org-card-name">${escHtml(org.full_name)}</div>
          ${org.address ? `<div class="org-card-addr">📍 ${escHtml(org.address)}</div>` : ''}
        </div>
      </div>
      <div class="org-card-body">
        <div class="org-stats">
          <div class="org-stat"><strong>${org.events_count}</strong>Событий</div>
          <div class="org-stat"><strong id="subs-${org.organization_id}">${org.subs_count}</strong>Подписчиков</div>
          ${org.avg_rating ? `<div class="org-stat"><strong style="color:#f0b429">★ ${org.avg_rating}</strong><span style="font-size:.72rem">(${org.review_count})</span></div>` : ''}
        </div>
        ${org.website ? `<div style="font-size:.8rem;color:var(--muted)">🌐 <a href="${/^https?:\/\//i.test(org.website) ? org.website : 'https://'+org.website}" target="_blank" rel="noopener" onclick="event.stopPropagation()" style="color:var(--muted)">${escHtml(org.website)}</a></div>` : ''}
      </div>
      <div class="org-card-footer">
        <a class="btn-open" href="${(window.APP_BASE||'')}/org/${org.organization_id}" onclick="event.stopPropagation()">Перейти →</a>
        ${auth.isLoggedIn() && auth.type() === 'user' ? `
          <button id="sub-btn-${org.organization_id}"
            class="btn-sub${subsSet.has(org.organization_id) ? ' subbed' : ''}"
            onclick="event.stopPropagation();toggleSub(${org.organization_id})">
            ${subsSet.has(org.organization_id) ? '🔕 Отписаться' : '🔔 Подписаться'}
          </button>
        ` : ''}
      </div>
    </div>
  `).join('');
}

async function toggleSub(orgId) {
  if (!auth.isLoggedIn() || auth.type() !== 'user') { toast('Войдите чтобы подписаться', 'error'); return; }
  const btn = document.getElementById('sub-btn-' + orgId);
  const cntEl = document.getElementById('subs-' + orgId);
  if (btn) btn.disabled = true;
  const already = subsSet.has(orgId);
  try {
    if (already) {
      await del('/orgs/' + orgId + '/subscribe');
      subsSet.delete(orgId);
      if (cntEl) cntEl.textContent = Math.max(0, (+cntEl.textContent || 0) - 1);
      toast('Вы отписались');
    } else {
      await post('/orgs/' + orgId + '/subscribe', {});
      subsSet.add(orgId);
      if (cntEl) cntEl.textContent = (+cntEl.textContent || 0) + 1;
      toast('Вы подписались 🔔', 'success');
    }
    if (btn) {
      btn.classList.toggle('subbed', !already);
      btn.textContent = !already ? '🔕 Отписаться' : '🔔 Подписаться';
    }
  } catch(e) { toast(e.message, 'error'); }
  finally { if (btn) btn.disabled = false; }
}

function onSearch(val) {
  activeLetter = null;
  document.querySelectorAll('.alpha-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('alpha-all')?.classList.add('active');
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => applyFilter(), 200);
}

loadOrgs();
</script>
@endsection
