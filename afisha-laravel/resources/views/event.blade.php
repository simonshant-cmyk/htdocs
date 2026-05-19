@extends('layouts.app')

@section('title', (isset($ogData) ? $ogData['title'] : 'Событие') . ' — АфишаКолыма')

@section('styles')
<style>
  .event-hero {
    height: 460px; position: relative; overflow: hidden;
    background: var(--hero-bg);
  }
  .event-hero img { width:100%; height:100%; object-fit:cover; opacity:.55; transition: transform 8s ease; }
  .event-hero:hover img { transform: scale(1.03); }
  .event-hero-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(15,12,10,.95) 0%, rgba(15,12,10,.5) 40%, transparent 70%),
                linear-gradient(to right, rgba(0,0,0,.3) 0%, transparent 60%);
    display: flex; align-items: flex-end; padding: 48px;
  }
  @media(max-width:600px){ .event-hero{ height:320px; } .event-hero-overlay{ padding:28px 20px; } }
  .event-hero-content { color: #fff; max-width: 820px; }
  .event-hero-tag {
    display: inline-block; padding: 5px 14px; border-radius: 20px;
    font-size: 0.72rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase;
    background: var(--accent); color: #fff; margin-bottom: 14px;
    box-shadow: 0 2px 12px rgba(200,80,42,.4);
  }
  .event-hero-title {
    font-family: var(--font-display); font-size: 2.8rem; font-weight: 700;
    line-height: 1.1; margin-bottom: 14px;
    text-shadow: 0 2px 24px rgba(0,0,0,.3);
  }
  @media(max-width:600px){ .event-hero-title{ font-size:1.7rem; } }
  .event-hero-meta { display: flex; gap: 16px; flex-wrap: wrap; color: rgba(255,255,255,.72); font-size: 0.88rem; }
  .event-hero-meta span { display: flex; align-items: center; gap: 6px; }

  .event-body {
    max-width: 1100px; margin: 0 auto; padding: 40px 32px;
    display: grid; grid-template-columns: 1fr 340px; gap: 44px;
  }
  @media(max-width:800px) { .event-body { grid-template-columns:1fr; padding:24px 16px; } }

  .event-section { margin-bottom: 40px; }
  .event-section-title {
    font-family: var(--font-display); font-size: 1.35rem; font-weight: 700;
    margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1.5px solid var(--border);
  }
  .event-desc { color: var(--muted); line-height: 1.85; font-size: 0.95rem; }

  .sidebar-card {
    background: var(--surface); border: 1px solid var(--border);
    border-top: 3px solid var(--accent);
    border-radius: var(--radius); padding: 26px; position: sticky; top: 84px;
    box-shadow: var(--shadow-card);
  }
  .sidebar-price {
    font-family: var(--font-display); font-size: 2.2rem; font-weight: 700;
    color: var(--accent); margin-bottom: 4px; letter-spacing: -.02em;
  }
  .sidebar-price-sub { color: var(--muted); font-size: 0.82rem; margin-bottom: 22px; }
  .sidebar-info { display: flex; flex-direction: column; gap: 14px; margin-bottom: 22px; }
  .sidebar-row { display: flex; gap: 12px; align-items: flex-start; font-size: 0.88rem; }
  .sidebar-row-icon { font-size: 1.15rem; flex-shrink: 0; margin-top: 1px; }
  .sidebar-row-label { color: var(--muted); font-size: 0.74rem; margin-bottom: 2px; }
  .sidebar-row-val { font-weight: 600; }

  .review-item {
    padding: 16px 18px; background: var(--surface2); border-radius: var(--radius-sm);
    border: 1px solid var(--border); border-left: 3px solid var(--accent2); margin-bottom: 12px;
    transition: border-left-color .22s, box-shadow .22s;
  }
  .review-item:hover { border-left-color: var(--accent); box-shadow: var(--shadow-card); }
  .review-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
  .review-author { font-weight: 700; font-size: 0.88rem; }
  .review-date { font-size: 0.75rem; color: var(--muted); }
  .review-text { font-size: 0.88rem; color: var(--muted); line-height: 1.75; }

  .review-form { background: var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:22px; margin-top:20px; box-shadow:var(--shadow-card); }
  .rating-picker { display:flex; gap:8px; margin-bottom:14px; }
  .rating-star { font-size:2rem; cursor:pointer; color:var(--border); transition:transform .15s, color .15s; }
  .rating-star:hover { transform: scale(1.2); }
  .rating-star.active { color:#f0b429; text-shadow: 0 0 10px rgba(240,180,41,.45); }

  .share-btns { display:flex; gap:8px; margin-top:12px; flex-wrap:wrap; }
  .share-btn { display:inline-flex; align-items:center; gap:7px; padding:9px 16px; border-radius:10px; font-size:.82rem; font-weight:700; text-decoration:none; transition:var(--transition); }
  .share-btn-tg  { background:#2aabee; color:#fff; }
  .share-btn-tg:hover  { background:#1a9bde; transform:translateY(-1px); }
  .share-btn-wa  { background:#25d366; color:#fff; }
  .share-btn-wa:hover  { background:#15c356; transform:translateY(-1px); }
  .share-btn-vk  { background:#0077ff; color:#fff; }
  .share-btn-vk:hover  { background:#0060cc; transform:translateY(-1px); }
  .tickets-left-badge { display:inline-flex; align-items:center; gap:6px; background:#fff3cd; border:1px solid #ffc107; color:#856404; border-radius:8px; padding:7px 14px; font-size:.85rem; font-weight:600; margin-bottom:10px; }
  .tickets-left-badge.few { background:#fde8e8; border-color:#f87171; color:#991b1b; }

  .similar-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:20px; }

  .gallery-photo { border-radius:10px; overflow:hidden; cursor:pointer; aspect-ratio:4/3; }
  .gallery-photo img { width:100%; height:100%; object-fit:cover; transition:transform .3s; display:block; }
  .gallery-photo:hover img { transform:scale(1.04); }
  .gallery-photo:first-child { grid-column:span 2; grid-row:span 1; aspect-ratio:16/7; }
  @media(max-width:600px){ .gallery-photo:first-child{ grid-column:span 1; aspect-ratio:4/3; } }

  .lightbox-overlay { position:fixed; inset:0; background:rgba(0,0,0,.9); z-index:9999; display:flex; align-items:center; justify-content:center; }
  .lightbox-img { max-width:92vw; max-height:88vh; border-radius:10px; box-shadow:0 8px 60px rgba(0,0,0,.7); }
  .lightbox-close { position:absolute; top:18px; right:22px; font-size:2rem; color:#fff; background:none; border:none; cursor:pointer; opacity:.8; line-height:1; }
  .lightbox-close:hover { opacity:1; }
  .lightbox-nav { position:absolute; top:50%; transform:translateY(-50%); font-size:2rem; color:#fff; background:rgba(255,255,255,.12); border:none; cursor:pointer; width:52px; height:52px; border-radius:50%; display:flex; align-items:center; justify-content:center; transition:background .15s; }
  .lightbox-nav:hover { background:rgba(255,255,255,.24); }
  .lightbox-prev { left:16px; }
  .lightbox-next { right:16px; }
  .lightbox-counter { position:absolute; bottom:18px; left:50%; transform:translateX(-50%); color:rgba(255,255,255,.7); font-size:.82rem; }
</style>
@endsection

@section('content')
<div class="btn-back-wrap">
  <button class="btn-back" onclick="history.length>1?history.back():nav('/')">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    Назад
  </button>
</div>
<div id="event-hero">
  <div class="loader"><div class="spinner"></div> Загрузка...</div>
</div>

<div class="event-body" id="event-body" style="display:none">
  <div>
    <div class="event-section">
      <div class="event-section-title">О событии</div>
      <div class="event-desc" id="event-desc"></div>
    </div>

    <div class="event-section" id="event-gallery-section" style="display:none">
      <div class="event-section-title">Фотогалерея</div>
      <div id="event-gallery-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px"></div>
    </div>

    <div class="event-section">
      <div class="event-section-title">Отзывы</div>
      <div id="reviews-list"><div class="loader"><div class="spinner"></div></div></div>

      <div class="review-form" id="review-form" style="display:none">
        <div style="font-weight:700;margin-bottom:12px">Оставить отзыв</div>
        <div class="rating-picker" id="rating-picker">
          <span class="rating-star" data-v="1">★</span>
          <span class="rating-star" data-v="2">★</span>
          <span class="rating-star" data-v="3">★</span>
          <span class="rating-star" data-v="4">★</span>
          <span class="rating-star" data-v="5">★</span>
        </div>
        <textarea class="form-control" id="review-text" rows="3" placeholder="Расскажите о своих впечатлениях..." style="margin-bottom:12px"></textarea>
        <button class="btn btn-primary" onclick="submitReview()">Отправить</button>
      </div>
      <div id="login-prompt" style="display:none;margin-top:16px;text-align:center;color:var(--muted);font-size:.88rem">
        <a href="#" onclick="nav('/login');return false" style="color:var(--accent);font-weight:600">Войдите</a>, чтобы оставить отзыв
      </div>
    </div>

    <div class="event-section" id="similar-section" style="display:none">
      <div class="event-section-title">Похожие события</div>
      <div class="similar-grid" id="similar-grid"></div>
    </div>
  </div>

  <div>
    <div class="sidebar-card" id="sidebar"></div>
  </div>
</div>
@endsection

@section('scripts')
<script>
const id = {{ $id }};
let selectedRating = 0;
let eventData = null;
let favId = null;

document.querySelectorAll('.rating-star').forEach(s => {
  s.addEventListener('click', () => {
    selectedRating = +s.dataset.v;
    document.querySelectorAll('.rating-star').forEach((x,i) => x.classList.toggle('active', i < selectedRating));
  });
});

async function init() {
  try {
    eventData = await get('/events/' + id);
    renderHero(eventData);
    renderBody(eventData);
    loadReviews();
    loadSimilar(eventData);
    loadFavState();
    document.getElementById('event-body').style.display = '';
    if (auth.isLoggedIn() && auth.type() === 'user') document.getElementById('review-form').style.display = '';
    else if (!auth.isLoggedIn()) document.getElementById('login-prompt').style.display = '';
    if (eventData.start_datetime) startCountdown(eventData.start_datetime);
    injectJsonLd(eventData);
    trackViewHistory('event', id, eventData.title, eventData.image || '');
  } catch(e) { document.getElementById('event-hero').innerHTML = '<div class="empty"><div class="empty-icon">⚠️</div>' + e.message + '</div>'; }
}

function startCountdown(dateStr) {
  const el = document.getElementById('countdown');
  if (!el) return;
  function update() {
    const diff = new Date(dateStr) - new Date();
    if (diff <= 0) { el.textContent = '🔴 Идёт сейчас'; return; }
    const d = Math.floor(diff / 86400000);
    const h = Math.floor(diff % 86400000 / 3600000);
    const m = Math.floor(diff % 3600000 / 60000);
    if (d > 30) { el.textContent = `🕐 Через ${d} дней`; return; }
    if (d > 0) el.textContent = `🕐 Через ${d}д ${h}ч`;
    else if (h > 0) el.textContent = `🕐 Через ${h}ч ${m}мин`;
    else el.textContent = `🕐 Через ${m} мин`;
  }
  update();
  setInterval(update, 60000);
}

function injectJsonLd(e) {
  const script = document.createElement('script');
  script.type = 'application/ld+json';
  const AT = '\x40';
  const data = {
    [AT+'context']: 'https://schema.org',
    [AT+'type']: 'Event',
    'name': e.title,
    'startDate': e.start_datetime,
    'endDate': e.end_datetime,
    'description': e.description || '',
    'url': window.location.href,
  };
  if (e.image) data.image = e.image;
  if (e.venue_name) data.location = { [AT+'type']: 'Place', 'name': e.venue_name, 'address': e.venue_address || e.venue_name };
  if (+e.price >= 0) data.offers = { [AT+'type']: 'Offer', 'price': +e.price, 'priceCurrency': 'RUB', 'availability': 'https://schema.org/InStock', 'url': window.location.href };
  script.textContent = JSON.stringify(data);
  document.head.appendChild(script);
}

function trackViewHistory(type, itemId, title, image) {
  try {
    const hist = JSON.parse(localStorage.getItem('view_history') || '[]');
    const entry = { type, id: +itemId, title, image, url: window.location.pathname, viewedAt: Date.now() };
    const filtered = hist.filter(h => !(h.type === type && h.id === +itemId));
    filtered.unshift(entry);
    localStorage.setItem('view_history', JSON.stringify(filtered.slice(0, 30)));
  } catch {}
}

function copyPageLink() {
  navigator.clipboard.writeText(window.location.href)
    .then(() => toast('Ссылка скопирована!', 'success'))
    .catch(() => {
      const el = document.createElement('input');
      el.value = window.location.href;
      document.body.appendChild(el);
      el.select(); document.execCommand('copy');
      document.body.removeChild(el);
      toast('Ссылка скопирована!', 'success');
    });
}

function renderHero(e) {
  document.title = e.title + ' — АфишаКолыма';
  document.getElementById('event-hero').innerHTML = `
    <div class="event-hero">
      ${e.image ? `<img src="${e.image}" alt="${escHtml(e.title)}">` : '<div style="width:100%;height:100%;background:var(--hero-bg)"></div>'}
      <div class="event-hero-overlay">
        <div class="event-hero-content">
          <span class="event-hero-tag">${escHtml(e.category_name || 'Событие')}</span>
          <h1 class="event-hero-title">${escHtml(e.title)}</h1>
          <div class="event-hero-meta">
            <span>📅 ${fmtDate(e.start_datetime)}</span>
            <span>🏁 ${fmtDate(e.end_datetime)}</span>
            ${e.venue_name ? `<span>📍 ${escHtml(e.venue_name)}</span>` : ''}
            ${e.age_restriction ? `<span>🔞 ${e.age_restriction}+</span>` : ''}
            ${e.start_datetime ? `<span id="countdown" class="countdown-badge"></span>` : ''}
          </div>
        </div>
      </div>
    </div>
  `;
}

function renderBody(e) {
  document.getElementById('event-desc').textContent = e.description || 'Описание не указано.';

  const gallery = Array.isArray(e.gallery) ? e.gallery.filter(Boolean) : [];
  if (gallery.length) {
    document.getElementById('event-gallery-section').style.display = '';
    document.getElementById('event-gallery-grid').innerHTML = gallery.map((url, i) => `
      <div class="gallery-photo" onclick="openLightbox(${i})">
        <img src="${escHtml(url)}" alt="Фото ${i+1}" loading="lazy">
      </div>
    `).join('');
  }
  window._galleryImages = gallery;

  const pageUrl = encodeURIComponent(window.location.href);
  const pageTitle = encodeURIComponent(e.title + ' — АфишаКолыма');

  document.getElementById('sidebar').innerHTML = `
    <div class="sidebar-price">${fmtPrice(e.price)}</div>
    <div class="sidebar-price-sub">${+e.price === 0 ? 'Вход свободный' : 'Стоимость билета'}</div>
    <div class="sidebar-info">
      <div class="sidebar-row">
        <span class="sidebar-row-icon">📅</span>
        <div><div class="sidebar-row-label">Начало</div><div class="sidebar-row-val">${fmtDate(e.start_datetime)}</div></div>
      </div>
      <div class="sidebar-row">
        <span class="sidebar-row-icon">🏁</span>
        <div><div class="sidebar-row-label">Окончание</div><div class="sidebar-row-val">${fmtDate(e.end_datetime)}</div></div>
      </div>
      ${e.venue_name ? `
      <div class="sidebar-row">
        <span class="sidebar-row-icon">📍</span>
        <div><div class="sidebar-row-label">Площадка</div>
        <div class="sidebar-row-val"><a href="${(window.APP_BASE||'')+'/venue/'+e.venue_id}" style="color:var(--accent)">${escHtml(e.venue_name)}</a></div>
        ${e.venue_address ? `<div style="font-size:.78rem;color:var(--muted)">${escHtml(e.venue_address)}</div>` : ''}
        </div>
      </div>` : ''}
      ${e.organization_name ? `
      <div class="sidebar-row">
        <span class="sidebar-row-icon">🏢</span>
        <div>
          <div class="sidebar-row-label">Организатор</div>
          <div class="sidebar-row-val">
            ${escHtml(e.organization_name)}
            ${e.org_status_id == OrgStatus.APPROVED ? '<span class="verified-badge">✓ Проверено</span>' : ''}
          </div>
        </div>
      </div>` : ''}
      ${e.status_name ? `
      <div class="sidebar-row">
        <span class="sidebar-row-icon">🔖</span>
        <div><div class="sidebar-row-label">Статус</div><div class="sidebar-row-val">${escHtml(e.status_name)}</div></div>
      </div>` : ''}
    </div>
    ${e.tickets_left !== null && e.tickets_left !== undefined ? (() => {
      const few = e.tickets_left <= 10;
      return `<div class="tickets-left-badge${few ? ' few' : ''}">
        ${few ? '🔥' : '🎟️'} Осталось билетов: <strong>${e.tickets_left}</strong>
      </div>`;
    })() : ''}
    ${+e.price > 0
      ? `<button class="btn btn-primary btn-full" style="margin-bottom:10px" onclick="addToCart()">🛒 Купить билет</button>`
      : `<button class="btn btn-green btn-full" style="margin-bottom:10px" onclick="addToCart()">🎟️ Получить билет</button>`
    }
    <button id="fav-btn" class="btn btn-secondary btn-full" style="margin-bottom:8px" onclick="addFav()">♡ В избранное</button>
    <button class="btn btn-secondary btn-full" style="margin-bottom:14px" onclick="exportIcs()">📅 Добавить в календарь</button>
    <div class="share-btns">
      <a href="https://t.me/share/url?url=${pageUrl}&text=${pageTitle}" target="_blank" class="share-btn share-btn-tg">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248l-2.04 9.607c-.15.677-.548.842-1.11.524l-3.073-2.263-1.484 1.428c-.164.164-.302.302-.618.302l.22-3.125 5.686-5.133c.247-.22-.054-.342-.384-.122L7.31 14.442 4.273 13.51c-.665-.208-.677-.665.14-.984l10.879-4.193c.554-.2 1.039.135.862.984z"/></svg>
        Telegram
      </a>
      <a href="https://vk.com/share.php?url=${pageUrl}&title=${pageTitle}" target="_blank" class="share-btn share-btn-vk">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.408 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.033-1.01-1.49-1.135-1.744-1.135-.356 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.118-5.335-3.202C4.624 10.857 4.03 8.57 4.03 8.096c0-.254.102-.491.593-.491h1.744c.44 0 .61.203.78.677.864 2.495 2.303 4.681 2.896 4.681.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.203.169-.407.44-.407h2.744c.373 0 .508.203.508.643v3.473c0 .372.169.508.271.508.22 0 .407-.136.813-.542 1.254-1.406 2.151-3.574 2.151-3.574.119-.254.322-.491.762-.491h1.744c.525 0 .644.271.525.643-.22 1.017-2.354 4.029-2.354 4.029-.186.305-.254.44 0 .78.186.254.796.78 1.203 1.253.745.847 1.312 1.558 1.464 2.049.17.491-.085.745-.576.745z"/></svg>
        ВКонтакте
      </a>
      <a href="https://wa.me/?text=${pageTitle}%20${pageUrl}" target="_blank" class="share-btn share-btn-wa">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        WhatsApp
      </a>
      <button class="share-btn share-btn-copy" onclick="copyPageLink()">📋 Скопировать</button>
    </div>
  `;
}

async function loadReviews() {
  const list = document.getElementById('reviews-list');
  try {
    const reviews = await get('/reviews?event_id=' + id);
    if (!reviews.length) { list.innerHTML = '<div class="empty" style="padding:24px"><div class="empty-icon">💬</div>Пока нет отзывов</div>'; return; }
    list.innerHTML = renderReviewsHtml(reviews);
  } catch(e) { list.innerHTML = ''; }
}

async function loadSimilar(e) {
  try {
    const params = new URLSearchParams({ limit: 4 });
    if (e.category_id) params.set('category_id', e.category_id);
    const items = await get('/events?' + params);
    const similar = items.filter(ev => ev.event_id != id).slice(0, 3);
    if (!similar.length) return;
    document.getElementById('similar-section').style.display = '';
    document.getElementById('similar-grid').innerHTML = similar.map(ev => `
      <a href="${(window.APP_BASE||'')+'/event/'+ev.event_id}" class="card" style="text-decoration:none">
        <div class="card-img">
          ${ev.image ? `<img src="${escHtml(ev.image)}" alt="${escHtml(ev.title)}" loading="lazy">` : '<div style="height:100%;display:flex;align-items:center;justify-content:center;font-size:2rem">🎭</div>'}
        </div>
        <div class="card-body">
          <div class="card-tag">${escHtml(ev.category_name || 'Событие')}</div>
          <div class="card-title">${escHtml(ev.title)}</div>
          <div class="card-meta">📅 ${fmtDate(ev.start_datetime)}</div>
          <div class="card-price">${fmtPrice(ev.price)}</div>
        </div>
      </a>
    `).join('');
  } catch(e) {}
}

async function submitReview() {
  if (!selectedRating) { toast('Выберите оценку', 'error'); return; }
  const text = document.getElementById('review-text').value.trim();
  if (!text) { toast('Напишите отзыв', 'error'); return; }
  try {
    await post('/reviews', { event_id: +id, text, rating: selectedRating });
    toast('Отзыв добавлен!', 'success');
    document.getElementById('review-text').value = '';
    selectedRating = 0;
    document.querySelectorAll('.rating-star').forEach(s => s.classList.remove('active'));
    loadReviews();
  } catch(e) { toast(e.message, 'error'); }
}

async function addToCart() {
  if (auth.isLoggedIn() && auth.type() === 'org') { toast('Организации не могут покупать билеты', 'error'); return; }
  if (!auth.isLoggedIn()) { nav('/login'); return; }
  try {
    await post('/tickets', { event_id: +id, price: eventData?.price ?? 0, quantity: 1 });
    toast('Билет добавлен в корзину!', 'success');
    updateCartBadge();
  } catch(e) { toast(e.message, 'error'); }
}

async function loadFavState() {
  if (!auth.isLoggedIn() || auth.type() !== 'user') return;
  try {
    const favs = await get('/favorites');
    const fav = favs.find(f => +f.event_id === +id);
    if (fav) {
      favId = fav.favorite_id;
      const btn = document.getElementById('fav-btn');
      if (btn) btn.innerHTML = '♥ В избранном';
    }
  } catch {}
}

async function addFav() {
  if (auth.isLoggedIn() && auth.type() === 'org') { toast('Недоступно для аккаунтов организации', 'error'); return; }
  if (!auth.isLoggedIn()) { nav('/login'); return; }
  const btn = document.getElementById('fav-btn');
  if (btn) btn.disabled = true;
  try {
    if (favId) {
      await del('/favorites/' + favId);
      favId = null;
      if (btn) btn.innerHTML = '♡ В избранное';
      toast('Убрано из избранного');
    } else {
      const r = await post('/favorites', { event_id: +id });
      favId = r?.favorite_id ?? null;
      if (btn) btn.innerHTML = '♥ В избранном';
      toast('Добавлено в избранное!', 'success');
    }
  } catch(e) { toast(e.message, 'error'); }
  finally { if (btn) btn.disabled = false; }
}

function exportIcs() {
  if (!eventData) return;
  const pad = n => String(n).padStart(2, '0');
  const toIcsDate = str => {
    const d = new Date(str);
    return `${d.getUTCFullYear()}${pad(d.getUTCMonth()+1)}${pad(d.getUTCDate())}T${pad(d.getUTCHours())}${pad(d.getUTCMinutes())}00Z`;
  };
  const esc = s => (s || '').replace(/\n/g, '\\n').replace(/,/g, '\\,').replace(/;/g, '\\;');
  const uid = `event-${eventData.event_id}-${Date.now()}@afishakolyma`;
  const ics = [
    'BEGIN:VCALENDAR',
    'VERSION:2.0',
    'PRODID:-//АфишаКолыма//RU',
    'CALSCALE:GREGORIAN',
    'BEGIN:VEVENT',
    `UID:${uid}`,
    `DTSTAMP:${toIcsDate(new Date().toISOString())}`,
    `DTSTART:${toIcsDate(eventData.start_datetime)}`,
    `DTEND:${toIcsDate(eventData.end_datetime || eventData.start_datetime)}`,
    `SUMMARY:${esc(eventData.title)}`,
    `DESCRIPTION:${esc(eventData.description)}`,
    eventData.venue_address ? `LOCATION:${esc(eventData.venue_address)}` : '',
    'END:VEVENT',
    'END:VCALENDAR',
  ].filter(Boolean).join('\r\n');

  const blob = new Blob([ics], { type: 'text/calendar;charset=utf-8' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = `${eventData.title.replace(/[^а-яёa-z0-9]/gi, '_')}.ics`;
  a.click();
  URL.revokeObjectURL(a.href);
  toast('Файл .ics скачан', 'success');
}

init();

let _lbIdx = 0;
function openLightbox(startIdx) {
  const imgs = window._galleryImages || [];
  if (!imgs.length) return;
  _lbIdx = startIdx;
  const ov = document.createElement('div');
  ov.className = 'lightbox-overlay';
  ov.id = 'lb-overlay';
  ov.onclick = e => { if (e.target === ov) closeLightbox(); };
  ov.innerHTML = `
    <button class="lightbox-close" onclick="closeLightbox()">✕</button>
    ${imgs.length > 1 ? `<button class="lightbox-nav lightbox-prev" onclick="lbNav(-1)">‹</button>` : ''}
    <img class="lightbox-img" id="lb-img" src="${escHtml(imgs[startIdx])}">
    ${imgs.length > 1 ? `<button class="lightbox-nav lightbox-next" onclick="lbNav(1)">›</button>` : ''}
    <div class="lightbox-counter" id="lb-counter">${startIdx+1} / ${imgs.length}</div>
  `;
  document.body.appendChild(ov);
  document.addEventListener('keydown', lbKey);
}
function closeLightbox() {
  const ov = document.getElementById('lb-overlay');
  if (ov) ov.remove();
  document.removeEventListener('keydown', lbKey);
}
function lbNav(dir) {
  const imgs = window._galleryImages || [];
  _lbIdx = (_lbIdx + dir + imgs.length) % imgs.length;
  const img = document.getElementById('lb-img');
  const ctr = document.getElementById('lb-counter');
  if (img) img.src = escHtml(imgs[_lbIdx]);
  if (ctr) ctr.textContent = (_lbIdx+1) + ' / ' + imgs.length;
}
function lbKey(e) {
  if (e.key === 'ArrowLeft')  lbNav(-1);
  if (e.key === 'ArrowRight') lbNav(1);
  if (e.key === 'Escape')     closeLightbox();
}
</script>
@endsection
