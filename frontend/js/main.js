// ── CONFIG ──
const API = (() => {
  const h = location.hostname;
  if (h === 'localhost' || h === '127.0.0.1') return 'http://localhost:8888/afisha/api';
  return location.protocol + '//' + location.host + '/afisha/api';
})();

// ── STORAGE ──
const store = {
  get: k => { try { return JSON.parse(localStorage.getItem(k)); } catch { return null; } },
  set: (k,v) => localStorage.setItem(k, JSON.stringify(v)),
  del: k => localStorage.removeItem(k),
};

// ── AUTH STATE ──
const auth = {
  token: () => store.get('token'),
  user:  () => store.get('user'),
  org:   () => store.get('org'),
  type:  () => store.get('auth_type'),
  isLoggedIn: () => !!store.get('token'),
  saveUser: (token, user) => { store.set('token',token); store.set('user',user); store.set('auth_type','user'); },
  saveOrg:  (token, org)  => { store.set('token',token); store.set('org',org);   store.set('auth_type','org'); },
  logout: () => { store.del('token'); store.del('user'); store.del('org'); store.del('auth_type'); window.location = 'login.html'; },
};

// ── API CLIENT ──
async function api(method, path, body = null, auth_required = false) {
  const headers = { 'Content-Type': 'application/json' };
  if (auth_required || auth.token()) headers['Authorization'] = 'Bearer ' + auth.token();
  const controller = new AbortController();
  const tid = setTimeout(() => controller.abort(), 15000);
  try {
    const res = await fetch(API + path, {
      method, headers,
      body: body ? JSON.stringify(body) : null,
      signal: controller.signal,
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || `Ошибка ${res.status}`);
    return data.data ?? data;
  } catch (e) {
    if (e.name === 'AbortError') throw new Error('Сервер не отвечает (таймаут)');
    throw e;
  } finally {
    clearTimeout(tid);
  }
}

const get  = (path)       => api('GET',    path);
const post = (path, body) => api('POST',   path, body, true);
const put  = (path, body) => api('PUT',    path, body, true);
const del  = (path)       => api('DELETE', path, null, true);

// ── HTML ESCAPE ──
function escHtml(s) {
  return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── TOAST ──
function toast(msg, type = '') {
  const el = document.getElementById('toast');
  if (!el) return;
  el.textContent = msg;
  el.className = 'show ' + type;
  clearTimeout(el._t);
  el._t = setTimeout(() => el.className = '', 3000);
}

// ── THEME ──
function initTheme() {
  const saved = store.get('theme') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
}

function toggleTheme() {
  const current = document.documentElement.getAttribute('data-theme') || 'light';
  const next = current === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  store.set('theme', next);
  const btn = document.getElementById('theme-btn');
  if (btn) btn.textContent = next === 'dark' ? '☀️' : '🌙';
}

// ── CART BADGE ──
async function updateCartBadge() {
  if (!auth.isLoggedIn() || auth.type() !== 'user') return;
  try {
    const data = await get('/tickets/count');
    const cnt = data.count ?? 0;
    const badge = document.getElementById('cart-badge');
    if (badge) {
      badge.textContent = cnt;
      badge.style.display = cnt > 0 ? 'flex' : 'none';
    }
  } catch(e) {}
}

// ── NAVBAR ──
function renderNavbar() {
  const nav = document.getElementById('navbar');
  if (!nav) return;
  const logged = auth.isLoggedIn();
  const type   = auth.type();
  const name   = type === 'user' ? auth.user()?.full_name : auth.org()?.full_name;
  const isDark = (store.get('theme') || 'light') === 'dark';
  const initials = name ? name.trim().split(/\s+/).slice(0,2).map(w => w[0] || '').join('').toUpperCase() : '?';

  const userObj  = auth.user();
  const isMod    = type === 'user' && [1, 3].includes(Number(userObj?.role_id));

  nav.innerHTML = `
    <a class="navbar-brand" href="index.html">АфишаКолыма</a>

    <div id="nav-mid" class="nav-mid"></div>

    ${isMod ? `
      <a href="moderator-panel.html" style="
        display:flex; align-items:center; gap:5px;
        padding:6px 14px; border-radius:20px; font-size:.85rem; font-weight:600;
        border:1.5px solid rgba(245,158,11,.5); color:#d97706; transition:var(--transition);
        text-decoration:none; background:rgba(245,158,11,.07);
      " onmouseover="this.style.borderColor='#d97706'" onmouseout="this.style.borderColor='rgba(245,158,11,.5)'">
        🛡 Модерация
      </a>
    ` : ''}

    ${logged && type === 'user' ? `
      <a href="favorites.html" id="fav-link" style="
        display:flex; align-items:center; gap:5px;
        padding:6px 14px; border-radius:20px; font-size:.85rem; font-weight:600;
        border:1.5px solid var(--border); color:var(--text); transition:var(--transition);
        text-decoration:none;
      " onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
        ♥ Избранное
      </a>
      <a href="cart.html" id="cart-link" style="
        position:relative; display:flex; align-items:center; gap:5px;
        padding:6px 14px; border-radius:20px; font-size:.85rem; font-weight:600;
        border:1.5px solid var(--border); color:var(--text); transition:var(--transition);
        text-decoration:none;
      " onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
        🛒 Корзина
        <span id="cart-badge" style="
          display:none; position:absolute; top:-6px; right:-6px;
          background:var(--accent); color:#fff; border-radius:50%;
          width:18px; height:18px; font-size:.65rem; font-weight:700;
          align-items:center; justify-content:center;
        ">0</span>
      </a>
    ` : ''}

    ${logged ? `
      <div id="user-menu-wrap" style="position:relative">
        <button onclick="toggleUserMenu()" style="
          display:flex; align-items:center; gap:8px;
          background:none; border:1.5px solid var(--border); border-radius:24px;
          padding:5px 12px 5px 5px; cursor:pointer; color:var(--text);
          font-family:var(--font); font-size:.83rem; transition:var(--transition);
        " onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
          <span style="
            width:26px; height:26px; border-radius:50%; flex-shrink:0;
            background:var(--accent); color:#fff;
            display:flex; align-items:center; justify-content:center;
            font-size:.68rem; font-weight:800;
          ">${initials}</span>
          <span style="max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${name?.split(' ')[0] || ''}</span>
          <svg width="11" height="11" viewBox="0 0 12 12" fill="none" style="opacity:.4;flex-shrink:0"><path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div id="user-menu" style="
          display:none; position:absolute; top:calc(100% + 8px); right:0;
          background:var(--surface); border:1px solid var(--border);
          border-radius:14px; box-shadow:var(--shadow-lg);
          min-width:184px; overflow:hidden; z-index:300;
        ">
          <div style="padding:12px 14px 10px; border-bottom:1px solid var(--border)">
            <div style="font-size:.72rem;color:var(--muted);margin-bottom:1px">Аккаунт</div>
            <div style="font-size:.88rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${name || ''}</div>
          </div>
          <a href="${type === 'org' ? 'org-cabinet.html' : 'cabinet.html'}" style="
            display:flex; align-items:center; gap:10px; padding:11px 14px;
            font-size:.85rem; color:var(--text); transition:background .15s;
          " onmouseover="this.style.background='var(--bg2)'" onmouseout="this.style.background=''">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
            Личный кабинет
          </a>
          <button onclick="toggleTheme()" style="
            display:flex; align-items:center; justify-content:space-between; width:100%;
            padding:11px 14px; font-size:.85rem; color:var(--muted);
            background:none; border:none; border-top:1px solid var(--border);
            cursor:pointer; font-family:var(--font); text-align:left; transition:background .15s;
          " onmouseover="this.style.background='var(--bg2)'" onmouseout="this.style.background=''">
            <span style="display:flex;align-items:center;gap:10px">
              <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">${isDark ? '<circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>' : '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>'}</svg>
              ${isDark ? 'Светлая тема' : 'Тёмная тема'}
            </span>
            <span style="font-size:.95rem">${isDark ? '☀️' : '🌙'}</span>
          </button>
          <button onclick="auth.logout()" style="
            display:flex; align-items:center; gap:10px; width:100%;
            padding:11px 14px; font-size:.85rem; color:var(--muted);
            background:none; border:none; border-top:1px solid var(--border);
            cursor:pointer; font-family:var(--font); text-align:left; transition:background .15s;
          " onmouseover="this.style.background='var(--bg2)';this.style.color='var(--accent)'" onmouseout="this.style.background='';this.style.color='var(--muted)'">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Выйти
          </button>
        </div>
      </div>
    ` : `
      <a class="navbar-btn outline" href="login.html">Войти</a>
      <a class="navbar-btn" href="register.html">Регистрация</a>
      <button id="theme-btn" onclick="toggleTheme()" title="Сменить тему" style="
        background:none; border:none; padding:4px 6px;
        font-size:1.1rem; cursor:pointer; line-height:1;
        color:rgba(255,255,255,.55); transition:var(--transition);
      " onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.55)'">${isDark ? '☀️' : '🌙'}</button>
    `}
  `;
}

function toggleUserMenu() {
  const menu = document.getElementById('user-menu');
  if (!menu) return;
  const open = menu.style.display !== 'none';
  menu.style.display = open ? 'none' : 'block';
  if (!open) {
    setTimeout(() => {
      document.addEventListener('click', function closeOnOut(e) {
        if (!document.getElementById('user-menu-wrap')?.contains(e.target)) {
          if (menu) menu.style.display = 'none';
          document.removeEventListener('click', closeOnOut);
        }
      });
    }, 50);
  }
}

// ── FOOTER ──
function renderFooter() {
  const el = document.getElementById('site-footer');
  if (!el) return;
  el.innerHTML = `
    <footer class="footer">
      <div class="footer-inner">

        <div class="footer-sub">
          <div class="footer-sub-title">Подпишитесь на акции и анонсы событий</div>
          <form class="footer-sub-form" onsubmit="footerSubscribe(event)">
            <input type="email" class="footer-sub-input" placeholder="Электронная почта" required>
            <button type="submit" class="footer-sub-btn">Далее</button>
          </form>
        </div>

        <div class="footer-links">
          <div class="footer-col">
            <div class="footer-col-title">АфишаКолыма</div>
            <a href="#">О проекте</a>
            <a href="#">Помощь</a>
            <a href="#">Пользовательское соглашение</a>
            <a href="#">Политика конфиденциальности</a>
            <a href="#">Возврат билетов</a>
          </div>
          <div class="footer-col">
            <div class="footer-col-title">Партнёрам и организаторам</div>
            <a href="register.html">Зарегистрировать организацию</a>
            <a href="org-cabinet.html">Разместить событие</a>
            <a href="#">Реклама на сайте</a>
            <a href="#">Корпоративным клиентам</a>
          </div>
          <div class="footer-col">
            <div class="footer-col-title">Оплата</div>
            <div class="footer-pay-methods">
              <span class="footer-pay-badge">СБП</span>
              <span class="footer-pay-badge">МИР</span>
              <span class="footer-pay-badge">Visa</span>
              <span class="footer-pay-badge">MC</span>
              <span class="footer-pay-badge">ЮMoney</span>
            </div>
            <a href="#">Способы оплаты</a>
            <a href="#">Безопасность платежей</a>
          </div>
        </div>

        <div class="footer-bottom">
          <span>© 2025 АфишаКолыма. Все права защищены.</span>
          <div class="footer-socials">
            <a href="#" title="ВКонтакте">ВК</a>
            <a href="#" title="Telegram">TG</a>
          </div>
        </div>

      </div>
    </footer>
  `;
}

function footerSubscribe(e) {
  e.preventDefault();
  const input = e.target.querySelector('input');
  toast('Вы подписались на рассылку!', 'success');
  input.value = '';
}

// ── STARS RENDER ──
function stars(rating) {
  return Array.from({length:5}, (_,i) =>
    `<span style="color:${i < rating ? '#f0b429' : 'var(--border)'}">${i < rating ? '★' : '☆'}</span>`
  ).join('');
}

// ── DATE FORMAT ──
function fmtDate(dt) {
  if (!dt) return '';
  const d = new Date(dt);
  return d.toLocaleString('ru-RU', { day:'numeric', month:'long', hour:'2-digit', minute:'2-digit' });
}
function fmtDateOnly(dt) {
  if (!dt) return '';
  return new Date(dt).toLocaleDateString('ru-RU', { day:'numeric', month:'long', year:'numeric' });
}

// ── PRICE FORMAT ──
function fmtPrice(p) {
  return +p === 0 ? 'Бесплатно' : Number(p).toLocaleString('ru-RU') + ' ₽';
}

// ── IMAGE PLACEHOLDER ──
function imgOrPlaceholder(src, emoji = '🎭') {
  if (src) return `<img class="card-img" src="${src}" alt="" onerror="this.outerHTML='<div class=card-img-placeholder>${emoji}</div>'">`;
  return `<div class="card-img-placeholder">${emoji}</div>`;
}

// ── TABS ──
function initTabs(container = document) {
  container.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const group = btn.dataset.group || 'default';
      container.querySelectorAll(`.tab-btn[data-group="${group}"]`).forEach(b => b.classList.remove('active'));
      container.querySelectorAll(`.tab-panel[data-group="${group}"]`).forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      container.getElementById ?
        container.getElementById(btn.dataset.tab)?.classList.add('active') :
        document.getElementById(btn.dataset.tab)?.classList.add('active');
    });
  });
}

// ── MODAL ──
function openModal(id)  { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }

// ── PHONE FORMAT ──
function initPhoneInput(el) {
  if (!el) return;
  el.addEventListener('input', function() {
    let digits = this.value.replace(/\D/g, '');
    if (digits.startsWith('8')) digits = '7' + digits.slice(1);
    if (!digits.startsWith('7')) digits = '7' + digits;
    digits = digits.slice(0, 11);
    let out = '+7';
    if (digits.length > 1) out += ' (' + digits.slice(1, 4);
    if (digits.length >= 4) out += ') ' + digits.slice(4, 7);
    if (digits.length >= 7) out += '-' + digits.slice(7, 9);
    if (digits.length >= 9) out += '-' + digits.slice(9, 11);
    this.value = out;
  });
  el.addEventListener('keydown', function(e) {
    if (e.key === 'Backspace' && this.value === '+7 (') { this.value = ''; e.preventDefault(); }
  });
  el.addEventListener('focus', function() { if (!this.value) this.value = '+7 ('; });
  el.addEventListener('blur',  function() { if (this.value === '+7 (') this.value = ''; });
}

function rawPhone(val) {
  const digits = val.replace(/\D/g, '');
  return digits ? '+' + digits : '';
}

// ── PWA SERVICE WORKER ──
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/frontend/sw.js').catch(() => {});
  });
}

// ── INIT ──
document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  renderNavbar();
  renderFooter();
  updateCartBadge();
  document.querySelectorAll('input[type="tel"]').forEach(initPhoneInput);
});
