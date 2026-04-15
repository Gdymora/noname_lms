<?php

namespace App\Filament\Pages;

use App\Models\Form;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class FormAnalyticsDetail extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.form-analytics-detail';
    
    // Скрываем из меню
    protected static bool $shouldRegisterNavigation = false;

    public Form $record;
    public array $charts = [];
    public int $totalSubmissions = 0;

    public static function getRoutePath(): string
    {
        return '/form-analytics/{form_id}';
    }

    // ИСПРАВЛЕНИЕ: Динамический заголовок через метод
    public function getTitle(): string | Htmlable
    {
        return 'Аналитика: ' . $this->record->title;
    }

    public function mount($form_id): void
    {
        $this->record = Form::findOrFail($form_id);
        // Строку $this->title = ... МЫ УБРАЛИ, она вызывала ошибку
        
        $this->calculateStats();
    }

    private function calculateStats(): void
    {
        $submissions = $this->record->submissions;
        $this->totalSubmissions = $submissions->count();

        if ($this->totalSubmissions === 0) return;

        $schema = $this->record->schema ?? [];

        foreach ($schema as $field) {
            if (in_array($field['type'], ['select', 'radio'])) {
                
                $answers = $submissions->map(function ($sub) use ($field) {
                    return $sub->data[$field['name']] ?? null;
                })->filter();

                $counts = $answers->countBy();
                
                $this->charts[] = [
                    'id' => 'chart_' . $field['name'],
                    'label' => $field['label'],
                    'type' => 'doughnut',
                    'labels' => $counts->keys()->toArray(),
                    'data' => $counts->values()->toArray(),
                    'colors' => $counts->map(fn() => '#' . substr(md5(mt_rand()), 0, 6))->values()->toArray(),
                ];
            }
        }
    }
}