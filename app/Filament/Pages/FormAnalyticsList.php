<?php

namespace App\Filament\Pages;

use App\Models\Form;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class FormAnalyticsList extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationLabel = 'Аналитика Анкет';
    protected static ?string $title = 'Статистика по анкетам';
    protected static ?string $navigationGroup = 'Маркетинг';
    
    protected static string $view = 'filament.pages.form-analytics-list';

    public function table(Table $table): Table
    {
        return $table
            ->query(Form::query()->withCount('submissions'))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Анкета')
                    ->searchable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('slug')
                    ->label('Ссылка')
                    ->prefix('/f/')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('submissions_count')
                    ->label('Всего ответов')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('view_stats')
                    ->label('Смотреть отчет')
                    ->icon('heroicon-o-presentation-chart-bar')
                    // ВАЖНО: Изменили ключ параметра с 'record' на 'form_id'
                    ->url(fn (Form $record) => route('filament.admin.pages.form-analytics-detail', ['form_id' => $record->id])),
            ]);
    }
}