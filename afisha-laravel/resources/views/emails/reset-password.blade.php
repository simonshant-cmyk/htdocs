<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Восстановление пароля</title>
<style>
  body { margin:0; padding:0; background:#f4f4f4; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; }
  .wrap { max-width:560px; margin:40px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.08); }
  .header { background:linear-gradient(135deg,#1a1a2e 0%,#16213e 100%); padding:36px 40px; text-align:center; }
  .logo { font-size:1.5rem; font-weight:700; color:#fff; font-style:italic; }
  .body { padding:36px 40px; }
  h1 { font-size:1.4rem; font-weight:700; color:#1a1a2e; margin:0 0 12px; }
  p { color:#555; line-height:1.65; margin:0 0 20px; font-size:.95rem; }
  .btn { display:inline-block; background:#c8502a; color:#fff; text-decoration:none; padding:14px 32px; border-radius:10px; font-weight:700; font-size:1rem; }
  .link-fallback { font-size:.78rem; color:#888; margin-top:20px; word-break:break-all; }
  .footer { background:#f9f9f9; padding:20px 40px; text-align:center; font-size:.75rem; color:#aaa; border-top:1px solid #eee; }
  .expire { color:#e57373; font-size:.82rem; margin-top:8px; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="logo">АфишаКолыма</div>
  </div>
  <div class="body">
    <h1>Восстановление пароля</h1>
    @if($recipientName)
    <p>Здравствуйте, {{ $recipientName }}!</p>
    @endif
    <p>Мы получили запрос на сброс пароля для вашего аккаунта. Нажмите на кнопку ниже, чтобы создать новый пароль:</p>
    <p style="text-align:center"><a class="btn" href="{{ $resetUrl }}">Сбросить пароль</a></p>
    <p class="expire">⏱ Ссылка действительна 60 минут.</p>
    <p>Если вы не запрашивали сброс пароля — просто проигнорируйте это письмо. Ваш пароль останется прежним.</p>
    <div class="link-fallback">Если кнопка не работает, скопируйте эту ссылку в браузер:<br>{{ $resetUrl }}</div>
  </div>
  <div class="footer">© {{ date('Y') }} АфишаКолыма. Магадан.</div>
</div>
</body>
</html>
