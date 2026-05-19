@extends('layouts.app')

@section('title', 'Личный кабинет — АфишаКолыма')

@section('styles')
<style>
  .cabinet-layout { max-width:1100px; margin:0 auto; padding:40px 32px; display:grid; grid-template-columns:260px 1fr; gap:32px; }
  @media(max-width:800px){ .cabinet-layout{grid-template-columns:1fr;} }

  .user-card { background:var(--hero-bg); border:none; border-radius:var(--radius); padding:28px; text-align:center; margin-bottom:16px; position:relative; overflow:hidden; box-shadow:var(--shadow-lg); }
  .user-card::before { content:''; position:absolute; inset:0; background:radial-gradient(ellipse at 80% 20%, rgba(200,80,42,.4) 0%, transparent 60%), radial-gradient(ellipse at 10% 80%, rgba(42,110,90,.25) 0%, transparent 55%); }
  .user-card > * { position:relative; z-index:1; }
  .user-avatar { width:80px; height:80px; border-radius:50%; background:var(--accent); color:#fff; font-size:2rem; font-weight:700; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; border:3px solid rgba(255,255,255,.2); }
  .user-name { font-weight:700; font-size:1.1rem; margin-bottom:4px; color:#fff; }
  .user-phone { color:rgba(255,255,255,.5); font-size:0.85rem; }
  .user-role { margin-top:10px; }

  .cabinet-nav { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); overflow:hidden; }
  .cabinet-nav-item {
    display:flex; align-items:center; gap:12px; padding:13px 18px;
    font-size:.9rem; font-weight:500; cursor:pointer; transition:var(--transition);
    border-bottom:1px solid var(--bg2); color:var(--muted);
  }
  .cabinet-nav-item:last-child{border-bottom:none;}
  .cabinet-nav-item:hover,.cabinet-nav-item.active{background:rgba(200,80,42,.06);color:var(--accent);}
  .cabinet-nav-item .nav-icon{font-size:1.1rem;}

  .cabinet-section { display:none; }
  .cabinet-section.active { display:block; animation:sectionIn .24s cubic-bezier(.4,0,.2,1) both; }
  @keyframes sectionIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
  .section-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
  .section-head h2 { font-family:var(--font-display); font-size:1.6rem; font-weight:700; }

  .fav-item { display:flex; gap:14px; align-items:flex-start; padding:16px; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-sm); margin-bottom:10px; transition:box-shadow var(--transition),border-color var(--transition); cursor:pointer; }
  .fav-item:hover { box-shadow:var(--shadow-card); border-color:rgba(200,80,42,.2); }
  .fav-img { width:64px; height:64px; border-radius:8px; object-fit:cover; background:var(--bg2); flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:1.6rem; }
  .fav-info { flex:1; }
  .fav-title { font-weight:600; font-size:.95rem; margin-bottom:4px; }
  .fav-meta { color:var(--muted); font-size:.8rem; }
  .fav-remove { color:var(--muted); font-size:1.2rem; cursor:pointer; transition:var(--transition); padding:4px; }
  .fav-remove:hover { color:var(--accent); }

  .avatar-upload {
    position:relative; width:80px; height:80px; margin:0 auto 16px;
    cursor:pointer; display:block; border-radius:50%;
  }
  .avatar-upload input { display:none; }
  .avatar-upload-overlay {
    position:absolute; inset:0; border-radius:50%;
    background:rgba(0,0,0,.5); color:#fff; font-size:.75rem; font-weight:700;
    display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px;
    opacity:0; transition:opacity .18s;
  }
  .avatar-upload:hover .avatar-upload-overlay { opacity:1; }
  .avatar-upload .user-avatar { margin:0; }
  .avatar-hint {
    font-size:.68rem; color:var(--muted); margin-top:8px; letter-spacing:.02em;
    display:flex; align-items:center; justify-content:center; gap:5px;
  }
  .avatar-hint-dot { width:3px; height:3px; border-radius:50%; background:var(--border); display:inline-block; }

  .field-wrap { position:relative; }
  .field-wrap.field-error .form-control   { border-color:#ef4444!important; box-shadow:0 0 0 3px rgba(239,68,68,.1)!important; }
  .field-wrap.field-success .form-control { border-color:#22c55e!important; box-shadow:0 0 0 3px rgba(34,197,94,.08)!important; }
  .field-hint { font-size:.72rem; min-height:16px; margin-top:3px; color:#ef4444; line-height:1.3; transition:color .15s; }
  .field-hint.ok { color:#22c55e; }
  @keyframes cabShake { 0%,100%{transform:translateX(0)} 15%{transform:translateX(-6px)} 30%{transform:translateX(6px)} 45%{transform:translateX(-4px)} 60%{transform:translateX(4px)} 75%{transform:translateX(-2px)} }
  .shake { animation:cabShake .35s ease; }
  @keyframes btnSpin { to { transform:rotate(360deg); } }
</style>
@endsection

@section('content')
<div class="cabinet-layout page-enter">
  <!-- SIDEBAR -->
  <div class="cabinet-sidebar">
    <div class="user-card">
      <label class="avatar-upload" title="Изменить фото">
        <input type="file" id="avatar-file" accept="image/*" onchange="uploadAvatar(this)">
        <div class="user-avatar" id="user-avatar">?</div>
        <div class="avatar-upload-overlay">📷<br><span style="font-size:.6rem">Изменить</span></div>
      </label>
      <div class="avatar-hint">
        JPG, PNG, WebP <span class="avatar-hint-dot"></span> до 5 МБ
      </div>
      <div class="user-name" id="user-name">—</div>
      <div class="user-phone" id="user-phone"></div>
      <div class="user-role"><span class="badge badge-blue" id="user-role-badge">Пользователь</span></div>
    </div>
    <div class="cabinet-nav">
      <div class="cabinet-nav-item active" onclick="showSection('favorites')"     id="nav-favorites">    <span class="nav-icon">♥</span> Избранное</div>
      <div class="cabinet-nav-item" onclick="showSection('subscriptions')" id="nav-subscriptions"><span class="nav-icon">🔔</span> Подписки</div>
      <div class="cabinet-nav-item" onclick="showSection('profile')"       id="nav-profile">      <span class="nav-icon">👤</span> Профиль</div>
      <div class="cabinet-nav-item" onclick="showSection('password')"      id="nav-password">     <span class="nav-icon">🔒</span> Пароль</div>
      <div class="cabinet-nav-item" onclick="auth.logout()" style="color:var(--accent2)"><span class="nav-icon">🚪</span> Выйти</div>
    </div>
  </div>

  <!-- MAIN -->
  <div class="cabinet-main">

    <!-- ИЗБРАННОЕ -->
    <div class="cabinet-section active" id="section-favorites">
      <div class="section-head">
        <h2>Избранное</h2>
      </div>
      <div class="tabs" style="margin-bottom:20px">
        <button class="tab-btn active" data-group="fav" onclick="filterFav('all',this)">Все</button>
        <button class="tab-btn"        data-group="fav" onclick="filterFav('events',this)">События</button>
        <button class="tab-btn"        data-group="fav" onclick="filterFav('venues',this)">Площадки</button>
      </div>
      <div id="favorites-list"><div class="loader"><div class="spinner"></div> Загрузка...</div></div>
    </div>

    <!-- ПОДПИСКИ -->
    <div class="cabinet-section" id="section-subscriptions">
      <div class="section-head"><h2>Мои подписки</h2></div>
      <div id="subscriptions-list"><div class="loader"><div class="spinner"></div> Загрузка...</div></div>
    </div>

    <!-- ПРОФИЛЬ -->
    <div class="cabinet-section" id="section-profile">
      <div class="section-head"><h2>Мой профиль</h2></div>
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:28px;max-width:500px">
        <div style="display:flex;flex-direction:column;gap:14px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Фамилия</label>
            <div class="field-wrap" id="wrap-p-lastname">
              <input class="form-control" id="p-lastname" placeholder="Иванов"
                oninput="blurCab('p-lastname')" onblur="blurCab('p-lastname')">
            </div>
            <div class="field-hint" id="hint-p-lastname"></div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Имя</label>
            <div class="field-wrap" id="wrap-p-firstname">
              <input class="form-control" id="p-firstname" placeholder="Иван"
                oninput="blurCab('p-firstname')" onblur="blurCab('p-firstname')">
            </div>
            <div class="field-hint" id="hint-p-firstname"></div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Отчество <span style="font-weight:400;color:var(--muted);font-size:.78rem">(необязательно)</span></label>
            <div class="field-wrap" id="wrap-p-patronymic">
              <input class="form-control" id="p-patronymic" placeholder="Петрович"
                oninput="blurCab('p-patronymic')" onblur="blurCab('p-patronymic')">
            </div>
            <div class="field-hint" id="hint-p-patronymic"></div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Email</label>
            <div class="field-wrap" id="wrap-p-email">
              <input class="form-control" id="p-email" type="email" placeholder="example@mail.ru"
                onblur="blurCab('p-email')">
            </div>
            <div class="field-hint" id="hint-p-email"></div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Телефон</label>
            <div class="field-wrap" id="wrap-p-phone">
              <input class="form-control" id="p-phone" type="tel" placeholder="+7 (999) 123-45-67"
                onblur="blurCab('p-phone')">
            </div>
            <div class="field-hint" id="hint-p-phone"></div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Дата рождения <span style="font-weight:400;color:var(--muted);font-size:.78rem">(необязательно)</span></label>
            <div class="field-wrap" id="wrap-p-dob">
              <input class="form-control" id="p-dob" type="date" onblur="blurCab('p-dob')">
            </div>
            <div class="field-hint" id="hint-p-dob"></div>
          </div>
          <button class="btn btn-primary" id="save-profile-btn" onclick="saveProfile()" style="margin-top:4px">Сохранить</button>
        </div>
      </div>
    </div>

    <!-- СМЕНА ПАРОЛЯ -->
    <div class="cabinet-section" id="section-password">
      <div class="section-head"><h2>Смена пароля</h2></div>
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:28px;max-width:500px">
        <div style="display:flex;flex-direction:column;gap:14px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Текущий пароль</label>
            <div class="field-wrap" id="wrap-pw-current">
              <input class="form-control" id="pw-current" type="password" placeholder="••••••••" autocomplete="current-password">
            </div>
            <div class="field-hint" id="hint-pw-current"></div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Новый пароль</label>
            <div class="field-wrap" id="wrap-pw-new">
              <input class="form-control" id="pw-new" type="password" placeholder="Минимум 8 символов" autocomplete="new-password"
                oninput="onNewPassInput(this)">
            </div>
            <div class="field-hint" id="hint-pw-new"></div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Повторите новый пароль</label>
            <div class="field-wrap" id="wrap-pw-confirm">
              <input class="form-control" id="pw-confirm" type="password" placeholder="Ещё раз пароль" autocomplete="new-password"
                oninput="onConfirmPassInput(this)">
            </div>
            <div class="field-hint" id="hint-pw-confirm"></div>
          </div>
          <button class="btn btn-primary" id="change-pw-btn" onclick="changePassword()" style="margin-top:4px">Сменить пароль</button>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection

@section('scripts')
<script>
if (!auth.isLoggedIn() || auth.type() !== 'user') nav('/login');

let allFavorites = [];
let favFilter = 'all';

function renderAvatar(user) {
  const el = document.getElementById('user-avatar');
  if (user.avatar) {
    el.style.background = 'none';
    el.innerHTML = `<img src="${user.avatar}" style="width:100%;height:100%;border-radius:50%;object-fit:cover">`;
  } else {
    el.style.background = '';
    el.textContent = user.full_name?.[0]?.toUpperCase() || '?';
  }
}

async function init() {
  const user = auth.user();
  if (user) {
    renderAvatar(user);
    document.getElementById('user-name').textContent  = user.full_name || '—';
    document.getElementById('user-phone').textContent = user.phone || '';
    const roleEl = document.getElementById('user-role-badge');
    if (roleEl) {
      const roleId = Number(user.role_id);
      if (roleId === 1) { roleEl.textContent = 'Администратор'; roleEl.className = 'badge badge-yellow'; }
      else if (roleId === 3) { roleEl.textContent = 'Модератор'; roleEl.className = 'badge badge-blue'; }
    }
    document.getElementById('p-lastname').value   = user.last_name   || '';
    document.getElementById('p-firstname').value  = user.first_name  || '';
    document.getElementById('p-patronymic').value = user.patronymic  || '';
    document.getElementById('p-email').value      = user.email       || '';
    document.getElementById('p-phone').value      = user.phone       || '';
    document.getElementById('p-dob').value        = user.date_of_birth || '';
  }
  await loadFavorites();
}

async function uploadAvatar(input) {
  const file = input.files[0];
  if (!file) return;
  input.value = '';
  const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
  if (!allowed.includes(file.type)) { toast('Недопустимый формат. Используйте JPG, PNG или WebP', 'error'); return; }
  if (file.size > 5 * 1024 * 1024) { toast(`Файл слишком большой. Максимум 5 МБ`, 'error'); return; }
  const form = new FormData();
  form.append('file', file);
  try {
    const res = await fetch(API + '/upload/avatar', {
      method: 'POST',
      headers: { 'Authorization': 'Bearer ' + auth.token() },
      body: form,
    });
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || 'Ошибка загрузки');
    const updated = await put('/auth/me', { avatar: json.data.url });
    store.set('user', updated);
    renderAvatar(updated);
    toast('Фото обновлено', 'success');
  } catch(e) { toast(e.message, 'error'); }
}

async function loadFavorites() {
  const list = document.getElementById('favorites-list');
  try {
    allFavorites = await get('/favorites');
    renderFavorites();
  } catch(e) { list.innerHTML = '<div class="empty"><div class="empty-icon">⚠️</div>' + e.message + '</div>'; }
}

function renderFavorites() {
  const list = document.getElementById('favorites-list');
  let items = allFavorites;
  if (favFilter === 'events') items = allFavorites.filter(f => f.event_id);
  if (favFilter === 'venues') items = allFavorites.filter(f => f.venue_id && !f.event_id);

  if (!items.length) {
    list.innerHTML = `<div class="empty"><div class="empty-icon">♡</div>
      <div>Здесь пока ничего нет</div>
      <div style="margin-top:12px"><a href="#" onclick="nav('/');return false" class="btn btn-primary" style="display:inline-flex">Смотреть афишу</a></div>
    </div>`;
    return;
  }
  list.innerHTML = items.map(f => `
    <div class="fav-item">
      <div class="fav-img">${f.event_id ? '🎭' : '🏛️'}</div>
      <div class="fav-info">
        <div class="fav-title">
          <a href="${(window.APP_BASE||'')}${f.event_id ? '/event/'+f.event_id : '/venue/'+f.venue_id}" style="color:inherit">
            ${escHtml(f.event_title || f.venue_name || '—')}
          </a>
        </div>
        <div class="fav-meta">
          ${f.event_id ? `<span class="badge badge-red" style="font-size:.7rem">Событие</span>` : `<span class="badge badge-green" style="font-size:.7rem">Площадка</span>`}
          ${f.start_datetime ? ` · ${fmtDate(f.start_datetime)}` : ''}
          · Добавлено ${fmtDateOnly(f.created_at)}
        </div>
      </div>
      <span class="fav-remove" onclick="removeFav(${f.favorite_id}, this)" title="Убрать">✕</span>
    </div>
  `).join('');
}

function filterFav(type, btn) {
  favFilter = type;
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  renderFavorites();
}

async function removeFav(id, el) {
  try {
    await del('/favorites/' + id);
    allFavorites = allFavorites.filter(f => f.favorite_id !== id);
    renderFavorites();
    toast('Удалено из избранного');
  } catch(e) { toast(e.message, 'error'); }
}

function setCabField(id, state, hint) {
  const wrap = document.getElementById('wrap-' + id);
  const el   = document.getElementById('hint-' + id);
  if (wrap) { wrap.classList.remove('field-error','field-success'); if (state) wrap.classList.add('field-' + state); }
  if (el)   { el.textContent = hint || ''; el.className = 'field-hint' + (state === 'success' ? ' ok' : ''); }
}

function vCabNamePart(v, required) {
  if (!v.trim()) return required ? 'Обязательное поле' : null;
  if (v.trim().length < 2) return 'Минимум 2 символа';
  if (/[\d!@#$%^&*()+={}\[\]|\\<>?/]/.test(v)) return 'Только буквы, пробел и дефис';
  if (v.trim().length > 100) return 'Максимум 100 символов';
  return null;
}
function vCabEmail(v) {
  if (!v) return null;
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return 'Некорректный email';
  return null;
}
function vCabPhone(v) {
  if (!v) return null;
  const d = v.replace(/\D/g,'');
  if (d.length < 10 || d.length > 12) return 'Введите корректный номер';
  return null;
}
function vCabDob(v) {
  if (!v) return null;
  const d = new Date(v), now = new Date();
  if (isNaN(d)) return 'Некорректная дата';
  const age = (now - d) / (365.25 * 24 * 3600 * 1000);
  if (age < 14) return 'Минимальный возраст 14 лет';
  if (age > 110) return 'Проверьте дату рождения';
  return null;
}

function blurCab(id) {
  const val = document.getElementById(id)?.value?.trim() ?? '';
  const map = {
    'p-lastname':   () => vCabNamePart(val, true),
    'p-firstname':  () => vCabNamePart(val, true),
    'p-patronymic': () => vCabNamePart(val, false),
    'p-email':      () => vCabEmail(val),
    'p-phone':      () => vCabPhone(val),
    'p-dob':        () => vCabDob(val),
  };
  const fn = map[id]; if (!fn) return;
  const err = fn();
  if (err)      setCabField(id, 'error', err);
  else if (val) setCabField(id, 'success', '');
  else          setCabField(id, '', '');
}

async function saveProfile() {
  const lastnameVal   = document.getElementById('p-lastname').value.trim();
  const firstnameVal  = document.getElementById('p-firstname').value.trim();
  const patronymicVal = document.getElementById('p-patronymic').value.trim();
  const emailVal      = document.getElementById('p-email').value.trim();
  const phoneVal      = document.getElementById('p-phone').value;
  const dobVal        = document.getElementById('p-dob').value;

  const checks = {
    'p-lastname':   vCabNamePart(lastnameVal, true),
    'p-firstname':  vCabNamePart(firstnameVal, true),
    'p-patronymic': vCabNamePart(patronymicVal, false),
    'p-email':      vCabEmail(emailVal),
    'p-phone':      vCabPhone(phoneVal),
    'p-dob':        vCabDob(dobVal),
  };

  let first = null;
  for (const [id, err] of Object.entries(checks)) {
    const val = document.getElementById(id).value.trim();
    if (err) { setCabField(id, 'error', err); if (!first) first = id; }
    else if (val) setCabField(id, 'success', '');
  }
  if (first) {
    document.getElementById(first)?.closest('.field-wrap')?.classList.add('shake');
    setTimeout(() => document.querySelectorAll('.shake').forEach(el => el.classList.remove('shake')), 400);
    return;
  }

  const btn = document.getElementById('save-profile-btn');
  btn.disabled = true;
  btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:btnSpin .7s linear infinite;display:inline-block;vertical-align:middle"><circle cx="12" cy="12" r="10" stroke-opacity=".25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg> Сохранение…';

  try {
    const updated = await put('/auth/me', {
      last_name:     lastnameVal   || null,
      first_name:    firstnameVal  || null,
      patronymic:    patronymicVal || null,
      email:         emailVal      || null,
      phone:         rawPhone(phoneVal),
      date_of_birth: dobVal        || null,
    });
    store.set('user', updated);
    renderAvatar(updated);
    document.getElementById('user-name').textContent  = updated.full_name || '—';
    document.getElementById('user-phone').textContent = updated.phone || '';
    toast('Профиль сохранён', 'success');
  } catch(e) { toast(e.message, 'error'); }
  finally {
    btn.disabled = false;
    btn.textContent = 'Сохранить';
  }
}

// ── Смена пароля ──
function onNewPassInput(el) {
  const v = el.value;
  if (!v) { setCabField('pw-new', '', ''); return; }
  if (v.length < 8) { setCabField('pw-new', 'error', 'Минимум 8 символов'); return; }
  if (!/[a-zA-Zа-яёА-ЯЁ]/.test(v)) { setCabField('pw-new', 'error', 'Добавьте букву'); return; }
  if (!/\d/.test(v)) { setCabField('pw-new', 'error', 'Добавьте цифру'); return; }
  setCabField('pw-new', 'success', '');
  onConfirmPassInput(document.getElementById('pw-confirm'));
}

function onConfirmPassInput(el) {
  const pass = document.getElementById('pw-new')?.value || '';
  if (!el.value) { setCabField('pw-confirm', '', ''); return; }
  if (el.value !== pass) { setCabField('pw-confirm', 'error', 'Пароли не совпадают'); return; }
  setCabField('pw-confirm', 'success', '');
}

async function changePassword() {
  const current = document.getElementById('pw-current').value;
  const newPass  = document.getElementById('pw-new').value;
  const confirm  = document.getElementById('pw-confirm').value;

  if (!current) { setCabField('pw-current', 'error', 'Введите текущий пароль'); return; }
  if (newPass.length < 8 || !/[a-zA-Zа-яёА-ЯЁ]/.test(newPass) || !/\d/.test(newPass)) {
    setCabField('pw-new', 'error', 'Пароль должен содержать мин. 8 символов, букву и цифру'); return;
  }
  if (newPass !== confirm) { setCabField('pw-confirm', 'error', 'Пароли не совпадают'); return; }

  const btn = document.getElementById('change-pw-btn');
  btn.disabled = true; btn.textContent = 'Сохранение...';
  try {
    await post('/auth/change-password', { current_password: current, new_password: newPass });
    toast('Пароль успешно изменён', 'success');
    document.getElementById('pw-current').value = '';
    document.getElementById('pw-new').value = '';
    document.getElementById('pw-confirm').value = '';
    setCabField('pw-current', '', '');
    setCabField('pw-new', '', '');
    setCabField('pw-confirm', '', '');
  } catch(e) {
    toast(e.message, 'error');
    if (e.message.toLowerCase().includes('пароль') || e.message.toLowerCase().includes('password')) {
      setCabField('pw-current', 'error', 'Неверный текущий пароль');
    }
  } finally {
    btn.disabled = false; btn.textContent = 'Сменить пароль';
  }
}

function showSection(name) {
  document.querySelectorAll('.cabinet-section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.cabinet-nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('section-' + name).classList.add('active');
  document.getElementById('nav-' + name)?.classList.add('active');
  if (name === 'subscriptions') loadSubscriptions();
}

async function loadSubscriptions() {
  const list = document.getElementById('subscriptions-list');
  list.innerHTML = '<div class="loader"><div class="spinner"></div> Загрузка...</div>';
  try {
    const orgs = await get('/subscriptions');
    if (!orgs.length) {
      list.innerHTML = '<div class="empty"><div class="empty-icon">🔔</div><div>Вы ни на кого не подписаны</div><div style="font-size:.82rem;color:var(--muted);margin-top:6px">Найдите организацию и нажмите «Подписаться»</div></div>';
      return;
    }
    list.innerHTML = orgs.map(org => `
      <div class="fav-item" style="cursor:pointer" onclick="nav('/org/${org.organization_id}')">
        <div class="fav-img" style="background:var(--bg2);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0">
          ${org.image ? `<img src="${escHtml(org.image)}" style="width:64px;height:64px;object-fit:cover;border-radius:8px">` : '🏢'}
        </div>
        <div class="fav-info">
          <div class="fav-title">${escHtml(org.full_name)}</div>
          ${org.address ? `<div class="fav-meta">📍 ${escHtml(org.address)}</div>` : ''}
        </div>
        <button class="fav-remove" title="Отписаться" onclick="event.stopPropagation();unsubscribeOrg(${org.organization_id},this)">🔕</button>
      </div>`).join('');
  } catch(e) { list.innerHTML = `<div class="empty"><div class="empty-icon">⚠️</div>${escHtml(e.message)}</div>`; }
}

async function unsubscribeOrg(orgId, btn) {
  btn.disabled = true;
  try {
    await del('/orgs/' + orgId + '/subscribe');
    toast('Подписка отменена');
    loadSubscriptions();
  } catch(e) { toast(e.message, 'error'); btn.disabled = false; }
}

init();
</script>
@endsection
