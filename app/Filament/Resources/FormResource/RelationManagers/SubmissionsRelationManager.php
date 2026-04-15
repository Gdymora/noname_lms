<?php

namespace App\Filament\Resources\FormResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

class SubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';

    protected static ?string $title = 'Ответы и Заявки';

    protected static ?string $icon = 'heroicon-o-inbox-arrow-down';

    // Форма нам не нужна (мы не редактируем ответы, только смотрим), но Filament требует метод
    public function form(Form $form): Form
    {
        return $form->schema([
            // Оставляем пустым или делаем read-only поля, если вдруг захочешь редактировать
             Forms\Components\KeyValue::make('data')
                ->label('Данные анкеты')
                ->disabled(),
        ]);
    }

    // === ТАБЛИЦА СПИСКА ===
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                // Показываем ID и Дату
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                
                // Пытаемся угадать, кто это (если юзер авторизован)
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Пользователь')
                    ->placeholder('Гость')
                    ->searchable(),

                // Показываем ключевые поля из JSON (например, email или phone), если они есть
                Tables\Columns\TextColumn::make('data.email')
                    ->label('Email (из формы)')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('data.phone')
                    ->label('Телефон')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Можно добавить экспорт в Excel (потребует доп. пакета, пока пропустим)
            ])
            ->actions([
                // Кнопка ПРОСМОТР (View) вместо Edit
                Tables\Actions\ViewAction::make()
                    ->modalHeading('Детали заявки'),
                    
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    // === КРАСИВЫЙ ПРОСМОТР (INFOLIST) ===
    // Это то, что откроется при нажатии на "Глаз"
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Информация о заявке')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Дата создания')
                            ->dateTime(),
                        
                        TextEntry::make('user.name')
                            ->label('Аккаунт в системе')
                            ->placeholder('Не привязан (Гость)'),
                    ])->columns(2),

                Section::make('Ответы пользователя')
                    ->schema([
                        // KeyValueEntry идеально подходит для отображения JSON "Вопрос -> Ответ"
                        KeyValueEntry::make('data')
                            ->label('Данные формы')
                            ->keyLabel('Поле')
                            ->valueLabel('Ответ'),
                    ]),

                Section::make('Маркетинг')
                    ->schema([
                        KeyValueEntry::make('utm_data')
                            ->label('UTM Метки')
                            ->placeholder('Нет данных'),
                    ])
                    ->collapsed(),
            ]);
    }
}