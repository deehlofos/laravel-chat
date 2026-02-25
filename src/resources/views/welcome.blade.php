<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Чат приложение</title>

    <!-- Bootstrap CSS (для простых стилей) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .welcome-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 90%;
        }

        h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        p {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .btn {
            padding: 12px 30px;
            margin: 0 10px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-login {
            background: #f0f0f0;
            color: #333;
            border: none;
        }

        .btn-login:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }

        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-chat {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.2rem;
        }

        .btn-chat:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="welcome-card">
    <div class="icon">💬</div>

    <h1>Добро пожаловать!</h1>

    <p>
        Простой чат для общения с друзьями и коллегами.
        Мгновенная доставка сообщений, никаких лишних функций.
    </p>

    @guest
        <div>
            <a href="{{ route('login') }}" class="btn btn-login">Войти</a>
            <a href="{{ route('register') }}" class="btn btn-register">Регистрация</a>
        </div>
    @else
        <div>
            <a href="{{ route('chat') }}" class="btn btn-chat">
                Перейти в чат →
            </a>
        </div>
    @endguest

    <div style="margin-top: 30px; color: #999; font-size: 0.9rem;">
        Версия 1.0
    </div>
</div>
</body>
</html>
