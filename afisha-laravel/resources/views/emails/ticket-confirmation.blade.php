<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Билеты куплены</title>
<style>
  body { margin:0; padding:0; background:#f4f4f4; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; }
  .wrap { max-width:560px; margin:40px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.08); }
  .header { background:linear-gradient(135deg,#1a1a2e 0%,#16213e 100%); padding:36px 40px; text-align:center; }
  .logo { font-size:1.5rem; font-weight:700; color:#fff; font-style:italic; }
  .header-sub { color:rgba(255,255,255,.6); font-size:.85rem; margin-top:4px; }
  .body { padding:36px 40px; }
  h1 { font-size:1.4rem; font-weight:700; color:#1a1a2e; margin:0 0 12px; }
  p { color:#555; line-height:1.65; margin:0 0 16px; font-size:.95rem; }
  .ticket-card { background:#f9f9f9; border:1px solid #eee; border-radius:12px; padding:16px 20px; margin-bottom:12px; }
  .ticket-title { font-weight:700; color:#1a1a2e; font-size:1rem; margin-bottom:4px; }
  .ticket-meta { font-size:.82rem; color:#888; margin-bottom:2px; }
  .ticket-price { font-weight:700; color:#c8502a; font-size:.95rem; margin-top:8px; }
  .total-row { display:flex; justify-content:space-between; align-items:center; background:#1a1a2e; color:#fff; border-radius:10px; padding:14px 20px; margin-top:20px; }
  .total-lbl { font-size:.88rem; opacity:.7; }
  .total-val { font-weight:700; font-size:1.1rem; }
  .btn { display:inline-block; background:#c8502a; color:#fff; text-decoration:none; padding:14px 32px; border-radius:10px; font-weight:700; font-size:1rem; }
  .footer { background:#f9f9f9; padding:20px 40px; text-align:center; font-size:.75rem; color:#aaa; border-top:1px solid #eee; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="logo">АфишаКолыма</div>
    <div class="header-sub">🎫 Подтверждение покупки</div>
  </div>
  <div class="body">
    <h1>Билеты успешно куплены!</h1>
    <p>Здравствуйте, {{ $recipientName }}! Ваша оплата прошла успешно. Ниже — детали вашей покупки.</p>

    @foreach($tickets as $t)
    <div class="ticket-card">
      <div class="ticket-title">{{ $t['title'] ?? 'Событие' }}</div>
      @if(!empty($t['start_datetime']))
      <div class="ticket-meta">📅 {{ \Carbon\Carbon::parse($t['start_datetime'])->format('d.m.Y H:i') }}</div>
      @endif
      @if(!empty($t['venue_name']))
      <div class="ticket-meta">📍 {{ $t['venue_name'] }}</div>
      @endif
      <div class="ticket-meta">🎫 Количество: {{ $t['quantity'] }}</div>
      <div class="ticket-price">{{ number_format((float)$t['price'] * (int)$t['quantity'], 0, '.', ' ') }} ₽</div>
    </div>
    @endforeach

    <div class="total-row">
      <span class="total-lbl">Итого оплачено ({{ $paymentMethod }})</span>
      <span class="total-val">{{ $totalFormatted }}</span>
    </div>

    <p style="margin-top:24px">Посмотреть все билеты можно в личном кабинете:</p>
    <p style="text-align:center"><a class="btn" href="{{ url('/tickets') }}">Мои билеты</a></p>
  </div>
  <div class="footer">© {{ date('Y') }} АфишаКолыма. Магадан.</div>
</div>
</body>
</html>
