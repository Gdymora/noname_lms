<?php

namespace App\Jobs;

use App\Mail\PromotionalEmail;
use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // Даем джобу час на выполнение (если база огромная)

    public function __construct(
        public Newsletter $newsletter
    ) {}

    public function handle(): void
    {
        // 1. Меняем статус на "В процессе"
        $this->newsletter->update(['status' => 'processing']);

        // 2. Собираем пользователей по фильтрам
        $filters = $this->newsletter->recipients_filter ?? [];
        
        $query = User::query()
            ->whereNotNull('email')
            // ВАЖНО: Шлем только тем, кто согласился на маркетинг!
            ->whereNotNull('accepted_marketing_at'); 

        // Фильтр: Купил определенный курс
        if (!empty($filters['course_id'])) {
            $query->whereHas('orders', function ($q) use ($filters) {
                $q->whereIn('course_id', $filters['course_id'])
                  ->where('status', 'paid');
            });
        }

        // Фильтр: Имеет определенную роль (например, только Студенты)
        if (!empty($filters['roles'])) {
            $query->role($filters['roles']);
        }

        // 3. Отправляем письма (chunks для экономии памяти)
        // Мы используем chunk, чтобы не грузить 10000 юзеров в память сразу
        $query->chunk(100, function ($users) {
            foreach ($users as $user) {
                // Отправляем каждое письмо в очередь
                Mail::to($user->email)->queue(new PromotionalEmail($this->newsletter));
            }
        });

        // 4. Финиш
        $this->newsletter->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}