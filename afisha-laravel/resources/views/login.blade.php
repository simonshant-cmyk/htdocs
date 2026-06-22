@extends('layouts.auth')

@section('title', 'Вход — АфишаКолыма')

@section('styles')
<style>
  body { display: flex; flex-direction: column; min-height: 100vh; }
  .auth-wrap { display: flex; flex: 1; }

  /* ── Left panel ── */
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
    position: absolute; top: 44px; left: 56px; z-index: 2;
    transition: opacity .2s;
  }
  .auth-logo:hover { opacity: .8; }

  .auth-left-content { position: relative; z-index: 2; }
  .auth-tagline { font-size: 2.6rem; font-weight: 700; color: #fff; line-height: 1.15; margin-bottom: 14px; }
  .auth-tagline em { font-family: var(--font-display); font-style: italic; color: rgba(255,255,255,.5); }
  .auth-sub { color: rgba(255,255,255,.48); font-size: .93rem; line-height: 1.7; max-width: 300px; margin-bottom: 36px; }
  .auth-stats { display: flex; gap: 32px; padding-top: 28px; border-top: 1px solid rgba(255,255,255,.1); }
  .auth-stat-num { display: block; font-weight: 700; font-size: 1.25rem; color: #fff; }
  .auth-stat-label { font-size: .72rem; color: rgba(255,255,255,.38); margin-top: 1px; }

  /* Floating event cards */
  .auth-cards { position: absolute; inset: 0; pointer-events: none; z-index: 1; }
  .auth-card {
    position: absolute; width: 178px;
    background: rgba(255,255,255,.07); backdrop-filter: blur(18px);
    border: 1px solid rgba(255,255,255,.12); border-radius: 16px;
    padding: 14px 16px; box-shadow: 0 12px 40px rgba(0,0,0,.28);
  }
  .auth-card-emoji { font-size: 1.4rem; margin-bottom: 8px; }
  .auth-card-tag { font-size: .65rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; color: rgba(255,255,255,.44); }
  .auth-card-title { font-weight: 700; color: #fff; font-size: .92rem; margin: 4px 0 3px; line-height: 1.2; }
  .auth-card-date { font-size: .72rem; color: rgba(255,255,255,.4); }
  .auth-card-price {
    display: inline-block; margin-top: 10px; font-size: .68rem; font-weight: 700;
    padding: 3px 9px; border-radius: 20px;
    background: rgba(200,80,42,.5); color: rgba(255,255,255,.92);
  }
  @keyframes float1 { 0%,100%{transform:rotate(5deg) translateY(0)} 50%{transform:rotate(5deg) translateY(-11px)} }
  @keyframes float2 { 0%,100%{transform:rotate(-4deg) translateY(0)} 50%{transform:rotate(-4deg) translateY(-9px)} }
  @keyframes float3 { 0%,100%{transform:rotate(3deg) translateY(0)} 50%{transform:rotate(3deg) translateY(-13px)} }
  .auth-card-1 { animation: float1 4.2s ease-in-out infinite; }
  .auth-card-2 { animation: float2 5.1s ease-in-out infinite .8s; }
  .auth-card-3 { animation: float3 4.7s ease-in-out infinite 1.6s; }

  /* ── Right panel ── */
  .auth-right {
    width: 500px; max-width: 100%; display: flex; flex-direction: column;
    justify-content: center; padding: 56px 48px;
    background: var(--bg); overflow-y: auto;
  }

  /* Tabs */
  .auth-tabs {
    display: flex; border: 1.5px solid var(--border);
    border-radius: 10px; padding: 3px; margin-bottom: 28px;
  }
  .auth-tab {
    flex: 1; padding: 9px; border-radius: 7px;
    font-size: .88rem; font-weight: 600; color: var(--muted);
    transition: var(--transition); text-align: center; cursor: pointer;
  }
  .auth-tab.active { background: var(--accent); color: #fff; box-shadow: 0 2px 10px rgba(200,80,42,.3); }

  .auth-head { margin-bottom: 24px; }
  .auth-title { font-family: var(--font-display); font-size: 1.85rem; font-weight: 700; margin-bottom: 5px; }
  .auth-subtitle { color: var(--muted); font-size: .88rem; }

  /* Form fields */
  .auth-form { display: flex; flex-direction: column; gap: 14px; }
  .field { display: flex; flex-direction: column; gap: 5px; }
  .field-label { font-size: .72rem; font-weight: 700; color: var(--muted); letter-spacing: .06em; text-transform: uppercase; }
  .field-wrap { position: relative; display: flex; align-items: center; }
  .field-ico {
    position: absolute; left: 14px; color: var(--muted);
    display: flex; align-items: center; pointer-events: none;
    transition: color .15s;
  }
  .field-wrap:focus-within .field-ico { color: var(--accent); }
  .field-wrap .form-control { padding-left: 42px; }
  .field-wrap .form-control.has-eye { padding-right: 44px; }
  .field-eye {
    position: absolute; right: 11px; background: none; border: none;
    color: var(--muted); cursor: pointer; padding: 5px; line-height: 0;
    border-radius: 6px; transition: var(--transition);
  }
  .field-eye:hover { color: var(--text); background: var(--bg2); }
  .field-wrap.field-error .form-control   { border-color: #ef4444 !important; box-shadow: 0 0 0 3px rgba(239,68,68,.1) !important; }
  .field-wrap.field-success .form-control { border-color: #22c55e !important; }
  .field-wrap.field-error  .field-ico { color: #ef4444; }
  .field-wrap.field-success .field-ico { color: #22c55e; }
  .field-hint { font-size: .72rem; min-height: 16px; margin-top: 2px; color: #ef4444; line-height: 1.3; }
  .field-hint.ok { color: #22c55e; }

  /* ── Step progress ── */
  .reg-progress {
    display: flex; align-items: center; margin-bottom: 28px; padding-bottom: 2px;
  }
  .reg-step-dot {
    width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center;
    justify-content: center; font-size: .75rem; font-weight: 800; flex-shrink: 0;
    border: 2px solid var(--border); background: var(--bg2); color: var(--muted);
    transition: all .25s ease; position: relative; z-index: 1;
  }
  .reg-step-dot.active {
    border-color: var(--accent); background: var(--accent); color: #fff;
    box-shadow: 0 0 0 4px rgba(200,80,42,.15);
  }
  .reg-step-dot.done { border-color: #22c55e; background: #22c55e; color: #fff; }
  .reg-step-label {
    position: absolute; top: 34px; left: 50%; transform: translateX(-50%);
    font-size: .6rem; font-weight: 700; color: var(--muted); white-space: nowrap;
    letter-spacing: .04em; text-transform: uppercase;
  }
  .reg-step-dot.active .reg-step-label { color: var(--accent); }
  .reg-step-dot.done  .reg-step-label { color: #22c55e; }
  .reg-step-line { flex: 1; height: 2px; background: var(--border); margin: 0 6px; transition: background .3s; }
  .reg-step-line.done { background: #22c55e; }

  /* Step containers */
  .reg-step { display: none; flex-direction: column; gap: 14px; }
  .reg-step.active { display: flex; }
  .reg-step-nav { display: flex; gap: 10px; margin-top: 4px; }
  .reg-btn-back {
    padding: 12px 18px; border-radius: var(--radius-sm);
    background: var(--bg2); border: 1.5px solid var(--border);
    color: var(--muted); font-family: var(--font); font-size: .9rem;
    font-weight: 600; cursor: pointer; transition: var(--transition);
    display: flex; align-items: center; gap: 6px;
  }
  .reg-btn-back:hover { border-color: var(--accent); color: var(--accent); background: rgba(200,80,42,.04); }

  /* Divider */
  .divider {
    display: flex; align-items: center; gap: 12px;
    color: var(--muted); font-size: .76rem; margin: 6px 0;
  }
  .divider::before, .divider::after { content:''; flex:1; height:1px; background: var(--border); }

  /* Social buttons — 2-column grid with labels */
  .social-btns { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
  .social-btn {
    display: flex; align-items: center; gap: 10px; padding: 11px 14px;
    border-radius: 12px; border: 1.5px solid var(--border); background: var(--surface);
    cursor: pointer; transition: all .2s; font-family: var(--font);
    font-size: .83rem; font-weight: 600; color: var(--text); text-align: left;
    position: relative; overflow: hidden;
  }
  .social-btn::after {
    content: ''; position: absolute; inset: 0; opacity: 0;
    background: linear-gradient(135deg, rgba(200,80,42,.06) 0%, transparent 100%);
    transition: opacity .2s;
  }
  .social-btn:hover { border-color: rgba(200,80,42,.4); transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.1); }
  .social-btn:hover::after { opacity: 1; }
  .social-btn:active { transform: translateY(0); box-shadow: none; }
  .social-btn-icon {
    width: 28px; height: 28px; border-radius: 8px; display: flex;
    align-items: center; justify-content: center; flex-shrink: 0;
    transition: transform .2s;
  }
  .social-btn:hover .social-btn-icon { transform: scale(1.08); }
  .social-btn-full { grid-column: span 2; }

  /* ── Кнопка «Войти с VK ID» (стиль VK) ── */
  .vkid-btn {
    display: flex; align-items: center; justify-content: center; gap: 10px;
    width: 100%; height: 44px; border: none; border-radius: 8px;
    background: #2787F5; color: #fff; cursor: pointer;
    font-family: var(--font); font-size: .95rem; font-weight: 600;
    transition: background .15s, transform .15s;
  }
  .vkid-btn:hover  { background: #1b78e6; transform: translateY(-1px); }
  .vkid-btn:active { transform: translateY(0); }
  .vkid-btn svg { width: 20px; height: 20px; flex-shrink: 0; }

  .auth-switch { text-align: center; font-size: .84rem; color: var(--muted); margin-top: 18px; }
  .auth-switch a { color: var(--accent); font-weight: 600; text-decoration: none; }
  .auth-switch a:hover { text-decoration: underline; }

  .pass-bar { height: 3px; background: var(--border); border-radius: 2px; overflow: hidden; margin: 6px 0 2px; }
  .pass-bar-fill { height: 100%; border-radius: 2px; transition: width .3s, background .3s; width: 0; }
  .pass-strength-lbl { font-size: .7rem; min-height: 1em; }
  .pass-req { overflow: hidden; }
  .req-row { display: flex; flex-direction: column; gap: 3px; padding: 4px 0 2px; }
  .req-item { display: flex; align-items: center; gap: 6px; font-size: .72rem; color: var(--muted); transition: color .15s; }
  .req-item.ok { color: #22c55e; }
  .req-item .ri { width: 12px; text-align: center; display: inline-block; }

  .caps-warn { font-size: .7rem; color: #f97316; margin-top: 2px; display: none; }
  .caps-warn.show { display: block; }
  .forgot-row { text-align: right; margin-top: -4px; }
  .forgot-row a { font-size: .8rem; color: var(--muted); text-decoration: none; }
  .forgot-row a:hover { color: var(--accent); }

  /* ── Login method toggle ── */
  .login-method-field { transition: opacity .22s ease; }
  .login-method-field.lm-hidden { display: none; }
  .login-method-field.lm-fading { opacity: 0; pointer-events: none; }
  .login-method-link {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: .75rem; color: var(--muted); cursor: pointer;
    border: none; background: none; padding: 0;
    font-family: var(--font); margin-top: 4px;
    transition: color .15s;
  }
  .login-method-link:hover { color: var(--accent); }
  .login-method-link svg { flex-shrink: 0; }

  @media (max-width: 860px) {
    .auth-wrap { flex-direction: column; }
    .auth-left { flex: none; min-height: 200px; padding: 80px 24px 32px; }
    .auth-logo { top: 28px; left: 24px; }
    .auth-cards { display: none; }
    .auth-tagline { font-size: 1.8rem; margin-bottom: 8px; }
    .auth-sub { display: none; }
    .auth-stats { gap: 20px; padding-top: 20px; }
    .auth-right { width: 100%; padding: 32px 20px; }
    .social-btns { grid-template-columns: 1fr; }
    .social-btn-full { grid-column: span 1; }
  }

  @keyframes authShake {
    0%,100%{transform:translateX(0)}
    15%{transform:translateX(-7px)} 30%{transform:translateX(7px)}
    45%{transform:translateX(-5px)} 60%{transform:translateX(5px)}
    75%{transform:translateX(-3px)}
  }
  .shake { animation: authShake .38s ease; }
  @keyframes spin { to { transform: rotate(360deg); } }
</style>
@endsection

@section('content')
<div class="auth-wrap">

<!-- ── LEFT PANEL ── -->
<div class="auth-left">
  <a class="auth-logo" href="{{ url('/') }}">АфишаКолыма</a>

  <div class="auth-cards">
    <div class="auth-card auth-card-1" style="top:13%;right:28px">
      <div class="auth-card-emoji">🎵</div>
      <div class="auth-card-tag">Концерт</div>
      <div class="auth-card-title">День города</div>
      <div class="auth-card-date">Магадан · Колыма</div>
      <span class="auth-card-price">от 500 ₽</span>
    </div>
    <div class="auth-card auth-card-2" style="top:41%;right:60px">
      <div class="auth-card-emoji">🎨</div>
      <div class="auth-card-tag">Выставка</div>
      <div class="auth-card-title">Арт-пространство</div>
      <div class="auth-card-date">до 30 июня</div>
      <span class="auth-card-price">Бесплатно</span>
    </div>
    <div class="auth-card auth-card-3" style="top:67%;right:16px">
      <div class="auth-card-emoji">🎪</div>
      <div class="auth-card-tag">Фестиваль</div>
      <div class="auth-card-title">Колымская весна</div>
      <div class="auth-card-date">1–3 июля</div>
      <span class="auth-card-price">от 900 ₽</span>
    </div>
  </div>

  <div class="auth-left-content">
    <div class="auth-tagline">Открывай<br><em>новые места</em><br>и события</div>
    <p class="auth-sub">Концерты, выставки, фестивали — всё в одном месте. Найди своё событие сегодня.</p>
    <div class="auth-stats">
      <div><span class="auth-stat-num">500+</span><span class="auth-stat-label">событий</span></div>
      <div><span class="auth-stat-num">50+</span><span class="auth-stat-label">площадок</span></div>
      <div><span class="auth-stat-num">1к+</span><span class="auth-stat-label">билетов</span></div>
    </div>
  </div>
</div>

<!-- ── RIGHT PANEL ── -->
<div class="auth-right">

  <div class="auth-tabs">
    <div class="auth-tab active" id="tab-user" onclick="switchType('user')">Пользователь</div>
    <div class="auth-tab" id="tab-org" onclick="switchType('org')">Организация</div>
  </div>

  <!-- ВХОД -->
  <div id="panel-login" class="page-enter">
    <div class="auth-head">
      <div class="auth-title">Добро пожаловать</div>
      <div class="auth-subtitle">Войдите в свой аккаунт</div>
    </div>

    <div class="auth-form" id="form-login">
      <div class="login-method-wrap">
        <!-- Поле телефона -->
        <div class="login-method-field" id="lm-phone">
          <div class="field">
            <label class="field-label" id="lbl-login-phone">Телефон</label>
            <div class="field-wrap" id="wrap-login-phone">
              <span class="field-ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.18 1.18 2 2 0 012 .84h3a2 2 0 012 1.72c.13.96.36 1.9.7 2.81a2 2 0 01-.45 2.11L6.91 8.18a16 16 0 006.91 6.91l1.27-1.27a2 2 0 012.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0122 16.92z"/></svg></span>
              <input class="form-control" id="login-phone" type="tel" placeholder="+7 (___) ___-__-__"
                onblur="blurLogin('phone')" onkeydown="enterKey(event,'doLogin')">
            </div>
            <button class="login-method-link" type="button" onclick="toggleLoginMethod()" id="switch-to-email">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              Войти по email
            </button>
            <div class="field-hint" id="hint-login-phone"></div>
          </div>
        </div>

        <!-- Поле email -->
        <div class="login-method-field lm-hidden" id="lm-email">
          <div class="field">
            <label class="field-label">Email</label>
            <div class="field-wrap" id="wrap-login-email">
              <span class="field-ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>
              <input class="form-control" id="login-email" type="email" placeholder="your@email.com"
                onblur="blurLogin('email')" onkeydown="enterKey(event,'doLogin')">
            </div>
            <button class="login-method-link" type="button" onclick="toggleLoginMethod()" id="switch-to-phone">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.18 1.18 2 2 0 012 .84h3a2 2 0 012 1.72c.13.96.36 1.9.7 2.81a2 2 0 01-.45 2.11L6.91 8.18a16 16 0 006.91 6.91l1.27-1.27a2 2 0 012.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0122 16.92z"/></svg>
              Войти по номеру телефона
            </button>
            <div class="field-hint" id="hint-login-email"></div>
          </div>
        </div>
      </div>

      <div class="field">
        <label class="field-label">Пароль</label>
        <div class="field-wrap" id="wrap-login-password">
          <span class="field-ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></span>
          <input class="form-control has-eye" id="login-password" type="password" placeholder="••••••••"
            onkeyup="checkCaps(event,'caps-login')" onkeydown="enterKey(event,'doLogin')" autocomplete="current-password">
          <button class="field-eye" type="button" onclick="togglePass('login-password',this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <div class="caps-warn" id="caps-login">⚠ Caps Lock включён</div>
        <div class="field-hint" id="hint-login-password"></div>
      </div>

      <div class="forgot-row"><a href="#" onclick="forgotPass(event)">Забыли пароль?</a></div>
      <button class="btn btn-primary btn-full btn-lg" style="margin-top:4px" id="login-btn" onclick="doLogin()">Войти</button>
    </div>

    <div class="divider">или войдите через</div>
    <div class="social-btns" style="grid-template-columns:1fr">
      <button type="button" class="vkid-btn" onclick="loginOAuth('vk')">
        <svg viewBox="0 0 24 24" fill="#fff" aria-hidden="true"><path d="M21.579 6.855c.14-.465 0-.806-.666-.806h-2.193c-.559 0-.817.295-.957.621 0 0-1.116 2.727-2.698 4.497-.514.514-.746.677-1.026.677-.14 0-.341-.163-.341-.627V6.855c0-.559-.163-.806-.626-.806H9.642c-.348 0-.558.258-.558.503 0 .528.791.65.872 2.138v3.228c0 .708-.127.838-.407.838-.745 0-2.558-2.737-3.63-5.872-.211-.611-.421-.857-.982-.857H2.742c-.628 0-.754.295-.754.62 0 .581.745 3.46 3.468 7.271 1.815 2.602 4.371 4.01 6.698 4.01 1.395 0 1.566-.313 1.566-.853v-1.97c0-.627.132-.752.573-.752.325 0 .883.162 2.184 1.42 1.488 1.487 1.733 2.157 2.571 2.157h2.193c.628 0 .942-.313.761-.933-.198-.616-.912-1.509-1.857-2.569-.514-.606-1.283-1.258-1.515-1.583-.324-.418-.231-.605 0-.977 0 0 2.681-3.775 2.96-5.061z"/></svg>
        Войти с VK ID
      </button>
    </div>

    <div class="auth-switch">Нет аккаунта? <a href="#" onclick="showRegister()">Зарегистрироваться</a></div>
  </div>

  <!-- ВОССТАНОВЛЕНИЕ ПАРОЛЯ -->
  <div id="panel-forgot" style="display:none" class="page-enter">
    <div class="auth-head">
      <div class="auth-title">Забыли пароль?</div>
      <div class="auth-subtitle">Введите email — пришлём ссылку для сброса</div>
    </div>
    <div class="auth-form" id="form-forgot">
      <div class="field">
        <label class="field-label">Email</label>
        <div class="field-wrap" id="wrap-forgot-email">
          <span class="field-ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>
          <input class="form-control" id="forgot-email" type="email" placeholder="your@email.com"
            onkeydown="enterKey(event,'doForgot')">
        </div>
        <div class="field-hint" id="hint-forgot-email"></div>
      </div>
      <button class="btn btn-primary btn-full btn-lg" id="forgot-btn" onclick="doForgot()">Отправить ссылку</button>
    </div>
    <div class="auth-switch" style="margin-top:16px"><a href="#" onclick="showLogin()">← Назад к входу</a></div>
  </div>

  <!-- РЕГИСТРАЦИЯ (3 шага) -->
  <div id="panel-register" style="display:none">
    <div class="auth-head">
      <div class="auth-title" id="reg-step-title">Ваши данные</div>
      <div class="auth-subtitle" id="reg-subtitle">Создайте аккаунт пользователя</div>
    </div>

    <!-- Progress bar -->
    <div class="reg-progress" style="margin-bottom:36px">
      <div class="reg-step-dot active" id="dot-1">
        <span class="dot-num">1</span>
        <span class="reg-step-label">Данные</span>
      </div>
      <div class="reg-step-line" id="line-1"></div>
      <div class="reg-step-dot" id="dot-2">
        <span class="dot-num">2</span>
        <span class="reg-step-label">Контакты</span>
      </div>
      <div class="reg-step-line" id="line-2"></div>
      <div class="reg-step-dot" id="dot-3">
        <span class="dot-num">3</span>
        <span class="reg-step-label">Пароль</span>
      </div>
    </div>

    <div id="form-register">

      <!-- ШАГ 1 -->
      <div class="reg-step active" id="step-1">
        <!-- user fields -->
        <div id="s1-lastname-group">
          <div class="field">
            <label class="field-label">Фамилия</label>
            <div class="field-wrap" id="wrap-reg-lastname">
              <span class="field-ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
              <input class="form-control" id="reg-lastname" placeholder="Иванов"
                oninput="onNamePartInput(this,'reg-lastname')" onblur="blurReg('reg-lastname')">
            </div>
            <div class="field-hint" id="hint-reg-lastname"></div>
          </div>
        </div>
        <div id="s1-firstname-group">
          <div class="field">
            <label class="field-label">Имя</label>
            <div class="field-wrap" id="wrap-reg-firstname">
              <span class="field-ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
              <input class="form-control" id="reg-firstname" placeholder="Иван"
                oninput="onNamePartInput(this,'reg-firstname')" onblur="blurReg('reg-firstname')">
            </div>
            <div class="field-hint" id="hint-reg-firstname"></div>
          </div>
        </div>
        <div id="s1-patronymic-group">
          <div class="field">
            <label class="field-label">Отчество <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--muted)">(необязательно)</span></label>
            <div class="field-wrap" id="wrap-reg-patronymic">
              <span class="field-ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
              <input class="form-control" id="reg-patronymic" placeholder="Петрович"
                oninput="onNamePartInput(this,'reg-patronymic')" onblur="blurReg('reg-patronymic')">
            </div>
            <div class="field-hint" id="hint-reg-patronymic"></div>
          </div>
        </div>
        <!-- org fields -->
        <div id="s1-name-group" style="display:none">
          <div class="field">
            <label class="field-label">Название организации</label>
            <div class="field-wrap" id="wrap-reg-name">
              <span class="field-ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg></span>
              <input class="form-control" id="reg-name" placeholder="ООО Ромашка"
                oninput="onNameInput(this)" onblur="blurReg('reg-name')">
            </div>
            <div class="field-hint" id="hint-reg-name"></div>
          </div>
        </div>
        <div id="s1-inn-group" style="display:none">
          <div class="field">
            <label class="field-label">ИНН <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--muted)">10 или 12 цифр</span></label>
            <div class="field-wrap" id="wrap-reg-inn">
              <span class="field-ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg></span>
              <input class="form-control" id="reg-inn" placeholder="123456789012" inputmode="numeric"
                oninput="onInnInput(this)" onblur="blurReg('reg-inn')">
            </div>
            <div class="field-hint" id="hint-reg-inn"></div>
          </div>
        </div>

        <button class="btn btn-primary btn-full btn-lg" style="margin-top:6px" onclick="nextStep()">Далее →</button>
        <div class="auth-switch" style="margin-top:12px">Уже есть аккаунт? <a href="#" onclick="showLogin()">Войти</a></div>
      </div>

      <!-- ШАГ 2 -->
      <div class="reg-step" id="step-2">
        <div id="s2-phone-group">
          <div class="field">
            <label class="field-label">Телефон</label>
            <div class="field-wrap" id="wrap-reg-phone">
              <span class="field-ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.18 1.18 2 2 0 012 .84h3a2 2 0 012 1.72c.13.96.36 1.9.7 2.81a2 2 0 01-.45 2.11L6.91 8.18a16 16 0 006.91 6.91l1.27-1.27a2 2 0 012.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0122 16.92z"/></svg></span>
              <input class="form-control" id="reg-phone" type="tel" placeholder="+7 (___) ___-__-__"
                onblur="blurReg('reg-phone')">
            </div>
            <div class="field-hint" id="hint-reg-phone"></div>
          </div>
        </div>
        <div class="field">
          <label class="field-label" id="lbl-reg-email">Email <span id="email-opt" style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--muted)">(необязательно)</span></label>
          <div class="field-wrap" id="wrap-reg-email">
            <span class="field-ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>
            <input class="form-control" id="reg-email" type="email" placeholder="email@example.com"
              onblur="blurReg('reg-email')">
          </div>
          <div class="field-hint" id="hint-reg-email"></div>
        </div>
        <div id="s2-dob-group">
          <div class="field">
            <label class="field-label">Дата рождения <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--muted)">(необязательно)</span></label>
            <div class="field-wrap" id="wrap-reg-dob">
              <span class="field-ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></span>
              <input class="form-control" id="reg-dob" type="date" onblur="blurReg('reg-dob')">
            </div>
            <div class="field-hint" id="hint-reg-dob"></div>
          </div>
        </div>

        <div class="reg-step-nav">
          <button class="reg-btn-back" onclick="prevStep()">← Назад</button>
          <button class="btn btn-primary btn-lg" style="flex:1" onclick="nextStep()">Далее →</button>
        </div>
      </div>

      <!-- ШАГ 3 -->
      <div class="reg-step" id="step-3">
        <div class="field">
          <label class="field-label">Пароль</label>
          <div class="field-wrap" id="wrap-reg-password">
            <span class="field-ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></span>
            <input class="form-control has-eye" id="reg-password" type="password" placeholder="Минимум 8 символов"
              oninput="onPassInput(this)" onkeyup="checkCaps(event,'caps-reg')" autocomplete="new-password">
            <button class="field-eye" type="button" onclick="togglePass('reg-password',this)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
          <div class="caps-warn" id="caps-reg">⚠ Caps Lock включён</div>
          <div id="pass-strength-wrap">
            <div class="pass-bar"><div class="pass-bar-fill" id="pass-fill"></div></div>
            <div class="pass-strength-lbl" id="pass-strength-lbl"></div>
          </div>
          <div class="pass-req" id="pass-req">
            <div class="req-row">
              <div class="req-item" id="req-len"><span class="ri">–</span> Минимум 8 символов</div>
              <div class="req-item" id="req-letter"><span class="ri">–</span> Содержит букву</div>
              <div class="req-item" id="req-digit"><span class="ri">–</span> Содержит цифру</div>
            </div>
          </div>
          <div class="field-hint" id="hint-reg-password"></div>
        </div>

        <div class="field">
          <label class="field-label">Повторите пароль</label>
          <div class="field-wrap" id="wrap-reg-confirm">
            <span class="field-ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></span>
            <input class="form-control has-eye" id="reg-confirm" type="password" placeholder="Ещё раз пароль"
              oninput="onConfirmInput(this)" onkeyup="checkCaps(event,'caps-confirm')" autocomplete="new-password">
            <button class="field-eye" type="button" onclick="togglePass('reg-confirm',this)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
          <div class="caps-warn" id="caps-confirm">⚠ Caps Lock включён</div>
          <div class="field-hint" id="hint-reg-confirm"></div>
        </div>

        <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:.83rem;color:var(--muted);line-height:1.5">
          <input type="checkbox" id="reg-consent" style="margin-top:2px;flex-shrink:0;accent-color:var(--accent);width:15px;height:15px">
          <span>Я согласен на обработку <a href="#" onclick="event.preventDefault();toast('Политика конфиденциальности — в разработке','success')" style="color:var(--accent)">персональных данных</a> согласно ФЗ № 152</span>
        </label>
        <div class="field-hint" id="hint-reg-consent"></div>

        <div class="reg-step-nav">
          <button class="reg-btn-back" onclick="prevStep()">← Назад</button>
          <button class="btn btn-primary btn-lg" style="flex:1" id="reg-btn" onclick="doRegister()">Создать аккаунт</button>
        </div>
      </div>

    </div><!-- /form-register -->

    <div class="divider" style="margin-top:16px">или зарегистрируйтесь через</div>
    <div class="social-btns" style="grid-template-columns:1fr">
      <button type="button" class="vkid-btn" onclick="loginOAuth('vk')">
        <svg viewBox="0 0 24 24" fill="#fff" aria-hidden="true"><path d="M21.579 6.855c.14-.465 0-.806-.666-.806h-2.193c-.559 0-.817.295-.957.621 0 0-1.116 2.727-2.698 4.497-.514.514-.746.677-1.026.677-.14 0-.341-.163-.341-.627V6.855c0-.559-.163-.806-.626-.806H9.642c-.348 0-.558.258-.558.503 0 .528.791.65.872 2.138v3.228c0 .708-.127.838-.407.838-.745 0-2.558-2.737-3.63-5.872-.211-.611-.421-.857-.982-.857H2.742c-.628 0-.754.295-.754.62 0 .581.745 3.46 3.468 7.271 1.815 2.602 4.371 4.01 6.698 4.01 1.395 0 1.566-.313 1.566-.853v-1.97c0-.627.132-.752.573-.752.325 0 .883.162 2.184 1.42 1.488 1.487 1.733 2.157 2.571 2.157h2.193c.628 0 .942-.313.761-.933-.198-.616-.912-1.509-1.857-2.569-.514-.606-1.283-1.258-1.515-1.583-.324-.418-.231-.605 0-.977 0 0 2.681-3.775 2.96-5.061z"/></svg>
        Войти с VK ID
      </button>
    </div>

  </div><!-- /panel-register -->

</div><!-- /auth-right -->
</div><!-- /auth-wrap -->
@endsection

@section('scripts')
<script>
let currentType = 'user';
let loginMethod = 'phone'; // 'phone' | 'email'
let regStep = 1;

if (auth.isLoggedIn()) {
  nav(auth.type() === 'org' ? '/org/cabinet' : '/cabinet');
}

if (new URLSearchParams(location.search).get('register') === '1') {
  document.getElementById('panel-login').style.display = 'none';
  document.getElementById('panel-register').style.display = '';
}

// ── Step management ──
const STEP_TITLES = ['Ваши данные', 'Контакты', 'Пароль и безопасность'];
const CHECK_SVG = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

function goStep(n) {
  regStep = n;
  document.querySelectorAll('.reg-step').forEach((el, i) => el.classList.toggle('active', i + 1 === n));
  for (let i = 1; i <= 3; i++) {
    const dot = document.getElementById('dot-' + i);
    if (!dot) continue;
    dot.classList.remove('active', 'done');
    if (i < n) { dot.classList.add('done'); dot.querySelector('.dot-num').innerHTML = CHECK_SVG; }
    else if (i === n) { dot.classList.add('active'); dot.querySelector('.dot-num').textContent = i; }
    else { dot.querySelector('.dot-num').textContent = i; }
    if (i < 3) {
      const line = document.getElementById('line-' + i);
      if (line) line.classList.toggle('done', i < n);
    }
  }
  const titles = { user: STEP_TITLES, org: ['Организация', 'Контакты', 'Пароль и безопасность'] };
  const t = titles[currentType] || STEP_TITLES;
  document.getElementById('reg-step-title').textContent = t[n - 1] || '';
}

function nextStep() {
  if (!validateStep(regStep)) return;
  if (regStep < 3) goStep(regStep + 1);
}

function prevStep() {
  if (regStep > 1) goStep(regStep - 1);
  else showLogin();
}

function validateStep(step) {
  const errors = [];
  const check = (id, err) => {
    if (err) { setField(id, 'error', err); errors.push(id); }
    else { const el = document.getElementById(id); if (el?.value?.trim()) setField(id, 'success', ''); }
  };

  if (step === 1) {
    if (currentType === 'user') {
      check('reg-lastname',  vNamePart(document.getElementById('reg-lastname')?.value || '', true));
      check('reg-firstname', vNamePart(document.getElementById('reg-firstname')?.value || '', true));
      const patErr = vNamePart(document.getElementById('reg-patronymic')?.value || '', false);
      if (patErr) { setField('reg-patronymic', 'error', patErr); errors.push('reg-patronymic'); }
    } else {
      check('reg-name', vName(document.getElementById('reg-name')?.value || ''));
      check('reg-inn',  vInn(document.getElementById('reg-inn')?.value || ''));
    }
  } else if (step === 2) {
    if (currentType === 'user') {
      check('reg-phone', vPhone(document.getElementById('reg-phone')?.value || ''));
    }
    const emailErr = vEmail(document.getElementById('reg-email')?.value || '', currentType === 'org');
    if (emailErr) { setField('reg-email', 'error', emailErr); errors.push('reg-email'); }
    else if (document.getElementById('reg-email')?.value?.trim()) setField('reg-email', 'success', '');
    if (currentType === 'user') {
      const dobErr = vDob(document.getElementById('reg-dob')?.value || '');
      if (dobErr) { setField('reg-dob', 'error', dobErr); errors.push('reg-dob'); }
    }
  } else if (step === 3) {
    const passVal = document.getElementById('reg-password')?.value || '';
    check('reg-password', vPass(passVal));
    check('reg-confirm',  vConfirm(passVal, document.getElementById('reg-confirm')?.value || ''));
    if (!document.getElementById('reg-consent')?.checked) {
      setField('reg-consent', 'error', 'Необходимо согласие на обработку данных');
      errors.push('reg-consent');
    }
  }

  if (errors.length) { shake('form-register'); document.getElementById(errors[0])?.focus(); return false; }
  return true;
}

// ── Login method toggle (телефон ↔ email для пользователя) ──
function setLoginMethod(method) {
  loginMethod = method;
  const hideId = method === 'email' ? 'lm-phone' : 'lm-email';
  const showId = method === 'email' ? 'lm-email' : 'lm-phone';
  const focusId = method === 'email' ? 'login-email' : 'login-phone';
  const hideEl = document.getElementById(hideId);
  const showEl = document.getElementById(showId);

  // Fade out → hide
  hideEl.classList.add('lm-fading');
  setTimeout(() => {
    hideEl.classList.add('lm-hidden');
    hideEl.classList.remove('lm-fading');
    // Show → fade in
    showEl.classList.remove('lm-hidden');
    showEl.classList.add('lm-fading');
    requestAnimationFrame(() => requestAnimationFrame(() => {
      showEl.classList.remove('lm-fading');
      document.getElementById(focusId)?.focus();
    }));
  }, 200);

  setField('login-phone', '', '');
  setField('login-email', '', '');
}

function toggleLoginMethod() {
  setLoginMethod(loginMethod === 'phone' ? 'email' : 'phone');
}

// ── Type switch ──
function switchType(type) {
  currentType = type;
  document.getElementById('tab-user').classList.toggle('active', type === 'user');
  document.getElementById('tab-org').classList.toggle('active',  type === 'org');

  // Step 1 fields
  document.getElementById('s1-lastname-group').style.display   = type === 'user' ? '' : 'none';
  document.getElementById('s1-firstname-group').style.display  = type === 'user' ? '' : 'none';
  document.getElementById('s1-patronymic-group').style.display = type === 'user' ? '' : 'none';
  document.getElementById('s1-name-group').style.display       = type === 'org'  ? '' : 'none';
  document.getElementById('s1-inn-group').style.display        = type === 'org'  ? '' : 'none';

  // Step 2 fields
  document.getElementById('s2-phone-group').style.display = type === 'user' ? '' : 'none';
  document.getElementById('s2-dob-group').style.display   = type === 'user' ? '' : 'none';
  const opt = document.getElementById('email-opt');
  if (opt) opt.textContent = type === 'org' ? '' : '(необязательно)';

  // Login fields: орг всегда по email, пользователь — по текущему методу
  if (type === 'org') {
    document.getElementById('lm-phone').classList.add('lm-hidden');
    document.getElementById('lm-email').classList.remove('lm-hidden');
    loginMethod = 'email';
  } else {
    // вернуть телефон для пользователя мгновенно (без анимации при смене таба)
    document.getElementById('lm-email').classList.add('lm-hidden');
    document.getElementById('lm-phone').classList.remove('lm-hidden');
    loginMethod = 'phone';
  }

  document.getElementById('reg-subtitle').textContent =
    type === 'user' ? 'Создайте аккаунт пользователя' : 'Зарегистрируйте организацию';

  // Reset
  document.querySelectorAll('.field-hint').forEach(el => el.textContent = '');
  document.querySelectorAll('.field-wrap').forEach(el => el.classList.remove('field-error','field-success'));
  goStep(1);

  const dob = document.getElementById('reg-dob');
  if (dob) { const m = new Date(); m.setFullYear(m.getFullYear()-14); dob.max = m.toISOString().slice(0,10); }
}

function showRegister() {
  document.getElementById('panel-login').style.display = 'none';
  document.getElementById('panel-register').style.display = '';
  goStep(1);
}
function showLogin() {
  document.getElementById('panel-register').style.display = 'none';
  document.getElementById('panel-forgot').style.display = 'none';
  document.getElementById('panel-login').style.display = '';
}

// ── Helpers ──
function togglePass(id, btn) {
  const inp = document.getElementById(id);
  const show = inp.type === 'password';
  inp.type = show ? 'text' : 'password';
  btn.innerHTML = show
    ? `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`
    : `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
}

function loginOAuth(service) {
  if (service === 'vk') {
    location.href = (window.APP_BASE || '') + '/auth/vk/redirect';
    return;
  }
  const names = { ok:'Одноклассники', yandex:'Яндекс', gosuslugi:'Госуслуги', max:'Макс' };
  toast('Вход через ' + (names[service] || service) + ' — скоро будет доступен', 'success');
}

// Показать ошибку, если VK-вход вернул её через query (?vk_error=...)
(function () {
  const vkErr = new URLSearchParams(location.search).get('vk_error');
  if (vkErr) toast(vkErr, 'error');
})();

function showForgot() {
  document.getElementById('panel-login').style.display = 'none';
  document.getElementById('panel-register').style.display = 'none';
  document.getElementById('panel-forgot').style.display = '';
  document.getElementById('forgot-email').focus();
}

function forgotPass(e) {
  e.preventDefault();
  showForgot();
}

async function doForgot() {
  const email = document.getElementById('forgot-email').value.trim();
  const err = vEmail(email, true);
  if (err) { setField('forgot-email', 'error', err); return; }
  setField('forgot-email', 'success', '');
  setBtnLoading('forgot-btn', true);
  try {
    await api('POST', '/auth/forgot-password', { email });
    document.getElementById('form-forgot').innerHTML = `
      <div style="text-align:center;padding:24px 0">
        <div style="font-size:2.5rem;margin-bottom:12px">📧</div>
        <div style="font-weight:700;font-size:1.05rem;margin-bottom:8px">Письмо отправлено</div>
        <div style="color:var(--muted);font-size:.88rem;line-height:1.6">Если аккаунт с email <strong>${escHtml(email)}</strong> существует, инструкция по сбросу пароля уже в пути.</div>
      </div>`;
  } catch(e) {
    setBtnLoading('forgot-btn', false);
    setField('forgot-email', 'error', e.message || 'Ошибка отправки');
  }
}

function enterKey(e, fn) { if (e.key === 'Enter') { e.preventDefault(); window[fn](); } }

function checkCaps(e, id) {
  const el = document.getElementById(id);
  if (el && e.getModifierState) el.classList.toggle('show', e.getModifierState('CapsLock'));
}

function setField(id, state, msg) {
  const wrap = document.getElementById('wrap-' + id);
  const hint = document.getElementById('hint-' + id);
  if (wrap) { wrap.classList.remove('field-error','field-success'); if (state) wrap.classList.add('field-' + state); }
  if (hint) { hint.textContent = msg || ''; hint.className = 'field-hint' + (state === 'success' ? ' ok' : ''); }
}

function shake(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('shake'); void el.offsetWidth; el.classList.add('shake');
}

function setBtnLoading(id, on) {
  const btn = document.getElementById(id);
  if (!btn) return;
  if (on) {
    btn.dataset.orig = btn.textContent; btn.disabled = true;
    btn.innerHTML = `<span style="display:flex;align-items:center;justify-content:center;gap:8px"><svg style="animation:spin 1s linear infinite;flex-shrink:0" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10" stroke-opacity=".2"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>Подождите...</span>`;
  } else {
    btn.disabled = false; btn.textContent = btn.dataset.orig || btn.textContent;
  }
}

// ── Validators ──
function vNamePart(v, required) {
  if (!v.trim()) return required ? 'Обязательное поле' : null;
  if (v.trim().length < 2) return 'Минимум 2 символа';
  if (/[\d!@#$%^&*()+={}\[\]|\\<>?/]/.test(v)) return 'Только буквы, пробел и дефис';
  if (v.trim().length > 100) return 'Слишком длинное';
  return null;
}
function vName(v) {
  if (!v.trim()) return 'Обязательное поле';
  if (v.trim().length < 2) return 'Слишком короткое (мин. 2 символа)';
  if (v.trim().length > 200) return 'Слишком длинное';
  return null;
}
function vPhone(v) {
  const d = v.replace(/\D/g, '');
  if (!d) return 'Обязательное поле';
  if (d.length !== 11) return 'Введите полный номер (11 цифр)';
  return null;
}
function vEmail(v, required) {
  if (!v.trim()) return required ? 'Обязательное поле' : null;
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim())) return 'Некорректный email';
  return null;
}
function vDob(v) {
  if (!v) return null;
  const d = new Date(v), now = new Date();
  if (isNaN(d)) return 'Некорректная дата';
  const age = (now - d) / (365.25 * 24 * 3600 * 1000);
  if (age < 14) return 'Минимальный возраст — 14 лет';
  if (age > 110) return 'Проверьте дату рождения';
  return null;
}
function vInn(v) {
  if (!v.trim()) return 'Обязательное поле';
  if (!/^\d+$/.test(v)) return 'ИНН содержит только цифры';
  if (v.length !== 10 && v.length !== 12) return `ИНН — 10 или 12 цифр (сейчас: ${v.length})`;
  return null;
}
function vPass(v) {
  if (!v) return 'Обязательное поле';
  if (v.length < 8) return 'Минимум 8 символов';
  if (!/[a-zA-Zа-яёА-ЯЁ]/.test(v)) return 'Добавьте хотя бы одну букву';
  if (!/\d/.test(v)) return 'Добавьте хотя бы одну цифру';
  return null;
}
function vConfirm(pass, v) {
  if (!v) return 'Повторите пароль';
  if (pass !== v) return 'Пароли не совпадают';
  return null;
}

// ── Input handlers ──
function onNamePartInput(el, id) {
  const required = id !== 'reg-patronymic';
  const err = vNamePart(el.value, required);
  if (el.value.length > 1) setField(id, err ? 'error' : 'success', err || '');
  else setField(id, '', '');
}
function onNameInput(el) {
  const err = vName(el.value);
  if (el.value.length > 1) setField('reg-name', err ? 'error' : 'success', err || '');
  else setField('reg-name', '', '');
}
function onInnInput(el) {
  el.value = el.value.replace(/[^\d]/g, '').slice(0, 12);
  if (!el.value) { setField('reg-inn', '', ''); return; }
  const err = vInn(el.value);
  setField('reg-inn', err ? 'error' : 'success', err || '');
}
function onPassInput(el) {
  updateStrength(el.value);
  const err = vPass(el.value);
  if (el.value) setField('reg-password', err ? 'error' : 'success', err || '');
  else setField('reg-password', '', '');
  const conf = document.getElementById('reg-confirm');
  if (conf && conf.value) onConfirmInput(conf);
}
function onConfirmInput(el) {
  const pass = document.getElementById('reg-password')?.value || '';
  const err = vConfirm(pass, el.value);
  if (el.value) setField('reg-confirm', err ? 'error' : 'success', err || '');
  else setField('reg-confirm', '', '');
}

function blurReg(id) {
  const el = document.getElementById(id);
  if (!el) return;
  const fns = {
    'reg-lastname':   () => vNamePart(el.value, true),
    'reg-firstname':  () => vNamePart(el.value, true),
    'reg-patronymic': () => vNamePart(el.value, false),
    'reg-name':       () => vName(el.value),
    'reg-phone':      () => vPhone(el.value),
    'reg-email':      () => vEmail(el.value, currentType === 'org'),
    'reg-dob':        () => vDob(el.value),
    'reg-inn':        () => vInn(el.value),
    'reg-password':   () => vPass(el.value),
    'reg-confirm':    () => vConfirm(document.getElementById('reg-password')?.value || '', el.value),
  };
  if (!fns[id]) return;
  if (!el.value.trim() && id !== 'reg-dob') return;
  const err = fns[id]();
  if (err) setField(id, 'error', err);
  else if (el.value.trim()) setField(id, 'success', '');
}

function blurLogin(type) {
  if (type === 'phone') {
    const v = document.getElementById('login-phone')?.value || '';
    const err = vPhone(v);
    if (v.replace(/\D/g,'').length > 1) setField('login-phone', err ? 'error' : 'success', err || '');
  } else {
    const v = document.getElementById('login-email')?.value || '';
    const err = vEmail(v, true);
    if (v) setField('login-email', err ? 'error' : 'success', err || '');
  }
}

function updateStrength(val) {
  const fill = document.getElementById('pass-fill');
  const lbl  = document.getElementById('pass-strength-lbl');
  if (!fill) return;
  if (!val) {
    fill.style.width = '0'; fill.style.background = '';
    if (lbl) { lbl.textContent = ''; lbl.style.color = ''; }
    const neutral = (id, text) => {
      const el = document.getElementById(id); if (!el) return;
      el.classList.remove('ok');
      el.innerHTML = `<span class="ri">–</span> ${text}`;
    };
    neutral('req-len',    'Минимум 8 символов');
    neutral('req-letter', 'Содержит букву');
    neutral('req-digit',  'Содержит цифру');
    return;
  }
  let s = 0;
  if (val.length >= 8) s++;
  if (val.length >= 12) s++;
  if (/[a-zA-Zа-яёА-ЯЁ]/.test(val) && /\d/.test(val)) s++;
  if (/[!@#$%^&*()\-_=+[\]{};':",.<>?/\\|`~]/.test(val)) s++;
  const cfg = [
    {w:0,c:'',t:''},
    {w:25,c:'#ef4444',t:'Слабый'},
    {w:55,c:'#f97316',t:'Средний'},
    {w:78,c:'#eab308',t:'Хороший'},
    {w:100,c:'#22c55e',t:'Отличный'}
  ];
  fill.style.width = cfg[s].w + '%';
  fill.style.background = cfg[s].c;
  if (lbl) { lbl.textContent = cfg[s].t; lbl.style.color = cfg[s].c || 'var(--muted)'; }
  const okLen = val.length >= 8, okLet = /[a-zA-Zа-яёА-ЯЁ]/.test(val), okDig = /\d/.test(val);
  const upd = (id, ok, text) => {
    const el = document.getElementById(id); if (!el) return;
    el.classList.toggle('ok', ok);
    el.innerHTML = `<span class="ri">${ok?'✓':'✗'}</span> ${text}`;
  };
  upd('req-len',    okLen, 'Минимум 8 символов');
  upd('req-letter', okLet, 'Содержит букву');
  upd('req-digit',  okDig, 'Содержит цифру');
}

// ── Auth requests ──
async function doLogin() {
  let firstErr = null;
  const passVal = document.getElementById('login-password')?.value || '';

  if (currentType === 'org' || loginMethod === 'email') {
    const err = vEmail(document.getElementById('login-email')?.value || '', true);
    if (err) { setField('login-email', 'error', err); firstErr = 'login-email'; }
    else setField('login-email', 'success', '');
  } else {
    const err = vPhone(document.getElementById('login-phone')?.value || '');
    if (err) { setField('login-phone', 'error', err); firstErr = 'login-phone'; }
    else setField('login-phone', 'success', '');
  }

  if (!passVal) { setField('login-password', 'error', 'Введите пароль'); firstErr = firstErr || 'login-password'; }
  else setField('login-password', 'success', '');

  if (firstErr) { shake('form-login'); document.getElementById(firstErr)?.focus(); return; }

  setBtnLoading('login-btn', true);
  try {
    let data;
    if (currentType === 'org') {
      data = await api('POST', '/auth/org/login', { email: document.getElementById('login-email').value.trim(), password: passVal });
      auth.saveOrg(data.token, data.organization);
    } else if (loginMethod === 'email') {
      data = await api('POST', '/auth/login', { email: document.getElementById('login-email').value.trim(), password: passVal });
      auth.saveUser(data.token, data.user);
    } else {
      data = await api('POST', '/auth/login', { phone: rawPhone(document.getElementById('login-phone').value), password: passVal });
      auth.saveUser(data.token, data.user);
    }
    nav(currentType === 'org' ? '/org/cabinet' : '/cabinet');
  } catch(e) {
    setBtnLoading('login-btn', false);
    shake('form-login');
    const errField = (currentType === 'org' || loginMethod === 'email') ? 'login-email' : 'login-phone';
    setField(errField, 'error', ' ');
    setField('login-password', 'error', 'Неверные данные для входа');
    toast(e.message, 'error');
  }
}

async function doRegister() {
  if (!validateStep(3)) return;

  setBtnLoading('reg-btn', true);
  try {
    let data;
    const passVal = document.getElementById('reg-password').value;
    if (currentType === 'user') {
      data = await api('POST', '/auth/register', {
        last_name:     document.getElementById('reg-lastname').value.trim(),
        first_name:    document.getElementById('reg-firstname').value.trim(),
        patronymic:    document.getElementById('reg-patronymic').value.trim() || null,
        phone:         rawPhone(document.getElementById('reg-phone').value),
        email:         document.getElementById('reg-email').value.trim() || null,
        date_of_birth: document.getElementById('reg-dob').value || null,
        password:      passVal,
        pd_consent:    true,
      });
      auth.saveUser(data.token, data.user);
      nav('/cabinet');
    } else {
      data = await api('POST', '/auth/org/register', {
        full_name:  document.getElementById('reg-name').value.trim(),
        email:      document.getElementById('reg-email').value.trim(),
        inn:        document.getElementById('reg-inn').value.trim(),
        password:   passVal,
        pd_consent: true,
      });
      auth.saveOrg(data.token, data.organization);
      nav('/org/cabinet');
    }
    toast('Добро пожаловать!', 'success');
  } catch(e) {
    setBtnLoading('reg-btn', false);
    shake('form-register');
    toast(e.message, 'error');
  }
}

switchType('user');
</script>
@endsection
