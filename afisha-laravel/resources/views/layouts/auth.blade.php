<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#c8502a">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="АфишаКолыма">
<title>@yield('title', 'АфишаКолыма')</title>
<script>try{if(JSON.parse(localStorage.getItem('theme'))==='dark')document.documentElement.setAttribute('data-theme','dark')}catch(e){}</script>
<link rel="stylesheet" href="{{ asset('css/main.css') }}">
@yield('styles')
</head>
<body>
@yield('content')
<div id="toast"></div>
<script>
window.APP_BASE = '{{ rtrim(url('/'), '/') }}';
function nav(path) { window.location.href = window.APP_BASE + path; }
</script>
<script src="{{ asset('js/main.js') }}"></script>
@yield('scripts')
</body>
</html>
