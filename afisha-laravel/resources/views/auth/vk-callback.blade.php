<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>Вход через ВКонтакте…</title>
  <meta name="robots" content="noindex">
</head>
<body>
  <script>
    try {
      localStorage.setItem('token', @json($token));
      localStorage.setItem('user', @json($user));
      localStorage.setItem('auth_type', 'user');
    } catch (e) {}
    location.replace('/cabinet');
  </script>
  <noscript>Включите JavaScript, чтобы завершить вход. <a href="/cabinet">Продолжить</a></noscript>
</body>
</html>
