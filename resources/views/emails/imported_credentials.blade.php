<!DOCTYPE html>
<html>
<head>
    <title>Доступы к платформе</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; padding: 20px; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .h1 { color: #333; margin-bottom: 20px; }
        .credentials { background: #f0f4f8; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #4f46e5; }
        .label { font-size: 12px; color: #666; text-transform: uppercase; font-weight: bold; }
        .value { font-size: 18px; color: #000; font-family: monospace; margin-bottom: 10px; }
        .btn { display: inline-block; background-color: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
        .footer { margin-top: 30px; font-size: 12px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Здравствуйте, {{ $user->name }}!</h1>
        
        <p>Мы перенесли обучение на новую, удобную платформу. Для вас уже создан личный кабинет, где находятся все ваши курсы.</p>
        
        <p>Вот ваши данные для входа:</p>
        
        <div class="credentials">
            <div class="label">Логин (Email):</div>
            <div class="value">{{ $user->email }}</div>
            
            <div class="label">Пароль:</div>
            <div class="value">{{ $rawPassword }}</div>
        </div>
        
        <p>Пожалуйста, сохраните эти данные или смените пароль после первого входа в настройках профиля.</p>
        
        <center>
            <a href="{{ route('login') }}" class="btn">Войти в кабинет</a>
        </center>

        <div class="footer">
            Если кнопка не работает, перейдите по ссылке: {{ route('login') }}
        </div>
    </div>
</body>
</html>