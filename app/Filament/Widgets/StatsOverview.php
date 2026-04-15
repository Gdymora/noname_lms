<?php

namespace App\Filament\Widgets;

use App\Models\HomeworkSubmission;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class StatsOverview extends BaseWidget
{
    // Обновлять данные каждые 15 секунд (живой дашборд)
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $user = auth()->user();
        $isTeacher = $user->hasRole('Teacher');
        $isAdmin = $user->hasRole('Super Admin');

        // 1. ЗАПРОС ДЛЯ ЗАКАЗОВ (ДОХОД)
        $ordersQuery = Order::query()->where('status', 'paid');
        
        if ($isTeacher) {
            $ordersQuery->whereHas('course', fn($q) => $q->where('teacher_id', $user->id));
        }

        $income = $ordersQuery->sum('amount'); // Сумма в копейках
        $ordersCount = $ordersQuery->count();


        // 2. ЗАПРОС ДЛЯ ДЗ НА ПРОВЕРКЕ
        $homeworksQuery = HomeworkSubmission::query()->where('status', 'pending');

        if ($isTeacher) {
            // Цепочка: Сдача -> ДЗ -> Урок -> Модуль -> Курс -> Учитель
            $homeworksQuery->whereHas('homework.lesson.module.course', fn($q) => $q->where('teacher_id', $user->id));
        }

        $pendingHomeworks = $homeworksQuery->count();


        return [
            Stat::make('Общий доход', number_format($income, 0, '.', ' ') . ' ₽')
                ->description($isTeacher ? 'С ваших курсов' : 'По всей платформе')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Оплаченных заказов', $ordersCount)
                ->description('Успешные продажи')
                ->color('primary'),

            Stat::make('ДЗ на проверке', $pendingHomeworks)
                ->description($pendingHomeworks > 0 ? 'Требуют внимания' : 'Всё проверено')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color($pendingHomeworks > 0 ? 'danger' : 'success'),
        ];
    }
}