<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Статус организации</title>
<style>
  body { margin:0; padding:0; background:#f4f4f4; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; }
  .wrap { max-width:560px; margin:40px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.08); }
  .header { padding:36px 40px; text-align:center; }
  .header-approved { background:linear-gradient(135deg,#14532d 0%,#166534 100%); }
  .header-rejected { background:linear-gradient(135deg,#7f1d1d 0%,#991b1b 100%); }
  .logo { font-size:1.5rem; font-weight:700; color:#fff; font-style:italic; }
  .header-sub { color:rgba(255,255,255,.7); font-size:.88rem; margin-top:4px; }
  .body { padding:36px 40px; }
  h1 { font-size:1.4rem; font-weight:700; color:#1a1a2e; margin:0 0 12px; }
  p { color:#555; line-height:1.65; margin:0 0 16px; font-size:.95rem; }
  .status-badge { display:inline-block; padding:6px 18px; border-radius:20px; font-weight:700; font-size:.88rem; margin-bottom:20px; }
  .badge-approved { background:#dcfce7; color:#166534; }
  .badge-rejected { background:#fee2e2; color:#991b1b; }
  .reason-box { background:#fff7ed; border:1px solid #fed7aa; border-radius:10px; padding:14px 18px; margin:16px 0; }
  .reason-box strong { color:#9a3412; display:block; margin-bottom:6px; font-size:.82rem; text-transform:uppercase; letter-spacing:.05em; }
  .btn { display:inline-block; background:#c8502a; color:#fff; text-decoration:none; padding:14px 32px; border-radius:10px; font-weight:700; font-size:1rem; }
  .footer { background:#f9f9f9; padding:20px 40px; text-align:center; font-size:.75rem; color:#aaa; border-top:1px solid #eee; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header {{ $approved ? 'header-approved' : 'header-rejected' }}">
    <div class="logo">АфишаКолыма</div>
    <div class="header-sub">{{ $approved ? '✅ Одобрение организации' : '❌ Отклонение заявки' }}</div>
  </div>
  <div class="body">
    <span class="status-badge {{ $approved ? 'badge-approved' : 'badge-rejected' }}">
      {{ $approved ? '✅ ОДОБРЕНО' : '❌ ОТКЛОНЕНО' }}
    </span>
    <h1>{{ $orgName }}</h1>

    @if($approved)
    <p>Поздравляем! Ваша организация прошла модерацию и теперь одобрена. Вы можете публиковать события и площадки на АфишаКолыма.</p>
    <p style="text-align:center"><a class="btn" href="{{ url('/org/cabinet') }}">Перейти в кабинет</a></p>
    @else
    <p>К сожалению, ваша заявка была отклонена модератором.</p>
    @if($rejectionReason)
    <div class="reason-box">
      <strong>Причина отклонения</strong>
      {{ $rejectionReason }}
    </div>
    @endif
    <p>Если вы считаете, что это ошибка, или хотите исправить данные и подать заявку повторно — свяжитесь с нами.</p>
    @endif
  </div>
  <div class="footer">© {{ date('Y') }} АфишаКолыма. Магадан.</div>
</div>
</body>
</html>
