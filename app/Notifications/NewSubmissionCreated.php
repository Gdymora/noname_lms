<?php

namespace App\Notifications;

use App\Models\HomeworkSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSubmissionCreated extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('Новая сдача ДЗ от ' . $this->submission->student->name)
            ->line('Студент сдал работу к уроку: ' . $this->submission->homework->lesson->title)
            ->action('Перейти к проверке', url('/admin/homework-submissions/' . $this->submission->id . '/edit'));
    }
}