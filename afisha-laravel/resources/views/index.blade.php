@extends('layouts.app')

@section('title', 'АфишаКолыма — События и площадки')

@section('styles')
<style>
/* ── Navbar overrides (transparent over banner) ── */
#navbar {
  background: var(--hero-bg) !important;
  backdrop-filter: none !important;
  border-bottom: none !important;
  height: 60px !important;
}
#navbar .navbar-brand { color:#fff!important;font-weight:800!important;font-size:1.25rem!important;margin-right:0!important;letter-spacing:-.02em; }
#navbar #theme-btn   { color:rgba(255,255,255,.5)!important; }
#navbar #fav-link    { border-color:rgba(255,255,255,.15)!important;color:rgba(255,255,255,.78)!important; }
#navbar #fav-link:hover  { border-color:rgba(255,255,255,.45)!important;color:#fff!important; }
#navbar #cart-link   { border-color:rgba(255,255,255,.15)!important;color:rgba(255,255,255,.78)!important; }
#navbar #cart-link:hover { border-color:rgba(255,255,255,.45)!important;color:#fff!important; }
#navbar #user-menu-wrap>button { border-color:rgba(255,255,255,.15)!important;color:rgba(255,255,255,.82)!important; }
#navbar #user-menu-wrap>button:hover { border-color:rgba(255,255,255,.45)!important;color:#fff!important; }
#navbar .navbar-btn.outline { border-color:rgba(255,255,255,.2)!important;color:rgba(255,255,255,.75)!important;background:transparent!important; }
#navbar .navbar-btn.outline:hover { border-color:rgba(255,255,255,.5)!important;color:#fff!important; }

/* ── Nav-mid: category links + search ── */
.nav-mid { flex:1;display:flex;align-items:center;padding:0 8px 0 20px;gap:8px;min-width:0; }
.nav-cats-row { display:flex;align-items:center;gap:2px;overflow:hidden;flex-shrink:1;min-width:0; }
.nav-cat-link { font-size:.8rem;font-weight:500;white-space:nowrap;color:rgba(255,255,255,.46);text-decoration:none;padding:5px 11px;border-radius:6px;transition:all .17s; }
.nav-cat-link:hover { color:rgba(255,255,255,.9);background:rgba(255,255,255,.08); }
.nav-cat-link.active { color:#fff;font-weight:700;background:rgba(255,255,255,.1); }
.nav-allcats-wrap { position:relative;flex-shrink:0; }
.nav-allcats-btn { display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:20px;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.07);color:rgba(255,255,255,.75);font-size:.8rem;font-weight:600;cursor:pointer;transition:all .17s;white-space:nowrap; }
.nav-allcats-btn:hover,.nav-allcats-btn.open { background:rgba(255,255,255,.14);border-color:rgba(255,255,255,.35);color:#fff; }
.nav-allcats-btn svg { transition:transform .2s; }
.nav-allcats-btn.open svg { transform:rotate(180deg); }
.nav-allcats-dd { position:absolute;top:calc(100%+10px);left:0;z-index:501;background:var(--surface);border-radius:16px;border:1px solid var(--border);box-shadow:0 14px 44px rgba(0,0,0,.22);min-width:220px;overflow:hidden;display:none; }
.nav-allcats-dd.open { display:block; }
.nav-allcats-dd-item { display:flex;align-items:center;gap:10px;padding:10px 18px;cursor:pointer;font-size:.88rem;color:var(--text);transition:background .12s;white-space:nowrap; }
.nav-allcats-dd-item:hover { background:var(--bg2); }
.nav-allcats-dd-item.active { color:var(--accent);font-weight:700; }
.nav-allcats-dd-item .dd-dot { width:7px;height:7px;border-radius:50%;background:var(--border);flex-shrink:0;transition:background .15s; }
.nav-allcats-dd-item.active .dd-dot { background:var(--accent); }
.nav-search-outer { position:relative; }
.nav-search-wrap { display:flex;align-items:center;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);border-radius:24px;padding:0 6px 0 16px;transition:all .2s; }
.nav-search-wrap:focus-within { background:rgba(255,255,255,.14);border-color:rgba(255,255,255,.4);box-shadow:0 0 0 3px rgba(255,255,255,.07); }
.nav-search-wrap input { background:none;border:none;outline:none;color:#fff;font-size:.88rem;font-family:var(--font);width:210px;padding:9px 0;transition:width .3s; }
.nav-search-wrap input:focus { width:280px; }
.nav-search-wrap input::placeholder { color:rgba(255,255,255,.38); }
.nav-search-wrap input:-webkit-autofill,.nav-search-wrap input:-webkit-autofill:focus { -webkit-box-shadow:0 0 0 1000px transparent inset!important;-webkit-text-fill-color:#fff!important;transition:background-color 600000s; }
.nav-search-btn { background:none;border:none;cursor:pointer;padding:6px 10px;color:rgba(255,255,255,.42);display:flex;align-items:center;transition:color .15s; }
.nav-search-btn:hover { color:rgba(255,255,255,.9); }
.search-sugg { position:absolute;top:calc(100%+10px);right:0;min-width:300px;z-index:500;background:var(--surface);border-radius:16px;border:1px solid var(--border);box-shadow:0 12px 40px rgba(0,0,0,.22);overflow:hidden; }
.sugg-section-label { padding:10px 16px 4px;font-size:.7rem;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.1em; }
.sugg-item { display:flex;align-items:center;gap:10px;padding:10px 16px;cursor:pointer;transition:background .12s;color:var(--text);font-size:.88rem;user-select:none; }
.sugg-item:hover { background:var(--bg2); }
.sugg-icon { color:var(--muted);flex-shrink:0;display:flex; }
.sugg-text { flex:1; }
.sugg-del { flex-shrink:0;padding:2px 7px;border-radius:6px;font-size:.72rem;color:var(--muted);transition:all .12s;line-height:1.6; }
.sugg-del:hover { color:var(--accent);background:rgba(200,80,42,.1); }
.sugg-item.sugg-clear { border-top:1px solid var(--border);font-size:.8rem;color:var(--muted);justify-content:center;padding:9px 16px; }
.sugg-item.sugg-clear:hover { color:var(--accent);background:rgba(200,80,42,.05); }
@media(max-width:768px){ .nav-cats-row{display:none} .nav-search-wrap input{width:130px} .nav-search-wrap input:focus{width:160px} }

/* ── Banner ── */
.banner { position:relative;width:100%;height:460px;overflow:hidden;background:var(--hero-bg); }
@media(max-width:768px){.banner{height:300px}}
@media(max-width:480px){.banner{height:240px}}
.banner-track { display:flex;height:100%;transition:transform .6s cubic-bezier(.4,0,.2,1); }
.banner-slide { min-width:100%;height:100%;background-size:cover;background-position:center;cursor:pointer;position:relative;overflow:hidden; }
.banner-slide::before { content:'';position:absolute;inset:0;background:linear-gradient(to right,rgba(0,0,0,.82) 0%,rgba(0,0,0,.45) 38%,rgba(0,0,0,.08) 65%,transparent 100%),linear-gradient(to top,rgba(0,0,0,.5) 0%,transparent 40%);z-index:1; }
.banner-slide-inner { position:absolute;inset:0;z-index:2;display:flex;align-items:center;padding:0 64px; }
@media(max-width:600px){.banner-slide-inner{padding:0 24px}}
.banner-slide-content { max-width:480px;color:#fff; }
.banner-slide-tag { display:inline-block;font-size:.68rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.55);background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);border-radius:4px;padding:3px 10px;margin-bottom:14px;backdrop-filter:blur(4px); }
.banner-slide-title { font-family:var(--font);font-size:2.4rem;font-weight:800;line-height:1.18;letter-spacing:-.035em;margin-bottom:12px;text-shadow:0 2px 20px rgba(0,0,0,.4); }
@media(max-width:600px){.banner-slide-title{font-size:1.5rem}}
.banner-slide-meta { display:flex;flex-direction:column;gap:5px;font-size:.83rem;color:rgba(255,255,255,.62);margin-bottom:24px; }
.banner-meta-row { display:flex;align-items:center;gap:7px; }
.banner-meta-row svg { flex-shrink:0;opacity:.65; }
.banner-slide-btn { display:inline-flex;align-items:center;gap:8px;padding:12px 30px;border-radius:10px;background:var(--accent);color:#fff;font-weight:700;font-size:.9rem;box-shadow:0 4px 24px rgba(200,80,42,.4);transition:opacity .2s,transform .2s;pointer-events:none; }
.banner-slide:hover .banner-slide-btn { opacity:.9;transform:translateY(-1px); }
.banner-arrow { position:absolute;top:50%;transform:translateY(-50%);width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.12);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.18);color:#fff;font-size:1.3rem;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .2s;z-index:10; }
.banner-arrow:hover { background:rgba(255,255,255,.22); }
.banner-prev { left:20px; } .banner-next { right:20px; }
@media(max-width:480px){.banner-arrow{display:none}}
.banner-progress { position:absolute;bottom:0;left:0;right:0;display:flex;gap:5px;padding:0 64px 22px;z-index:10; }
@media(max-width:600px){.banner-progress{padding:0 24px 16px}}
.bp-seg { flex:1;height:3px;border-radius:2px;background:rgba(255,255,255,.2);overflow:hidden;cursor:pointer;position:relative; }
.bp-seg .bp-fill { position:absolute;inset:0;width:0;background:rgba(255,255,255,.9);border-radius:2px; }
.bp-seg.done .bp-fill { width:100%;transition:none; }
.banner::after { content:'';position:absolute;bottom:0;left:0;right:0;height:100px;pointer-events:none;z-index:3;background:linear-gradient(to bottom,transparent 0%,var(--bg) 100%); }

/* ── Tabs ── */
.main-tabs { display:flex;gap:4px;border-bottom:1.5px solid var(--border);padding:0 32px; }
@media(max-width:600px){.main-tabs{padding:0 16px}}
.main-tab { padding:11px 24px;font-size:.92rem;font-weight:700;color:var(--muted);cursor:pointer;transition:var(--transition);border-bottom:2.5px solid transparent;margin-bottom:-1.5px;border-radius:8px 8px 0 0; }
.main-tab.active { color:var(--text);border-bottom-color:var(--accent); }
.main-tab:hover:not(.active) { color:var(--text);background:rgba(200,80,42,.04); }

/* ── Calendar strip — FULL WIDTH ── */
.date-strip-wrap { width:100%;background:var(--bg);border-bottom:1.5px solid var(--border);padding:16px 0 12px; }
.date-strip { display:flex;gap:6px;overflow-x:auto;padding:0 32px 2px;scrollbar-width:none; }
.date-strip::-webkit-scrollbar { display:none; }
.date-chip { display:flex;flex-direction:column;align-items:center;min-width:54px;padding:9px 8px;border-radius:14px;flex-shrink:0;border:1.5px solid var(--border);background:var(--surface);cursor:pointer;transition:var(--transition);user-select:none; }
.date-chip:hover { border-color:rgba(200,80,42,.35);background:rgba(200,80,42,.04); }
.date-chip.active { border-color:var(--accent);background:rgba(200,80,42,.09); }
.dc-day { font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted); }
.dc-num { font-size:1.05rem;font-weight:800;color:var(--text);margin-top:3px;line-height:1; }
.dc-mon { font-size:.6rem;color:var(--muted);margin-top:2px; }
.date-chip.weekend .dc-day,.date-chip.weekend .dc-num { color:#e05a3a; }
.date-chip.today .dc-day { color:var(--accent); }
.date-chip.active .dc-day,.date-chip.active .dc-num { color:var(--accent); }

/* ── Page body: cards + sidebar ── */
.page-body { display:flex;gap:24px;align-items:flex-start;max-width:1280px;margin:0 auto;padding:24px 32px 48px; }
@media(max-width:600px){.page-body{padding:16px 16px 40px;}}

/* Cards column */
.cards-col { flex:1;min-width:0; }
.cards-top { display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:12px;flex-wrap:wrap; }
.cards-count { font-size:.82rem;color:var(--muted);font-weight:500; }
.active-tags { display:flex;gap:6px;flex-wrap:wrap;flex:1; }
.afilter-tag { display:inline-flex;align-items:center;gap:5px;padding:4px 10px 4px 12px;border-radius:20px;background:rgba(200,80,42,.08);color:var(--accent);border:1.5px solid rgba(200,80,42,.25);font-size:.78rem;font-weight:600;cursor:pointer;transition:var(--transition);white-space:nowrap; }
.afilter-tag:hover { background:rgba(200,80,42,.16); }
.aft-x { opacity:.55;font-size:.7rem;margin-left:1px; }
.tab-viewport { overflow:hidden; }
.event-card { cursor:pointer; }
.event-card .card-footer { padding:12px 18px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center; }
.fav-btn { width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:1.5px solid var(--border);background:var(--surface);font-size:1.05rem;cursor:pointer;transition:var(--transition); }
.fav-btn:hover,.fav-btn.active { border-color:var(--accent);background:rgba(200,80,42,.08);color:var(--accent); }

/* ── Filter sidebar ── */
.filter-col { width:252px;flex-shrink:0;position:sticky;top:76px;max-height:calc(100vh - 96px);overflow-y:auto;scrollbar-width:thin;scrollbar-color:var(--border) transparent;display:flex;flex-direction:column;gap:10px; }
.filter-col::-webkit-scrollbar { width:4px; }
.filter-col::-webkit-scrollbar-thumb { background:var(--border);border-radius:2px; }

.fs-card { background:var(--surface);border-radius:16px;border:1px solid var(--border);overflow:hidden; }
.fs-section { padding:14px 16px;border-bottom:1px solid var(--bg2); }
.fs-section:last-child { border-bottom:none; }
.fs-label { font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:10px;display:flex;align-items:center;justify-content:space-between; }
.fs-label-clear { font-size:.7rem;font-weight:600;color:var(--muted);cursor:pointer;letter-spacing:0;text-transform:none;transition:color .15s; }
.fs-label-clear:hover { color:var(--accent); }

/* Sort chips */
.fs-opts { display:flex;flex-wrap:wrap;gap:5px; }
.fs-opt { padding:5px 11px;border-radius:20px;font-size:.79rem;font-weight:600;border:1.5px solid var(--border);background:var(--bg2);color:var(--muted);cursor:pointer;transition:var(--transition);white-space:nowrap;font-family:var(--font); }
.fs-opt:hover { border-color:rgba(200,80,42,.35);color:var(--accent); }
.fs-opt.active { border-color:var(--accent);color:var(--accent);background:rgba(200,80,42,.08); }

/* Category list */
.fs-cat-list { display:flex;flex-direction:column;gap:1px; }
.fs-cat-item { display:flex;align-items:center;gap:9px;padding:7px 4px;cursor:pointer;border-radius:8px;transition:background .12s,color .12s;color:var(--text); }
.fs-cat-item:hover { background:var(--bg2);color:var(--accent); }
.fs-cat-item.active { color:var(--accent);font-weight:700; }
.fs-cat-dot { width:7px;height:7px;border-radius:50%;background:var(--border);flex-shrink:0;transition:background .2s; }
.fs-cat-item.active .fs-cat-dot { background:var(--accent); }
.fs-cat-icon { font-size:.9rem;flex-shrink:0;width:18px;text-align:center; }
.fs-cat-name { font-size:.85rem;flex:1; }

/* Toggle row */
.fs-toggle-row { display:flex;align-items:center;justify-content:space-between;gap:10px; }
.fs-toggle-label { font-size:.87rem;font-weight:500;color:var(--text); }
.fs-toggle { position:relative;width:40px;height:24px;flex-shrink:0; }
.fs-toggle input { opacity:0;width:0;height:0;position:absolute; }
.fs-toggle-track { position:absolute;inset:0;border-radius:24px;background:var(--border);cursor:pointer;transition:background .2s; }
.fs-toggle input:checked ~ .fs-toggle-track { background:var(--accent); }
.fs-toggle-track::after { content:'';position:absolute;width:18px;height:18px;left:3px;top:3px;border-radius:50%;background:#fff;transition:transform .2s;box-shadow:0 1px 4px rgba(0,0,0,.2); }
.fs-toggle input:checked ~ .fs-toggle-track::after { transform:translateX(16px); }

/* Reset */
.fs-reset { width:100%;padding:10px;border-radius:12px;border:1.5px solid var(--border);background:none;color:var(--muted);font-size:.84rem;font-weight:600;cursor:pointer;transition:var(--transition);font-family:var(--font);text-align:center; }
.fs-reset:hover { border-color:rgba(200,80,42,.4);color:var(--accent);background:rgba(200,80,42,.04); }

/* Mobile: sidebar below cards */
@media(max-width:900px){
  .page-body { flex-direction:column; }
  .filter-col { width:100%;position:static;max-height:none;overflow-y:visible; }
  .filter-col .fs-card { display:none; } /* collapsed by default on mobile */
  .filter-col.mobile-open .fs-card { display:block; }
  .filter-mobile-toggle { display:flex; }
}
@media(min-width:901px){ .filter-mobile-toggle { display:none!important; } }
.filter-mobile-toggle { align-items:center;gap:7px;padding:9px 18px;border-radius:22px;border:1.5px solid var(--border);background:var(--surface);color:var(--text);font-size:.84rem;font-weight:600;cursor:pointer;transition:var(--transition);margin-bottom:12px;font-family:var(--font);display:none; }
.filter-mobile-toggle:hover { border-color:var(--accent);color:var(--accent); }
.filter-mobile-toggle.active { border-color:var(--accent);color:var(--accent);background:rgba(200,80,42,.07); }
</style>
@endsection

@section('content')
<div class="banner" id="banner">
  <div class="banner-track" id="banner-track"><div style="min-width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.3);font-size:.85rem">Загрузка...</div></div>
  <button class="banner-arrow banner-prev" onclick="bannerMove(-1)">&#8249;</button>
  <button class="banner-arrow banner-next" onclick="bannerMove(1)">&#8250;</button>
  <div class="banner-progress" id="banner-progress"></div>
</div>

<div class="main-tabs">
  <div class="main-tab active" id="tab-events" onclick="switchTab('events')">🎭 События</div>
  <div class="main-tab" id="tab-venues" onclick="switchTab('venues')">📍 Площадки</div>
</div>

<!-- Full-width calendar strip (events only) -->
<div class="date-strip-wrap" id="date-strip-wrap">
  <div class="date-strip" id="date-strip"></div>
</div>

<!-- Main layout -->
<div class="page-body">

  <!-- Cards column -->
  <div class="cards-col">
    <div class="cards-top" id="cards-top" style="display:none">
      <span class="cards-count" id="cards-count"></span>
      <div class="active-tags" id="active-tags"></div>
    </div>
    <div class="tab-viewport">
      <div id="events-grid" class="grid-3"></div>
      <div id="venues-grid" class="grid-3" style="display:none"></div>
    </div>
    <div id="pagination" class="pagination"></div>
  </div>

  <!-- Filter sidebar -->
  <div class="filter-col" id="filter-col">
    <button class="filter-mobile-toggle" id="filter-mobile-toggle" onclick="toggleMobileSidebar()">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
      Фильтры
      <span id="fs-badge" style="display:none;background:var(--accent);color:#fff;border-radius:9px;padding:0 6px;font-size:.68rem;font-weight:800">0</span>
    </button>

    <div class="fs-card">
      <!-- Sort -->
      <div class="fs-section">
        <div class="fs-label">Сортировка</div>
        <div class="fs-opts" id="fs-sort-opts"></div>
      </div>

      <!-- Categories -->
      <div class="fs-section">
        <div class="fs-label">
          Категория
          <span class="fs-label-clear" id="fs-cat-clear" onclick="clearCatFilter()" style="display:none">Сбросить</span>
        </div>
        <div class="fs-cat-list" id="fs-cat-list">
          <div style="color:var(--muted);font-size:.82rem;padding:4px 0">Загрузка...</div>
        </div>
      </div>

      <!-- Events-only sections -->
      <div id="fs-events-body">
        <div class="fs-section">
          <div class="fs-label">Когда</div>
          <div class="fs-opts" id="fs-presets">
            <button class="fs-opt preset-btn" data-period="today"   onclick="fsSetPreset('today',this)">Сегодня</button>
            <button class="fs-opt preset-btn" data-period="tomorrow" onclick="fsSetPreset('tomorrow',this)">Завтра</button>
            <button class="fs-opt preset-btn" data-period="weekend" onclick="fsSetPreset('weekend',this)">Выходные</button>
            <button class="fs-opt preset-btn" data-period="week"    onclick="fsSetPreset('week',this)">Эта неделя</button>
          </div>
          <div style="display:flex;gap:8px;margin-top:10px;align-items:center">
            <input type="date" id="fs-date-from" onchange="fsOnDateChange()"
              style="flex:1;background:var(--bg2);border:1.5px solid var(--border);border-radius:9px;padding:7px 10px;font-size:.8rem;color:var(--text);font-family:var(--font);outline:none">
            <span style="color:var(--muted);font-size:.8rem;flex-shrink:0">—</span>
            <input type="date" id="fs-date-to" onchange="fsOnDateChange()"
              style="flex:1;background:var(--bg2);border:1.5px solid var(--border);border-radius:9px;padding:7px 10px;font-size:.8rem;color:var(--text);font-family:var(--font);outline:none">
          </div>
        </div>
        <div class="fs-section fs-toggle-row">
          <span class="fs-toggle-label">Только бесплатные</span>
          <label class="fs-toggle"><input type="checkbox" id="fs-free" onchange="fsApply()"><span class="fs-toggle-track"></span></label>
        </div>
        <div class="fs-section fs-toggle-row">
          <span class="fs-toggle-label">Только с фото</span>
          <label class="fs-toggle"><input type="checkbox" id="fs-img" onchange="fsApply()"><span class="fs-toggle-track"></span></label>
        </div>
      </div>

      <!-- Venues-only sections -->
      <div id="fs-venues-body" style="display:none">
        <div class="fs-section">
          <div class="fs-label">Возрастное ограничение</div>
          <div class="fs-opts" id="fs-age-opts">
            <button class="fs-opt active" data-age="" onclick="fsSetAge('',this)">Любое</button>
            <button class="fs-opt" data-age="0"  onclick="fsSetAge('0',this)">0+</button>
            <button class="fs-opt" data-age="12" onclick="fsSetAge('12',this)">12+</button>
            <button class="fs-opt" data-age="16" onclick="fsSetAge('16',this)">16+</button>
            <button class="fs-opt" data-age="18" onclick="fsSetAge('18',this)">18+</button>
          </div>
        </div>
      </div>
    </div>

    <button class="fs-reset" onclick="resetFilters()">Сбросить все фильтры</button>
  </div>

</div>

<!-- Hidden inputs for dates -->
<input type="date" id="filter-date-from" style="display:none">
<input type="date" id="filter-date-to"   style="display:none">
@endsection

@section('scripts')
<script>
const PAGE_SIZE = 12;
let currentTab = 'events';
let categories = [];
let activeCategory = null;
let currentPage = 1;
let totalPages = 1;

// Events state
let freeOnly = false;
let imgOnly  = false;
let activePreset = null;
let selectedDateStr = null;
let currentSort = 'date_asc';

// Venues state
let venueSort = 'name_asc';
let venueAge  = '';

// Favs
let favEventIds = new Set(), favMap = {};
let favVenueIds = new Set(), favVenueMap = {};

let tabAnimating = false;

const EVENT_SORTS = [['date_asc','По дате ↑'],['date_desc','По дате ↓'],['price_asc','Дешевле'],['price_desc','Дороже']];
const VENUE_SORTS = [['name_asc','А — Я'],['name_desc','Я — А'],['cat_asc','По категории']];

const CAT_ICONS = {
  'концерт':'🎵','музык':'🎵','театр':'🎭','кино':'🎬','выставк':'🖼️',
  'спорт':'⚽','фестивал':'🎉','лекци':'📚','мастер':'🛠️','клуб':'🍸',
  'шоу':'✨','детск':'👶','танц':'💃','цирк':'🎪','вечерин':'🎊',
};
function catIcon(name) { const lc=(name||'').toLowerCase(); for(const[k,v]of Object.entries(CAT_ICONS))if(lc.includes(k))return v; return ''; }

const BANNER_GRADIENTS = [
  'linear-gradient(135deg,#0d1b2a 0%,#1a3a5c 40%,#c8502a 100%)',
  'linear-gradient(135deg,#0a1628 0%,#1e3a5f 45%,#2a6e5a 100%)',
  'linear-gradient(135deg,#130d20 0%,#2d1b4e 45%,#7b2d8b 100%)',
  'linear-gradient(135deg,#0a1f1a 0%,#0f3d2e 50%,#1a7a4a 100%)',
  'linear-gradient(135deg,#1a0e0a 0%,#3d1f0f 45%,#8a4a1a 100%)',
];
let bannerItems=[],bannerIdx=0,bannerTimer=null;
const BANNER_MS=5000;

// ── Init ──
async function loadFavState() {
  if(!auth.isLoggedIn()||auth.type()!=='user')return;
  try{ const f=await get('/favorites'); f.filter(x=>x.event_id).forEach(x=>{favEventIds.add(+x.event_id);favMap[+x.event_id]=x.favorite_id;}); f.filter(x=>x.venue_id&&!x.event_id).forEach(x=>{favVenueIds.add(+x.venue_id);favVenueMap[+x.venue_id]=x.favorite_id;}); }catch{}
}

async function init() {
  buildDateStrip();
  renderSortOpts();
  initBanner();
  await loadCategories();
  // Inject nav extras (nav-mid was created by renderNavbar before init runs)
  if (document.getElementById('nav-mid')) {
    injectNavExtras();
    const nc = document.getElementById('nav-cats');
    if (nc && categories.length) {
      nc.innerHTML = categories.map(c => `<a class="nav-cat-link" href="#" data-cat-id="${c.id}" onclick="event.preventDefault();selectNavCat(${c.id},this)">${escHtml(c.name)}</a>`).join('');
      requestAnimationFrame(() => requestAnimationFrame(updateNavCatsOverflow));
    }
  }
  await loadFavState();
  await loadEvents();
}

// ── Banner ──
async function initBanner() {
  try{ const r=await get('/events?limit=6&sort=date_asc'); bannerItems=(r.items??r).slice(0,6); if(!bannerItems.length){document.getElementById('banner').style.display='none';return;} renderBannerSlides();startBannerAuto(); }
  catch(e){ document.getElementById('banner').style.display='none'; }
}
function renderBannerSlides() {
  const track=document.getElementById('banner-track'),prog=document.getElementById('banner-progress');
  track.innerHTML=bannerItems.map((e,i)=>{
    const bg=e.image?`url('${e.image}')`:BANNER_GRADIENTS[i%BANNER_GRADIENTS.length];
    return `<div class="banner-slide" style="background-image:${bg}" onclick="nav('/event/${e.event_id}')">
      <div class="banner-slide-inner"><div class="banner-slide-content">
        <div class="banner-slide-tag">${escHtml(e.category_name||'Событие')}</div>
        <div class="banner-slide-title">${escHtml(e.title)}</div>
        <div class="banner-slide-meta">
          <div class="banner-meta-row"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>${fmtDate(e.start_datetime)}</div>
          ${e.venue_name?`<div class="banner-meta-row"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>${escHtml(e.venue_name)}</div>`:''}
        </div>
        <div class="banner-slide-btn">${+e.price===0?'Получить':'Купить'} билет →</div>
      </div></div>
    </div>`;
  }).join('');
  prog.innerHTML=bannerItems.map((_,i)=>`<div class="bp-seg" onclick="bannerGo(${i})"><div class="bp-fill"></div></div>`).join('');
  updateBannerProgress(true);
}
function bannerSetPos(){const t=document.getElementById('banner-track');if(t)t.style.transform=`translateX(-${bannerIdx*100}%)`;updateBannerProgress();}
function updateBannerProgress(initial=false){
  document.querySelectorAll('.bp-seg').forEach((seg,i)=>{
    const fill=seg.querySelector('.bp-fill');
    if(i<bannerIdx){seg.className='bp-seg done';fill.style.transition='none';fill.style.width='100%';}
    else if(i===bannerIdx){seg.className='bp-seg';fill.style.transition='none';fill.style.width='0%';fill.offsetHeight;fill.style.transition=`width ${BANNER_MS}ms linear`;requestAnimationFrame(()=>{fill.style.width='100%';});}
    else{seg.className='bp-seg';fill.style.transition='none';fill.style.width='0%';}
  });
}
function bannerMove(dir){if(!bannerItems.length)return;bannerIdx=(bannerIdx+dir+bannerItems.length)%bannerItems.length;bannerSetPos();resetBannerAuto();}
function bannerGo(i){bannerIdx=i;bannerSetPos();resetBannerAuto();}
function startBannerAuto(){bannerTimer=setInterval(()=>{bannerIdx=(bannerIdx+1)%bannerItems.length;bannerSetPos();},BANNER_MS);}
function resetBannerAuto(){clearInterval(bannerTimer);startBannerAuto();}

// ── Nav extras ──
function injectNavExtras() {
  const navMid=document.getElementById('nav-mid'); if(!navMid)return;
  navMid.innerHTML=`
    <nav class="nav-cats-row" id="nav-cats"></nav>
    <div style="flex:1"></div>
    <div class="nav-allcats-wrap">
      <button class="nav-allcats-btn" id="nav-allcats-btn" onclick="toggleAllCats()" style="display:none">Ещё <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></button>
      <div class="nav-allcats-dd" id="nav-allcats-dd"></div>
    </div>
    <div class="nav-search-outer">
      <div class="nav-search-wrap">
        <input type="text" id="search-input" placeholder="Найти..." autocomplete="off" autocorrect="off" spellcheck="false"
          oninput="onSearchInput(this.value)" onfocus="onSearchInput(this.value)" onblur="hideSearchSugg()" onkeydown="handleSearchKey(event)">
        <button class="nav-search-btn" onclick="doSearch()"><svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg></button>
      </div>
      <div class="search-sugg" id="search-sugg" style="display:none"></div>
    </div>`;
}

// ── Categories ──
async function loadCategories() {
  try {
    categories = await get('/categories') || [];
    // Nav bar
    const navCats = document.getElementById('nav-cats');
    if(navCats){ navCats.innerHTML=categories.map(c=>`<a class="nav-cat-link" href="#" data-cat-id="${c.id}" onclick="event.preventDefault();selectNavCat(${c.id},this)">${escHtml(c.name)}</a>`).join(''); requestAnimationFrame(()=>requestAnimationFrame(updateNavCatsOverflow)); }
    // Sidebar list
    renderSidebarCats();
  } catch(e) {}
}

function renderSidebarCats() {
  const list = document.getElementById('fs-cat-list'); if(!list) return;
  list.innerHTML = `<div class="fs-cat-item${!activeCategory?' active':''}" onclick="selectCat(null,this)"><div class="fs-cat-dot"></div><span class="fs-cat-icon"></span><span class="fs-cat-name">Все</span></div>` +
    categories.map(c => {
      const icon = catIcon(c.name);
      return `<div class="fs-cat-item${activeCategory===c.id?' active':''}" onclick="selectCat(${c.id},this)"><div class="fs-cat-dot"></div><span class="fs-cat-icon">${icon}</span><span class="fs-cat-name">${escHtml(c.name)}</span></div>`;
    }).join('');
  document.getElementById('fs-cat-clear').style.display = activeCategory ? '' : 'none';
}

function selectCat(id, el) {
  activeCategory = (activeCategory === id && id !== null) ? null : id;
  renderSidebarCats();
  // sync nav links
  document.querySelectorAll('.nav-cat-link').forEach(a => a.classList.toggle('active', id!==null && +a.dataset.catId===id));
  currentPage = 1; updateActiveTags();
  currentTab==='events' ? loadEvents() : loadVenues();
}

function selectNavCat(id, el) {
  activeCategory = activeCategory===id ? null : id;
  document.querySelectorAll('.nav-cat-link').forEach(a => a.classList.toggle('active', activeCategory!==null && +a.dataset.catId===activeCategory));
  renderSidebarCats(); currentPage=1; updateActiveTags();
  if(currentTab!=='events') switchTab('events'); else loadEvents();
  const tabs=document.querySelector('.main-tabs'); if(tabs) window.scrollTo({top:tabs.offsetTop-68,behavior:'smooth'});
}

function clearCatFilter() { activeCategory=null; renderSidebarCats(); document.querySelectorAll('.nav-cat-link').forEach(a=>a.classList.remove('active')); currentPage=1; updateActiveTags(); currentTab==='events'?loadEvents():loadVenues(); }

// ── Sort ──
function renderSortOpts() {
  const el = document.getElementById('fs-sort-opts'); if(!el) return;
  const opts = currentTab==='events' ? EVENT_SORTS : VENUE_SORTS;
  const cur  = currentTab==='events' ? currentSort : venueSort;
  el.innerHTML = opts.map(([val,lbl])=>`<button class="fs-opt${cur===val?' active':''}" onclick="setSort('${val}')">${lbl}</button>`).join('');
}

function setSort(val) {
  if(currentTab==='events'){currentSort=val;}else{venueSort=val;}
  renderSortOpts(); updateActiveTags(); currentPage=1;
  currentTab==='events' ? loadEvents() : loadVenues();
}

// ── Date strip ──
function buildDateStrip() {
  const strip=document.getElementById('date-strip'); if(!strip)return;
  const today=new Date(); today.setHours(0,0,0,0);
  const DAY=['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],MON=['янв','фев','мар','апр','май','июн','июл','авг','сен','окт','ноя','дек'];
  strip.innerHTML=Array.from({length:60},(_,i)=>{
    const d=new Date(today); d.setDate(today.getDate()+i);
    const iso=d.toISOString().split('T')[0]; const dow=d.getDay(); const we=dow===0||dow===6;
    return `<div class="date-chip${we?' weekend':''}${i===0?' today':''}" data-date="${iso}" onclick="selectDate('${iso}',this)">
      <span class="dc-day">${i===0?'Сег':DAY[dow]}</span>
      <span class="dc-num">${d.getDate()}</span>
      <span class="dc-mon">${MON[d.getMonth()]}</span>
    </div>`;
  }).join('');
}

function selectDate(iso, el) {
  const was = selectedDateStr===iso;
  document.querySelectorAll('.date-chip').forEach(c=>c.classList.remove('active'));
  selectedDateStr = was ? null : iso;
  if(!was){ el.classList.add('active'); document.getElementById('fs-date-from').value=iso; document.getElementById('fs-date-to').value=iso; document.getElementById('filter-date-from').value=iso; document.getElementById('filter-date-to').value=iso; activePreset=null; document.querySelectorAll('.preset-btn').forEach(b=>b.classList.remove('active')); }
  else { ['fs-date-from','fs-date-to','filter-date-from','filter-date-to'].forEach(id=>{const e=document.getElementById(id);if(e)e.value='';}); }
  updateActiveTags(); currentPage=1; loadEvents();
}

// ── Sidebar filter actions ──
function fsSetPreset(period, el) {
  const today=new Date(); today.setHours(0,0,0,0);
  const toIso=d=>d.toISOString().split('T')[0];
  if(activePreset===period){activePreset=null;document.querySelectorAll('.preset-btn').forEach(b=>b.classList.remove('active'));document.getElementById('fs-date-from').value='';document.getElementById('fs-date-to').value='';fsApply();return;}
  activePreset=period; document.querySelectorAll('.preset-btn').forEach(b=>b.classList.remove('active')); el.classList.add('active');
  let from,to; const day=today.getDay();
  if(period==='today'){from=today;to=today;}
  else if(period==='tomorrow'){from=new Date(today);from.setDate(today.getDate()+1);to=new Date(from);}
  else if(period==='weekend'){if(day===0){from=today;to=today;}else if(day===6){from=today;to=new Date(today);to.setDate(today.getDate()+1);}else{from=new Date(today);from.setDate(today.getDate()+(6-day));to=new Date(from);to.setDate(from.getDate()+1);}}
  else if(period==='week'){from=today;to=new Date(today);to.setDate(today.getDate()+7);}
  document.getElementById('fs-date-from').value=toIso(from); document.getElementById('fs-date-to').value=toIso(to);
  fsApply();
}

function fsOnDateChange() {
  activePreset=null; selectedDateStr=null;
  document.querySelectorAll('.preset-btn,.date-chip').forEach(b=>b.classList.remove('active'));
  fsApply();
}

function fsSetAge(val, el) {
  venueAge=val; document.querySelectorAll('#fs-age-opts .fs-opt').forEach(b=>b.classList.toggle('active',b.dataset.age===val));
  updateActiveTags(); currentPage=1; loadVenues();
}

function fsApply() {
  document.getElementById('filter-date-from').value=document.getElementById('fs-date-from').value;
  document.getElementById('filter-date-to').value=document.getElementById('fs-date-to').value;
  freeOnly=document.getElementById('fs-free').checked;
  imgOnly=document.getElementById('fs-img').checked;
  selectedDateStr=null;
  document.querySelectorAll('.date-chip').forEach(c=>c.classList.remove('active'));
  const dfrom=document.getElementById('filter-date-from').value, dto=document.getElementById('filter-date-to').value;
  if(dfrom&&dfrom===dto){const chip=document.querySelector(`.date-chip[data-date="${dfrom}"]`);if(chip){chip.classList.add('active');selectedDateStr=dfrom;}}
  updateActiveTags(); currentPage=1; loadEvents();
}

// ── Active filter tags ──
function fmtD(iso){return new Date(iso+'T12:00').toLocaleDateString('ru-RU',{day:'numeric',month:'short'});}

function updateActiveTags() {
  const tagsEl=document.getElementById('active-tags'),topEl=document.getElementById('cards-top'),badge=document.getElementById('fs-badge'); if(!tagsEl)return;
  const tags=[];
  const dateFrom=document.getElementById('filter-date-from')?.value, dateTo=document.getElementById('filter-date-to')?.value;
  const presetLbls={today:'Сегодня',tomorrow:'Завтра',weekend:'Выходные',week:'Неделя'};

  if(currentTab==='events'){
    if(activePreset) tags.push(`<span class="afilter-tag" onclick="clearTag('preset')">📅 ${presetLbls[activePreset]} <span class="aft-x">✕</span></span>`);
    else if(selectedDateStr) tags.push(`<span class="afilter-tag" onclick="clearTag('date')">📅 ${fmtD(selectedDateStr)} <span class="aft-x">✕</span></span>`);
    else if(dateFrom||dateTo){const lbl=(dateFrom&&dateTo)?`${fmtD(dateFrom)} — ${fmtD(dateTo)}`:dateFrom?`С ${fmtD(dateFrom)}`:`До ${fmtD(dateTo)}`;tags.push(`<span class="afilter-tag" onclick="clearTag('date')">📅 ${lbl} <span class="aft-x">✕</span></span>`);}
    if(freeOnly) tags.push(`<span class="afilter-tag" onclick="clearTag('free')">Бесплатно <span class="aft-x">✕</span></span>`);
    if(imgOnly)  tags.push(`<span class="afilter-tag" onclick="clearTag('img')">С фото <span class="aft-x">✕</span></span>`);
    if(currentSort!=='date_asc'){const l=EVENT_SORTS.find(([v])=>v===currentSort)?.[1];if(l)tags.push(`<span class="afilter-tag" onclick="clearTag('sort')">${l} <span class="aft-x">✕</span></span>`);}
  } else {
    if(venueAge!==''){const lbL={'0':'0+','12':'12+','16':'16+','18':'18+'};tags.push(`<span class="afilter-tag" onclick="clearTag('age')">${lbL[venueAge]||venueAge+'+'} <span class="aft-x">✕</span></span>`);}
    if(venueSort!=='name_asc'){const l=VENUE_SORTS.find(([v])=>v===venueSort)?.[1];if(l)tags.push(`<span class="afilter-tag" onclick="clearTag('vsort')">${l} <span class="aft-x">✕</span></span>`);}
  }
  if(activeCategory){const c=categories.find(x=>x.id===activeCategory);if(c)tags.push(`<span class="afilter-tag" onclick="clearTag('cat')">${escHtml(c.name)} <span class="aft-x">✕</span></span>`);}

  tagsEl.innerHTML=tags.join('');
  topEl.style.display=tags.length?'':'none';

  let cnt=tags.length; if(badge){badge.textContent=cnt;badge.style.display=cnt>0?'':'none';}
}

function clearTag(type) {
  if(type==='preset'||type==='date'){activePreset=null;selectedDateStr=null;document.querySelectorAll('.preset-btn,.date-chip').forEach(b=>b.classList.remove('active'));['fs-date-from','fs-date-to','filter-date-from','filter-date-to'].forEach(id=>{const e=document.getElementById(id);if(e)e.value='';})}
  else if(type==='free'){freeOnly=false;document.getElementById('fs-free').checked=false;}
  else if(type==='img'){imgOnly=false;document.getElementById('fs-img').checked=false;}
  else if(type==='sort'){currentSort='date_asc';renderSortOpts();}
  else if(type==='cat'){clearCatFilter();return;}
  else if(type==='age'){venueAge='';document.querySelectorAll('#fs-age-opts .fs-opt').forEach(b=>b.classList.toggle('active',b.dataset.age===''));}
  else if(type==='vsort'){venueSort='name_asc';renderSortOpts();}
  updateActiveTags(); currentPage=1;
  currentTab==='events'?loadEvents():loadVenues();
}

function resetFilters() {
  activePreset=null;selectedDateStr=null;freeOnly=false;imgOnly=false;currentSort='date_asc';venueSort='name_asc';venueAge='';
  ['fs-date-from','fs-date-to','filter-date-from','filter-date-to'].forEach(id=>{const e=document.getElementById(id);if(e)e.value='';});
  document.getElementById('fs-free').checked=false; document.getElementById('fs-img').checked=false;
  document.querySelectorAll('.preset-btn,.date-chip').forEach(b=>b.classList.remove('active'));
  document.querySelectorAll('#fs-age-opts .fs-opt').forEach(b=>b.classList.toggle('active',b.dataset.age===''));
  activeCategory=null; document.querySelectorAll('.nav-cat-link').forEach(a=>a.classList.remove('active'));
  renderSortOpts(); renderSidebarCats(); updateActiveTags(); currentPage=1;
  currentTab==='events'?loadEvents():loadVenues();
}

// ── Mobile sidebar toggle ──
function toggleMobileSidebar() {
  const col=document.getElementById('filter-col'),btn=document.getElementById('filter-mobile-toggle');
  col.classList.toggle('mobile-open'); btn.classList.toggle('active');
}

// ── Load events ──
async function loadEvents(search='') {
  const grid=document.getElementById('events-grid');
  grid.innerHTML=skeletonGrid(6); renderPagination(0,0);
  try{
    const dateFrom=document.getElementById('filter-date-from')?.value, dateTo=document.getElementById('filter-date-to')?.value;
    const params=['limit='+PAGE_SIZE,'offset='+((currentPage-1)*PAGE_SIZE)];
    if(activeCategory)params.push('category_id='+activeCategory);
    if(search)params.push('search='+encodeURIComponent(search));
    if(currentSort)params.push('sort='+currentSort);
    if(dateFrom)params.push('date_from='+dateFrom);
    if(dateTo)params.push('date_to='+dateTo);
    if(freeOnly)params.push('free=1');
    const result=await get('/events?'+params.join('&'));
    let events=result.items??result; totalPages=result.pages??1;
    if(imgOnly) events=events.filter(e=>e.image);
    if(!events.length){grid.innerHTML='<div class="empty"><div class="empty-icon">🎭</div><div>По вашему запросу ничего не найдено</div><div style="font-size:.82rem;color:var(--muted);margin-top:6px">Попробуйте изменить фильтры</div></div>';renderPagination(0,0);return;}
    grid.innerHTML=events.map(e=>`
      <div class="card event-card" onclick="nav('/event/${e.event_id}')">
        ${imgOrPlaceholder(e.image,'🎭')}
        <div class="card-body">
          <span class="card-tag">${escHtml(e.category_name||'Событие')}</span>
          <div class="card-title">${escHtml(e.title)}</div>
          <div class="card-meta">📅 ${fmtDate(e.start_datetime)}</div>
          <div class="card-meta">📍 ${escHtml(e.venue_name||'—')}</div>
          <div class="card-price">${fmtPrice(e.price)}</div>
        </div>
        <div class="card-footer">
          <span class="badge badge-gray">${escHtml(e.organization_name||'')}${e.org_status_id==OrgStatus.APPROVED?'<span class="verified-badge">✓</span>':''}</span>
          <button class="fav-btn${favEventIds.has(+e.event_id)?' active':''}" onclick="event.stopPropagation();toggleFav(${e.event_id},this)">${favEventIds.has(+e.event_id)?'♥':'♡'}</button>
        </div>
      </div>`).join('');
    renderPagination(currentPage,totalPages);
  }catch(e){grid.innerHTML=`<div class="empty"><div class="empty-icon">⚠️</div><div>${e.message}</div></div>`;}
}

// ── Load venues ──
function sortVenues(list){const s=[...list];if(venueSort==='name_asc')s.sort((a,b)=>(a.name||'').localeCompare(b.name||'','ru'));else if(venueSort==='name_desc')s.sort((a,b)=>(b.name||'').localeCompare(a.name||'','ru'));else if(venueSort==='cat_asc')s.sort((a,b)=>(a.category_name||'').localeCompare(b.category_name||'','ru'));return s;}
function filterAge(list){if(venueAge==='')return list;const age=parseInt(venueAge,10);return list.filter(v=>(v.age==null?0:+v.age)===age);}

async function loadVenues(search='') {
  const grid=document.getElementById('venues-grid');
  grid.innerHTML=skeletonGrid(6); renderPagination(0,0);
  try{
    const params=[];
    if(activeCategory)params.push('category_id='+activeCategory);
    if(search)params.push('search='+encodeURIComponent(search));
    let venues=await get('/venues'+(params.length?'?'+params.join('&'):''));
    venues=filterAge(sortVenues(venues));
    if(!venues.length){grid.innerHTML='<div class="empty"><div class="empty-icon">📍</div><div>По вашему запросу ничего не найдено</div></div>';return;}
    grid.innerHTML=venues.map(v=>`
      <div class="card event-card" onclick="nav('/venue/${v.venue_id}')">
        ${imgOrPlaceholder(v.image,'🏛️')}
        <div class="card-body">
          <span class="card-tag green">${escHtml(v.category_name||'Площадка')}</span>
          <div class="card-title">${escHtml(v.name)}</div>
          <div class="card-meta">📍 ${escHtml(v.address||'Адрес не указан')}</div>
          ${v.age?`<div class="card-meta">🔞 ${v.age}+</div>`:''}
        </div>
        <div class="card-footer">
          <span></span>
          <button class="fav-btn${favVenueIds.has(+v.venue_id)?' active':''}" onclick="event.stopPropagation();toggleVenueFav(${v.venue_id},this)">${favVenueIds.has(+v.venue_id)?'♥':'♡'}</button>
        </div>
      </div>`).join('');
  }catch(e){grid.innerHTML=`<div class="empty"><div class="empty-icon">⚠️</div><div>${e.message}</div></div>`;}
}

// ── Pagination ──
function renderPagination(page,pages){const el=document.getElementById('pagination');if(pages<=1){el.innerHTML='';return;}const nums=[];for(let i=Math.max(1,page-2);i<=Math.min(pages,page+2);i++)nums.push(i);el.innerHTML=`<button class="pag-btn" onclick="goPage(${page-1})" ${page<=1?'disabled':''}>‹</button>${nums.map(n=>`<button class="pag-btn${n===page?' active':''}" onclick="goPage(${n})">${n}</button>`).join('')}<button class="pag-btn" onclick="goPage(${page+1})" ${page>=pages?'disabled':''}>›</button>`;}
function goPage(page){currentPage=page;loadEvents(document.getElementById('search-input')?.value.trim()||'');window.scrollTo({top:0,behavior:'smooth'});}

// ── Tab switch (animated) ──
async function switchTab(tab) {
  if(tab===currentTab||tabAnimating)return;
  tabAnimating=true;
  const prev=currentTab; currentTab=tab; currentPage=1;

  document.getElementById('tab-events').classList.toggle('active',tab==='events');
  document.getElementById('tab-venues').classList.toggle('active',tab==='venues');
  document.getElementById('date-strip-wrap').style.display=tab==='events'?'':'none';
  document.getElementById('fs-events-body').style.display=tab==='events'?'':'none';
  document.getElementById('fs-venues-body').style.display=tab==='venues'?'':'none';

  activeCategory=null; document.querySelectorAll('.nav-cat-link').forEach(a=>a.classList.remove('active'));
  renderSortOpts(); renderSidebarCats(); updateActiveTags();

  const dir=tab==='venues'?1:-1;
  const oldGrid=document.getElementById(prev+'-grid'), newGrid=document.getElementById(tab+'-grid');
  const searchQ=document.getElementById('search-input')?.value.trim()||'';
  const loadP=tab==='events'?loadEvents(searchQ):loadVenues(searchQ);

  oldGrid.style.transition='opacity .2s ease,transform .2s ease';
  oldGrid.style.opacity='0'; oldGrid.style.transform=`translateX(${dir*-36}px)`;
  await new Promise(r=>setTimeout(r,210));
  oldGrid.style.display='none'; oldGrid.style.transition=oldGrid.style.transform=oldGrid.style.opacity='';

  newGrid.style.transition='none'; newGrid.style.opacity='0'; newGrid.style.transform=`translateX(${dir*36}px)`; newGrid.style.display='';
  newGrid.offsetHeight;
  newGrid.style.transition='opacity .3s ease,transform .3s cubic-bezier(.25,.8,.25,1)'; newGrid.style.opacity='1'; newGrid.style.transform='translateX(0)';
  await new Promise(r=>setTimeout(r,320));
  newGrid.style.transition=newGrid.style.transform=newGrid.style.opacity='';

  tabAnimating=false; await loadP;
}

// ── Search ──
function doSearch(){const q=document.getElementById('search-input')?.value.trim()||'';if(q)saveSearchHistory(q);hideSearchSugg();currentPage=1;currentTab==='events'?loadEvents(q):loadVenues(q);}
const SRCH_KEY='afisha_search_hist',SRCH_MAX=8;
function getSearchHistory(){try{return JSON.parse(localStorage.getItem(SRCH_KEY))||[];}catch{return[];}}
function saveSearchHistory(q){if(!q||q.length<2)return;let h=getSearchHistory().filter(x=>x.toLowerCase()!==q.toLowerCase());h.unshift(q);try{localStorage.setItem(SRCH_KEY,JSON.stringify(h.slice(0,SRCH_MAX)));}catch{}}
function removeFromHistory(q){const h=getSearchHistory().filter(x=>x!==q);try{localStorage.setItem(SRCH_KEY,JSON.stringify(h));}catch{}onSearchInput(document.getElementById('search-input')?.value||'');}
function clearHistory(){try{localStorage.removeItem(SRCH_KEY);}catch{}hideSearchSugg();}
function onSearchInput(q){
  const box=document.getElementById('search-sugg');if(!box)return;
  const hist=getSearchHistory(),trimQ=(q||'').trim(),rows=[];
  if(trimQ){rows.push(`<div class="sugg-item" data-q="${escHtml(trimQ)}" onmousedown="pickSugg(this.dataset.q)"><span class="sugg-icon"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg></span><span class="sugg-text">${escHtml(trimQ)}</span></div>`);hist.filter(h=>h.toLowerCase().includes(trimQ.toLowerCase())&&h.toLowerCase()!==trimQ.toLowerCase()).slice(0,4).forEach(h=>{rows.push(`<div class="sugg-item" data-q="${escHtml(h)}" onmousedown="pickSugg(this.dataset.q)"><span class="sugg-icon"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg></span><span class="sugg-text">${escHtml(h)}</span><span class="sugg-del" data-h="${escHtml(h)}" onmousedown="event.stopPropagation();removeFromHistory(this.dataset.h)">✕</span></div>`);});}
  else if(hist.length){rows.push('<div class="sugg-section-label">Недавние</div>');hist.forEach(h=>{rows.push(`<div class="sugg-item" data-q="${escHtml(h)}" onmousedown="pickSugg(this.dataset.q)"><span class="sugg-icon"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg></span><span class="sugg-text">${escHtml(h)}</span><span class="sugg-del" data-h="${escHtml(h)}" onmousedown="event.stopPropagation();removeFromHistory(this.dataset.h)">✕</span></div>`);});rows.push(`<div class="sugg-item sugg-clear" onmousedown="clearHistory()">Очистить историю</div>`);}
  box.style.display=rows.length?'':'none';box.innerHTML=rows.join('');
}
function pickSugg(q){const inp=document.getElementById('search-input');if(inp)inp.value=q;saveSearchHistory(q);hideSearchSugg();currentPage=1;currentTab==='events'?loadEvents(q):loadVenues(q);}
function hideSearchSugg(){setTimeout(()=>{const b=document.getElementById('search-sugg');if(b)b.style.display='none';},160);}
function handleSearchKey(e){if(e.key==='Enter')doSearch();else if(e.key==='Escape'){hideSearchSugg();e.target.blur();}}

// ── Nav cats overflow ──
let allCatsOpen=false,_overflowCats=[];
function updateNavCatsOverflow(){const nc=document.getElementById('nav-cats'),btn=document.getElementById('nav-allcats-btn');if(!nc||!btn)return;const links=[...nc.querySelectorAll('.nav-cat-link')];links.forEach(l=>{l.style.display='';});const cr=nc.getBoundingClientRect();_overflowCats=[];links.forEach(l=>{if(l.getBoundingClientRect().right>cr.right+2){l.style.display='none';const cat=categories.find(c=>c.id===+l.dataset.catId);if(cat)_overflowCats.push(cat);}});btn.style.display=_overflowCats.length?'':'none';if(!_overflowCats.length)closeAllCats();}
function toggleAllCats(){allCatsOpen=!allCatsOpen;const btn=document.getElementById('nav-allcats-btn'),dd=document.getElementById('nav-allcats-dd');if(!btn||!dd)return;if(allCatsOpen){dd.innerHTML=_overflowCats.map(c=>`<div class="nav-allcats-dd-item${activeCategory===c.id?' active':''}" onclick="selectNavCat(${c.id},this)"><span class="dd-dot"></span>${escHtml(c.name)}</div>`).join('');}btn.classList.toggle('open',allCatsOpen);dd.classList.toggle('open',allCatsOpen);}
function closeAllCats(){allCatsOpen=false;document.getElementById('nav-allcats-btn')?.classList.remove('open');document.getElementById('nav-allcats-dd')?.classList.remove('open');}
window.addEventListener('resize',()=>requestAnimationFrame(updateNavCatsOverflow));
document.addEventListener('click',e=>{if(allCatsOpen&&!e.target.closest('.nav-allcats-wrap'))closeAllCats();});

// ── Favs ──
async function toggleFav(id,btn){if(auth.isLoggedIn()&&auth.type()==='org'){toast('Недоступно для аккаунтов организации','error');return;}if(!auth.isLoggedIn()){toast('Войдите чтобы добавить в избранное','error');return;}const ok=favEventIds.has(+id);btn.disabled=true;try{if(ok){await del('/favorites/'+favMap[+id]);favEventIds.delete(+id);delete favMap[+id];btn.classList.remove('active');btn.textContent='♡';toast('Убрано из избранного');}else{const r=await post('/favorites',{event_id:id});favEventIds.add(+id);favMap[+id]=r.favorite_id;btn.classList.add('active');btn.textContent='♥';toast('Добавлено в избранное ♥','success');}}catch(e){toast(e.message,'error');}finally{btn.disabled=false;}}
async function toggleVenueFav(id,btn){if(auth.isLoggedIn()&&auth.type()==='org'){toast('Недоступно','error');return;}if(!auth.isLoggedIn()){toast('Войдите чтобы добавить в избранное','error');return;}const ok=favVenueIds.has(+id);btn.disabled=true;try{if(ok){await del('/favorites/'+favVenueMap[+id]);favVenueIds.delete(+id);delete favVenueMap[+id];btn.classList.remove('active');btn.textContent='♡';toast('Убрано из избранного');}else{const r=await post('/favorites',{venue_id:id});favVenueIds.add(+id);favVenueMap[+id]=r?.favorite_id;btn.classList.add('active');btn.textContent='♥';toast('Добавлено в избранное ♥','success');}}catch(e){toast(e.message,'error');}finally{btn.disabled=false;}}

// ── Boot: two-layer init trigger with guard against double-call ──
let _initStarted = false;
function bootInit() {
  if (_initStarted) return;
  _initStarted = true;
  init().catch(() => {});
}

// Layer 1: main.js calls window.__pageInit() at the end of its DOMContentLoaded
//          (after renderNavbar has set up nav-mid) — primary path
window.__pageInit = bootInit;

// Layer 2: fallback via setTimeout inside DOMContentLoaded
//          setTimeout fires AFTER DOMContentLoaded completes, so renderNavbar is done
document.addEventListener('DOMContentLoaded', () => setTimeout(bootInit, 0));
</script>
@endsection
