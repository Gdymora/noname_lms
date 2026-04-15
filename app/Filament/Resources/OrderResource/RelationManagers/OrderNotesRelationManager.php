<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrderNotesRelationManager extends RelationManager
{
    protected static string $relationship = 'orderNotes';

    protected static ?string $title = 'История работы с клиентом (Чат)';

    protected static ?string $icon = 'heroicon-o-chat-bubble-left-right';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('content')
                    ->label('Текст заметки')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                // ИСПОЛЬЗУЕМ STACK LAYOUT ДЛЯ ЭФФЕКТА ЧАТА
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\Layout\Split::make([
                        // Имя автора и Аватарка
                        Tables\Columns\ImageColumn::make('user.avatar_url')
                            ->circular()
                            ->defaultImageUrl('https://ui-avatars.com/api/?background=random')
                            ->grow(false), // Не растягивать
                        
                        Tables\Columns\TextColumn::make('user.name')
                            ->weight('bold')
                            ->searchable(),
                        
                        // Дата сообщения (серым цветом)
                        Tables\Columns\TextColumn::make('created_at')
                            ->dateTime('d.m.Y H:i')
                            ->color('gray')
                            ->alignEnd(),
                    ]),

                    // Само сообщение
                    Tables\Columns\TextColumn::make('content')
                        ->markdown() // Можно использовать Markdown
                        ->extraAttributes(['class' => 'py-2 text-sm text-gray-700']),
                ])->space(3), // Отступ между блоками
            ])
            ->contentGrid([
                'md' => 1, // Одна колонка (как лента)
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить заметку')
                    ->icon('heroicon-o-pencil-square')
                    ->modalWidth('lg')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Автоматически подставляем текущего менеджера
                        $data['user_id'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                // Разрешаем редактировать/удалять только свои заметки (или если Админ)
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->user_id === auth()->id() || auth()->user()->hasRole('Super Admin')),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->user_id === auth()->id() || auth()->user()->hasRole('Super Admin')),
            ])
            ->bulkActions([
                // Убираем массовые действия, здесь они не нужны
            ]);
    }
}