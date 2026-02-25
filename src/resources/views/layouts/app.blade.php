<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-param" content="_token">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel Chat'))</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    @stack('styles')
    @vite(['resources/css/app.css'])
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            {{ config('app.name', 'Laravel Chat') }}
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Вход</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Регистрация</a></li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('chat') }}">Чат</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Выйти</a></li>
                        </ul>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>

<main class="py-4">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

<script>
    console.log('🛠️ Инициализация Echo с кастомным Pusher');

    // Делаем Pusher глобальным (обязательно)
    window.Pusher = Pusher;

    // Ждём загрузки Pusher
    function initEcho() {
        if (typeof Pusher === 'undefined') {
            console.warn('Pusher ещё не загружен');
            setTimeout(initEcho, 300);
            return;
        }

        console.log('Pusher загружен');

        try {
            window.Echo = new Echo({
                broadcaster: 'pusher',  // ← важно: 'pusher', а НЕ 'reverb'
                key: '{{ env('REVERB_APP_KEY') }}',

                // Точная копия конфигурации из твоего тестового HTML
                wsHost: '127.0.0.1',          // или 'localhost', или 'host.docker.internal'
                wsPort: 8082,
                forceTLS: false,
                encrypted: false,
                disableStats: true,
                enabledTransports: ['ws'],
            });

            console.log('Echo создан с кастомным Pusher');

            // Отладка
            window.Echo.connector.pusher.connection.bind('connected', () => {
                console.log('✅ WebSocket соединение установлено');
            });

            window.Echo.connector.pusher.connection.bind('error', err => {
                console.error('❌ Ошибка соединения:', err);
            });

            window.Echo.connector.pusher.connection.bind('disconnected', () => {
                console.warn('⚠️ Соединение разорвано');
            });

        } catch (e) {
            console.error('Ошибка при создании Echo:', e);
        }
    }

    // Запускаем
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEcho);
    } else {
        initEcho();
    }
</script>

@stack('scripts')
@vite(['resources/js/app.js'])
</body>
</html>
