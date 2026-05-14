@extends('layouts.app')

@section('title', '500 — Ошибка сервера')

@section('styles')
<style>
  .error-page { min-height: calc(100vh - 68px - 120px); display: flex; align-items: center; justify-content: center; padding: 40px 24px; }
  .error-card { text-align: center; max-width: 480px; }
  .error-code { font-family: var(--font-display); font-size: 8rem; font-weight: 900; line-height: 1; color: var(--accent); opacity: .15; margin-bottom: -20px; }
  .error-icon { font-size: 4rem; margin-bottom: 20px; }
  .error-title { font-family: var(--font-display); font-size: 1.8rem; font-weight: 800; margin-bottom: 12px; }
  .error-sub { color: var(--muted); font-size: .95rem; line-height: 1.7; margin-bottom: 32px; }
  .error-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
</style>
@endsection

@section('content')
<div class="error-page">
  <div class="error-card">
    <div class="error-code">500</div>
    <div class="error-icon">⚙️</div>
    <h1 class="error-title">Что-то пошло не так</h1>
    <p class="error-sub">На сервере произошла ошибка. Мы уже знаем о проблеме и работаем над её устранением. Попробуйте вернуться чуть позже.</p>
    <div class="error-actions">
      <a href="/" class="btn btn-primary">На главную</a>
      <a href="javascript:location.reload()" class="btn btn-secondary">Обновить страницу</a>
    </div>
  </div>
</div>
@endsection
