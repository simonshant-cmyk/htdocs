@extends('layouts.app')

@section('title', (isset($ogData) ? $ogData['title'] : 'Площадка') . ' — АфишаКолыма')

@section('styles')
<style>
  .section-heading {
    font-family: var(--font-display); font-size: 1.3rem; font-weight: 700;
    margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1.5px solid var(--border);
  }
  .venue-header {
    background: var(--hero-bg); color: #fff; padding: 60px 32px 44px; position: relative; overflow: hidden;
  }
  .venue-header::before {
    content:''; position:absolute; inset:0;
    background: radial-gradient(ellipse at 75% 40%, rgba(42,110,90,.45) 0%, transparent 55%),
                radial-gradient(ellipse at 20% 80%, rgba(200,80,42,.2) 0%, transparent 50%);
  }
  .venue-header-inner { max-width:1100px; margin:0 auto; position:relative; z-index:1; display:flex; gap:32px; align-items:flex-end; flex-wrap:wrap; }
  .venue-avatar {
    width:120px; height:120px; border-radius:18px; object-fit:cover;
    background:rgba(255,255,255,.1); border:2px solid rgba(255,255,255,.18); flex-shrink:0;
    display:flex; align-items:center; justify-content:center; font-size:3rem;
    box-shadow: 0 8px 32px rgba(0,0,0,.3);
  }
  .venue-title { font-family:var(--font-display); font-size:2.4rem; font-weight:700; margin-bottom:10px; line-height:1.1; }
  .venue-meta { display:flex; gap:16px; flex-wrap:wrap; color:rgba(255,255,255,.68); font-size:0.88rem; }
  .venue-meta span { display:flex; align-items:center; gap:6px; }

  .venue-body { max-width:1100px; margin:0 auto; padding:40px 32px; display:grid; grid-template-columns:1fr 300px; gap:40px; }
  @media(max-width:800px){ .venue-body{grid-template-columns:1fr;} }

  .schedule-table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
  .schedule-table { width:100%; border-collapse:collapse; font-size:0.88rem; min-width:360px; }
  .schedule-table th { text-align:left; padding:8px 12px; color:var(--muted); font-weight:600; font-size:.75rem; text-transform:uppercase; letter-spacing:.05em; border-bottom:1.5px solid var(--border); }
  .schedule-table td { padding:10px 12px; border-bottom:1px solid var(--bg2); }
  .schedule-table tr:last-child td { border-bottom:none; }

  .contact-item { display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid var(--bg2); font-size:.88rem; }
  .contact-item:last-child { border-bottom:none; }
  .contact-icon { width:32px; height:32px; border-radius:8px; background:var(--bg2); display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }

  .review-item { padding:16px; background:var(--surface2); border-radius:var(--radius-sm); border:1px solid var(--border); border-left:3px solid var(--accent2); margin-bottom:12px; transition:border-left-color .2s; }
  .review-item:hover { border-left-color: var(--accent); }
  .review-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
  .rating-picker { display:flex; gap:6px; margin-bottom:12px; }
  .rating-star { font-size:1.8rem; cursor:pointer; color:var(--border); transition:transform .15s, color .15s; }
  .rating-star:hover { transform: scale(1.2); }
  .rating-star.active { color:#f0b429; text-shadow: 0 0 8px rgba(240,180,41,.4); }

  .sidebar-card { background:var(--surface); border:1px solid var(--border); border-top:3px solid var(--accent2); border-radius:var(--radius); padding:26px; position:sticky; top:84px; box-shadow:var(--shadow-card); }
  .map-wrap { border-radius: var(--radius); overflow: hidden; border: 1px solid var(--border); margin-top: 16px; background: var(--bg2); }
  .map-wrap iframe { width: 100%; height: 280px; border: none; display: block; }
  .map-placeholder { height: 200px; display: flex; align-items: center; justify-content: center; color: var(--muted); font-size: .9rem; flex-direction:column; gap:8px; }

  .share-btns { display:flex; gap:8px; margin-top:16px; flex-wrap:wrap; }
  .share-btn { display:inline-flex; align-items:center; gap:7px; padding:9px 16px; border-radius:10px; font-size:.82rem; font-weight:700; text-decoration:none; transition:var(--transition); }
  .share-btn-tg  { background:#2aabee; color:#fff; }
  .share-btn-tg:hover  { background:#1a9bde; transform:translateY(-1px); }
  .share-btn-wa  { background:#25d366; color:#fff; }
  .share-btn-wa:hover  { background:#15c356; transform:translateY(-1px); }
  .share-btn-vk  { background:#0077ff; color:#fff; }
  .share-btn-vk:hover  { background:#0060cc; transform:translateY(-1px); }

  .gallery-grid-view { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:10px; }
  .gallery-photo { border-radius:10px; overflow:hidden; cursor:pointer; aspect-ratio:4/3; }
  .gallery-photo img { width:100%; height:100%; object-fit:cover; transition:transform .3s; display:block; }
  .gallery-photo:hover img { transform:scale(1.04); }
  .gallery-photo:first-child { grid-column:span 2; aspect-ratio:16/7; }
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
<div id="venue-header-wrap">
  <div class="loader"><div class="spinner"></div> Загрузка...</div>
</div>

<div class="venue-body" id="venue-body" style="display:none">
  <div>
    <div class="event-section" style="margin-bottom:36px">
      <div class="section-heading">О площадке</div>
      <div class="event-desc" id="venue-desc" style="color:var(--muted);line-height:1.8;font-size:.95rem"></div>
    </div>

    <div class="event-section" id="venue-gallery-section" style="margin-bottom:36px;display:none">
      <div class="section-heading">Фотогалерея</div>
      <div class="gallery-grid-view" id="venue-gallery-grid"></div>
    </div>

    <div class="event-section" style="margin-bottom:36px">
      <div class="section-heading">На карте</div>
      <div class="map-wrap" id="map-wrap">
        <div class="map-placeholder">📍 Адрес не указан</div>
      </div>
    </div>

    <div class="event-section" style="margin-bottom:36px">
      <div class="section-heading">Расписание</div>
      <div id="schedule-wrap"></div>
    </div>

    <div class="event-section" style="margin-bottom:36px">
      <div class="section-heading">Контакты</div>
      <div id="contacts-wrap"></div>
    </div>

    <div class="event-section" style="margin-bottom:36px">
      <div class="section-heading">Отзывы</div>
      <div id="reviews-list"></div>
      <div id="review-form" style="display:none;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px;margin-top:20px">
        <div style="font-weight:700;margin-bottom:12px">Оставить отзыв</div>
        <div class="rating-picker" id="rating-picker">
          <span class="rating-star" data-v="1">★</span><span class="rating-star" data-v="2">★</span>
          <span class="rating-star" data-v="3">★</span><span class="rating-star" data-v="4">★</span>
          <span class="rating-star" data-v="5">★</span>
        </div>
        <textarea class="form-control" id="review-text" rows="3" placeholder="Расскажите о месте..." style="margin-bottom:12px"></textarea>
        <button class="btn btn-primary" onclick="submitReview()">Отправить</button>
      </div>
      <div id="login-prompt" style="display:none;margin-top:16px;text-align:center;color:var(--muted);font-size:.88rem">
        <a href="#" onclick="nav('/login');return false" style="color:var(--accent);font-weight:600">Войдите</a>, чтобы оставить отзыв
      </div>
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
let favId = null;

document.querySelectorAll('.rating-star').forEach(s => {
  s.addEventListener('click', () => {
    selectedRating = +s.dataset.v;
    document.querySelectorAll('.rating-star').forEach((x,i) => x.classList.toggle('active', i < selectedRating));
  });
});

async function init() {
  try {
    const v = await get('/venues/' + id);
    document.title = v.name + ' — АфишаКолыма';

    document.getElementById('venue-header-wrap').innerHTML = `
      <div class="venue-header">
        <div class="venue-header-inner">
          <div class="venue-avatar">${v.image ? `<img src="${v.image}" style="width:100%;height:100%;object-fit:cover;border-radius:13px">` : '🏛️'}</div>
          <div>
            <span class="card-tag green" style="margin-bottom:8px">${v.category_name || 'Площадка'}</span>
            <div class="venue-title">${v.name}</div>
            <div class="venue-meta">
              ${v.address ? `<span>📍 ${v.address}</span>` : ''}
              ${v.age ? `<span>🔞 ${v.age}+</span>` : ''}
            </div>
          </div>
        </div>
      </div>
    `;

    document.getElementById('venue-desc').textContent = v.description || 'Описание не указано.';

    const vGallery = Array.isArray(v.gallery) ? v.gallery.filter(Boolean) : [];
    if (vGallery.length) {
      document.getElementById('venue-gallery-section').style.display = '';
      document.getElementById('venue-gallery-grid').innerHTML = vGallery.map((url, i) => `
        <div class="gallery-photo" onclick="openLightbox(${i})">
          <img src="${escHtml(url)}" alt="Фото ${i+1}" loading="lazy">
        </div>
      `).join('');
      window._venueGalleryImages = vGallery;
    }

    if (v.address) {
      document.getElementById('map-wrap').innerHTML =
        `<iframe src="https://yandex.ru/maps/?text=${encodeURIComponent(v.address)}&z=15&output=embed" allowfullscreen loading="lazy"></iframe>`;
    }

    const sw = document.getElementById('schedule-wrap');
    if (v.schedule && v.schedule.length) {
      const days = {monday:'Пн',tuesday:'Вт',wednesday:'Ср',thursday:'Чт',friday:'Пт',saturday:'Сб',sunday:'Вс'};
      sw.innerHTML = `<div class="schedule-table-wrap"><table class="schedule-table">
        <thead><tr><th>День</th><th>Открытие</th><th>Закрытие</th><th>Примечание</th></tr></thead>
        <tbody>${v.schedule.map(s=>`
          <tr><td><strong>${days[s.day_of_week?.toLowerCase()] || s.day_of_week || '—'}</strong></td>
          <td>${s.start_time || '—'}</td><td>${s.end_time || '—'}</td><td style="color:var(--muted)">${s.description || ''}</td></tr>
        `).join('')}</tbody>
      </table></div>`;
    } else { sw.innerHTML = '<div style="color:var(--muted);font-size:.9rem">Расписание не указано</div>'; }

    const cw = document.getElementById('contacts-wrap');
    if (v.contacts && v.contacts.length) {
      cw.innerHTML = v.contacts.map(c => `
        <div class="contact-item">
          ${c.number ? `<div class="contact-icon">📞</div><div><div style="font-size:.75rem;color:var(--muted)">Телефон</div><div>${c.number}</div></div>` : ''}
          ${c.email ? `<div class="contact-icon" style="margin-left:${c.number?'16px':'0'}">✉️</div><div><div style="font-size:.75rem;color:var(--muted)">Email</div><div>${c.email}</div></div>` : ''}
          ${c.website ? `<div class="contact-icon" style="margin-left:${(c.number||c.email)?'16px':'0'}">🌐</div><div><div style="font-size:.75rem;color:var(--muted)">Сайт</div><a href="${c.website}" target="_blank" style="color:var(--accent)">${c.website}</a></div>` : ''}
          ${c.social_media ? `<div style="margin-left:auto"><a href="${c.social_media}" target="_blank" style="color:var(--accent3);font-size:.85rem">Соц. сети →</a></div>` : ''}
        </div>
      `).join('');
    } else { cw.innerHTML = '<div style="color:var(--muted);font-size:.9rem">Контакты не указаны</div>'; }

    const pageUrl = encodeURIComponent(window.location.href);
    const pageTitle = encodeURIComponent(v.name + ' — АфишаКолыма');

    document.getElementById('sidebar').innerHTML = `
      <div style="font-weight:700;margin-bottom:16px">Информация</div>
      <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:20px">
        ${v.address ? `<div class="sidebar-row" style="display:flex;gap:10px;font-size:.88rem"><span>📍</span><div><div style="font-size:.75rem;color:var(--muted)">Адрес</div><div style="font-weight:600">${v.address}</div></div></div>` : ''}
        ${v.age ? `<div class="sidebar-row" style="display:flex;gap:10px;font-size:.88rem"><span>🔞</span><div><div style="font-size:.75rem;color:var(--muted)">Возраст</div><div style="font-weight:600">${v.age}+</div></div></div>` : ''}
        ${v.category_name ? `<div class="sidebar-row" style="display:flex;gap:10px;font-size:.88rem"><span>🏷️</span><div><div style="font-size:.75rem;color:var(--muted)">Категория</div><div style="font-weight:600">${v.category_name}</div></div></div>` : ''}
      </div>
      <button id="fav-btn" class="btn btn-green btn-full" onclick="addFav()" style="margin-bottom:16px">♡ В избранное</button>
      <div class="share-btns">
        <a href="https://t.me/share/url?url=${pageUrl}&text=${pageTitle}" target="_blank" class="share-btn share-btn-tg">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248l-2.04 9.607c-.15.677-.548.842-1.11.524l-3.073-2.263-1.484 1.428c-.164.164-.302.302-.618.302l.22-3.125 5.686-5.133c.247-.22-.054-.342-.384-.122L7.31 14.442 4.273 13.51c-.665-.208-.677-.665.14-.984l10.879-4.193c.554-.2 1.039.135.862.984-.002-.001-.002.001 0 0l-.592-.069z"/></svg>
          Telegram
        </a>
        <a href="https://vk.com/share.php?url=${pageUrl}&title=${pageTitle}" target="_blank" class="share-btn share-btn-vk">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.408 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.033-1.01-1.49-1.135-1.744-1.135-.356 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.118-5.335-3.202C4.624 10.857 4.03 8.57 4.03 8.096c0-.254.102-.491.593-.491h1.744c.44 0 .61.203.78.677.864 2.495 2.303 4.681 2.896 4.681.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.203.169-.407.44-.407h2.744c.373 0 .508.203.508.643v3.473c0 .372.169.508.271.508.22 0 .407-.136.813-.542 1.254-1.406 2.151-3.574 2.151-3.574.119-.254.322-.491.762-.491h1.744c.525 0 .644.271.525.643-.22 1.017-2.354 4.029-2.354 4.029-.186.305-.254.44 0 .78.186.254.796.78 1.203 1.253.745.847 1.312 1.558 1.464 2.049.17.491-.085.745-.576.745z"/></svg>
          ВКонтакте
        </a>
        <button class="share-btn share-btn-copy" onclick="copyPageLink()">📋 Скопировать</button>
      </div>
    `;

    document.getElementById('venue-body').style.display = '';
    loadReviews();
    loadFavState();
    if (auth.isLoggedIn() && auth.type() === 'user') {
      document.getElementById('review-form').style.display = '';
    } else {
      document.getElementById('login-prompt').style.display = '';
    }
    trackViewHistory('venue', id, v.name, v.image || '');
  } catch(e) {
    document.getElementById('venue-header-wrap').innerHTML = `<div class="empty"><div class="empty-icon">⚠️</div>${e.message}</div>`;
  }
}

async function loadReviews() {
  const list = document.getElementById('reviews-list');
  try {
    const reviews = await get('/reviews?venue_id=' + id);
    if (!reviews.length) { list.innerHTML = '<div class="empty" style="padding:24px"><div class="empty-icon">💬</div>Пока нет отзывов</div>'; return; }
    list.innerHTML = renderReviewsHtml(reviews);
  } catch(e) {}
}

async function submitReview() {
  if (!selectedRating) { toast('Выберите оценку', 'error'); return; }
  const text = document.getElementById('review-text').value.trim();
  if (!text) { toast('Напишите отзыв', 'error'); return; }
  try {
    await post('/reviews', { venue_id: +id, text, rating: selectedRating });
    toast('Отзыв добавлен!', 'success');
    document.getElementById('review-text').value = '';
    selectedRating = 0;
    document.querySelectorAll('.rating-star').forEach(s => s.classList.remove('active'));
    loadReviews();
  } catch(e) { toast(e.message, 'error'); }
}

async function loadFavState() {
  if (!auth.isLoggedIn() || auth.type() !== 'user') return;
  try {
    const favs = await get('/favorites');
    const fav = favs.find(f => +f.venue_id === +id && !f.event_id);
    if (fav) {
      favId = fav.favorite_id;
      const btn = document.getElementById('fav-btn');
      if (btn) btn.innerHTML = '♥ В избранном';
    }
  } catch(e) {}
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
      const r = await post('/favorites', { venue_id: +id });
      favId = r?.favorite_id ?? null;
      if (btn) btn.innerHTML = '♥ В избранном';
      toast('Добавлено в избранное!', 'success');
    }
  } catch(e) { toast(e.message, 'error'); }
  finally { if (btn) btn.disabled = false; }
}

function trackViewHistory(type, itemId, title, image) {
  try {
    const hist = JSON.parse(localStorage.getItem('view_history') || '[]');
    const entry = { type, id: +itemId, title, image, url: window.location.pathname, viewedAt: Date.now() };
    const filtered = hist.filter(h => !(h.type === type && h.id === +itemId));
    filtered.unshift(entry);
    localStorage.setItem('view_history', JSON.stringify(filtered.slice(0, 30)));
  } catch(e) {}
}

function copyPageLink() {
  navigator.clipboard.writeText(window.location.href)
    .then(() => toast('Ссылка скопирована!', 'success'))
    .catch(() => {
      const el = document.createElement('input');
      el.value = window.location.href;
      document.body.appendChild(el); el.select(); document.execCommand('copy'); document.body.removeChild(el);
      toast('Ссылка скопирована!', 'success');
    });
}

init();

let _lbIdx = 0;
function openLightbox(startIdx) {
  const imgs = window._venueGalleryImages || [];
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
  const imgs = window._venueGalleryImages || [];
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
