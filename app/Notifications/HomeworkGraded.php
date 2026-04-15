<?php

namespace App\Notifications;

use App\Models\HomeworkSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // <--- Важно для очереди
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Добавляем implements ShouldQueue, чтобы письмо шло в фон
class HomeworkGraded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public HomeworkSubmission $submission
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusText = match($this->submission->status) {
            'approved' => '✅ Принято',
            'rejected' => '❌ Отклонено',
            'revision' => '⚠️ На доработку',
            default => 'Проверено'
        };

        return (new MailMessage)
            ->subject('Результат проверки ДЗ: ' . $this->submission->homework->lesson->title)
            ->greeting('Привет, ' . $notifiable->name . '!')
            ->line('Преподаватель проверил вашу работу.')
            ->line('Статус: ' . $statusText)
            ->line('Оценка: ' . ($this->submission->grade_percent ? $this->submission->grade_percent . '%' : '-'))
            ->when($this->submission->curator_comment, function (MailMessage $mail) {
                 return $mail->line('Комментарий: "' . $this->submission->curator_comment . '"');
            })
            ->action('Посмотреть результат', route('learning.lesson', [
                'course' => $this->submission->homework->lesson->module->course->slug,
                'lessonSlug' => $this->submission->homework->lesson->slug
            ]));
    }
}