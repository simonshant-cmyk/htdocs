@extends('layouts.app')

@section('title', 'Кабинет организации — АфишаКолыма')

@section('styles')
<style>
  .cabinet-layout { max-width:1100px; margin:0 auto; padding:40px 32px; display:grid; grid-template-columns:260px 1fr; gap:32px; }
  @media(max-width:800px){ .cabinet-layout{grid-template-columns:1fr;} }

  .org-card { background:var(--hero-bg); border-radius:var(--radius); padding:28px; text-align:center; margin-bottom:16px; position:relative; overflow:hidden; }
  .org-card::before { content:''; position:absolute; inset:0; background:radial-gradient(ellipse at 80% 20%, rgba(42,110,90,.35) 0%, transparent 60%); }
  .org-avatar { width:72px; height:72px; border-radius:14px; background:var(--accent2); color:#fff; font-size:1.8rem; font-weight:700; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; position:relative; z-index:1; }
  .org-name { font-weight:700; font-size:1rem; color:#fff; margin-bottom:4px; position:relative; z-index:1; }
  .org-email { color:rgba(255,255,255,.55); font-size:.8rem; position:relative; z-index:1; }

  .cabinet-nav { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); overflow:hidden; }
  .cabinet-nav-item { display:flex; align-items:center; gap:12px; padding:13px 18px; font-size:.9rem; font-weight:500; cursor:pointer; transition:var(--transition); border-bottom:1px solid var(--bg2); color:var(--muted); }
  .cabinet-nav-item:last-child{border-bottom:none;}
  .cabinet-nav-item:hover,.cabinet-nav-item.active{background:rgba(42,110,90,.06);color:var(--accent2);}

  .section-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
  .section-head h2 { font-family:var(--font-display); font-size:1.6rem; font-weight:700; }
  .cabinet-section { display:none; }
  .cabinet-section.active { display:block; animation:sectionIn .24s cubic-bezier(.4,0,.2,1) both; }
  @keyframes sectionIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }

  .event-row { display:flex; gap:14px; align-items:center; padding:14px 16px; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-sm); margin-bottom:10px; }
  .event-row-thumb { width:56px; height:56px; border-radius:8px; background:var(--bg2); flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:1.4rem; }
  .event-row-info { flex:1; }
  .event-row-title { font-weight:600; font-size:.92rem; margin-bottom:3px; }
  .event-row-meta { font-size:.78rem; color:var(--muted); }
  .event-row-actions { display:flex; gap:8px; }

  .event-form { display:flex; flex-direction:column; gap:14px; }
  .form-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
  @media(max-width:480px){ .form-row{grid-template-columns:1fr;} }

  .avatar-upload { position:relative; width:72px; height:72px; margin:0 auto 14px; cursor:pointer; display:block; border-radius:14px; }
  .avatar-upload input { display:none; }
  .avatar-upload-overlay { position:absolute; inset:0; border-radius:14px; background:rgba(0,0,0,.52); color:#fff; font-size:.72rem; font-weight:700; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px; opacity:0; transition:opacity .18s; }
  .avatar-upload:hover .avatar-upload-overlay { opacity:1; }
  .avatar-upload .org-avatar { margin:0; }
  .avatar-hint { font-size:.68rem; color:rgba(255,255,255,.4); margin-top:8px; letter-spacing:.02em; display:flex; align-items:center; justify-content:center; gap:5px; }
  .avatar-hint-dot { width:3px; height:3px; border-radius:50%; background:rgba(255,255,255,.25); display:inline-block; }

  .field-wrap { position:relative; }
  .field-wrap.field-error .form-control   { border-color:#ef4444!important; box-shadow:0 0 0 3px rgba(239,68,68,.1)!important; }
  .field-wrap.field-success .form-control { border-color:#22c55e!important; box-shadow:0 0 0 3px rgba(34,197,94,.08)!important; }
  .field-hint { font-size:.72rem; min-height:16px; margin-top:3px; color:#ef4444; line-height:1.3; transition:color .15s; }
  .field-hint.ok { color:#22c55e; }
  @keyframes cabShake { 0%,100%{transform:translateX(0)} 15%{transform:translateX(-6px)} 30%{transform:translateX(6px)} 45%{transform:translateX(-4px)} 60%{transform:translateX(4px)} 75%{transform:translateX(-2px)} }
  .shake { animation:cabShake .35s ease; }
  @keyframes btnSpin { to { transform:rotate(360deg); } }

  .stat-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:28px; }
  @media(max-width:640px){ .stat-grid{grid-template-columns:1fr 1fr;} }
  @media(max-width:400px){ .stat-grid{grid-template-columns:1fr;} }
  .stat-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:20px 22px; }
  .stat-label { font-size:.72rem; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px; }
  .stat-value { font-size:1.7rem; font-weight:800; font-family:var(--font-display); line-height:1; }
  .stat-sub   { font-size:.75rem; color:var(--muted); margin-top:4px; }

  .bar-chart  { display:flex; align-items:flex-end; gap:4px; height:120px; }
  .bar-col    { flex:1; display:flex; flex-direction:column; align-items:center; gap:3px; }
  .bar-fill   { width:100%; background:var(--accent2); border-radius:3px 3px 0 0; min-height:2px; transition:height .3s; }
  .bar-label  { font-size:.6rem; color:var(--muted); transform:rotate(-45deg); white-space:nowrap; }

  .analytics-table { width:100%; border-collapse:collapse; font-size:.85rem; }
  .analytics-table th { text-align:left; padding:8px 10px; color:var(--muted); font-weight:600; font-size:.72rem; text-transform:uppercase; border-bottom:1.5px solid var(--border); }
  .analytics-table td { padding:10px; border-bottom:1px solid var(--bg2); }
  .analytics-table tr:last-child td { border-bottom:none; }
  .analytics-table tr:hover td { background:rgba(42,110,90,.04); }

  .buyers-table-wrap { overflow-x:auto; }
  .review-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:18px 20px; margin-bottom:12px; }
  .review-card-header { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:10px; }
  .review-user { display:flex; align-items:center; gap:10px; }
  .review-user-avatar { width:36px; height:36px; border-radius:50%; background:var(--accent2); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.9rem; flex-shrink:0; }
  .review-user-avatar img { width:100%; height:100%; border-radius:50%; object-fit:cover; }
  .review-stars { font-size:1rem; letter-spacing:.1em; }
  .social-input-wrap { display:flex; align-items:center; gap:8px; }
  .social-prefix { font-size:.85rem; color:var(--muted); white-space:nowrap; }

  .gallery-grid { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:10px; min-height:0; }
  .gallery-thumb { position:relative; width:84px; height:84px; border-radius:10px; overflow:visible; flex-shrink:0; }
  .gallery-thumb img { width:84px; height:84px; object-fit:cover; border-radius:10px; border:2px solid var(--border); display:block; transition:border-color .15s; }
  .gallery-thumb:first-child img { border-color:var(--accent2); }
  .gallery-cover-badge { position:absolute; bottom:0; left:0; right:0; background:rgba(42,110,90,.85); color:#fff; font-size:.58rem; font-weight:700; text-align:center; padding:2px 0; border-radius:0 0 9px 9px; pointer-events:none; }
  .gallery-btn { position:absolute; width:18px; height:18px; border-radius:50%; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:.65rem; line-height:1; transition:transform .12s; }
  .gallery-btn:hover { transform:scale(1.15); }
  .gallery-btn-remove { top:-7px; right:-7px; background:var(--accent); color:#fff; }
  .gallery-btn-left   { top:-7px; left:-7px; background:var(--surface); border:1.5px solid var(--border); color:var(--text); font-size:.7rem; }
  .gallery-upload-hint { font-size:.72rem; color:var(--muted); margin-top:4px; }

  .venue-row { display:flex; gap:14px; align-items:flex-start; padding:16px; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-sm); margin-bottom:10px; }
  .venue-row-thumb { width:72px; height:72px; border-radius:10px; background:var(--bg2); flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:1.6rem; overflow:hidden; }
  .venue-row-thumb img { width:100%; height:100%; object-fit:cover; }
  .venue-row-info { flex:1; min-width:0; }
  .venue-row-title { font-weight:700; font-size:.93rem; margin-bottom:3px; }
  .venue-row-meta { font-size:.77rem; color:var(--muted); margin-bottom:8px; }
  .venue-gallery-strip { display:flex; gap:6px; flex-wrap:wrap; margin-top:8px; }
  .venue-gallery-strip-thumb { width:52px; height:52px; border-radius:7px; object-fit:cover; cursor:pointer; border:1.5px solid var(--border); }
  .venue-gallery-panel { background:var(--bg2); border:1px solid var(--border); border-radius:var(--radius-sm); padding:14px 16px; margin-top:8px; }
  .lightbox-overlay { position:fixed; inset:0; background:rgba(0,0,0,.88); z-index:9999; display:flex; align-items:center; justify-content:center; }
  .lightbox-img { max-width:92vw; max-height:88vh; border-radius:10px; box-shadow:0 8px 60px rgba(0,0,0,.7); }
  .lightbox-close { position:absolute; top:18px; right:22px; font-size:2rem; color:#fff; background:none; border:none; cursor:pointer; opacity:.8; }
  .lightbox-close:hover { opacity:1; }
  .lightbox-nav { position:absolute; top:50%; transform:translateY(-50%); font-size:2rem; color:#fff; background:rgba(255,255,255,.1); border:none; cursor:pointer; width:50px; height:50px; border-radius:50%; display:flex; align-items:center; justify-content:center; transition:background .15s; }
  .lightbox-nav:hover { background:rgba(255,255,255,.22); }
  .lightbox-prev { left:18px; }
  .lightbox-next { right:18px; }

  .status-gate { min-height:calc(100vh - 68px); display:flex; align-items:center; justify-content:center; padding:40px 24px; }
  .status-card { background:var(--surface); border:1.5px solid var(--border); border-radius:24px; padding:56px 48px; text-align:center; max-width:520px; width:100%; box-shadow:var(--shadow-lg); }
  .status-icon { width:96px; height:96px; border-radius:50%; margin:0 auto 28px; display:flex; align-items:center; justify-content:center; font-size:2.8rem; }
  .status-icon.pending { background:rgba(245,158,11,.12); border:2px solid rgba(245,158,11,.3); animation:pulse-ring 2.2s ease-out infinite; }
  .status-icon.rejected { background:rgba(239,68,68,.1); border:2px solid rgba(239,68,68,.25); }
  .status-title { font-family:var(--font-display); font-size:1.7rem; font-weight:800; margin-bottom:10px; }
  .status-sub { color:var(--muted); font-size:.93rem; line-height:1.7; margin-bottom:28px; }
  .status-org-name { display:inline-block; padding:6px 18px; background:var(--bg2); border:1px solid var(--border); border-radius:20px; font-size:.85rem; font-weight:600; color:var(--text); margin-bottom:28px; }
  .status-badge-wrap { margin-bottom:32px; }
  .status-actions { display:flex; flex-direction:column; gap:10px; }
  @keyframes pulse-ring { 0%{box-shadow:0 0 0 0 rgba(245,158,11,.4)} 70%{box-shadow:0 0 0 16px rgba(245,158,11,0)} 100%{box-shadow:0 0 0 0 rgba(245,158,11,0)} }
</style>
@endsection

@section('content')
<div id="status-gate" style="display:none">
  <div class="status-gate">
    <div class="status-card" id="status-card-content"></div>
  </div>
</div>

<div class="cabinet-layout page-enter" id="cabinet-layout">
  <div>
    <div class="org-card">
      <label class="avatar-upload" title="Изменить фото">
        <input type="file" id="org-avatar-file" accept="image/*" onchange="uploadAvatar(this)">
        <div class="org-avatar" id="org-avatar">О</div>
        <div class="avatar-upload-overlay">📷<br><span style="font-size:.6rem">Изменить</span></div>
      </label>
      <div class="avatar-hint">
        JPG, PNG, WebP <span class="avatar-hint-dot"></span> до 5 МБ
      </div>
      <div class="org-name" id="org-name">—</div>
      <div class="org-email" id="org-email"></div>
    </div>
    <div class="cabinet-nav">
      <div class="cabinet-nav-item active" onclick="showSection('events')"    id="nav-events">   <span>🎭</span> Мои события</div>
      <div class="cabinet-nav-item"        onclick="showSection('analytics')" id="nav-analytics"><span>📊</span> Аналитика</div>
      <div class="cabinet-nav-item"        onclick="showSection('venues')"    id="nav-venues">   <span>🏛</span> Площадки</div>
      <div class="cabinet-nav-item"        onclick="showSection('buyers')"    id="nav-buyers">   <span>🎫</span> Покупатели</div>
      <div class="cabinet-nav-item"        onclick="showSection('reviews')"   id="nav-reviews">  <span>⭐</span> Отзывы</div>
      <div class="cabinet-nav-item"        onclick="showSection('promo')"     id="nav-promo">    <span>🎟️</span> Промокоды</div>
      <div class="cabinet-nav-item"        onclick="showSection('profile')"   id="nav-profile">  <span>🏢</span> Профиль</div>
      <div class="cabinet-nav-item" onclick="auth.logout()" style="color:var(--accent)"><span>🚪</span> Выйти</div>
    </div>
  </div>

  <div>
    <div class="cabinet-section active" id="section-events">
      <div class="section-head">
        <h2>Мои события</h2>
        <button class="btn btn-green" onclick="openModal('modal-create')">+ Создать событие</button>
      </div>
      <div id="events-list"><div class="loader"><div class="spinner"></div></div></div>
    </div>

    <div class="cabinet-section" id="section-analytics">
      <div class="section-head"><h2>Аналитика</h2></div>
      <div id="analytics-wrap"><div class="loader"><div class="spinner"></div></div></div>
    </div>

    <div class="cabinet-section" id="section-venues">
      <div class="section-head">
        <h2>Мои площадки</h2>
        <button class="btn btn-green" onclick="openVenueModal()">+ Новая площадка</button>
      </div>
      <div id="venues-list-cabinet"><div class="loader"><div class="spinner"></div></div></div>
    </div>

    <div class="cabinet-section" id="section-buyers">
      <div class="section-head">
        <h2>Покупатели</h2>
        <select id="buyers-event-filter" class="form-control" style="width:auto;min-width:0;max-width:100%" onchange="loadBuyers()">
          <option value="">Все события</option>
        </select>
      </div>
      <div id="buyers-wrap"><div class="loader"><div class="spinner"></div></div></div>
    </div>

    <div class="cabinet-section" id="section-reviews">
      <div class="section-head"><h2>Отзывы гостей</h2></div>
      <div id="reviews-wrap"><div class="loader"><div class="spinner"></div></div></div>
    </div>

    <div class="cabinet-section" id="section-promo">
      <div class="section-head">
        <h2>Промокоды</h2>
        <button class="btn btn-green" onclick="openPromoModal()">+ Создать промокод</button>
      </div>
      <div id="promo-list"><div class="loader"><div class="spinner"></div></div></div>
    </div>

    <div class="cabinet-section" id="section-profile">
      <div class="section-head"><h2>Профиль организации</h2></div>
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:28px;max-width:500px">
        <div style="display:flex;flex-direction:column;gap:14px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Название</label>
            <div class="field-wrap" id="wrap-p-name">
              <input class="form-control" id="p-name" placeholder="ООО Ромашка" oninput="blurOrg('p-name')" onblur="blurOrg('p-name')">
            </div>
            <div class="field-hint" id="hint-p-name"></div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Email</label>
            <div class="field-wrap" id="wrap-p-email">
              <input class="form-control" id="p-email" type="email" placeholder="org@example.ru" onblur="blurOrg('p-email')">
            </div>
            <div class="field-hint" id="hint-p-email"></div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Адрес</label>
            <div class="field-wrap" id="wrap-p-address">
              <input class="form-control" id="p-address" placeholder="г. Магадан, ул. Ленина, 1" onblur="blurOrg('p-address')">
            </div>
            <div class="field-hint" id="hint-p-address"></div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">ИНН <span style="font-weight:400;color:var(--muted);font-size:.78rem">10 или 12 цифр</span></label>
            <div class="field-wrap" id="wrap-p-inn">
              <input class="form-control" id="p-inn" placeholder="1234567890" maxlength="12" inputmode="numeric"
                oninput="this.value=this.value.replace(/\D/g,'').slice(0,12);blurOrg('p-inn')" onblur="blurOrg('p-inn')">
            </div>
            <div class="field-hint" id="hint-p-inn"></div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">ОГРН <span style="font-weight:400;color:var(--muted);font-size:.78rem">13 цифр (ООО/АО) или 15 (ИП)</span></label>
            <div class="field-wrap" id="wrap-p-ogrn">
              <input class="form-control" id="p-ogrn" placeholder="1234567890123" maxlength="15" inputmode="numeric"
                oninput="this.value=this.value.replace(/\D/g,'').slice(0,15);blurOrg('p-ogrn')" onblur="blurOrg('p-ogrn')">
            </div>
            <div class="field-hint" id="hint-p-ogrn"></div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">КПП <span style="font-weight:400;color:var(--muted);font-size:.78rem">9 цифр, только для юрлиц</span></label>
            <div class="field-wrap" id="wrap-p-kpp">
              <input class="form-control" id="p-kpp" placeholder="123456789" maxlength="9" inputmode="numeric"
                oninput="this.value=this.value.replace(/\D/g,'').slice(0,9);blurOrg('p-kpp')" onblur="blurOrg('p-kpp')">
            </div>
            <div class="field-hint" id="hint-p-kpp"></div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Телефон организации</label>
            <div class="field-wrap" id="wrap-p-phone">
              <input class="form-control" id="p-phone" type="tel" placeholder="+7 (999) 123-45-67" onblur="blurOrg('p-phone')">
            </div>
            <div class="field-hint" id="hint-p-phone"></div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Сайт <span style="font-weight:400;color:var(--muted);font-size:.78rem">(необязательно)</span></label>
            <div class="field-wrap" id="wrap-p-website">
              <input class="form-control" id="p-website" type="url" placeholder="https://example.ru" onblur="blurOrg('p-website')">
            </div>
            <div class="field-hint" id="hint-p-website"></div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">ВКонтакте <span style="font-weight:400;color:var(--muted);font-size:.78rem">(необязательно)</span></label>
            <div class="field-wrap" id="wrap-p-vk">
              <div class="social-input-wrap">
                <span class="social-prefix">vk.com/</span>
                <input class="form-control" id="p-vk" placeholder="club123456" onblur="blurOrg('p-vk')">
              </div>
            </div>
            <div class="field-hint" id="hint-p-vk"></div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Telegram <span style="font-weight:400;color:var(--muted);font-size:.78rem">(необязательно)</span></label>
            <div class="field-wrap" id="wrap-p-telegram">
              <div class="social-input-wrap">
                <span class="social-prefix">t.me/</span>
                <input class="form-control" id="p-telegram" placeholder="mychannel" onblur="blurOrg('p-telegram')">
              </div>
            </div>
            <div class="field-hint" id="hint-p-telegram"></div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">О нас <span style="font-weight:400;color:var(--muted);font-size:.78rem">(необязательно, до 2000 символов)</span></label>
            <div class="field-wrap" id="wrap-p-description">
              <textarea class="form-control" id="p-description" rows="4" placeholder="Расскажите о вашей организации..." maxlength="2000" oninput="blurOrg('p-description')" onblur="blurOrg('p-description')"></textarea>
            </div>
            <div class="field-hint" id="hint-p-description"></div>
          </div>
          <button class="btn btn-green" id="save-org-btn" onclick="saveProfile()" style="margin-top:4px">Сохранить</button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-create" onclick="if(event.target===this)closeModal('modal-create')">
  <div class="modal" style="max-width:600px;max-height:90vh;overflow-y:auto">
    <div class="modal-title" id="modal-title">Новое событие</div>
    <div class="event-form">
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label">Название *</label>
        <div class="field-wrap" id="wrap-ev-title">
          <input class="form-control" id="ev-title" placeholder="Название события" oninput="blurEv('ev-title')" onblur="blurEv('ev-title')">
        </div>
        <div class="field-hint" id="hint-ev-title"></div>
      </div>
      <div class="form-group"><label class="form-label">Описание</label><textarea class="form-control" id="ev-desc" rows="3" placeholder="Описание..."></textarea></div>
      <div class="form-row">
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label">Начало *</label>
          <div class="field-wrap" id="wrap-ev-start">
            <input class="form-control" id="ev-start" type="datetime-local" onchange="blurEv('ev-start');blurEv('ev-end')" onblur="blurEv('ev-start');blurEv('ev-end')">
          </div>
          <div class="field-hint" id="hint-ev-start"></div>
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label">Конец *</label>
          <div class="field-wrap" id="wrap-ev-end">
            <input class="form-control" id="ev-end" type="datetime-local" onchange="blurEv('ev-end')" onblur="blurEv('ev-end')">
          </div>
          <div class="field-hint" id="hint-ev-end"></div>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label">Цена (₽)</label>
          <div class="field-wrap" id="wrap-ev-price">
            <input class="form-control" id="ev-price" type="text" inputmode="numeric" placeholder="0 — бесплатно" maxlength="10"
              oninput="this.value=this.value.replace(/[^0-9]/g,'').replace(/^0+(?=\d)/,'')" onblur="blurEv('ev-price')">
          </div>
          <div class="field-hint" id="hint-ev-price"></div>
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label">Возраст +</label>
          <div class="field-wrap" id="wrap-ev-age">
            <input class="form-control" id="ev-age" type="text" inputmode="numeric" placeholder="0" maxlength="2"
              oninput="this.value=this.value.replace(/\D/g,'').slice(0,2)" onblur="blurEv('ev-age')">
          </div>
          <div class="field-hint" id="hint-ev-age"></div>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label">Мест всего (вместимость)</label>
          <div class="field-wrap" id="wrap-ev-capacity">
            <input class="form-control" id="ev-capacity" type="text" inputmode="numeric" placeholder="Не ограничено" maxlength="6"
              oninput="this.value=this.value.replace(/\D/g,'')" onblur="blurEv('ev-capacity')">
          </div>
          <div class="field-hint" id="hint-ev-capacity"></div>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Площадка</label><select class="form-control" id="ev-venue"><option value="">— не указана —</option></select></div>
        <div class="form-group"><label class="form-label">Категория</label><select class="form-control" id="ev-cat"><option value="">— не указана —</option></select></div>
      </div>
      <div class="form-group">
        <label class="form-label">Изображение события</label>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
          <label style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:.85rem;font-weight:600;color:var(--muted);transition:var(--transition)" onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)'">
            📎 Загрузить файл
            <input type="file" id="ev-image-file" accept="image/*" style="display:none" onchange="uploadEventImage(this)">
          </label>
          <span style="font-size:.8rem;color:var(--muted)">или</span>
          <input class="form-control" id="ev-image" placeholder="https://... (ссылка на изображение)" style="flex:1;min-width:0">
        </div>
        <div id="ev-image-preview" style="margin-top:8px;display:none">
          <img id="ev-image-thumb" style="height:80px;border-radius:8px;object-fit:cover;border:1px solid var(--border)">
          <button onclick="clearEventImage()" style="margin-left:8px;background:none;border:none;color:var(--muted);cursor:pointer;font-size:.85rem">✕ Убрать</button>
        </div>
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label">Галерея <span style="font-weight:400;color:var(--muted);font-size:.78rem">до 20 фото · первое фото = обложка</span></label>
        <div class="gallery-grid" id="ev-gallery-grid"></div>
        <label style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:.85rem;font-weight:600;color:var(--muted);transition:var(--transition)" onmouseover="this.style.borderColor='var(--accent2)';this.style.color='var(--accent2)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)'">
          🖼 Добавить в галерею
          <input type="file" id="ev-gallery-file" accept="image/*" multiple style="display:none" onchange="uploadGalleryImages(this,'event','ev')">
        </label>
        <div class="gallery-upload-hint">JPG, PNG, WebP · мин. 800px по ширине · до 5 МБ каждый</div>
      </div>
    </div>
    <div style="display:flex;gap:10px;margin-top:20px;flex-wrap:wrap">
      <button class="btn btn-primary" onclick="saveEvent()">Сохранить</button>
      <button class="btn btn-secondary" id="draft-btn" onclick="saveEvent(true)">💾 Черновик</button>
      <button class="btn btn-secondary" onclick="closeModal('modal-create')" style="margin-left:auto">Отмена</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-venue" onclick="if(event.target===this)closeVenueModal()">
  <div class="modal" style="max-width:640px;max-height:92vh;overflow-y:auto">
    <div class="modal-title" id="venue-modal-title">Новая площадка</div>
    <div style="display:flex;flex-direction:column;gap:14px">
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label">Название *</label>
        <div class="field-wrap" id="wrap-vn-name">
          <input class="form-control" id="vn-name" placeholder="Название площадки" oninput="blurVn('vn-name')" onblur="blurVn('vn-name')">
        </div>
        <div class="field-hint" id="hint-vn-name"></div>
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label">Описание</label>
        <textarea class="form-control" id="vn-desc" rows="3" placeholder="Краткое описание площадки..."></textarea>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Категория</label><select class="form-control" id="vn-cat"><option value="">— не указана —</option></select></div>
        <div class="form-group"><label class="form-label">Возраст +</label><input class="form-control" id="vn-age" type="text" inputmode="numeric" placeholder="0" maxlength="2" oninput="this.value=this.value.replace(/\D/g,'').slice(0,2)"></div>
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label">Адрес</label>
        <div style="display:flex;gap:8px">
          <div class="field-wrap" id="wrap-vn-address" style="flex:1">
            <input class="form-control" id="vn-address" placeholder="ул. Ленина, 1" oninput="blurVn('vn-address')">
          </div>
          <button type="button" class="btn btn-secondary" style="white-space:nowrap;flex-shrink:0" onclick="geocodeVenueAddress()">📍 Найти на карте</button>
        </div>
        <div style="font-size:.72rem;color:var(--muted);margin-top:4px">Введите улицу и номер дома — «Магадан» подставляется автоматически. Или кликните по карте вручную.</div>
        <div class="field-hint" id="hint-vn-address"></div>
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label" style="display:flex;justify-content:space-between">
          <span>Местоположение на карте</span>
          <span id="vn-coords-display" style="font-size:.72rem;color:var(--muted)">Кликните по карте или введите адрес</span>
        </label>
        <div id="vn-map-picker" style="height:260px;border-radius:var(--radius-sm);border:1.5px solid var(--border);background:var(--bg2);display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:.88rem">
          <span>🗺 Карта загружается...</span>
        </div>
        <input type="hidden" id="vn-lat">
        <input type="hidden" id="vn-lng">
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label">Фото площадки (обложка)</label>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
          <label style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:.85rem;font-weight:600;color:var(--muted);transition:var(--transition)" onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)'">
            📎 Загрузить файл
            <input type="file" id="vn-image-file" accept="image/*" style="display:none" onchange="uploadVenueModalImage(this)">
          </label>
          <span style="font-size:.8rem;color:var(--muted)">или</span>
          <input class="form-control" id="vn-image" placeholder="https://... (ссылка)" style="flex:1;min-width:0">
        </div>
        <div id="vn-image-preview" style="margin-top:8px;display:none">
          <img id="vn-image-thumb" style="height:72px;border-radius:8px;object-fit:cover;border:1px solid var(--border)">
          <button onclick="clearVenueModalImage()" style="margin-left:8px;background:none;border:none;color:var(--muted);cursor:pointer;font-size:.85rem">✕ Убрать</button>
        </div>
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label">Галерея <span style="font-weight:400;color:var(--muted);font-size:.78rem">до 20 фото · первое = обложка</span></label>
        <div class="gallery-grid" id="vn-gallery-grid"></div>
        <label style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:.85rem;font-weight:600;color:var(--muted);transition:var(--transition)" onmouseover="this.style.borderColor='var(--accent2)';this.style.color='var(--accent2)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)'">
          🖼 Добавить в галерею
          <input type="file" id="vn-gallery-file" accept="image/*" multiple style="display:none" onchange="uploadGalleryImages(this,'venue','vn')">
        </label>
        <div class="gallery-upload-hint">JPG, PNG, WebP · мин. 1000px · до 5 МБ</div>
      </div>
    </div>
    <div style="display:flex;gap:10px;margin-top:20px">
      <button class="btn btn-primary" onclick="saveVenue()">Сохранить площадку</button>
      <button class="btn btn-secondary" onclick="closeVenueModal()">Отмена</button>
    </div>
  </div>
</div>

<!-- Модалка создания промокода -->
<div class="modal-overlay" id="modal-promo" onclick="if(event.target===this)closeModal('modal-promo')">
  <div class="modal" style="max-width:440px">
    <div class="modal-header">
      <div class="modal-title">Новый промокод</div>
      <button class="modal-close" onclick="closeModal('modal-promo')">✕</button>
    </div>
    <div style="display:flex;flex-direction:column;gap:14px;padding:4px 0">
      <div class="form-group" style="margin:0">
        <label class="form-label">Код <span style="color:var(--accent)">*</span></label>
        <input class="form-control" id="promo-code-inp" placeholder="ЛЕТО2025" style="text-transform:uppercase">
        <div style="font-size:.75rem;color:var(--muted);margin-top:4px">Только латиница/цифры, без пробелов</div>
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">Тип скидки <span style="color:var(--accent)">*</span></label>
        <select class="form-control" id="promo-type-inp">
          <option value="percent">Процент (%)</option>
          <option value="fixed">Фиксированная сумма (₽)</option>
        </select>
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">Размер скидки <span style="color:var(--accent)">*</span></label>
        <input class="form-control" id="promo-value-inp" type="number" min="1" placeholder="10">
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">Макс. использований</label>
        <input class="form-control" id="promo-uses-inp" type="number" min="1" placeholder="Не ограничено">
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">Действует до</label>
        <input class="form-control" id="promo-expires-inp" type="date">
      </div>
      <div style="display:flex;gap:10px;margin-top:6px">
        <button class="btn btn-primary" onclick="savePromo()">Создать</button>
        <button class="btn btn-secondary" onclick="closeModal('modal-promo')">Отмена</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
if (!auth.isLoggedIn() || auth.type() !== 'org') nav('/login');

let editingId = null;
let org = auth.org();
let _eventsCache = {};
let _statusPollTimer = null;
let categories = [];

function showStatusGate(orgData) {
  const status = +orgData.status_id;
  if (status === OrgStatus.APPROVED) return false;

  document.getElementById('cabinet-layout').style.display = 'none';
  document.getElementById('status-gate').style.display = '';

  const isPending = status === OrgStatus.PENDING;
  document.getElementById('status-card-content').innerHTML = isPending ? `
    <div class="status-icon pending">⏳</div>
    <div class="status-title">Заявка на рассмотрении</div>
    <div class="status-sub">
      Мы получили вашу заявку и в ближайшее время проверим данные организации.<br>
      Обычно проверка занимает до 24 часов. Мы уведомим вас по email при изменении статуса.
    </div>
    <div class="status-org-name">🏢 ${escHtml(orgData.full_name || '—')}</div>
    <div class="status-badge-wrap">
      <span class="badge" style="background:rgba(245,158,11,.15);color:#d97706;font-size:.8rem">⏳ На рассмотрении</span>
    </div>
    <div class="status-actions">
      <button class="btn btn-primary" id="check-status-btn" onclick="checkStatus()">🔄 Проверить статус</button>
      <button class="btn btn-secondary" onclick="auth.logout()">Выйти из аккаунта</button>
    </div>
    <div id="check-status-msg" style="margin-top:14px;font-size:.82rem;color:var(--muted)">Автопроверка каждые 30 секунд</div>
  ` : `
    <div class="status-icon rejected">✕</div>
    <div class="status-title" style="color:#ef4444">Заявка отклонена</div>
    <div class="status-sub">К сожалению, ваша заявка не прошла проверку модератора. Свяжитесь с нами, чтобы подать повторную заявку.</div>
    <div class="status-org-name">🏢 ${escHtml(orgData.full_name || '—')}</div>
    ${orgData.rejection_reason ? `
    <div style="background:rgba(239,68,68,.06);border:1.5px solid rgba(239,68,68,.2);border-radius:12px;padding:14px 18px;margin-bottom:24px;text-align:left">
      <div style="font-size:.72rem;color:#ef4444;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Причина отклонения</div>
      <div style="font-size:.9rem;line-height:1.5;color:var(--text)">${escHtml(orgData.rejection_reason)}</div>
    </div>` : `<div class="status-badge-wrap"><span class="badge" style="background:rgba(239,68,68,.1);color:#ef4444;font-size:.8rem">✕ Отклонено</span></div>`}
    <div class="status-actions">
      <a href="mailto:support@afishakolyma.ru" class="btn btn-primary" style="text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px">✉️ Написать в поддержку</a>
      <button class="btn btn-secondary" onclick="auth.logout()">Выйти из аккаунта</button>
    </div>
  `;

  if (isPending) startStatusPoll();
  return true;
}

async function checkStatus() {
  const btn = document.getElementById('check-status-btn');
  const msg = document.getElementById('check-status-msg');
  if (btn) { btn.disabled = true; btn.textContent = '⏳ Проверяем...'; }
  try {
    const fresh = await get('/auth/me');
    auth.saveOrg(auth.token(), fresh);
    org = fresh;
    if (+fresh.status_id === OrgStatus.APPROVED) {
      stopStatusPoll();
      toast('Заявка одобрена! Добро пожаловать 🎉', 'success');
      setTimeout(() => location.reload(), 1200);
    } else if (+fresh.status_id === OrgStatus.REJECTED) {
      stopStatusPoll();
      showStatusGate(fresh);
    } else {
      if (msg) msg.textContent = 'Статус не изменился. Следующая проверка через 30 сек';
      if (btn) { btn.disabled = false; btn.textContent = '🔄 Проверить статус'; }
    }
  } catch(e) {
    if (btn) { btn.disabled = false; btn.textContent = '🔄 Проверить статус'; }
    toast('Ошибка проверки статуса', 'error');
  }
}

function startStatusPoll() {
  stopStatusPoll();
  _statusPollTimer = setInterval(checkStatus, 30000);
}
function stopStatusPoll() {
  if (_statusPollTimer) { clearInterval(_statusPollTimer); _statusPollTimer = null; }
}

function renderOrgAvatar(o) {
  const el = document.getElementById('org-avatar');
  if (o.image) {
    el.style.background = 'none';
    el.innerHTML = `<img src="${o.image}" style="width:100%;height:100%;border-radius:13px;object-fit:cover">`;
  } else {
    el.style.background = '';
    el.textContent = o.full_name?.[0]?.toUpperCase() || 'О';
  }
}

async function uploadAvatar(input) {
  const file = input.files[0];
  if (!file) return;
  input.value = '';
  const allowed = ['image/jpeg','image/png','image/webp','image/gif'];
  if (!allowed.includes(file.type)) { toast('Недопустимый формат. Используйте JPG, PNG или WebP', 'error'); return; }
  if (file.size > 5*1024*1024) { toast(`Файл слишком большой. Максимум 5 МБ`, 'error'); return; }
  const form = new FormData();
  form.append('file', file);
  try {
    const res = await fetch(API + '/upload/avatar', { method:'POST', headers:{'Authorization':'Bearer '+auth.token()}, body:form });
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || 'Ошибка загрузки');
    const updated = await put('/auth/me', { image: json.data.url });
    store.set('org', updated);
    renderOrgAvatar(updated);
    toast('Фото обновлено', 'success');
  } catch(e) { toast(e.message, 'error'); }
}

async function uploadEventImage(input) {
  const file = input.files[0];
  if (!file) return;
  input.value = '';
  const allowed = ['image/jpeg','image/png','image/webp','image/gif'];
  if (!allowed.includes(file.type)) { toast('Недопустимый формат. Используйте JPG, PNG или WebP', 'error'); return; }
  if (file.size > 5*1024*1024) { toast(`Файл слишком большой. Максимум 5 МБ`, 'error'); return; }
  const form = new FormData();
  form.append('file', file);
  try {
    const res = await fetch(API + '/upload/event', { method:'POST', headers:{'Authorization':'Bearer '+auth.token()}, body:form });
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || 'Ошибка загрузки');
    document.getElementById('ev-image').value = json.data.url;
    document.getElementById('ev-image-thumb').src = json.data.url;
    document.getElementById('ev-image-preview').style.display = '';
    toast('Изображение загружено', 'success');
  } catch(e) { toast(e.message, 'error'); }
}

function clearEventImage() {
  document.getElementById('ev-image').value = '';
  document.getElementById('ev-image-preview').style.display = 'none';
  document.getElementById('ev-image-thumb').src = '';
}

async function initOrg() {
  if (!org) return;
  renderOrgAvatar(org);
  document.getElementById('org-name').textContent  = org.full_name || '—';
  document.getElementById('org-email').textContent = org.email || '';
  document.getElementById('p-name').value    = org.full_name || '';
  document.getElementById('p-email').value   = org.email    || '';
  document.getElementById('p-address').value = org.address  || '';
  document.getElementById('p-inn').value     = org.inn      || '';
  document.getElementById('p-ogrn').value    = org.ogrn     || '';
  document.getElementById('p-kpp').value     = org.kpp      || '';
  document.getElementById('p-phone').value       = org.phone       || '';
  document.getElementById('p-website').value     = org.website     || '';
  document.getElementById('p-vk').value          = org.vk          || '';
  document.getElementById('p-telegram').value    = org.telegram    || '';
  document.getElementById('p-description').value = org.description || '';
  try {
    const [cats, venues] = await Promise.all([get('/categories'), get('/venues')]);
    categories = cats;
    const catSel = document.getElementById('ev-cat');
    cats.forEach(c => { const o = document.createElement('option'); o.value = c.id; o.textContent = c.name; catSel.appendChild(o); });
    const venSel = document.getElementById('ev-venue');
    venues.forEach(v => { const o = document.createElement('option'); o.value = v.venue_id; o.textContent = v.name; venSel.appendChild(o); });
  } catch(e) {}
}

async function loadEvents() {
  const list = document.getElementById('events-list');
  try {
    const events = await get('/events?organization_id=' + org.organization_id);
    if (!events.length) { list.innerHTML = '<div class="empty"><div class="empty-icon">🎭</div>У вас пока нет событий</div>'; return; }
    _eventsCache = Object.fromEntries(events.map(e => [e.event_id, e]));
    list.innerHTML = events.map(e => `
      <div class="event-row">
        <div class="event-row-thumb">🎭</div>
        <div class="event-row-info">
          <div class="event-row-title">${escHtml(e.title)}</div>
          <div class="event-row-meta">
            ${fmtDate(e.start_datetime)} · ${fmtPrice(e.price)}
            <span class="badge badge-${e.status_name === 'Активно' ? 'green' : e.status_name === 'Черновик' ? 'blue' : 'gray'}" style="margin-left:8px;font-size:.7rem">${escHtml(e.status_name || '—')}</span>
          </div>
        </div>
        <div class="event-row-actions">
          <button class="btn btn-secondary btn-sm" onclick="editEvent(${e.event_id})">✏️ Изменить</button>
          <button class="btn btn-secondary btn-sm" title="Копировать событие" onclick="copyEvent(${e.event_id})">⧉</button>
          <button class="btn btn-sm" style="background:rgba(200,80,42,.1);color:var(--accent)" onclick="deleteEvent(${e.event_id})">🗑️</button>
        </div>
      </div>
    `).join('');
  } catch(e) { list.innerHTML = '<div class="empty"><div class="empty-icon">⚠️</div>' + e.message + '</div>'; }
}

async function editEvent(eventId) {
  let e = _eventsCache[eventId];
  if (!e) return;
  // Open modal immediately with cached data so UX is snappy
  editingId = eventId;
  document.getElementById('modal-title').textContent = 'Редактировать событие';
  document.getElementById('ev-title').value = e.title || '';
  document.getElementById('ev-desc').value  = e.description || '';
  document.getElementById('ev-price').value    = e.price || 0;
  document.getElementById('ev-age').value      = e.age_restriction || '';
  document.getElementById('ev-capacity').value = e.capacity || '';
  document.getElementById('ev-venue').value = e.venue_id || '';
  document.getElementById('ev-cat').value   = e.category_id || '';
  document.getElementById('ev-image').value = e.image || '';
  if (e.image) { document.getElementById('ev-image-thumb').src = e.image; document.getElementById('ev-image-preview').style.display = ''; }
  else clearEventImage();
  evGallery = Array.isArray(e.gallery) ? [...e.gallery] : [];
  renderGalleryGrid('ev-gallery-grid', evGallery, 'removeEvGallery', 'promoteEvGallery');
  if (e.start_datetime) document.getElementById('ev-start').value = e.start_datetime.replace(' ','T').slice(0,16);
  if (e.end_datetime)   document.getElementById('ev-end').value   = e.end_datetime.replace(' ','T').slice(0,16);
  const draftBtn = document.getElementById('draft-btn');
  if (draftBtn) draftBtn.style.display = e.status_id == 5 ? '' : '';
  openModal('modal-create');
  // Fetch fresh data in background to ensure gallery is always current
  try {
    const fresh = await get('/events/' + eventId);
    _eventsCache[eventId] = fresh;
    if (editingId === eventId) {
      evGallery = Array.isArray(fresh.gallery) ? [...fresh.gallery] : [];
      renderGalleryGrid('ev-gallery-grid', evGallery, 'removeEvGallery', 'promoteEvGallery');
      syncGalleryCover();
    }
  } catch(err) {}
}

function resetForm() {
  editingId = null;
  document.getElementById('modal-title').textContent = 'Новое событие';
  ['ev-title','ev-desc','ev-start','ev-end','ev-price','ev-capacity','ev-age','ev-venue','ev-cat','ev-image'].forEach(id => document.getElementById(id).value = '');
  ['ev-title','ev-start','ev-end','ev-price','ev-age'].forEach(id => setOrgField(id, '', ''));
  clearEventImage();
  evGallery = [];
  renderGalleryGrid('ev-gallery-grid', evGallery, 'removeEvGallery', 'promoteEvGallery');
}

document.getElementById('modal-create').addEventListener('click', e => { if(e.target.id==='modal-create') { resetForm(); closeModal('modal-create'); } });

function setOrgField(id, state, hint) {
  const wrap = document.getElementById('wrap-' + id);
  const el   = document.getElementById('hint-' + id);
  if (wrap) { wrap.classList.remove('field-error','field-success'); if (state) wrap.classList.add('field-' + state); }
  if (el)   { el.textContent = hint || ''; el.className = 'field-hint' + (state === 'success' ? ' ok' : ''); }
}
function orgShake(ids) {
  ids.forEach(id => {
    const w = document.getElementById('wrap-' + id);
    if (w) { w.classList.add('shake'); setTimeout(() => w.classList.remove('shake'), 400); }
  });
}

function vOrgName(v)     { if (!v) return 'Введите название'; if (v.length < 2) return 'Минимум 2 символа'; if (v.length > 200) return 'Максимум 200 символов'; return null; }
function vOrgEmail(v)    { if (!v) return null; if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return 'Некорректный email'; return null; }
function vOrgAddress(v)  { if (!v) return null; if (v.length > 300) return 'Слишком длинный адрес'; return null; }
function vOrgInn(v)      { if (!v) return null; if (!/^\d{10}$|^\d{12}$/.test(v)) return 'ИНН: 10 или 12 цифр'; return null; }
function vOrgOgrn(v)     { if (!v) return null; if (!/^\d{13}$|^\d{15}$/.test(v)) return 'ОГРН: 13 цифр (юрлицо) или 15 (ИП)'; return null; }
function vOrgKpp(v)      { if (!v) return null; if (!/^\d{9}$/.test(v)) return 'КПП: ровно 9 цифр'; return null; }
function vOrgPhone(v)    { if (!v) return null; const d = v.replace(/\D/g,''); if (d.length < 10 || d.length > 12) return 'Введите корректный номер'; return null; }
function vOrgWebsite(v)  { if (!v) return null; try { new URL(v); return null; } catch(e) { return 'Введите корректный URL (начиная с https://)'; } }
function vOrgVk(v)       { if (!v) return null; if (v.length > 100) return 'Слишком длинная ссылка'; return null; }
function vOrgTelegram(v) { if (!v) return null; if (v.length > 100) return 'Слишком длинная ссылка'; return null; }
function vOrgDesc(v)     { if (!v) return null; if (v.length > 2000) return 'Максимум 2000 символов'; return null; }
function vEvTitle(v)    { if (!v) return 'Введите название события'; if (v.length < 3) return 'Минимум 3 символа'; if (v.length > 200) return 'Максимум 200 символов'; return null; }
function vEvStart(v)    { if (!v) return 'Укажите дату начала'; return null; }
function vEvEnd(v)      { if (!v) return 'Укажите дату окончания'; const s = document.getElementById('ev-start').value; if (s && v <= s) return 'Конец должен быть позже начала'; return null; }
function vEvPrice(v)    { if (v === '') return null; const n = Number(v); if (isNaN(n) || n < 0) return 'Цена не может быть отрицательной'; return null; }
function vEvAge(v)      { if (v === '') return null; const n = parseInt(v,10); if (isNaN(n) || n < 0) return 'Введите корректный возраст'; if (n > 21) return 'Максимальный возраст ограничения: 21+'; return null; }

function blurOrg(id) {
  const val = document.getElementById(id)?.value?.trim() ?? '';
  const map = { 'p-name':vOrgName,'p-email':vOrgEmail,'p-address':vOrgAddress,'p-inn':vOrgInn,'p-ogrn':vOrgOgrn,'p-kpp':vOrgKpp,'p-phone':vOrgPhone,'p-website':vOrgWebsite,'p-vk':vOrgVk,'p-telegram':vOrgTelegram,'p-description':vOrgDesc };
  const fn = map[id]; if (!fn) return;
  const err = fn(val);
  if (err) setOrgField(id,'error',err); else if (val) setOrgField(id,'success',''); else setOrgField(id,'','');
}
function blurEv(id) {
  const val = document.getElementById(id)?.value ?? '';
  const map = { 'ev-title':()=>vEvTitle(val.trim()),'ev-start':()=>vEvStart(val),'ev-end':()=>vEvEnd(val),'ev-price':()=>vEvPrice(val.trim()),'ev-age':()=>vEvAge(val.trim()) };
  const fn = map[id]; if (!fn) return;
  const err = fn();
  if (err) setOrgField(id,'error',err); else if (val.trim()) setOrgField(id,'success',''); else setOrgField(id,'','');
}

async function saveEvent(asDraft = false) {
  const titleVal = document.getElementById('ev-title').value.trim();
  const startVal = document.getElementById('ev-start').value;
  const endVal   = document.getElementById('ev-end').value;
  const priceVal = document.getElementById('ev-price').value.trim();
  const ageVal   = document.getElementById('ev-age').value.trim();
  if (!asDraft) {
    const checks = { 'ev-title':vEvTitle(titleVal),'ev-start':vEvStart(startVal),'ev-end':vEvEnd(endVal),'ev-price':vEvPrice(priceVal),'ev-age':vEvAge(ageVal) };
    const errs = Object.entries(checks).filter(([,e]) => e);
    errs.forEach(([id,err]) => setOrgField(id,'error',err));
    if (errs.length) { orgShake(errs.map(([id]) => id)); document.getElementById(errs[0][0])?.focus(); return; }
  } else {
    if (!titleVal) { setOrgField('ev-title','error','Введите название'); orgShake(['ev-title']); return; }
  }

  const capacityVal = document.getElementById('ev-capacity').value.trim();
  const data = {
    title: titleVal, description: document.getElementById('ev-desc').value.trim(),
    start_datetime: startVal || null, end_datetime: endVal || null,
    price: priceVal !== '' ? Number(priceVal) : 0,
    capacity: capacityVal !== '' ? parseInt(capacityVal, 10) : null,
    age_restriction: ageVal !== '' ? parseInt(ageVal,10) : null,
    venue_id: document.getElementById('ev-venue').value || null,
    category_id: document.getElementById('ev-cat').value || null,
    image: document.getElementById('ev-image').value.trim() || (evGallery[0] || null),
    gallery: evGallery,
    ...(asDraft ? { as_draft: true } : {}),
  };
  const btn = asDraft ? document.getElementById('draft-btn') : document.querySelector('#modal-create .btn-primary');
  if (btn) { btn.disabled = true; btn.textContent = '⏳ Сохранение…'; }
  try {
    if (editingId) { await put('/events/' + editingId, data); toast(asDraft ? 'Черновик сохранён' : 'Событие обновлено', 'success'); }
    else           { await post('/events', data); toast(asDraft ? 'Черновик сохранён' : 'Событие создано!', 'success'); }
    closeModal('modal-create'); resetForm(); loadEvents();
  } catch(e) { toast(e.message, 'error'); }
  finally { if (btn) { btn.disabled = false; btn.textContent = asDraft ? '💾 Черновик' : 'Сохранить'; } }
}

function copyEvent(eventId) {
  const e = _eventsCache[eventId];
  if (!e) return;
  editingId = null;
  document.getElementById('modal-title').textContent = 'Копия: ' + (e.title || '');
  document.getElementById('ev-title').value    = (e.title || '') + ' (копия)';
  document.getElementById('ev-desc').value     = e.description || '';
  document.getElementById('ev-price').value    = e.price || 0;
  document.getElementById('ev-capacity').value = e.capacity || '';
  document.getElementById('ev-age').value      = e.age_restriction || '';
  document.getElementById('ev-venue').value    = e.venue_id || '';
  document.getElementById('ev-cat').value      = e.category_id || '';
  document.getElementById('ev-start').value    = '';
  document.getElementById('ev-end').value      = '';
  document.getElementById('ev-image').value    = e.image || '';
  if (e.image) { document.getElementById('ev-image-thumb').src = e.image; document.getElementById('ev-image-preview').style.display = ''; }
  else clearEventImage();
  evGallery = Array.isArray(e.gallery) ? [...e.gallery] : [];
  renderGalleryGrid('ev-gallery-grid', evGallery, 'removeEvGallery', 'promoteEvGallery');
  openModal('modal-create');
  toast('Заполните дату и сохраните копию', 'info');
}

async function deleteEvent(id) {
  if (!confirm('Удалить это событие?')) return;
  try { await del('/events/' + id); toast('Удалено', 'success'); loadEvents(); }
  catch(e) { toast(e.message, 'error'); }
}

async function saveProfile() {
  const nameVal    = document.getElementById('p-name').value.trim();
  const emailVal   = document.getElementById('p-email').value.trim();
  const addressVal = document.getElementById('p-address').value.trim();
  const innVal     = document.getElementById('p-inn').value.trim();
  const ogrnVal    = document.getElementById('p-ogrn').value.trim();
  const kppVal     = document.getElementById('p-kpp').value.trim();
  const phoneVal   = document.getElementById('p-phone').value.trim();
  const websiteVal     = document.getElementById('p-website').value.trim();
  const vkVal          = document.getElementById('p-vk').value.trim();
  const telegramVal    = document.getElementById('p-telegram').value.trim();
  const descriptionVal = document.getElementById('p-description').value.trim();
  const checks = { 'p-name':vOrgName(nameVal),'p-email':vOrgEmail(emailVal),'p-address':vOrgAddress(addressVal),'p-inn':vOrgInn(innVal),'p-ogrn':vOrgOgrn(ogrnVal),'p-kpp':vOrgKpp(kppVal),'p-phone':vOrgPhone(phoneVal),'p-website':vOrgWebsite(websiteVal),'p-vk':vOrgVk(vkVal),'p-telegram':vOrgTelegram(telegramVal),'p-description':vOrgDesc(descriptionVal) };
  const errs = Object.entries(checks).filter(([,e]) => e);
  errs.forEach(([id,err]) => setOrgField(id,'error',err));
  if (errs.length) { orgShake(errs.map(([id]) => id)); document.getElementById(errs[0][0])?.focus(); return; }
  Object.keys(checks).forEach(id => { const val = document.getElementById(id).value.trim(); if (val) setOrgField(id,'success',''); });
  const btn = document.getElementById('save-org-btn');
  btn.disabled = true;
  btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:btnSpin .7s linear infinite;display:inline-block;vertical-align:middle"><circle cx="12" cy="12" r="10" stroke-opacity=".25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg> Сохранение…';
  try {
    const updated = await put('/auth/me', { full_name:nameVal,email:emailVal,address:addressVal,inn:innVal,ogrn:ogrnVal||null,kpp:kppVal||null,phone:phoneVal||null,website:websiteVal||null,vk:vkVal||null,telegram:telegramVal||null,description:descriptionVal||null });
    store.set('org', updated);
    renderOrgAvatar(updated);
    document.getElementById('org-name').textContent  = updated.full_name || '—';
    document.getElementById('org-email').textContent = updated.email || '';
    toast('Профиль сохранён', 'success');
  } catch(e) { toast(e.message, 'error'); }
  finally { btn.disabled = false; btn.textContent = 'Сохранить'; }
}

function showSection(name) {
  document.querySelectorAll('.cabinet-section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.cabinet-nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('section-' + name).classList.add('active');
  document.getElementById('nav-' + name)?.classList.add('active');
  if (name === 'analytics') loadAnalytics();
  if (name === 'venues')    loadVenuesCabinet();
  if (name === 'buyers')    loadBuyers();
  if (name === 'reviews')   loadReviews();
  if (name === 'promo')     loadPromos();
}

// ── GALLERY ──────────────────────────────────────────────────────────────

let evGallery = [];

function renderGalleryGrid(containerId, galleryArr, removeFn, moveFn) {
  const grid = document.getElementById(containerId);
  if (!grid) return;
  if (!galleryArr.length) { grid.innerHTML = ''; return; }
  grid.innerHTML = galleryArr.map((url, i) => `
    <div class="gallery-thumb">
      <img src="${escHtml(url)}" alt="" onclick="openLightbox(${JSON.stringify(galleryArr)},${i})">
      ${i === 0 ? '<div class="gallery-cover-badge">Обложка</div>' : ''}
      <button class="gallery-btn gallery-btn-remove" onclick="${removeFn}(${i})" title="Удалить">✕</button>
      ${i > 0 ? `<button class="gallery-btn gallery-btn-left" onclick="${moveFn}(${i})" title="Сделать обложкой">★</button>` : ''}
    </div>
  `).join('');
}

// uploadGalleryImages is defined later in the venues section (unified with prefix param)

function syncGalleryCover() {
  if (evGallery.length && !document.getElementById('ev-image').value) {
    document.getElementById('ev-image').value = evGallery[0];
    document.getElementById('ev-image-thumb').src = evGallery[0];
    document.getElementById('ev-image-preview').style.display = '';
  }
}

function removeEvGallery(idx) {
  const wasFirst = idx === 0;
  evGallery.splice(idx, 1);
  renderGalleryGrid('ev-gallery-grid', evGallery, 'removeEvGallery', 'promoteEvGallery');
  if (wasFirst) {
    if (evGallery.length) {
      document.getElementById('ev-image').value = evGallery[0];
      document.getElementById('ev-image-thumb').src = evGallery[0];
    } else {
      clearEventImage();
    }
  }
}

function promoteEvGallery(idx) {
  if (idx <= 0 || idx >= evGallery.length) return;
  const [item] = evGallery.splice(idx, 1);
  evGallery.unshift(item);
  renderGalleryGrid('ev-gallery-grid', evGallery, 'removeEvGallery', 'promoteEvGallery');
  document.getElementById('ev-image').value = evGallery[0];
  document.getElementById('ev-image-thumb').src = evGallery[0];
  document.getElementById('ev-image-preview').style.display = '';
}

// ── LIGHTBOX ──────────────────────────────────────────────────────────────

let _lbImages = [], _lbIdx = 0;

function openLightbox(images, startIdx) {
  _lbImages = images; _lbIdx = startIdx;
  const ov = document.createElement('div');
  ov.className = 'lightbox-overlay';
  ov.id = 'lightbox-overlay';
  ov.onclick = e => { if (e.target === ov) closeLightbox(); };
  ov.innerHTML = `
    <button class="lightbox-close" onclick="closeLightbox()">✕</button>
    <button class="lightbox-nav lightbox-prev" onclick="lbNav(-1)">‹</button>
    <img class="lightbox-img" id="lb-img" src="${escHtml(images[startIdx])}">
    <button class="lightbox-nav lightbox-next" onclick="lbNav(1)">›</button>
  `;
  document.body.appendChild(ov);
  document.addEventListener('keydown', lbKeydown);
}

function closeLightbox() {
  const ov = document.getElementById('lightbox-overlay');
  if (ov) ov.remove();
  document.removeEventListener('keydown', lbKeydown);
}

function lbNav(dir) {
  _lbIdx = (_lbIdx + dir + _lbImages.length) % _lbImages.length;
  const img = document.getElementById('lb-img');
  if (img) img.src = escHtml(_lbImages[_lbIdx]);
}

function lbKeydown(e) {
  if (e.key === 'ArrowLeft')  lbNav(-1);
  if (e.key === 'ArrowRight') lbNav(1);
  if (e.key === 'Escape')     closeLightbox();
}

// ── VENUES CABINET ────────────────────────────────────────────────────────

let _venuesCabinetLoaded = false;
// ── VENUES CABINET ────────────────────────────────────────────────────────

let _venuesCacheById = {};
let _editingVenueId  = null;
let _vnGallery       = [];
let _vnMap           = null;
let _vnMarker        = null;
const MAGADAN        = [59.5683, 150.8084];
const YMAPS_KEY      = 'da23ed3a-9052-4d2f-8ce7-e597d90a7afc';

async function loadVenuesCabinet() {
  if (_venuesCabinetLoaded) return;
  _venuesCabinetLoaded = true;
  await refreshVenuesList();
}

async function refreshVenuesList() {
  const list = document.getElementById('venues-list-cabinet');
  list.innerHTML = '<div class="loader"><div class="spinner"></div></div>';
  try {
    const venues = await get('/venues?organization_id=' + org.organization_id);
    venues.forEach(v => { _venuesCacheById[v.venue_id] = v; });
    if (!venues.length) {
      list.innerHTML = '<div class="empty"><div class="empty-icon">🏛</div>У вас пока нет площадок. Создайте первую!</div>';
      return;
    }
    list.innerHTML = venues.map(v => {
      const g = Array.isArray(v.gallery) ? v.gallery : [];
      const coordLabel = (v.latitude && v.longitude)
        ? `<span style="color:var(--accent2)">📍 ${(+v.latitude).toFixed(4)}, ${(+v.longitude).toFixed(4)}</span>`
        : `<span style="color:var(--muted)">📍 Координаты не указаны</span>`;
      return `
        <div class="venue-row" id="vrow-${v.venue_id}">
          <div class="venue-row-thumb">
            ${v.image ? `<img src="${escHtml(v.image)}" alt="">` : '🏛'}
          </div>
          <div class="venue-row-info">
            <div class="venue-row-title">${escHtml(v.name)}</div>
            <div class="venue-row-meta">
              ${v.address ? escHtml(v.address) + ' · ' : ''}${coordLabel}
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
              <button class="btn btn-secondary btn-sm" onclick="openVenueModal(${v.venue_id})">✏️ Редактировать</button>
              <button class="btn btn-sm" style="background:rgba(200,80,42,.1);color:var(--accent)" onclick="deleteVenue(${v.venue_id})">🗑️ Удалить</button>
              <button class="btn btn-sm" style="background:rgba(42,110,90,.09);color:var(--accent2)" onclick="toggleVenueGallery(${v.venue_id})">
                🖼 Галерея <span id="vg-count-${v.venue_id}">${g.length}</span>
              </button>
            </div>
            <div id="vpanel-${v.venue_id}" style="display:none">
              <div class="venue-gallery-panel">
                <div class="gallery-grid" id="vgallery-${v.venue_id}"></div>
                <label style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:.82rem;font-weight:600;color:var(--muted);margin-top:6px">
                  🖼 Добавить фото
                  <input type="file" accept="image/*" multiple style="display:none" onchange="directVenueGalleryUpload(this,${v.venue_id})">
                </label>
              </div>
            </div>
          </div>
        </div>
      `;
    }).join('');
    venues.forEach(v => renderDirectVenueGallery(v.venue_id));
  } catch(e) {
    list.innerHTML = `<div class="empty"><div class="empty-icon">⚠️</div>${escHtml(e.message)}</div>`;
  }
}

function toggleVenueGallery(venueId) {
  const panel = document.getElementById('vpanel-' + venueId);
  panel.style.display = (panel.style.display === 'none' || panel.style.display === '') ? '' : 'none';
  if (panel.style.display === '') renderDirectVenueGallery(venueId);
}

function renderDirectVenueGallery(venueId) {
  const v = _venuesCacheById[venueId];
  if (!v) return;
  const g = Array.isArray(v.gallery) ? v.gallery : [];
  const grid = document.getElementById('vgallery-' + venueId);
  if (!grid) return;
  grid.innerHTML = g.map((url, i) => `
    <div class="gallery-thumb">
      <img src="${escHtml(url)}" alt="" onclick="openLightbox(${JSON.stringify(g)},${i})">
      ${i === 0 ? '<div class="gallery-cover-badge">Обложка</div>' : ''}
      <button class="gallery-btn gallery-btn-remove" onclick="directRemoveVenuePhoto(${venueId},${i})">✕</button>
      ${i > 0 ? `<button class="gallery-btn gallery-btn-left" onclick="directPromoteVenuePhoto(${venueId},${i})">★</button>` : ''}
    </div>
  `).join('') || '<div style="font-size:.8rem;color:var(--muted);margin-bottom:8px">Фото пока нет</div>';
}

async function directVenueGalleryUpload(input, venueId) {
  const files = Array.from(input.files);
  input.value = '';
  const v = _venuesCacheById[venueId];
  if (!v) return;
  const g = Array.isArray(v.gallery) ? [...v.gallery] : [];
  const remaining = 20 - g.length;
  if (remaining <= 0) { toast('Максимум 20 фото', 'error'); return; }
  const allowed = ['image/jpeg','image/png','image/webp','image/gif'];
  for (const file of files.slice(0, remaining)) {
    if (!allowed.includes(file.type)) { toast(`${file.name}: недопустимый формат`, 'error'); continue; }
    if (file.size > 5*1024*1024) { toast(`${file.name}: слишком большой`, 'error'); continue; }
    const form = new FormData();
    form.append('file', file);
    try {
      const res = await fetch(API + '/upload/venue', { method:'POST', headers:{'Authorization':'Bearer '+auth.token()}, body:form });
      const json = await res.json();
      if (!res.ok) throw new Error(json.error || 'Ошибка');
      g.push(json.data.url);
    } catch(e) { toast(e.message, 'error'); }
  }
  v.gallery = g;
  v.image   = g[0] || v.image;
  await put('/venues/' + venueId, { gallery: g, image: v.image });
  renderDirectVenueGallery(venueId);
  const cnt = document.getElementById('vg-count-' + venueId);
  if (cnt) cnt.textContent = g.length;
  toast('Галерея обновлена', 'success');
}

async function directRemoveVenuePhoto(venueId, idx) {
  const v = _venuesCacheById[venueId];
  if (!v) return;
  const g = Array.isArray(v.gallery) ? [...v.gallery] : [];
  g.splice(idx, 1);
  v.gallery = g;
  v.image   = g[0] || null;
  await put('/venues/' + venueId, { gallery: g, image: v.image });
  renderDirectVenueGallery(venueId);
  const cnt = document.getElementById('vg-count-' + venueId);
  if (cnt) cnt.textContent = g.length;
}

async function directPromoteVenuePhoto(venueId, idx) {
  const v = _venuesCacheById[venueId];
  if (!v || idx <= 0) return;
  const g = Array.isArray(v.gallery) ? [...v.gallery] : [];
  const [item] = g.splice(idx, 1);
  g.unshift(item);
  v.gallery = g;
  v.image   = g[0];
  await put('/venues/' + venueId, { gallery: g, image: v.image });
  renderDirectVenueGallery(venueId);
}

async function deleteVenue(venueId) {
  if (!confirm('Удалить площадку? Это действие нельзя отменить.')) return;
  try {
    await del('/venues/' + venueId);
    delete _venuesCacheById[venueId];
    toast('Площадка удалена', 'success');
    await refreshVenuesList();
  } catch(e) { toast(e.message, 'error'); }
}

// ── VENUE MODAL WITH MAP ──────────────────────────────────────────────────

function openVenueModal(venueId) {
  _editingVenueId = venueId || null;
  _vnGallery = [];
  const modal = document.getElementById('modal-venue');
  document.getElementById('venue-modal-title').textContent = venueId ? 'Редактировать площадку' : 'Новая площадка';

  // Fill category options (load on demand if initOrg hasn't run yet)
  const catSel = document.getElementById('vn-cat');
  if (catSel.options.length <= 1) {
    if (categories.length) {
      categories.forEach(c => {
        const o = document.createElement('option');
        o.value = c.id; o.textContent = c.name; catSel.appendChild(o);
      });
    } else {
      get('/categories').then(cats => {
        categories = cats;
        cats.forEach(c => {
          const o = document.createElement('option');
          o.value = c.id; o.textContent = c.name; catSel.appendChild(o);
        });
        if (_editingVenueId) catSel.value = (_venuesCacheById[_editingVenueId]?.category_id || '');
      }).catch(e => {});
    }
  }

  if (venueId) {
    const v = _venuesCacheById[venueId];
    if (v) {
      document.getElementById('vn-name').value    = v.name    || '';
      document.getElementById('vn-desc').value    = v.description || '';
      document.getElementById('vn-address').value = v.address || '';
      document.getElementById('vn-age').value     = v.age     || '';
      document.getElementById('vn-cat').value     = v.category_id || '';
      document.getElementById('vn-image').value   = v.image   || '';
      document.getElementById('vn-lat').value     = v.latitude || '';
      document.getElementById('vn-lng').value     = v.longitude || '';
      _vnGallery = Array.isArray(v.gallery) ? [...v.gallery] : [];
      if (v.image) {
        document.getElementById('vn-image-thumb').src = v.image;
        document.getElementById('vn-image-preview').style.display = '';
      } else {
        clearVenueModalImage();
      }
      updateVnCoordsDisplay(v.latitude, v.longitude);
    }
  } else {
    ['vn-name','vn-desc','vn-address','vn-age','vn-image','vn-lat','vn-lng'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    document.getElementById('vn-cat').value = '';
    clearVenueModalImage();
    updateVnCoordsDisplay(null, null);
  }
  renderGalleryGrid('vn-gallery-grid', _vnGallery, 'removeVnGallery', 'promoteVnGallery');
  openModal('modal-venue');
  initVenueMap();
}

function closeVenueModal() {
  closeModal('modal-venue');
  _vnMap = null;
  _vnMarker = null;
  document.getElementById('vn-map-picker').innerHTML = '<span>🗺 Карта загружается...</span>';
}

function initVenueMap() {
  loadYmaps(function() {
    const lat = parseFloat(document.getElementById('vn-lat').value) || MAGADAN[0];
    const lng = parseFloat(document.getElementById('vn-lng').value) || MAGADAN[1];
    const hasPin = !!(document.getElementById('vn-lat').value);

    document.getElementById('vn-map-picker').innerHTML = '';
    _vnMap = new ymaps.Map('vn-map-picker', {
      center: [lat, lng],
      zoom: hasPin ? 15 : 12,
      controls: ['zoomControl'],
    }, { suppressMapOpenBlock: true });

    if (hasPin) {
      _vnMarker = new ymaps.Placemark([lat, lng], {}, { draggable: true, iconColor: '#2a6e5a' });
      _vnMap.geoObjects.add(_vnMarker);
      _vnMarker.events.add('dragend', function() {
        const c = _vnMarker.geometry.getCoordinates();
        document.getElementById('vn-lat').value = c[0].toFixed(8);
        document.getElementById('vn-lng').value = c[1].toFixed(8);
        updateVnCoordsDisplay(c[0], c[1]);
      });
    }

    _vnMap.events.add('click', function(e) {
      const c = e.get('coords');
      document.getElementById('vn-lat').value = c[0].toFixed(8);
      document.getElementById('vn-lng').value = c[1].toFixed(8);
      updateVnCoordsDisplay(c[0], c[1]);
      if (_vnMarker) {
        _vnMarker.geometry.setCoordinates(c);
      } else {
        _vnMarker = new ymaps.Placemark(c, {}, { draggable: true, iconColor: '#2a6e5a' });
        _vnMap.geoObjects.add(_vnMarker);
        _vnMarker.events.add('dragend', function() {
          const nc = _vnMarker.geometry.getCoordinates();
          document.getElementById('vn-lat').value = nc[0].toFixed(8);
          document.getElementById('vn-lng').value = nc[1].toFixed(8);
          updateVnCoordsDisplay(nc[0], nc[1]);
        });
      }
    });
  });
}

let _ymapsCallbacks = null;
function loadYmaps(cb) {
  if (typeof ymaps !== 'undefined') { ymaps.ready(cb); return; }
  if (_ymapsCallbacks) { _ymapsCallbacks.push(cb); return; }
  _ymapsCallbacks = [cb];
  const s = document.createElement('script');
  s.src = `https://api-maps.yandex.ru/2.1/?apikey=${YMAPS_KEY}&lang=ru_RU`;
  s.onload = function() {
    ymaps.ready(function() {
      const cbs = _ymapsCallbacks;
      _ymapsCallbacks = null;
      cbs.forEach(function(fn) { try { fn(); } catch(ex) { console.error(ex); } });
    });
  };
  s.onerror = function() {
    _ymapsCallbacks = null;
    toast('Не удалось загрузить Яндекс.Карты', 'error');
  };
  document.head.appendChild(s);
}

function geocodeVenueAddress() {
  const addr = document.getElementById('vn-address').value.trim();
  if (!addr) { toast('Введите адрес', 'error'); return; }
  const query = /магадан/i.test(addr) ? addr : 'Магадан, ' + addr;
  loadYmaps(function() {
    ymaps.geocode(query, { results: 1 }).then(function(res) {
      const obj = res.geoObjects.get(0);
      if (!obj) { toast('Адрес не найден — попробуйте написать точнее', 'error'); return; }
      const c = obj.geometry.getCoordinates();
      document.getElementById('vn-lat').value = c[0].toFixed(8);
      document.getElementById('vn-lng').value = c[1].toFixed(8);
      updateVnCoordsDisplay(c[0], c[1]);
      if (_vnMap) {
        _vnMap.setCenter(c, 16);
        if (_vnMarker) {
          _vnMarker.geometry.setCoordinates(c);
        } else {
          _vnMarker = new ymaps.Placemark(c, {}, { draggable: true, iconColor: '#2a6e5a' });
          _vnMap.geoObjects.add(_vnMarker);
          _vnMarker.events.add('dragend', function() {
            const nc = _vnMarker.geometry.getCoordinates();
            document.getElementById('vn-lat').value = nc[0].toFixed(8);
            document.getElementById('vn-lng').value = nc[1].toFixed(8);
            updateVnCoordsDisplay(nc[0], nc[1]);
          });
        }
      }
      toast('Адрес найден на карте', 'success');
    }, function(e) {
      console.error('Geocoding error:', e);
      toast('Ошибка геокодирования: ' + (e && e.message ? e.message : 'нет ответа от сервера'), 'error');
    });
  });
}

function updateVnCoordsDisplay(lat, lng) {
  const el = document.getElementById('vn-coords-display');
  if (!el) return;
  el.textContent = (lat && lng)
    ? `${(+lat).toFixed(5)}, ${(+lng).toFixed(5)} — перетащите маркер для уточнения`
    : 'Кликните по карте или введите адрес';
}

async function uploadVenueModalImage(input) {
  const file = input.files[0]; if (!file) return;
  input.value = '';
  const allowed = ['image/jpeg','image/png','image/webp','image/gif'];
  if (!allowed.includes(file.type)) { toast('Недопустимый формат', 'error'); return; }
  if (file.size > 5*1024*1024) { toast('Файл слишком большой (макс. 5 МБ)', 'error'); return; }
  const form = new FormData();
  form.append('file', file);
  try {
    const res = await fetch(API + '/upload/venue', { method:'POST', headers:{'Authorization':'Bearer '+auth.token()}, body:form });
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || 'Ошибка загрузки');
    document.getElementById('vn-image').value = json.data.url;
    document.getElementById('vn-image-thumb').src = json.data.url;
    document.getElementById('vn-image-preview').style.display = '';
    toast('Фото загружено', 'success');
  } catch(e) { toast(e.message, 'error'); }
}

function clearVenueModalImage() {
  document.getElementById('vn-image').value = '';
  document.getElementById('vn-image-preview').style.display = 'none';
  document.getElementById('vn-image-thumb').src = '';
}

function removeVnGallery(idx) {
  _vnGallery.splice(idx, 1);
  renderGalleryGrid('vn-gallery-grid', _vnGallery, 'removeVnGallery', 'promoteVnGallery');
}

function promoteVnGallery(idx) {
  if (idx <= 0 || idx >= _vnGallery.length) return;
  const [item] = _vnGallery.splice(idx, 1);
  _vnGallery.unshift(item);
  renderGalleryGrid('vn-gallery-grid', _vnGallery, 'removeVnGallery', 'promoteVnGallery');
  if (!document.getElementById('vn-image').value) {
    document.getElementById('vn-image').value = _vnGallery[0];
    document.getElementById('vn-image-thumb').src = _vnGallery[0];
    document.getElementById('vn-image-preview').style.display = '';
  }
}

async function saveVenue() {
  const nameVal = document.getElementById('vn-name').value.trim();
  if (!nameVal) { setOrgField('vn-name', 'error', 'Введите название'); document.getElementById('vn-name').focus(); return; }

  const data = {
    name:         nameVal,
    description:  document.getElementById('vn-desc').value.trim() || null,
    address:      document.getElementById('vn-address').value.trim() || null,
    age:          document.getElementById('vn-age').value ? parseInt(document.getElementById('vn-age').value) : null,
    category_id:  document.getElementById('vn-cat').value  || null,
    image:        document.getElementById('vn-image').value.trim() || (_vnGallery[0] || null),
    gallery:      _vnGallery,
    latitude:     document.getElementById('vn-lat').value  ? parseFloat(document.getElementById('vn-lat').value)  : null,
    longitude:    document.getElementById('vn-lng').value  ? parseFloat(document.getElementById('vn-lng').value) : null,
  };

  const btn = document.querySelector('#modal-venue .btn-primary');
  if (btn) { btn.disabled = true; btn.textContent = 'Сохранение…'; }
  try {
    let result;
    if (_editingVenueId) {
      result = await put('/venues/' + _editingVenueId, data);
      toast('Площадка обновлена', 'success');
    } else {
      result = await post('/venues', data);
      toast('Площадка создана!', 'success');
    }
    _venuesCacheById[result.venue_id] = result;
    closeVenueModal();
    _venuesCabinetLoaded = false;
    await refreshVenuesList();
    // Refresh venue selector in event modal
    const venSel = document.getElementById('ev-venue');
    if (venSel) {
      const opt = venSel.querySelector(`option[value="${result.venue_id}"]`);
      if (opt) { opt.textContent = result.name; }
      else { const o = document.createElement('option'); o.value = result.venue_id; o.textContent = result.name; venSel.appendChild(o); }
    }
  } catch(e) { toast(e.message, 'error'); }
  finally { if (btn) { btn.disabled = false; btn.textContent = 'Сохранить площадку'; } }
}

// Unified gallery upload for both event (type='event') and venue modal (type='venue')
// prefix: 'ev' or 'vn'
async function uploadGalleryImages(input, type, prefix) {
  const files = Array.from(input.files);
  input.value = '';
  if (!files.length) return;
  const gallery = prefix === 'vn' ? _vnGallery : evGallery;
  const remaining = 20 - gallery.length;
  if (remaining <= 0) { toast('Максимум 20 фото в галерее', 'error'); return; }
  const toUpload = files.slice(0, remaining);
  const allowed = ['image/jpeg','image/png','image/webp','image/gif'];
  let uploaded = 0;
  for (const file of toUpload) {
    if (!allowed.includes(file.type)) { toast(`${file.name}: недопустимый формат`, 'error'); continue; }
    if (file.size > 5*1024*1024) { toast(`${file.name}: файл слишком большой (макс. 5 МБ)`, 'error'); continue; }
    const form = new FormData();
    form.append('file', file);
    try {
      const res = await fetch(API + '/upload/' + type, { method:'POST', headers:{'Authorization':'Bearer '+auth.token()}, body:form });
      const json = await res.json();
      if (!res.ok) throw new Error(json.error || 'Ошибка загрузки');
      gallery.push(json.data.url);
      uploaded++;
    } catch(e) { toast(e.message, 'error'); }
  }
  const gridId = prefix + '-gallery-grid';
  const removeF = prefix === 'vn' ? 'removeVnGallery' : 'removeEvGallery';
  const promoteF = prefix === 'vn' ? 'promoteVnGallery' : 'promoteEvGallery';
  renderGalleryGrid(gridId, gallery, removeF, promoteF);
  if (uploaded && prefix === 'ev') syncGalleryCover();
  if (uploaded && prefix === 'vn' && !document.getElementById('vn-image').value && gallery.length) {
    document.getElementById('vn-image').value = gallery[0];
    document.getElementById('vn-image-thumb').src = gallery[0];
    document.getElementById('vn-image-preview').style.display = '';
  }
}

// ── BUYERS ────────────────────────────────────────────────────────────────

let _buyersFilterFilled = false;
async function loadBuyers() {
  const wrap = document.getElementById('buyers-wrap');
  const filterSel = document.getElementById('buyers-event-filter');
  if (!_buyersFilterFilled) {
    _buyersFilterFilled = true;
    const events = Object.values(_eventsCache);
    if (events.length) {
      events.forEach(e => {
        const opt = document.createElement('option');
        opt.value = e.event_id;
        opt.textContent = e.title;
        filterSel.appendChild(opt);
      });
    } else {
      try {
        const evts = await get('/events?organization_id=' + org.organization_id);
        evts.forEach(e => {
          const opt = document.createElement('option');
          opt.value = e.event_id;
          opt.textContent = e.title;
          filterSel.appendChild(opt);
        });
      } catch(e) {}
    }
  }
  const eventId = filterSel.value;
  wrap.innerHTML = '<div class="loader"><div class="spinner"></div></div>';
  try {
    const url = '/analytics/org/buyers' + (eventId ? '?event_id=' + eventId : '');
    const buyers = await get(url);
    if (!buyers.length) { wrap.innerHTML = '<div class="empty"><div class="empty-icon">🎫</div>Покупателей пока нет</div>'; return; }
    wrap.innerHTML = `<div class="buyers-table-wrap">
      <table class="analytics-table">
        <thead><tr><th>Покупатель</th><th>Email</th><th>Телефон</th><th>Событие</th><th>Кол-во</th><th>Сумма</th><th>Дата оплаты</th></tr></thead>
        <tbody>${buyers.map(b => `
          <tr>
            <td style="font-weight:600">${escHtml(b.full_name||'—')}</td>
            <td style="color:var(--muted)">${escHtml(b.email||'—')}</td>
            <td style="color:var(--muted);white-space:nowrap">${escHtml(b.phone||'—')}</td>
            <td><a href="${(window.APP_BASE||'')}/event/${b.event_id}" style="color:var(--accent2)">${escHtml(b.event_title)}</a></td>
            <td style="text-align:center">${b.quantity}</td>
            <td style="font-weight:600;white-space:nowrap">${fmtPrice(b.price * b.quantity)}</td>
            <td style="color:var(--muted);white-space:nowrap">${fmtDate(b.paid_at)}</td>
          </tr>`).join('')}
        </tbody>
      </table>
    </div>`;
  } catch(e) {
    wrap.innerHTML = `<div class="empty"><div class="empty-icon">⚠️</div>${escHtml(e.message)}</div>`;
  }
}

let _reviewsLoaded = false;
async function loadReviews() {
  if (_reviewsLoaded) return;
  const wrap = document.getElementById('reviews-wrap');
  try {
    const reviews = await get('/analytics/org/reviews');
    _reviewsLoaded = true;
    if (!reviews.length) { wrap.innerHTML = '<div class="empty"><div class="empty-icon">⭐</div>Отзывов пока нет</div>'; return; }
    wrap.innerHTML = reviews.map(r => {
      const stars = Math.max(0, Math.min(5, +r.rating));
      const avatarHtml = r.user_avatar
        ? `<div class="review-user-avatar"><img src="${escHtml(r.user_avatar)}" alt=""></div>`
        : `<div class="review-user-avatar">${escHtml((r.user_name||'?')[0].toUpperCase())}</div>`;
      return `<div class="review-card">
        <div class="review-card-header">
          <div class="review-user">
            ${avatarHtml}
            <div>
              <div style="font-weight:600;font-size:.9rem">${escHtml(r.user_name||'Аноним')}</div>
              <div style="font-size:.75rem;color:var(--muted)">${fmtDate(r.created_at)}</div>
            </div>
          </div>
          <div class="review-stars">${'⭐'.repeat(stars)}${'☆'.repeat(5-stars)}</div>
        </div>
        ${r.text ? `<div style="font-size:.88rem;line-height:1.6;margin-bottom:8px">${escHtml(r.text)}</div>` : ''}
        <div style="font-size:.75rem;color:var(--muted)">Событие: <a href="${(window.APP_BASE||'')}/event/${r.event_id}" style="color:var(--accent2)">${escHtml(r.event_title)}</a></div>
      </div>`;
    }).join('');
  } catch(e) {
    wrap.innerHTML = `<div class="empty"><div class="empty-icon">⚠️</div>${escHtml(e.message)}</div>`;
  }
}

let analyticsLoaded = false;
async function loadAnalytics() {
  if (analyticsLoaded) return;
  const wrap = document.getElementById('analytics-wrap');
  try {
    const d = await get('/analytics/org');
    analyticsLoaded = true;
    const t = d.totals;
    const maxRev = d.by_day.length ? Math.max(...d.by_day.map(x => +x.revenue)) : 1;
    wrap.innerHTML = `
      <div class="stat-grid">
        <div class="stat-card"><div class="stat-label">Всего событий</div><div class="stat-value">${t.total_events}</div></div>
        <div class="stat-card"><div class="stat-label">Продано билетов</div><div class="stat-value">${t.total_tickets}</div><div class="stat-sub">${t.pending_tickets} ожидают оплаты</div></div>
        <div class="stat-card"><div class="stat-label">Выручка</div><div class="stat-value">${fmtPrice(t.total_revenue)}</div></div>
      </div>
      ${d.by_day.length ? `
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px 22px;margin-bottom:24px">
        <div style="font-weight:700;margin-bottom:16px">Выручка за 30 дней</div>
        <div class="bar-chart">
          ${d.by_day.map(row => {
            const h = maxRev > 0 ? Math.round((+row.revenue / maxRev) * 110) : 2;
            return `<div class="bar-col" title="${row.day}: ${fmtPrice(row.revenue)}">
              <div class="bar-fill" style="height:${h}px"></div>
              <div class="bar-label">${row.day.slice(5)}</div>
            </div>`;
          }).join('')}
        </div>
      </div>` : ''}
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px 22px">
        <div style="font-weight:700;margin-bottom:16px">Топ событий</div>
        ${d.by_event.length ? `
        <table class="analytics-table">
          <thead><tr><th>Событие</th><th>Дата</th><th>Билетов</th><th>Выручка</th></tr></thead>
          <tbody>
            ${d.by_event.map(e => `
              <tr>
                <td><a href="${(window.APP_BASE||'')}/event/${e.event_id}" style="color:inherit;font-weight:600">${escHtml(e.title)}</a></td>
                <td style="color:var(--muted)">${fmtDate(e.start_datetime)}</td>
                <td>${e.tickets_sold}</td>
                <td style="font-weight:600">${fmtPrice(e.revenue)}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>` : '<div style="color:var(--muted);font-size:.9rem">Продаж пока нет</div>'}
      </div>
    `;
  } catch(e) {
    wrap.innerHTML = `<div class="empty"><div class="empty-icon">⚠️</div>${e.message}</div>`;
  }
}

if (!showStatusGate(org)) {
  initOrg();
  loadEvents();
}

// ── Промокоды ──────────────────────────────────────────
function openPromoModal() {
  ['promo-code-inp','promo-value-inp','promo-uses-inp','promo-expires-inp'].forEach(id => {
    const el = document.getElementById(id); if (el) el.value = '';
  });
  document.getElementById('promo-type-inp').value = 'percent';
  openModal('modal-promo');
}

async function loadPromos() {
  const wrap = document.getElementById('promo-list');
  wrap.innerHTML = '<div class="loader"><div class="spinner"></div></div>';
  try {
    const promos = await get('/promo');
    if (!promos.length) {
      wrap.innerHTML = '<div style="color:var(--muted);padding:24px 0">Промокодов пока нет. Создайте первый!</div>';
      return;
    }
    wrap.innerHTML = `
      <div style="overflow-x:auto">
        <table class="analytics-table">
          <thead><tr>
            <th>Код</th><th>Скидка</th><th>Использований</th><th>Действует до</th><th>Статус</th><th></th>
          </tr></thead>
          <tbody>
            ${promos.map(p => `
              <tr>
                <td><strong>${escHtml(p.code)}</strong></td>
                <td>${p.discount_type === 'percent' ? p.discount_value + '%' : fmtPrice(p.discount_value)}</td>
                <td>${p.uses_count}${p.max_uses ? ' / ' + p.max_uses : ''}</td>
                <td>${p.expires_at ? new Date(p.expires_at).toLocaleDateString('ru-RU') : '∞'}</td>
                <td>
                  <span style="color:${p.is_active ? 'var(--accent2)' : 'var(--muted)'}">
                    ${p.is_active ? '● Активен' : '○ Отключён'}
                  </span>
                </td>
                <td style="display:flex;gap:8px;justify-content:flex-end">
                  <button class="btn btn-secondary" style="padding:5px 12px;font-size:.8rem" onclick="togglePromo(${p.id},this)">
                    ${p.is_active ? 'Откл.' : 'Вкл.'}
                  </button>
                  <button class="btn" style="padding:5px 12px;font-size:.8rem;background:rgba(239,68,68,.1);color:#ef4444;border:1px solid rgba(239,68,68,.2)" onclick="deletePromo(${p.id})">
                    Удалить
                  </button>
                </td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>`;
  } catch(e) {
    wrap.innerHTML = `<div class="empty"><div class="empty-icon">⚠️</div>${e.message}</div>`;
  }
}

async function savePromo() {
  const code  = document.getElementById('promo-code-inp').value.trim().toUpperCase();
  const type  = document.getElementById('promo-type-inp').value;
  const value = parseFloat(document.getElementById('promo-value-inp').value);
  const uses  = document.getElementById('promo-uses-inp').value;
  const exp   = document.getElementById('promo-expires-inp').value;

  if (!code) { toast('Введите код', 'error'); return; }
  if (!value || value < 1) { toast('Введите размер скидки', 'error'); return; }
  if (type === 'percent' && value > 100) { toast('Процент не может быть больше 100', 'error'); return; }

  try {
    await post('/promo', {
      code, discount_type: type, discount_value: value,
      max_uses: uses || null,
      expires_at: exp || null,
    });
    closeModal('modal-promo');
    toast('Промокод создан', 'success');
    loadPromos();
  } catch(e) { toast(e.message, 'error'); }
}

async function deletePromo(id) {
  if (!confirm('Удалить промокод?')) return;
  try {
    await del('/promo/' + id);
    toast('Удалено');
    loadPromos();
  } catch(e) { toast(e.message, 'error'); }
}

async function togglePromo(id, btn) {
  btn.disabled = true;
  try {
    const r = await api('PATCH', '/promo/' + id, {});
    toast(r.is_active ? 'Промокод включён' : 'Промокод отключён');
    loadPromos();
  } catch(e) { toast(e.message, 'error'); }
  finally { btn.disabled = false; }
}
</script>
@endsection
