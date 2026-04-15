<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeStudent extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Получаем название приложения из конфига (из .env)
        $appName = config('app.name');

        return (new MailMessage)
            ->subject('Добро пожаловать в ' . $appName . '! 🚀')
            ->greeting('Привет, ' . $notifiable->name . '!')
            ->line('Спасибо за регистрацию на нашей платформе.')
            ->line('Теперь вы можете выбирать курсы и проходить обучение.')
            ->action('Перейти в каталог', url('/courses'))
            ->line('Успехов в учебе!');
    }
}