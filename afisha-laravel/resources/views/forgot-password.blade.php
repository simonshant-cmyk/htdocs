@extends('layouts.auth')

@section('title', 'Восстановление пароля — АфишаКолыма')

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
      radial-gradient(ellipse at 75% 20%, rgba(42,110,90,.25) 0%, transparent 50%),
      radial-gradient(ellipse at 55% 95%, rgba(74,95,168,.2) 0%, transparent 40%);
  }
  .auth-left::after {
    content: ''; position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.055) 1px, transparent 1px);
    background-size: 26px 26px; pointer-events: none;
  }
  .auth-logo {
    font-family: var(--font-display); font-style: italic;
    font-size: 2rem; color: #fff; display: block; text-decoration: none;
    position: absolute; top: 44px; left: 56px; z-index: 2; transition: opacity .2s;
  }
  .auth-logo:hover { opacity: .8; }
  .auth-left-content { position: relative; z-index: 2; }
  .auth-tagline { font-size: 2.6rem; font-weight: 700; color: #fff; line-height: 1.15; margin-bottom: 14px; }
  .auth-tagline em { font-family: var(--font-display); font-style: italic; color: rgba(255,255,255,.5); }
  .auth-sub { color: rgba(255,255,255,.48); font-size: .93rem; line-height: 1.7; max-width: 300px; }

  .auth-right {
    width: 500px; display: flex; flex-direction: column;
    justify-content: center; padding: 56px 48px;
    background: var(--bg); overflow-y: auto;
  }
  .auth-head { margin-bottom: 24px; }
  .auth-title { font-family: var(--font-display); font-size: 1.85rem; font-weight: 700; margin-bottom: 5px; }
  .auth-subtitle { color: var(--muted); font-size: .88rem; line-height: 1.5; }

  .auth-form { display: flex; flex-direction: column; gap: 14px; }
  .field { display: flex; flex-direction: column; gap: 5px; }
  .field-label { font-size: .72rem; font-weight: 700; color: var(--muted); letter-spacing: .06em; text-transform: uppercase; }
  .field-wrap { position: relative; display: flex; align-items: center; }
  .field-ico { position: absolute; left: 14px; color: var(--muted); display: flex; align-items: center; pointer-events: none; transition: color .15s; }
  .field-wrap:focus-within .field-ico { color: var(--accent); }
  .field-wrap .form-control { padding-left: 42px; }
  .field-wrap.field-error .form-control   { border-color: #ef4444 !important; box-shadow: 0 0 0 3px rgba(239,68,68,.1) !important; }
  .field-wrap.field-success .form-control { border-color: #22c55e !important; }
  .field-hint { font-size: .72rem; min-height: 16px; margin-top: 2px; color: #ef4444; }
  .field-hint.ok { color: #22c55e; }

  .auth-switch { text-align: center; font-size: .84rem; color: var(--muted); margin-top: 18px; }
  .auth-switch a { color: var(--accent); font-weight: 600; text-decoration: none; }
  .auth-switch a:hover { text-decoration: underline; }

  @media (max-width: 860px) {
    .auth-wrap { flex-direction: column; }
    .auth-left { flex: none; min-height: 160px; padding: 72px 24px 28px; }
    .auth-logo { top: 24px; left: 24px; }
    .auth-tagline { font-size: 1.6rem; }
    .auth-right { width: 100%; padding: 32px 20px; }
  }
  @keyframes spin { to { transform: rotate(360deg); } }
  @keyframes authShake {
    0%,100%{transform:translateX(0)} 15%{transform:translateX(-7px)} 30%{transform:translateX(7px)}
    45%{transform:translateX(-5px)} 60%{transform:translateX(5px)} 75%{transform:translateX(-3px)}
  }
  .shake { animation: authShake .38s ease; }
</style>
@endsection

@section('content')
<div class="auth-wrap">
<div class="auth-left">
  <a class="auth-logo" href="{{ url('/') }}">АфишаКолыма</a>
  <div class="auth-left-content">
    <div class="auth-tagline">Забыли пароль?<br><em>Восстановим</em></div>
    <div class="auth-sub" style="margin-top:14px">Введите email, указанный при регистрации — пришлём ссылку для создания нового пароля.</div>
  </div>
</div>

<div class="auth-right">

  <div id="panel-form" class="page-enter">
    <div class="auth-head">
      <div class="auth-title">Восстановление пароля</div>
      <div class="auth-subtitle">Укажите email вашего аккаунта — пришлём инструкцию на почту.</div>
    </div>
    <div class="auth-form">
      <div class="field">
        <label class="field-label">Email</label>
        <div class="field-wrap" id="wrap-email">
          <span class="field-ico">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
          </span>
          <input class="form-control" id="inp-email" type="email" placeholder="example@mail.ru"
            autocomplete="email" oninput="onEmailInput(this)" onkeydown="if(event.key==='Enter')doSend()">
        </div>
        <div class="field-hint" id="hint-email"></div>
      </div>
      <button class="btn btn-primary btn-full btn-lg" id="send-btn" onclick="doSend()">Отправить ссылку</button>
    </div>
    <div class="auth-switch"><a href="{{ url('/login') }}">← Вернуться к входу</a></div>
  </div>

  <div id="panel-success" style="display:none" class="page-enter">
    <div style="text-align:center;padding:24px 0">
      <div style="font-size:3rem;margin-bottom:16px">📬</div>
      <div style="font-weight:700;font-size:1.1rem;margin-bottom:10px">Письмо отправлено</div>
      <div style="color:var(--muted);font-size:.9rem;margin-bottom:24px;line-height:1.6">
        Если аккаунт с таким email существует — письмо со ссылкой уже в пути.<br>
        Проверьте папку «Спам», если не нашли в основной.
      </div>
      <a href="{{ url('/login') }}" class="btn btn-primary">Вернуться к входу</a>
    </div>
  </div>

</div>
</div>
@endsection

@section('scripts')
<script>
function onEmailInput(el) {
  const wrap = document.getElementById('wrap-email');
  const hint = document.getElementById('hint-email');
  wrap.classList.remove('field-error', 'field-success');
  hint.textContent = '';
  if (el.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(el.value)) {
    wrap.classList.add('field-error');
    hint.textContent = 'Введите корректный email';
  }
}

function setBtnLoading(on) {
  const btn = document.getElementById('send-btn');
  if (on) {
    btn.dataset.orig = btn.textContent; btn.disabled = true;
    btn.innerHTML = `<span style="display:flex;align-items:center;justify-content:center;gap:8px"><svg style="animation:spin 1s linear infinite" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10" stroke-opacity=".2"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>Отправляем...</span>`;
  } else {
    btn.disabled = false; btn.textContent = btn.dataset.orig;
  }
}

async function doSend() {
  const email = document.getElementById('inp-email').value.trim();
  const wrap  = document.getElementById('wrap-email');
  const hint  = document.getElementById('hint-email');

  if (!email) {
    wrap.classList.add('field-error'); hint.textContent = 'Введите email'; return;
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    wrap.classList.add('field-error'); hint.textContent = 'Введите корректный email'; return;
  }

  setBtnLoading(true);
  try {
    await api('POST', '/auth/forgot-password', { email });
    document.getElementById('panel-form').style.display = 'none';
    document.getElementById('panel-success').style.display = '';
  } catch(e) {
    setBtnLoading(false);
    wrap.classList.add('field-error');
    hint.textContent = e.message || 'Ошибка отправки';
  }
}
</script>
@endsection
