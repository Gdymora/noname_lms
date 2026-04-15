<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class IncomeChart extends ChartWidget
{
    protected static ?string $heading = 'Динамика доходов (за год)';
    
    // Сортировка: пусть график будет под карточками
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $user = auth()->user();

        // Формируем запрос
        $query = Order::query()->where('status', 'paid');

        // Если учитель - фильтруем
        if ($user->hasRole('Teacher')) {
            $query->whereHas('course', fn($q) => $q->where('teacher_id', $user->id));
        }

        // Магия пакета Trend: группировка по месяцам
        $data = Trend::query($query)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->sum('amount');

        return [
            'datasets' => [
                [
                    'label' => 'Доход (в рублях)',
                    // Делим на 100, чтобы получить рубли, а не копейки
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => '#6366f1', // Indigo цвет
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}