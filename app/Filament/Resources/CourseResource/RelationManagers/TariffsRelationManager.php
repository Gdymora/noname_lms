<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TariffsRelationManager extends RelationManager
{
    protected static string $relationship = 'tariffs';
    protected static ?string $title = 'Тарифы курса';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Название тарифа')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('price')
                    ->label('Цена (руб)')
                    ->numeric()
                    ->prefix('₽')
                    ->required(),

                // === НОВЫЕ ПОЛЯ ===
                Forms\Components\Section::make('Telegram для этого тарифа')
                    ->schema([
                        Forms\Components\TextInput::make('telegram_channel_link')
                            ->label('Ссылка на Канал')
                            ->url(),
                        Forms\Components\TextInput::make('telegram_chat_link')
                            ->label('Ссылка на Чат')
                            ->url(),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Название'),
                Tables\Columns\TextColumn::make('price')->money('rub')->label('Цена'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}