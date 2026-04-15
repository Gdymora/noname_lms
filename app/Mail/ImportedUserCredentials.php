<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ImportedUserCredentials extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $rawPassword // Пароль в открытом виде
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ваш доступ к обучающей платформе',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.imported_credentials',
        );
    }
}