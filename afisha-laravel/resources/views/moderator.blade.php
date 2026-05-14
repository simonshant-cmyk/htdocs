@extends('layouts.app')

@section('title', 'Панель модератора — АфишаКолыма')

@section('styles')
<style>
  .mod-layout { max-width: 1100px; margin: 0 auto; padding: 40px 32px; }
  @media(max-width:700px){ .mod-layout{ padding: 24px 16px; } }

  .mod-header { margin-bottom: 32px; }
  .mod-title { font-family: var(--font-display); font-size: 2rem; font-weight: 700; margin-bottom: 6px; }
  .mod-subtitle { color: var(--muted); font-size: .9rem; }

  .mod-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; margin-bottom: 36px; }
  .mod-stat { background: var(--surface); border: 1.5px solid var(--border); border-radius: var(--radius); padding: 18px 20px; }
  .mod-stat.warning { border-color: rgba(245,158,11,.5); background: rgba(245,158,11,.06); }
  .mod-stat-num { font-size: 1.8rem; font-weight: 800; line-height: 1; margin-bottom: 4px; }
  .mod-stat.warning .mod-stat-num { color: #d97706; }
  .mod-stat-label { font-size: .78rem; color: var(--muted); font-weight: 500; }

  .mod-tabs { display: flex; gap: 4px; border-bottom: 1.5px solid var(--border); margin-bottom: 28px; overflow-x: auto; scrollbar-width: none; }
  .mod-tabs::-webkit-scrollbar { display: none; }
  .mod-tab { padding: 10px 22px; font-size: .9rem; font-weight: 600; color: var(--muted); cursor: pointer; border-bottom: 2.5px solid transparent; margin-bottom: -1.5px; transition: var(--transition); position: relative; white-space: nowrap; flex-shrink: 0; }
  .mod-tab.active { color: var(--accent); border-bottom-color: var(--accent); }
  .mod-tab .badge-dot { display: inline-flex; align-items: center; justify-content: center; background: #ef4444; color: #fff; border-radius: 20px; font-size: .62rem; font-weight: 700; min-width: 16px; height: 16px; padding: 0 4px; margin-left: 6px; vertical-align: middle; }

  .filter-bar { display: flex; gap: 8px; margin-bottom: 20px; align-items: center; flex-wrap: wrap; }
  .filter-btn { padding: 6px 16px; border-radius: 20px; font-size: .82rem; font-weight: 600; border: 1.5px solid var(--border); background: var(--surface); color: var(--muted); cursor: pointer; transition: var(--transition); }
  .filter-btn.active { background: var(--accent); border-color: var(--accent); color: #fff; }

  .org-card { background: var(--surface); border: 1.5px solid var(--border); border-radius: var(--radius); padding: 20px 22px; margin-bottom: 14px; display: flex; gap: 20px; align-items: flex-start; cursor: pointer; transition: var(--transition); }
  .org-card:hover { border-color: var(--accent); box-shadow: 0 2px 12px rgba(0,0,0,.07); }
  .org-card.pending { border-color: rgba(245,158,11,.4); background: rgba(245,158,11,.03); }
  .org-card.pending:hover { border-color: #d97706; }
  .org-card.rejected { border-color: rgba(239,68,68,.3); opacity: .7; }
  .org-card.rejected:hover { border-color: #ef4444; }
  .org-avatar { width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0; background: var(--bg2); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: 700; color: var(--accent); }
  .org-body { flex: 1; min-width: 0; }
  .org-name { font-weight: 700; font-size: 1rem; margin-bottom: 3px; }
  .org-meta { font-size: .8rem; color: var(--muted); margin-bottom: 8px; line-height: 1.6; }
  .org-actions { display: flex; gap: 8px; flex-wrap: wrap; }

  .btn-approve { padding: 6px 16px; border-radius: 8px; font-size: .82rem; font-weight: 600; background: rgba(42,110,90,.12); color: var(--accent2); border: 1.5px solid rgba(42,110,90,.25); cursor: pointer; transition: var(--transition); text-decoration: none; display: inline-flex; align-items: center; }
  .btn-approve:hover { background: rgba(42,110,90,.22); }
  .btn-reject { padding: 6px 16px; border-radius: 8px; font-size: .82rem; font-weight: 600; background: rgba(239,68,68,.07); color: #ef4444; border: 1.5px solid rgba(239,68,68,.2); cursor: pointer; transition: var(--transition); }
  .btn-reject:hover { background: rgba(239,68,68,.15); }
  .btn-edit-reason { padding: 6px 16px; border-radius: 8px; font-size: .82rem; font-weight: 600; background: rgba(99,102,241,.07); color: #6366f1; border: 1.5px solid rgba(99,102,241,.25); cursor: pointer; transition: var(--transition); }
  .btn-edit-reason:hover { background: rgba(99,102,241,.15); }
  .btn-mail { padding: 6px 16px; border-radius: 8px; font-size: .82rem; font-weight: 600; background: var(--bg2); color: var(--muted); border: 1.5px solid var(--border); cursor: pointer; transition: var(--transition); text-decoration: none; display: inline-flex; align-items: center; }
  .btn-mail:hover { border-color: var(--accent); color: var(--accent); }

  .rejection-reason-strip { font-size: .8rem; color: var(--muted); line-height: 1.5; background: rgba(239,68,68,.05); border-left: 3px solid rgba(239,68,68,.4); padding: 7px 12px; border-radius: 0 6px 6px 0; margin-bottom: 10px; }
  .rejection-reason-label { font-weight: 700; color: #ef4444; margin-right: 6px; }

  .status-badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: .7rem; font-weight: 700; letter-spacing: .04em; }
  .status-1 { background: rgba(42,110,90,.12); color: var(--accent2); }
  .status-3 { background: rgba(239,68,68,.1); color: #ef4444; }
  .status-4 { background: rgba(245,158,11,.12); color: #d97706; }

  .review-card { background: var(--surface); border: 1.5px solid var(--border); border-radius: var(--radius); padding: 18px 20px; margin-bottom: 12px; display: flex; gap: 16px; align-items: flex-start; }
  .review-card-body { flex: 1; min-width: 0; }
  .review-meta { font-size: .78rem; color: var(--muted); margin-bottom: 6px; }
  .review-text { font-size: .88rem; line-height: 1.6; word-break: break-word; }
  .review-target { font-size: .78rem; color: var(--accent); margin-top: 4px; }

  .events-table { width: 100%; border-collapse: collapse; }
  .events-table th { text-align: left; padding: 10px 14px; font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); border-bottom: 1.5px solid var(--border); }
  .events-table td { padding: 12px 14px; border-bottom: 1px solid var(--border); font-size: .88rem; vertical-align: middle; }
  .events-table tr:hover td { background: var(--bg2); }
  .event-status-select { padding: 4px 10px; border-radius: 6px; font-size: .8rem; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); cursor: pointer; }

  .users-table { width: 100%; border-collapse: collapse; }
  .users-table th { text-align: left; padding: 10px 14px; font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); border-bottom: 1.5px solid var(--border); }
  .users-table td { padding: 12px 14px; border-bottom: 1px solid var(--border); font-size: .88rem; vertical-align: middle; }
  .users-table tr:hover td { background: var(--bg2); }
  .mod-search { padding: 7px 14px; border-radius: 8px; font-size: .85rem; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); outline: none; transition: var(--transition); min-width: 200px; }
  .mod-search:focus { border-color: var(--accent); }
  .user-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 14px 18px; margin-bottom: 10px; transition: var(--transition); }
  .user-card:hover { border-color: var(--accent); }

  .detail-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 500; display: flex; align-items: center; justify-content: center; padding: 20px; animation: fadeIn .15s ease; }
  .detail-modal { background: var(--surface); border: 1.5px solid var(--border); border-radius: 20px; padding: 32px; max-width: 580px; width: 100%; max-height: 88vh; overflow-y: auto; box-shadow: var(--shadow-lg); animation: slideUp .2s ease; }
  .detail-modal-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
  .detail-modal-title { font-family: var(--font-display); font-size: 1.25rem; font-weight: 800; line-height: 1.3; }
  .detail-close-btn { background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--muted); padding: 0; line-height: 1; flex-shrink: 0; margin-left: 12px; }
  .detail-row { display: flex; gap: 16px; padding: 10px 0; border-bottom: 1px solid var(--border); }
  .detail-row:last-child { border-bottom: none; }
  .detail-label { font-size: .78rem; color: var(--muted); font-weight: 600; width: 130px; flex-shrink: 0; padding-top: 1px; }
  .detail-value { font-size: .88rem; flex: 1; line-height: 1.5; word-break: break-word; }
  .detail-actions { display: flex; gap: 8px; margin-top: 22px; flex-wrap: wrap; align-items: center; }

  .reject-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 500; display: flex; align-items: center; justify-content: center; padding: 20px; animation: fadeIn .15s ease; }
  .reject-modal { background: var(--surface); border: 1.5px solid var(--border); border-radius: 20px; padding: 32px; max-width: 480px; width: 100%; box-shadow: var(--shadow-lg); animation: slideUp .2s ease; }
  .reject-modal-title { font-family: var(--font-display); font-size: 1.2rem; font-weight: 800; margin-bottom: 6px; }
  .reject-modal-sub { font-size: .83rem; color: var(--muted); margin-bottom: 20px; }
  .reason-option { display: flex; align-items: flex-start; gap: 12px; padding: 12px 14px; border: 1.5px solid var(--border); border-radius: 10px; margin-bottom: 8px; cursor: pointer; transition: var(--transition); }
  .reason-option:hover { border-color: #ef4444; background: rgba(239,68,68,.03); }
  .reason-option.selected { border-color: #ef4444; background: rgba(239,68,68,.05); }
  .reason-option input[type=radio] { margin-top: 2px; accent-color: #ef4444; flex-shrink: 0; }
  .reason-label { font-size: .88rem; font-weight: 500; line-height: 1.4; }
  .reason-label small { display: block; font-size: .75rem; color: var(--muted); font-weight: 400; }
  .reject-custom { width: 100%; margin-top: 8px; padding: 10px 14px; font-size: .88rem; border: 1.5px solid var(--border); border-radius: 10px; background: var(--surface); color: var(--text); resize: vertical; min-height: 72px; font-family: var(--font); transition: var(--transition); outline: none; box-sizing: border-box; }
  .reject-custom:focus { border-color: #ef4444; }
  .reject-modal-footer { display: flex; gap: 10px; margin-top: 20px; }

  @keyframes fadeIn { from{opacity:0}to{opacity:1} }
  @keyframes slideUp { from{transform:translateY(16px);opacity:0}to{transform:translateY(0);opacity:1} }
  @media(max-width:700px){
    .org-card{flex-direction:column;gap:12px}
    .events-table th:nth-child(3),.events-table td:nth-child(3){display:none}
    .users-table th:nth-child(3),.users-table td:nth-child(3){display:none}
  }
</style>
@endsection

@section('content')
<div class="mod-layout page-enter">

  <div class="mod-header">
    <h1 class="mod-title" id="panel-title">🛡 Панель модератора</h1>
    <p class="mod-subtitle">Проверка организаций, отзывов и управление событиями</p>
  </div>

  <div class="mod-stats" id="mod-stats">
    <div class="mod-stat"><div class="mod-stat-num">—</div><div class="mod-stat-label">Ожидают проверки</div></div>
    <div class="mod-stat"><div class="mod-stat-num">—</div><div class="mod-stat-label">Организаций всего</div></div>
    <div class="mod-stat"><div class="mod-stat-num">—</div><div class="mod-stat-label">Событий</div></div>
    <div class="mod-stat"><div class="mod-stat-num">—</div><div class="mod-stat-label">Отзывов</div></div>
    <div class="mod-stat"><div class="mod-stat-num">—</div><div class="mod-stat-label">Пользователей</div></div>
  </div>

  <div class="mod-tabs">
    <div class="mod-tab active" onclick="switchTab('orgs', this)">
      Организации <span class="badge-dot" id="pending-badge" style="display:none">0</span>
    </div>
    <div class="mod-tab" onclick="switchTab('reviews', this)">Отзывы</div>
    <div class="mod-tab" onclick="switchTab('events', this)">
      События <span class="badge-dot" id="review-events-badge" style="display:none">0</span>
    </div>
    <div class="mod-tab" onclick="switchTab('users', this)">Пользователи</div>
    <div class="mod-tab" id="tab-logs-btn" onclick="switchTab('logs', this)" style="display:none">📋 Логи</div>
  </div>

  <div id="tab-orgs">
    <div class="filter-bar">
      <div style="display:flex;gap:4px;align-items:center">
        <span style="font-size:.83rem;color:var(--muted);font-weight:600">Статус:</span>
        <button class="filter-btn active" onclick="loadOrgs('pending', this)">На проверке</button>
        <button class="filter-btn" onclick="loadOrgs('approved', this)">Одобренные</button>
        <button class="filter-btn" onclick="loadOrgs('rejected', this)">Отклонённые</button>
        <button class="filter-btn" onclick="loadOrgs('all', this)">Все</button>
      </div>
      <input class="mod-search" id="orgs-search" type="text" placeholder="Поиск по названию, email, ИНН..." oninput="loadOrgsWithSearch()" style="min-width:220px">
    </div>
    <div id="orgs-list"><div class="loader"><div class="spinner"></div></div></div>
  </div>

  <div id="tab-reviews" style="display:none">
    <div id="reviews-list"><div class="loader"><div class="spinner"></div></div></div>
  </div>

  <div id="tab-events" style="display:none">
    <div class="filter-bar" id="events-filter-bar" style="display:none">
      <span style="font-size:.83rem;color:var(--muted);font-weight:600">Статус:</span>
      <button class="filter-btn active" data-status="" onclick="filterEvents('', this)">Все</button>
      <button class="filter-btn" data-status="1" onclick="filterEvents('1', this)">Активные</button>
      <button class="filter-btn" data-status="4" onclick="filterEvents('4', this)">На проверке</button>
      <button class="filter-btn" data-status="2" onclick="filterEvents('2', this)">Завершённые</button>
      <button class="filter-btn" data-status="3" onclick="filterEvents('3', this)">Отменённые</button>
      <input class="mod-search" id="events-search" type="text" placeholder="Поиск по названию..." oninput="filterEvents(currentEventStatus, null)">
    </div>
    <div id="events-list"><div class="loader"><div class="spinner"></div></div></div>
  </div>

  <div id="tab-users" style="display:none">
    <div class="filter-bar" id="users-filter-bar" style="display:none;flex-wrap:wrap;gap:8px">
      <input class="mod-search" id="users-search" type="text" placeholder="Имя, телефон, email..." oninput="applyUserFilters()" style="min-width:200px">
      <select class="mod-search" id="users-status" onchange="applyUserFilters()" style="width:auto">
        <option value="">Все статусы</option>
        <option value="active">Активные</option>
        <option value="warned">С предупреждением</option>
        <option value="restricted">Ограниченные</option>
        <option value="blocked">Заблокированные</option>
      </select>
      <select class="mod-search" id="users-role" onchange="applyUserFilters()" style="width:auto;display:none">
        <option value="">Все роли</option>
        <option value="2">Пользователи</option>
        <option value="3">Модераторы</option>
      </select>
      <div style="display:flex;align-items:center;gap:6px;font-size:.83rem;color:var(--muted)">
        Возраст:
        <input type="number" id="users-age-from" min="1" max="120" placeholder="от" oninput="applyUserFilters()" style="width:52px;padding:4px 8px;border:1.5px solid var(--border);border-radius:6px;background:var(--surface);color:var(--text);font-size:.83rem">
        <input type="number" id="users-age-to" min="1" max="120" placeholder="до" oninput="applyUserFilters()" style="width:52px;padding:4px 8px;border:1.5px solid var(--border);border-radius:6px;background:var(--surface);color:var(--text);font-size:.83rem">
      </div>
    </div>
    <div id="users-list"><div class="loader"><div class="spinner"></div></div></div>
  </div>

  <div id="tab-logs" style="display:none">
    <div class="filter-bar" style="margin-bottom:16px">
      <select class="mod-search" id="log-action-filter" onchange="loadLogs()" style="width:auto">
        <option value="">Все действия</option>
        <option value="user_registered">Регистрации пользователей</option>
        <option value="org_registered">Регистрации организаций</option>
        <option value="org_approved">Одобрение организаций</option>
        <option value="org_rejected">Отклонение организаций</option>
        <option value="user_warned">Предупреждения</option>
        <option value="user_blocked">Блокировки</option>
        <option value="user_unblocked">Разблокировки</option>
        <option value="user_restricted">Ограничения</option>
        <option value="review_deleted">Удаление отзывов</option>
        <option value="event_status_changed">Изменение статуса событий</option>
        <option value="role_changed">Изменение ролей</option>
      </select>
    </div>
    <div id="logs-list"><div class="loader"><div class="spinner"></div></div></div>
  </div>

</div>

<!-- REJECT MODAL -->
<div id="reject-overlay" class="reject-overlay" style="display:none" onclick="if(event.target===this)closeRejectModal()">
  <div class="reject-modal">
    <div class="reject-modal-title">✕ <span id="reject-modal-title-text">Причина отклонения</span></div>
    <div class="reject-modal-sub" id="reject-modal-org-name"></div>
    <form id="reject-form">
      <div id="reason-options"></div>
      <textarea class="reject-custom" id="reject-custom" placeholder="Дополнительный комментарий (необязательно)..." style="display:none"></textarea>
    </form>
    <div class="reject-modal-footer">
      <button class="btn btn-primary" style="background:#ef4444;border-color:#ef4444;flex:1" onclick="confirmReject()">Отклонить</button>
      <button class="btn btn-secondary" style="flex:1" onclick="closeRejectModal()">Отмена</button>
    </div>
  </div>
</div>

<!-- USER ACTION MODAL -->
<div class="modal-overlay" id="modal-user-action" onclick="if(event.target===this)closeModal('modal-user-action')">
  <div class="modal" style="max-width:460px">
    <div class="modal-title" id="user-action-title">Действие</div>
    <div style="margin-bottom:14px;color:var(--muted);font-size:.9rem" id="user-action-sub"></div>
    <div id="user-action-duration-wrap" style="margin-bottom:14px;display:none">
      <label style="font-size:.83rem;color:var(--muted);font-weight:600;display:block;margin-bottom:6px">Срок (дней, пусто = бессрочно)</label>
      <input type="number" id="user-action-duration" min="1" max="365" placeholder="Например: 7" style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font-size:.9rem">
    </div>
    <label style="font-size:.83rem;color:var(--muted);font-weight:600;display:block;margin-bottom:6px">Причина <span style="color:var(--accent)">*</span></label>
    <textarea id="user-action-reason" rows="3" placeholder="Опишите причину..." style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font-size:.9rem;resize:vertical;box-sizing:border-box"></textarea>
    <div style="display:flex;gap:10px;margin-top:18px">
      <button class="btn btn-primary" id="user-action-confirm-btn" onclick="confirmUserAction()" style="flex:1">Применить</button>
      <button class="btn btn-secondary" onclick="closeModal('modal-user-action')">Отмена</button>
    </div>
  </div>
</div>

<!-- ORG DETAIL MODAL -->
<div class="detail-overlay" id="org-detail-overlay" style="display:none" onclick="if(event.target===this)closeOrgDetail()">
  <div class="detail-modal">
    <div class="detail-modal-header">
      <div><div class="detail-modal-title" id="org-detail-name">—</div><div style="margin-top:8px" id="org-detail-status-badge"></div></div>
      <button class="detail-close-btn" onclick="closeOrgDetail()">✕</button>
    </div>
    <div id="org-detail-body"></div>
    <div class="detail-actions" id="org-detail-actions"></div>
  </div>
</div>

<!-- EVENT DETAIL MODAL -->
<div class="detail-overlay" id="event-detail-overlay" style="display:none" onclick="if(event.target===this)closeEventDetail()">
  <div class="detail-modal">
    <div class="detail-modal-header">
      <div><div class="detail-modal-title" id="event-detail-title">—</div><div style="margin-top:8px" id="event-detail-status-badge"></div></div>
      <button class="detail-close-btn" onclick="closeEventDetail()">✕</button>
    </div>
    <div id="event-detail-body"></div>
    <div class="detail-actions" id="event-detail-actions"></div>
  </div>
</div>
@endsection

@section('scripts')
<script>
const user    = auth.user();
const isAdmin = Number(user?.role_id) === UserRole.ADMIN;
if (!auth.isLoggedIn() || auth.type() !== 'user' || ![UserRole.ADMIN, UserRole.MODERATOR].includes(Number(user?.role_id))) {
  nav('/');
}

if (isAdmin) {
  document.getElementById('panel-title').textContent = '👑 Панель администратора';
  document.getElementById('tab-logs-btn').style.display = '';
  const roleFilter = document.getElementById('users-role');
  if (roleFilter) roleFilter.style.display = '';
}

let currentTab = 'orgs';
let currentOrgFilter = 'pending';
let _allOrgs = [];
let _allEvents = [];
let currentEventStatus = '';
let _allUsers = [];
let _currentDetailEventId = null;

async function loadStats() {
  try {
    const s = await get('/moderation/stats');
    const el = document.getElementById('mod-stats');
    el.innerHTML = `
      <div class="mod-stat ${s.pending_orgs > 0 ? 'warning' : ''}">
        <div class="mod-stat-num">${s.pending_orgs}</div><div class="mod-stat-label">Ожидают проверки</div>
      </div>
      <div class="mod-stat"><div class="mod-stat-num">${s.total_orgs}</div><div class="mod-stat-label">Организаций всего</div></div>
      <div class="mod-stat"><div class="mod-stat-num">${s.total_events}</div><div class="mod-stat-label">Событий</div></div>
      <div class="mod-stat"><div class="mod-stat-num">${s.total_reviews}</div><div class="mod-stat-label">Отзывов</div></div>
      <div class="mod-stat"><div class="mod-stat-num">${s.total_users}</div><div class="mod-stat-label">Пользователей</div></div>
      ${isAdmin ? `<div class="mod-stat warning" style="${s.blocked_users>0?'':'opacity:.5'}">
        <div class="mod-stat-num" style="${s.blocked_users>0?'color:#d97706':''}">${s.blocked_users}</div>
        <div class="mod-stat-label">Заблокировано</div>
      </div>` : ''}
    `;
    const badge = document.getElementById('pending-badge');
    if (s.pending_orgs > 0) { badge.textContent = s.pending_orgs; badge.style.display = 'inline-flex'; }
    else { badge.style.display = 'none'; }
  } catch(e) {}
}

async function loadOrgs(filter = 'pending', btnEl = null) {
  currentOrgFilter = filter;
  document.querySelectorAll('#tab-orgs .filter-btn').forEach(b => b.classList.remove('active'));
  if (btnEl) btnEl.classList.add('active');
  const list = document.getElementById('orgs-list');
  list.innerHTML = '<div class="loader"><div class="spinner"></div></div>';
  try {
    const search = document.getElementById('orgs-search')?.value.trim() || '';
    const params = ['filter=' + filter];
    if (search) params.push('search=' + encodeURIComponent(search));
    const items = await get('/moderation/orgs?' + params.join('&'));
    _allOrgs = items;
    if (!items.length) {
      list.innerHTML = `<div class="empty"><div class="empty-icon">${filter === 'pending' ? '✅' : '🏢'}</div><div>${filter === 'pending' ? 'Нет организаций на проверке' : 'Организаций нет'}</div></div>`;
      return;
    }
    list.innerHTML = items.map(o => {
      const nameAttr   = escHtml(o.full_name).replace(/'/g,"&#39;");
      const reasonAttr = escHtml(o.rejection_reason || '').replace(/'/g,"&#39;");
      return `
      <div class="org-card ${o.status_id == OrgStatus.PENDING ? 'pending' : o.status_id == OrgStatus.REJECTED ? 'rejected' : ''}" id="org-${o.organization_id}" onclick="openOrgDetail(${o.organization_id})">
        <div class="org-avatar">${o.full_name.charAt(0).toUpperCase()}</div>
        <div class="org-body">
          <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:4px">
            <div class="org-name">${escHtml(o.full_name)}</div>
            <span class="status-badge status-${o.status_id}">${escHtml(o.status_name || '')}</span>
          </div>
          <div class="org-meta">
            ${o.email ? `📧 ${escHtml(o.email)}` : ''}
            ${o.inn ? ` &nbsp;·&nbsp; ИНН: ${escHtml(o.inn)}` : ''}
            ${o.type_name ? ` &nbsp;·&nbsp; ${escHtml(o.type_name)}` : ''}
            ${o.address ? `<br>📍 ${escHtml(o.address)}` : ''}
            <br>📅 Событий: <strong>${o.events_count}</strong>
          </div>
          ${o.status_id == OrgStatus.REJECTED && o.rejection_reason ? `
          <div class="rejection-reason-strip">
            <span class="rejection-reason-label">Причина отклонения:</span>${escHtml(o.rejection_reason)}
          </div>` : ''}
          <div class="org-actions">
            ${o.status_id != OrgStatus.APPROVED ? `<button class="btn-approve" onclick="event.stopPropagation();setOrgStatus(${o.organization_id}, ${OrgStatus.APPROVED})">✓ Одобрить</button>` : ''}
            ${o.status_id == OrgStatus.REJECTED
              ? `<button class="btn-edit-reason" onclick="event.stopPropagation();openRejectModal(${o.organization_id}, '${nameAttr}', '${reasonAttr}')">✏️ Изменить решение</button>`
              : `<button class="btn-reject" onclick="event.stopPropagation();openRejectModal(${o.organization_id}, '${nameAttr}')">✕ Отклонить</button>`}
            ${o.email ? `<a class="btn-mail" href="mailto:${escHtml(o.email)}?subject=${encodeURIComponent('Заявка на регистрацию — АфишаКолыма')}" target="_blank" onclick="event.stopPropagation()">✉️ Написать</a>` : ''}
          </div>
        </div>
      </div>
    `}).join('');
  } catch(e) {
    list.innerHTML = `<div class="empty"><div class="empty-icon">⚠️</div><div>${e.message}</div></div>`;
  }
}

const REJECT_REASONS = [
  { label:'Неверные или неполные данные', hint:'Название, ИНН, адрес не совпадают с реальными данными организации' },
  { label:'Организация не найдена в реестре', hint:'Юридическое лицо или ИП не зарегистрировано в ЕГРЮЛ / ЕГРИП' },
  { label:'Деятельность не соответствует платформе', hint:'Тематика мероприятий выходит за рамки культурно-развлекательной сферы' },
  { label:'Дублирующая заявка', hint:'Организация уже зарегистрирована под другим аккаунтом' },
  { label:'Подозрение на мошенничество', hint:'Данные вызывают сомнения — требуется дополнительная проверка' },
  { label:'Другое', hint:'Укажите причину в комментарии ниже', custom: true },
];

let _rejectOrgId = null;

function openRejectModal(orgId, orgName, existingReason = '') {
  _rejectOrgId = orgId;
  document.getElementById('reject-modal-org-name').textContent = orgName;
  document.getElementById('reject-modal-title-text').textContent = existingReason ? 'Изменить решение об отклонении' : 'Причина отклонения';
  const opts = document.getElementById('reason-options');
  opts.innerHTML = REJECT_REASONS.map((r, i) => `
    <label class="reason-option" id="reason-opt-${i}" onclick="selectReason(${i})">
      <input type="radio" name="reject-reason" value="${i}" id="reason-radio-${i}">
      <div class="reason-label">${escHtml(r.label)}<small>${escHtml(r.hint)}</small></div>
    </label>
  `).join('');
  const customEl = document.getElementById('reject-custom');
  customEl.value = ''; customEl.style.display = 'none';
  if (existingReason) {
    const matchIdx = REJECT_REASONS.findIndex(r => !r.custom && r.label === existingReason);
    if (matchIdx !== -1) { selectReason(matchIdx); }
    else { const ci = REJECT_REASONS.findIndex(r => r.custom); selectReason(ci); customEl.value = existingReason; }
  }
  document.getElementById('reject-overlay').style.display = 'flex';
}

function selectReason(idx) {
  document.querySelectorAll('.reason-option').forEach((el, i) => el.classList.toggle('selected', i === idx));
  document.getElementById('reason-radio-' + idx).checked = true;
  const isCustom = REJECT_REASONS[idx].custom;
  const custom = document.getElementById('reject-custom');
  custom.style.display = isCustom ? '' : 'none';
  if (isCustom) custom.focus();
}

function closeRejectModal() { document.getElementById('reject-overlay').style.display = 'none'; _rejectOrgId = null; }

async function confirmReject() {
  const selected = document.querySelector('input[name="reject-reason"]:checked');
  if (!selected) { toast('Выберите причину отклонения', 'error'); return; }
  const idx = +selected.value;
  const isCustom = REJECT_REASONS[idx].custom;
  const customText = document.getElementById('reject-custom').value.trim();
  if (isCustom && !customText) { toast('Опишите причину в комментарии', 'error'); return; }
  const reason = isCustom ? customText : REJECT_REASONS[idx].label;
  try {
    await put('/moderation/orgs/' + _rejectOrgId, { status_id: 3, rejection_reason: reason });
    toast('✕ Организация отклонена', 'error');
    closeRejectModal(); loadOrgs(currentOrgFilter); loadStats();
  } catch(e) { toast(e.message, 'error'); }
}

async function setOrgStatus(id, statusId) {
  if (statusId !== OrgStatus.APPROVED) return;
  try {
    await put('/moderation/orgs/' + id, { status_id: OrgStatus.APPROVED, rejection_reason: null });
    toast('✓ Организация одобрена', 'success');
    loadOrgs(currentOrgFilter); loadStats();
  } catch(e) { toast(e.message, 'error'); }
}

async function loadReviews() {
  const list = document.getElementById('reviews-list');
  list.innerHTML = '<div class="loader"><div class="spinner"></div></div>';
  try {
    const items = await get('/moderation/reviews');
    if (!items.length) { list.innerHTML = `<div class="empty"><div class="empty-icon">💬</div><div>Отзывов нет</div></div>`; return; }
    list.innerHTML = items.map(r => `
      <div class="review-card" id="rev-${r.review_id}">
        <div style="flex:1;min-width:0">
          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:6px">
            <strong style="font-size:.88rem">${escHtml(r.user_name || 'Аноним')}</strong>
            <span style="font-size:1rem">${stars(r.rating || 0)}</span>
            <span style="font-size:.75rem;color:var(--muted)">${fmtDate(r.created_at)}</span>
          </div>
          <div class="review-text">${escHtml(r.text || '— без текста —')}</div>
          <div class="review-target">
            ${r.event_title ? `🎭 ${escHtml(r.event_title)}` : ''}
            ${r.venue_name  ? `📍 ${escHtml(r.venue_name)}` : ''}
          </div>
        </div>
        <button class="btn-reject" onclick="deleteReview(${r.review_id})" title="Удалить отзыв" style="flex-shrink:0">🗑</button>
      </div>
    `).join('');
  } catch(e) { list.innerHTML = `<div class="empty"><div class="empty-icon">⚠️</div><div>${e.message}</div></div>`; }
}

async function deleteReview(id) {
  if (!confirm('Удалить этот отзыв?')) return;
  try { await del('/moderation/reviews/' + id); document.getElementById('rev-' + id)?.remove(); toast('Отзыв удалён'); loadStats(); }
  catch(e) { toast(e.message, 'error'); }
}

async function loadModEvents() {
  const list = document.getElementById('events-list');
  list.innerHTML = '<div class="loader"><div class="spinner"></div></div>';
  try {
    _allEvents = await get('/moderation/events');
    const pending = _allEvents.filter(e => e.status_id == EventStatus.PENDING).length;
    const badge = document.getElementById('review-events-badge');
    if (pending > 0) { badge.textContent = pending; badge.style.display = 'inline-flex'; } else { badge.style.display = 'none'; }
    document.getElementById('events-filter-bar').style.display = '';
    renderEventsTable(_allEvents);
  } catch(e) { list.innerHTML = `<div class="empty"><div class="empty-icon">⚠️</div><div>${e.message}</div></div>`; }
}

function filterEvents(status, btnEl) {
  currentEventStatus = status;
  if (btnEl) { document.querySelectorAll('#events-filter-bar .filter-btn').forEach(b => b.classList.remove('active')); btnEl.classList.add('active'); }
  const q = (document.getElementById('events-search')?.value || '').toLowerCase();
  const filtered = _allEvents.filter(e => {
    const matchStatus = !status || String(e.status_id) === status;
    const matchQ = !q || e.title.toLowerCase().includes(q) || (e.organization_name || '').toLowerCase().includes(q);
    return matchStatus && matchQ;
  });
  renderEventsTable(filtered);
}

function renderEventsTable(items) {
  const list = document.getElementById('events-list');
  if (!items.length) { list.innerHTML = `<div class="empty"><div class="empty-icon">🎭</div><div>Событий не найдено</div></div>`; return; }
  list.innerHTML = `
    <table class="events-table">
      <thead><tr><th>Событие</th><th>Организатор</th><th>Дата</th><th>Статус</th><th></th></tr></thead>
      <tbody>
        ${items.map(e => `
        <tr style="cursor:pointer" onclick="openEventDetail(${e.event_id})">
          <td><span style="font-weight:600">${escHtml(e.title)}</span><div style="font-size:.76rem;color:var(--muted)">${escHtml(e.category_name||'')} ${e.venue_name ? '· '+escHtml(e.venue_name) : ''}</div></td>
          <td style="color:var(--muted);font-size:.85rem">${escHtml(e.organization_name||'—')}</td>
          <td style="color:var(--muted);font-size:.82rem;white-space:nowrap">${fmtDateOnly(e.start_datetime)}</td>
          <td onclick="event.stopPropagation()">
            <select class="event-status-select" onchange="setEventStatus(${e.event_id}, this.value)">
              <option value="${EventStatus.ACTIVE}"    ${e.status_id==EventStatus.ACTIVE?'selected':''}>Активно</option>
              <option value="${EventStatus.COMPLETED}" ${e.status_id==EventStatus.COMPLETED?'selected':''}>Завершено</option>
              <option value="${EventStatus.CANCELLED}" ${e.status_id==EventStatus.CANCELLED?'selected':''}>Отменено</option>
              <option value="${EventStatus.PENDING}"   ${e.status_id==EventStatus.PENDING?'selected':''}>На проверке</option>
            </select>
          </td>
          <td onclick="event.stopPropagation()">
            <button class="btn-mail" onclick="openEventDetail(${e.event_id})" style="padding:4px 10px;font-size:.78rem">🔍</button>
          </td>
        </tr>
        `).join('')}
      </tbody>
    </table>
  `;
}

async function setEventStatus(id, statusId) {
  try {
    await put('/moderation/events/' + id, { status_id: parseInt(statusId) });
    toast('Статус обновлён', 'success');
    const ev = _allEvents.find(e => e.event_id == id);
    if (ev) ev.status_id = parseInt(statusId);
    const pending = _allEvents.filter(e => e.status_id == EventStatus.PENDING).length;
    const badge = document.getElementById('review-events-badge');
    badge.textContent = pending; badge.style.display = pending > 0 ? 'inline-flex' : 'none';
    loadStats();
  } catch(e) { toast(e.message, 'error'); }
}

let _orgSearchTimer = null;
function loadOrgsWithSearch() { clearTimeout(_orgSearchTimer); _orgSearchTimer = setTimeout(() => loadOrgs(currentOrgFilter), 350); }

let _userSearchTimer = null;

async function loadUsers() {
  const list = document.getElementById('users-list');
  list.innerHTML = '<div class="loader"><div class="spinner"></div></div>';
  try {
    const params = buildUserParams();
    _allUsers = await get('/moderation/users' + (params ? '?' + params : ''));
    document.getElementById('users-filter-bar').style.display = '';
    renderUsersTable(_allUsers);
  } catch(e) { list.innerHTML = `<div class="empty"><div class="empty-icon">⚠️</div><div>${e.message}</div></div>`; }
}

function buildUserParams() {
  const p = [];
  const s      = document.getElementById('users-search')?.value.trim();
  const status = document.getElementById('users-status')?.value;
  const role   = document.getElementById('users-role')?.value;
  const ageFrom= document.getElementById('users-age-from')?.value;
  const ageTo  = document.getElementById('users-age-to')?.value;
  if (s)       p.push('search=' + encodeURIComponent(s));
  if (status)  p.push('status=' + status);
  if (role)    p.push('role=' + role);
  if (ageFrom) p.push('age_from=' + ageFrom);
  if (ageTo)   p.push('age_to=' + ageTo);
  return p.join('&');
}

function applyUserFilters() { clearTimeout(_userSearchTimer); _userSearchTimer = setTimeout(loadUsers, 350); }

function renderUsersTable(items) {
  const list = document.getElementById('users-list');
  const myId = auth.user()?.user_id;
  if (!items.length) { list.innerHTML = `<div class="empty"><div class="empty-icon">👤</div><div>Пользователей не найдено</div></div>`; return; }
  const statusMap   = { active:'Активен', warned:'Предупреждён', restricted:'Ограничен', blocked:'Заблокирован' };
  const statusColor = { active:'green', warned:'#d97706', restricted:'#7c3aed', blocked:'var(--accent)' };
  list.innerHTML = items.map(u => {
    const isSelf   = u.user_id == myId;
    const isTarget = ![UserRole.ADMIN, UserRole.MODERATOR].includes(Number(u.role_id));
    const isMod    = u.role_id == UserRole.MODERATOR;
    const roleLabel = u.role_id == UserRole.ADMIN ? 'Администратор' : isMod ? 'Модератор' : 'Пользователь';
    const isBlocked = u.status === 'blocked';
    const dobStr    = u.date_of_birth ? calcAge(u.date_of_birth) + ' лет' : '—';
    return `
    <div class="user-card" id="user-card-${u.user_id}">
      <div style="display:flex;align-items:flex-start;gap:14px;flex-wrap:wrap">
        <div style="flex:1;min-width:200px">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;flex-wrap:wrap">
            <span style="font-weight:700">${escHtml(u.full_name||'—')}</span>
            <span style="font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:20px;background:rgba(0,0,0,.06)">${roleLabel}</span>
            <span style="font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:20px;background:rgba(0,0,0,.06);color:${statusColor[u.status]||'var(--muted)'}">${statusMap[u.status]||u.status}</span>
            ${u.warning_count > 0 ? `<span title="Предупреждений: ${u.warning_count}" style="font-size:.72rem;color:#d97706">⚠️ ×${u.warning_count}</span>` : ''}
          </div>
          <div style="font-size:.8rem;color:var(--muted)">
            ${u.phone ? escHtml(u.phone) : ''}${u.email ? ' · '+escHtml(u.email) : ''}
            ${u.date_of_birth ? ' · '+dobStr : ''}
            · 🎫 ${u.tickets_count}
            ${isBlocked && u.blocked_until ? ` · до ${fmtDateOnly(u.blocked_until)}` : ''}
            ${u.restriction_until ? ` · огр. до ${fmtDateOnly(u.restriction_until)}` : ''}
          </div>
        </div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
          ${isTarget && !isSelf ? `
            <button class="filter-btn" style="font-size:.78rem;padding:4px 10px" onclick="openUserAction('warn',${u.user_id},'${escHtml(u.full_name||'').replace(/'/g,"\\'")}')" >⚠️ Предупредить</button>
            <button class="filter-btn" style="font-size:.78rem;padding:4px 10px" onclick="openUserAction('restrict',${u.user_id},'${escHtml(u.full_name||'').replace(/'/g,"\\'")}')">🔒 Ограничить</button>
            ${isBlocked
              ? `<button class="filter-btn" style="font-size:.78rem;padding:4px 10px;color:green" onclick="doUnblock(${u.user_id})">✓ Разблокировать</button>`
              : `<button class="filter-btn" style="font-size:.78rem;padding:4px 10px;color:var(--accent)" onclick="openUserAction('block',${u.user_id},'${escHtml(u.full_name||'').replace(/'/g,"\\'")}')">🚫 Заблокировать</button>`}
          ` : ''}
          ${isAdmin && !isSelf ? `
            <button style="padding:4px 12px;border-radius:6px;font-size:.78rem;font-weight:600;border:1.5px solid var(--border);background:var(--surface);color:var(--text);cursor:pointer" onclick="toggleUserRole(${u.user_id}, ${isMod ? UserRole.USER : UserRole.MODERATOR})">
              ${isMod ? '✕ Снять модератора' : '+ Модератор'}
            </button>` : ''}
        </div>
      </div>
    </div>`;
  }).join('');
}

function calcAge(dob) {
  const d = new Date(dob), now = new Date();
  let age = now.getFullYear() - d.getFullYear();
  if (now.getMonth() < d.getMonth() || (now.getMonth() === d.getMonth() && now.getDate() < d.getDate())) age--;
  return age;
}

let _userActionType = null;
let _userActionId   = null;

function openUserAction(type, userId, userName) {
  _userActionType = type; _userActionId = userId;
  const titles = { warn:'⚠️ Выдать предупреждение', block:'🚫 Заблокировать', restrict:'🔒 Наложить ограничение' };
  document.getElementById('user-action-title').textContent = titles[type] || 'Действие';
  document.getElementById('user-action-sub').textContent   = userName;
  document.getElementById('user-action-reason').value      = '';
  document.getElementById('user-action-duration').value    = '';
  document.getElementById('user-action-duration-wrap').style.display = type === 'warn' ? 'none' : '';
  const btn = document.getElementById('user-action-confirm-btn');
  btn.style.background = type==='block' ? '#ef4444' : type==='restrict' ? '#7c3aed' : '';
  btn.style.borderColor = btn.style.background;
  openModal('modal-user-action');
}

async function confirmUserAction() {
  const reason   = document.getElementById('user-action-reason').value.trim();
  const duration = document.getElementById('user-action-duration').value.trim();
  if (!reason) { toast('Укажите причину', 'error'); return; }
  const body = { reason };
  if (duration && _userActionType !== 'warn') body.duration = parseInt(duration);
  try {
    await post('/moderation/users/' + _userActionId + '/' + _userActionType, body);
    closeModal('modal-user-action');
    toast({ warn:'Предупреждение выдано', block:'Пользователь заблокирован', restrict:'Ограничение наложено' }[_userActionType], 'success');
    loadUsers(); loadStats();
  } catch(e) { toast(e.message, 'error'); }
}

async function doUnblock(userId) {
  try { await post('/moderation/users/' + userId + '/unblock', {}); toast('Блокировка снята', 'success'); loadUsers(); loadStats(); }
  catch(e) { toast(e.message, 'error'); }
}

async function toggleUserRole(userId, newRoleId) {
  if (!isAdmin) return;
  try {
    await put('/moderation/users/' + userId, { role_id: newRoleId });
    toast(newRoleId === UserRole.MODERATOR ? 'Роль модератора выдана' : 'Роль модератора снята', 'success');
    loadUsers();
  } catch(e) { toast(e.message, 'error'); }
}

async function loadLogs() {
  if (!isAdmin) return;
  const list = document.getElementById('logs-list');
  list.innerHTML = '<div class="loader"><div class="spinner"></div></div>';
  try {
    const action = document.getElementById('log-action-filter')?.value || '';
    const logs   = await get('/moderation/logs' + (action ? '?action=' + action : ''));
    if (!logs.length) { list.innerHTML = '<div class="empty"><div class="empty-icon">📋</div><div>Логов нет</div></div>'; return; }
    const actionLabels = { user_registered:'Регистрация пользователя',org_registered:'Регистрация организации',org_approved:'Организация одобрена',org_rejected:'Организация отклонена',user_warned:'Предупреждение',user_blocked:'Блокировка',user_unblocked:'Разблокировка',user_restricted:'Ограничение',review_deleted:'Отзыв удалён',event_status_changed:'Статус события изменён',role_changed:'Роль изменена' };
    const actionColors = { user_registered:'green',org_registered:'green',org_approved:'green',org_rejected:'var(--accent)',user_warned:'#d97706',user_blocked:'var(--accent)',user_unblocked:'green',user_restricted:'#7c3aed',review_deleted:'var(--accent)',event_status_changed:'#0ea5e9',role_changed:'#7c3aed' };
    const roleLabels = { 1:'Администратор',2:'Пользователь',3:'Модератор' };
    list.innerHTML = `<table class="users-table">
      <thead><tr><th>Время</th><th>Действие</th><th>Исполнитель</th><th>Объект</th><th>Детали</th></tr></thead>
      <tbody>
        ${logs.map(l => {
          const d = l.details || {};
          let detail = '';
          if (d.reason) detail = escHtml(d.reason);
          else if (d.name) detail = escHtml(d.name);
          if (d.old_role !== undefined) detail = `${roleLabels[d.old_role]||d.old_role} → ${roleLabels[d.new_role]||d.new_role}`;
          return `<tr>
            <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">${fmtDateOnly(l.created_at)}</td>
            <td><span style="font-size:.75rem;font-weight:700;color:${actionColors[l.action]||'var(--muted)'}">${actionLabels[l.action]||l.action}</span></td>
            <td style="font-size:.82rem">${l.actor_id ? `#${l.actor_id} (${roleLabels[l.actor_role]||'—'})` : '<span style="color:var(--muted)">Система</span>'}</td>
            <td style="font-size:.82rem;color:var(--muted)">${l.target_type||'—'} #${l.target_id||'—'}</td>
            <td style="font-size:.8rem;color:var(--muted);max-width:200px;overflow:hidden;text-overflow:ellipsis">${detail}</td>
          </tr>`;
        }).join('')}
      </tbody>
    </table>`;
  } catch(e) { list.innerHTML = `<div class="empty"><div class="empty-icon">⚠️</div><div>${e.message}</div></div>`; }
}

function openOrgDetail(orgId) {
  const o = _allOrgs.find(x => x.organization_id == orgId);
  if (!o) return;
  document.getElementById('org-detail-name').textContent = o.full_name;
  document.getElementById('org-detail-status-badge').innerHTML = `<span class="status-badge status-${o.status_id}">${escHtml(o.status_name||'')}</span>`;
  const rows = [];
  if (o.email)   rows.push(['📧 Email', `<a href="mailto:${escHtml(o.email)}" style="color:var(--accent)">${escHtml(o.email)}</a>`]);
  if (o.inn)     rows.push(['ИНН', escHtml(o.inn)]);
  if (o.address) rows.push(['📍 Адрес', escHtml(o.address)]);
  rows.push(['📅 Событий', `<strong>${o.events_count}</strong>`]);
  if (o.rejection_reason) rows.push(['Причина отклонения', `<span style="color:#ef4444">${escHtml(o.rejection_reason)}</span>`]);
  document.getElementById('org-detail-body').innerHTML = rows.map(([label, value]) =>
    `<div class="detail-row"><div class="detail-label">${label}</div><div class="detail-value">${value}</div></div>`
  ).join('');
  const nameAttr   = escHtml(o.full_name).replace(/'/g,"&#39;");
  const reasonAttr = escHtml(o.rejection_reason||'').replace(/'/g,"&#39;");
  document.getElementById('org-detail-actions').innerHTML = `
    ${o.status_id != OrgStatus.APPROVED ? `<button class="btn-approve" onclick="setOrgStatus(${o.organization_id},${OrgStatus.APPROVED});closeOrgDetail()">✓ Одобрить</button>` : ''}
    ${o.status_id == OrgStatus.REJECTED
      ? `<button class="btn-edit-reason" onclick="closeOrgDetail();openRejectModal(${o.organization_id},'${nameAttr}','${reasonAttr}')">✏️ Изменить решение</button>`
      : `<button class="btn-reject" onclick="closeOrgDetail();openRejectModal(${o.organization_id},'${nameAttr}')">✕ Отклонить</button>`}
    ${o.email ? `<a class="btn-mail" href="mailto:${escHtml(o.email)}?subject=${encodeURIComponent('Заявка на регистрацию — АфишаКолыма')}" target="_blank">✉️ Написать</a>` : ''}
  `;
  document.getElementById('org-detail-overlay').style.display = 'flex';
}
function closeOrgDetail() { document.getElementById('org-detail-overlay').style.display = 'none'; }

async function openEventDetail(eventId) {
  _currentDetailEventId = eventId;
  document.getElementById('event-detail-title').textContent = 'Загрузка...';
  document.getElementById('event-detail-status-badge').innerHTML = '';
  document.getElementById('event-detail-body').innerHTML = '<div class="loader"><div class="spinner"></div></div>';
  document.getElementById('event-detail-actions').innerHTML = '';
  document.getElementById('event-detail-overlay').style.display = 'flex';
  try {
    const e = await get('/events/' + eventId);
    document.getElementById('event-detail-title').textContent = e.title || '—';
    document.getElementById('event-detail-status-badge').innerHTML = `<span class="status-badge status-${e.status_id}">${escHtml(e.status_name||'')}</span>`;
    const rows = [];
    if (e.category_name)     rows.push(['Категория',   escHtml(e.category_name)]);
    if (e.organization_name) rows.push(['Организатор', escHtml(e.organization_name)]);
    if (e.venue_name)        rows.push(['Площадка',    escHtml(e.venue_name)]);
    if (e.venue_address)     rows.push(['Адрес',       escHtml(e.venue_address)]);
    if (e.start_datetime)    rows.push(['Начало',      fmtDate(e.start_datetime)]);
    if (e.end_datetime)      rows.push(['Конец',       fmtDate(e.end_datetime)]);
    if (e.price !== null && e.price !== undefined) rows.push(['Цена', e.price > 0 ? e.price+' ₽' : 'Бесплатно']);
    if (e.age_restriction)   rows.push(['Возраст',     e.age_restriction+'+']);
    if (e.description)       rows.push(['Описание',    `<span style="white-space:pre-wrap">${escHtml(e.description)}</span>`]);
    document.getElementById('event-detail-body').innerHTML = rows.map(([label, value]) =>
      `<div class="detail-row"><div class="detail-label">${label}</div><div class="detail-value">${value}</div></div>`
    ).join('');
    const ev = _allEvents.find(x => x.event_id == eventId);
    const curStatus = ev ? ev.status_id : (e.status_id || EventStatus.PENDING);
    document.getElementById('event-detail-actions').innerHTML = `
      <select class="event-status-select" id="event-detail-status-select" style="font-size:.88rem;padding:7px 12px">
        <option value="${EventStatus.ACTIVE}"    ${curStatus==EventStatus.ACTIVE?'selected':''}>Активно</option>
        <option value="${EventStatus.COMPLETED}" ${curStatus==EventStatus.COMPLETED?'selected':''}>Завершено</option>
        <option value="${EventStatus.CANCELLED}" ${curStatus==EventStatus.CANCELLED?'selected':''}>Отменено</option>
        <option value="${EventStatus.PENDING}"   ${curStatus==EventStatus.PENDING?'selected':''}>На проверке</option>
      </select>
      <button class="btn-approve" onclick="applyEventDetailStatus()">Сохранить</button>
      <a class="btn-mail" href="${(window.APP_BASE||'')}/event/${eventId}" target="_blank">🔗 Открыть</a>
    `;
  } catch(err) { document.getElementById('event-detail-body').innerHTML = `<div class="empty"><div class="empty-icon">⚠️</div><div>${err.message}</div></div>`; }
}

async function applyEventDetailStatus() {
  const select = document.getElementById('event-detail-status-select');
  if (!select || !_currentDetailEventId) return;
  await setEventStatus(_currentDetailEventId, select.value);
  closeEventDetail();
}
function closeEventDetail() { document.getElementById('event-detail-overlay').style.display = 'none'; _currentDetailEventId = null; }

function switchTab(tab, el) {
  currentTab = tab;
  ['orgs','reviews','events','users','logs'].forEach(t => {
    document.getElementById('tab-' + t).style.display = t === tab ? '' : 'none';
  });
  document.querySelectorAll('.mod-tab').forEach(b => b.classList.remove('active'));
  el.classList.add('active');
  if (tab === 'reviews' && document.getElementById('reviews-list').querySelector('.loader'))  loadReviews();
  if (tab === 'events'  && document.getElementById('events-list').querySelector('.loader'))   loadModEvents();
  if (tab === 'users'   && document.getElementById('users-list').querySelector('.loader'))    loadUsers();
  if (tab === 'logs'    && document.getElementById('logs-list').querySelector('.loader'))     loadLogs();
}

loadStats();
loadOrgs('pending');
</script>
@endsection
