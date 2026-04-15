<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ModulesRelationManager extends RelationManager
{
    protected static string $relationship = 'modules';

    // Меняем заголовок на русский
    protected static ?string $title = 'Модули курса';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Название модуля')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\Textarea::make('description')
                    ->label('Описание')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Порядок сортировки')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // ВАЖНО: Показываем только корневые модули (у которых нет parent_id)
            ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('parent_id'))
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Название'),
                
                // Считаем количество уроков внутри (автомагия Laravel)
                Tables\Columns\TextColumn::make('lessons_count')
                    ->counts('lessons')
                    ->label('Уроков'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить модуль')
                    ->slideOver(), // Открывать форму в шторке справа, а не в модалке
            ])
            ->actions([
                Tables\Actions\EditAction::make()->slideOver(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc'); // Сортировать по порядку
    }
}