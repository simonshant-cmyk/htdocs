<!DOCTYPE html>
<html lang="ru" @if(isset($ogData)) @endif>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="theme-color" content="#c8502a">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="АфишаКолыма">

@if(isset($ogData))
<meta property="og:type"        content="{{ $ogData['type'] ?? 'website' }}">
<meta property="og:title"       content="{{ $ogData['title'] }}">
<meta property="og:description" content="{{ $ogData['description'] ?? '' }}">
@if(!empty($ogData['image']))
<meta property="og:image"       content="{{ $ogData['image'] }}">
@endif
<meta property="og:url"         content="{{ request()->url() }}">
<meta property="og:locale"      content="ru_RU">
<meta name="twitter:card"       content="summary_large_image">
<meta name="twitter:title"      content="{{ $ogData['title'] }}">
<meta name="twitter:description" content="{{ $ogData['description'] ?? '' }}">
@if(!empty($ogData['image']))
<meta name="twitter:image"      content="{{ $ogData['image'] }}">
@endif
@endif

<title>@yield('title', 'АфишаКолыма — События и площадки')</title>
<script>try{if(JSON.parse(localStorage.getItem('theme'))==='dark')document.documentElement.setAttribute('data-theme','dark')}catch(e){}</script>
<link rel="stylesheet" href="{{ asset('css/main.css') }}">
@yield('styles')
</head>
<body>

<nav class="navbar" id="navbar"></nav>

@yield('content')

<div id="site-footer"></div>
<div id="toast"></div>
<script>
window.APP_BASE = '{{ rtrim(url('/'), '/') }}';
function nav(path) { window.location.href = window.APP_BASE + path; }
</script>
<script src="{{ asset('js/main.js') }}"></script>
@yield('scripts')

</body>
</html>
