@extends('layouts.auth')

@section('title', 'Сброс пароля — АфишаКолыма')

@section('styles')
<style>
  body { display: flex; flex-direction: column; min-height: 100vh; }
  .auth-wrap { display: flex; flex: 1; }

  .auth-left {
    flex: 1; background: var(--hero-bg);
    display: flex; flex-direction: column; justify-content: flex-end;
    padding: 56px; position: relative; overflow: hidden;
  }
  .auth-left::before {
    content: ''; position: absolute; inset: 0;
    background:
      radial-gradient(ellipse at 20% 75%, rgba(200,80,42,.45) 0%, transparent 55%),
      radial-gradient(ellipse at 75% 20%, rgba(42,110,90,.25) 0%, transparent 50%);
  }
  .auth-left::after {
    content: ''; position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.055) 1px, transparent 1px);
    background-size: 26px 26px; pointer-events: none;
  }
  .auth-logo {
    font-family: var(--font-display); font-style: italic;
    font-size: 2rem; color: #fff; display: block; text-decoration: none;
    position: absolute; top: 44px; left: 56px; z-index: 2;
  }
  .auth-left-content { position: relative; z-index: 2; }
  .auth-tagline { font-size: 2.6rem; font-weight: 700; color: #fff; line-height: 1.15; margin-bottom: 14px; }
  .auth-tagline em { font-family: var(--font-display); font-style: italic; color: rgba(255,255,255,.5); }

  .auth-right {
    width: 500px; max-width: 100%; display: flex; flex-direction: column;
    justify-content: center; padding: 56px 48px;
    background: var(--bg); overflow-y: auto;
  }

  .auth-head { margin-bottom: 24px; }
  .auth-title { font-family: var(--font-display); font-size: 1.85rem; font-weight: 700; margin-bottom: 5px; }
  .auth-subtitle { color: var(--muted); font-size: .88rem; }

  .auth-form { display: flex; flex-direction: column; gap: 14px; }
  .field { display: flex; flex-direction: column; gap: 5px; }
  .field-label { font-size: .72rem; font-weight: 700; color: var(--muted); letter-spacing: .06em; text-transform: uppercase; }
  .field-wrap { position: relative; display: flex; align-items: center; }
  .field-ico { position: absolute; left: 14px; color: var(--muted); display: flex; align-items: center; pointer-events: none; transition: color .15s; }
  .field-wrap:focus-within .field-ico { color: var(--accent); }
  .field-wrap .form-control { padding-left: 42px; }
  .field-wrap .form-control.has-eye { padding-right: 44px; }
  .field-eye { position: absolute; right: 11px; background: none; border: none; color: var(--muted); cursor: pointer; padding: 5px; line-height: 0; border-radius: 6px; transition: var(--transition); }
  .field-eye:hover { color: var(--text); background: var(--bg2); }
  .field-wrap.field-error .form-control   { border-color: #ef4444 !important; box-shadow: 0 0 0 3px rgba(239,68,68,.1) !important; }
  .field-wrap.field-success .form-control { border-color: #22c55e !important; }
  .field-hint { font-size: .72rem; min-height: 16px; margin-top: 2px; color: #ef4444; }
  .field-hint.ok { color: #22c55e; }

  .pass-bar { height: 3px; background: var(--border); border-radius: 2px; overflow: hidden; margin: 6px 0 2px; }
  .pass-bar-fill { height: 100%; border-radius: 2px; transition: width .3s, background .3s; width: 0; }
  .pass-strength-lbl { font-size: .7rem; min-height: 1em; }

  .auth-switch { text-align: center; font-size: .84rem; color: var(--muted); margin-top: 18px; }
  .auth-switch a { color: var(--accent); font-weight: 600; text-decoration: none; }

  @media (max-width: 860px) {
    .auth-wrap { flex-direction: column; }
    .auth-left { flex: none; min-height: 160px; padding: 72px 24px 28px; }
    .auth-logo { top: 24px; left: 24px; }
    .auth-tagline { font-size: 1.6rem; }
    .auth-right { width: 100%; padding: 32px 20px; }
  }
  @keyframes spin { to { transform: rotate(360deg); } }
  @keyframes authShake {
    0%,100%{transform:translateX(0)}
    15%{transform:translateX(-7px)} 30%{transform:translateX(7px)}
    45%{transform:translateX(-5px)} 60%{transform:translateX(5px)}
    75%{transform:translateX(-3px)}
  }
  .shake { animation: authShake .38s ease; }
</style>
@endsection

@section('content')
<div class="auth-wrap">
<div class="auth-left">
  <a class="auth-logo" href="{{ url('/') }}">АфишаКолыма</a>
  <div class="auth-left-content">
    <div class="auth-tagline">Новый пароль<br>за <em>пару минут</em></div>
  </div>
</div>

<div class="auth-right">
  <div id="panel-form" class="page-enter">
    <div class="auth-head">
      <div class="auth-title">Новый пароль</div>
      <div class="auth-subtitle">Придумайте надёжный пароль для аккаунта</div>
    </div>
    <div class="auth-form" id="form-reset">
      <div class="field">
        <label class="field-label">Новый пароль</label>
        <div class="field-wrap" id="wrap-new-password">
          <span class="field-ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></span>
          <input class="form-control has-eye" id="new-password" type="password" placeholder="Минимум 8 символов"
            oninput="onPassInput(this)" autocomplete="new-password">
          <button class="field-eye" type="button" onclick="togglePass('new-password',this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <div class="pass-bar"><div class="pass-bar-fill" id="pass-fill"></div></div>
        <div class="pass-strength-lbl" id="pass-lbl"></div>
        <div class="field-hint" id="hint-new-password"></div>
      </div>
      <div class="field">
        <label class="field-label">Повторите пароль</label>
        <div class="field-wrap" id="wrap-confirm-password">
          <span class="field-ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></span>
          <input class="form-control has-eye" id="confirm-password" type="password" placeholder="Ещё раз пароль"
            oninput="onConfirmInput(this)" autocomplete="new-password">
          <button class="field-eye" type="button" onclick="togglePass('confirm-password',this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <div class="field-hint" id="hint-confirm-password"></div>
      </div>
      <button class="btn btn-primary btn-full btn-lg" id="reset-btn" onclick="doReset()">Сохранить пароль</button>
    </div>
    <div class="auth-switch"><a href="{{ url('/login') }}">← Вернуться к входу</a></div>
  </div>

  <div id="panel-invalid" style="display:none" class="page-enter">
    <div style="text-align:center;padding:24px 0">
      <div style="font-size:3rem;margin-bottom:16px">🔗</div>
      <div style="font-weight:700;font-size:1.1rem;margin-bottom:10px">Ссылка недействительна</div>
      <div style="color:var(--muted);font-size:.9rem;margin-bottom:24px">Ссылка для сброса пароля устарела или уже была использована.</div>
      <a href="{{ url('/login') }}" class="btn btn-primary">Запросить новую ссылку</a>
    </div>
  </div>

  <div id="panel-success" style="display:none" class="page-enter">
    <div style="text-align:center;padding:24px 0">
      <div style="font-size:3rem;margin-bottom:16px">✅</div>
      <div style="font-weight:700;font-size:1.1rem;margin-bottom:10px">Пароль изменён!</div>
      <div style="color:var(--muted);font-size:.9rem;margin-bottom:24px">Теперь вы можете войти с новым паролем.</div>
      <a href="{{ url('/login') }}" class="btn btn-primary">Войти</a>
    </div>
  </div>
</div>
</div>
@endsection

@section('scripts')
<script>
const params = new URLSearchParams(location.search);
const token = params.get('token') || '';
const email = params.get('email') || '';
const type  = params.get('type') || 'user';

if (!token || !email) {
  document.getElementById('panel-form').style.display = 'none';
  document.getElementById('panel-invalid').style.display = '';
}

function togglePass(id, btn) {
  const inp = document.getElementById(id);
  const show = inp.type === 'password';
  inp.type = show ? 'text' : 'password';
  btn.innerHTML = show
    ? `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`
    : `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
}

function setField(id, state, msg) {
  const wrap = document.getElementById('wrap-' + id);
  const hint = document.getElementById('hint-' + id);
  if (wrap) { wrap.classList.remove('field-error','field-success'); if (state) wrap.classList.add('field-' + state); }
  if (hint) { hint.textContent = msg || ''; hint.className = 'field-hint' + (state === 'success' ? ' ok' : ''); }
}

function vPass(v) {
  if (!v) return 'Обязательное поле';
  if (v.length < 8) return 'Минимум 8 символов';
  if (!/[a-zA-Zа-яёА-ЯЁ]/.test(v)) return 'Добавьте хотя бы одну букву';
  if (!/\d/.test(v)) return 'Добавьте хотя бы одну цифру';
  return null;
}

function updateStrength(val) {
  const fill = document.getElementById('pass-fill');
  const lbl  = document.getElementById('pass-lbl');
  if (!val) { fill.style.width = '0'; if (lbl) lbl.textContent = ''; return; }
  let s = 0;
  if (val.length >= 8) s++;
  if (val.length >= 12) s++;
  if (/[a-zA-Zа-яёА-ЯЁ]/.test(val) && /\d/.test(val)) s++;
  if (/[!@#$%^&*()\-_=+]/.test(val)) s++;
  const cfg = [{w:0,c:'',t:''},{w:25,c:'#ef4444',t:'Слабый'},{w:55,c:'#f97316',t:'Средний'},{w:78,c:'#eab308',t:'Хороший'},{w:100,c:'#22c55e',t:'Отличный'}];
  fill.style.width = cfg[s].w + '%'; fill.style.background = cfg[s].c;
  if (lbl) { lbl.textContent = cfg[s].t; lbl.style.color = cfg[s].c || 'var(--muted)'; }
}

function onPassInput(el) {
  updateStrength(el.value);
  const err = vPass(el.value);
  if (el.value) setField('new-password', err ? 'error' : 'success', err || '');
  else setField('new-password', '', '');
  const conf = document.getElementById('confirm-password');
  if (conf && conf.value) onConfirmInput(conf);
}

function onConfirmInput(el) {
  const pass = document.getElementById('new-password')?.value || '';
  const err = el.value && pass !== el.value ? 'Пароли не совпадают' : null;
  if (el.value) setField('confirm-password', err ? 'error' : 'success', err || '');
  else setField('confirm-password', '', '');
}

function setBtnLoading(id, on) {
  const btn = document.getElementById(id);
  if (!btn) return;
  if (on) {
    btn.dataset.orig = btn.textContent; btn.disabled = true;
    btn.innerHTML = `<span style="display:flex;align-items:center;justify-content:center;gap:8px"><svg style="animation:spin 1s linear infinite" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10" stroke-opacity=".2"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>Подождите...</span>`;
  } else {
    btn.disabled = false; btn.textContent = btn.dataset.orig || btn.textContent;
  }
}

async function doReset() {
  const passVal = document.getElementById('new-password').value;
  const confVal = document.getElementById('confirm-password').value;
  const passErr = vPass(passVal);
  if (passErr) { setField('new-password', 'error', passErr); return; }
  if (passVal !== confVal) { setField('confirm-password', 'error', 'Пароли не совпадают'); return; }

  setBtnLoading('reset-btn', true);
  try {
    await api('POST', '/auth/reset-password', { token, email, password: passVal, type });
    document.getElementById('panel-form').style.display = 'none';
    document.getElementById('panel-success').style.display = '';
  } catch(e) {
    setBtnLoading('reset-btn', false);
    if (e.message && (e.message.includes('устарела') || e.message.includes('недействительна'))) {
      document.getElementById('panel-form').style.display = 'none';
      document.getElementById('panel-invalid').style.display = '';
    } else {
      setField('new-password', 'error', e.message || 'Ошибка сброса пароля');
    }
  }
}
</script>
@endsection
