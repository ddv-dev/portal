<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body { font-family: Arial; padding: 50px; background: #f0f2f5; }
        .container { max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .user { color: #1a73e8; font-weight: bold; }
        .btn { display: inline-block; padding: 10px 20px; background: #1a73e8; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #1557b0; }
        .info { margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 5px; }
        .status { display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; }
        .online { background: #e6f4ea; color: #1e7e34; }
        .offline { background: #fce8e6; color: #d93025; }
    </style>
</head>
<body>
    <div class="container">
        <h1>👋 Добро пожаловать!</h1>
        <div class="info">
            <p><strong>Пользователь:</strong> <span class="user">{{ $user }}</span></p>
            <p><strong>Статус:</strong> <span class="status {{ $authenticated ? 'online' : 'offline' }}">{{ $authenticated ? '✅ Онлайн' : '❌ Гость' }}</span></p>
            <p><strong>Время:</strong> {{ now()->format('d.m.Y H:i:s') }}</p>
        </div>
        @if($authenticated)
            <a href="/logout" class="btn">🚪 Выйти</a>
        @endif
    </div>
</body>
</html>
