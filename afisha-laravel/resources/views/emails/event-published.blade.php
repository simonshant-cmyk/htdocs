<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Новое событие</title>
<style>
  body { margin:0; padding:0; background:#f4f4f4; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; }
  .wrap { max-width:560px; margin:40px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.08); }
  .header { background:linear-gradient(135deg,#1a1a2e 0%,#c8502a 100%); padding:36px 40px; text-align:center; }
  .logo { font-size:1.5rem; font-weight:700; color:#fff; font-style:italic; }
  .header-sub { color:rgba(255,255,255,.7); font-size:.88rem; margin-top:4px; }
  .body { padding:36px 40px; }
  h1 { font-size:1.4rem; font-weight:700; color:#1a1a2e; margin:0 0 12px; }
  p { color:#555; line-height:1.65; margin:0 0 16px; font-size:.95rem; }
  .event-card { background:#f9f9f9; border:1px solid #eee; border-radius:12px; padding:20px 24px; margin-bottom:20px; }
  .event-title { font-weight:700; color:#1a1a2e; font-size:1.05rem; margin-bottom:10px; }
  .event-meta { font-size:.84rem; color:#888; margin-bottom:5px; }
  .org-tag { display:inline-block; background:#fff3cd; border:1px solid #ffc107; border-radius:6px; padding:4px 10px; font-size:.8rem; color:#856404; font-weight:600; margin-bottom:14px; }
  .btn { display:inline-block; background:#c8502a; color:#fff; text-decoration:none; padding:14px 32px; border-radius:10px; font-weight:700; font-size:1rem; }
  .footer { background:#f9f9f9; padding:20px 40px; text-align:center; font-size:.75rem; color:#aaa; border-top:1px solid #eee; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="logo">АфишаКолыма</div>
    <div class="header-sub">🔔 Новое событие от организатора</div>
  </div>
  <div class="body">
    <h1>Привет, {{ $recipientName }}!</h1>
    <p>Организатор, на которого вы подписаны, опубликовал новое событие:</p>

    <span class="org-tag">🏢 {{ $orgName }}</span>

    <div class="event-card">
      <div class="event-title">{{ $eventTitle }}</div>
      @if($eventDate)
      <div class="event-meta">📅 {{ \Carbon\Carbon::parse($eventDate)->format('d.m.Y H:i') }}</div>
      @endif
      @if($venueName)
      <div class="event-meta">📍 {{ $venueName }}</div>
      @endif
    </div>

    <p style="text-align:center">
      <a class="btn" href="{{ url('/event/' . $eventId) }}">Смотреть событие</a>
    </p>
    <p style="font-size:.82rem;color:#aaa;text-align:center;margin-top:16px">
      Вы получили это письмо, так как подписаны на организатора «{{ $orgName }}».<br>
      Управлять подписками можно в <a href="{{ url('/cabinet') }}" style="color:#c8502a">личном кабинете</a>.
    </p>
  </div>
  <div class="footer">© {{ date('Y') }} АфишаКолыма. Магадан.</div>
</div>
</body>
</html>
