<!DOCTYPE html>
<html>
<head>
    <title>{{ $newsletter->subject }}</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .footer { margin-top: 30px; font-size: 12px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        {{-- Выводим HTML из редактора (будь осторожен, это доверяемый контент админа) --}}
        {!! $newsletter->content !!}

        <div class="footer">
            <p>Вы получили это письмо, так как подписались на рассылку платформы.</p>
            {{-- Тут в будущем можно добавить ссылку на отписку --}}
        </div>
    </div>
</body>
</html>