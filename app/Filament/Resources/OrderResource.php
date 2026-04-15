<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Продажи';
    protected static ?string $navigationLabel = 'Заказы';
    protected static ?string $modelLabel = 'Заказ';
    protected static ?string $pluralModelLabel = 'Заказы';

    // Бейджик: показываем количество новых заказов
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'new')->count() ?: null;
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    // === ФИЛЬТРАЦИЯ (SCOPE) ===
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Админ видит всё
        if (auth()->user()->hasRole('Super Admin')) {
            return $query;
        }

        // Менеджер видит всё
        if (auth()->user()->hasRole('Manager')) {
            return $query;
        }

        // Учитель видит только заказы на СВОИ курсы
        if (auth()->user()->hasRole('Teacher')) {
            return $query->whereHas('course', function ($q) {
                $q->where('teacher_id', auth()->id());
            });
        }

        return $query->where('id', -1);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ЛЕВАЯ КОЛОНКА (Детали)
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Детали заказа')
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->label('Клиент')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),

                                Forms\Components\Select::make('course_id')
                                    ->relationship('course', 'title')
                                    ->label('Курс')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),

                                // === ТАРИФ ===
                                Forms\Components\Select::make('tariff_id')
                                    ->relationship('tariff', 'name')
                                    ->label('Тариф')
                                    ->placeholder('Без тарифа (Стандарт)')
                                    ->disabled() 
                                    ->dehydrated(),

                                Forms\Components\TextInput::make('amount')
                                    ->label('Сумма (руб)')
                                    ->prefix('₽')
                                    ->numeric()
                                    ->disabled(),
                            ])->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),

                // ПРАВАЯ КОЛОНКА (Статусы)
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Управление')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Статус')
                                    ->options([
                                        'new' => 'Новый',
                                        'processing' => 'В работе',
                                        'paid' => 'Оплачен (Доступ открыт)',
                                        'cancelled' => 'Отменен',
                                        'refund' => 'Возврат',
                                    ])
                                    ->required()
                                    ->native(false),

                                Forms\Components\Select::make('manager_id')
                                    ->relationship('manager', 'name')
                                    ->label('Менеджер')
                                    ->searchable()
                                    ->preload(),
                                
                                Forms\Components\DateTimePicker::make('created_at')
                                    ->label('Создан')
                                    ->disabled(),

                                Forms\Components\DateTimePicker::make('paid_at')
                                    ->label('Дата оплаты'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Клиент')
                    ->searchable()
                    ->weight('bold')
                    // ИСПРАВЛЕНИЕ: Используем ?-> для безопасного доступа, если юзер удален
                    ->description(fn (Order $record) => $record->user?->email ?? 'Удален'),

                Tables\Columns\TextColumn::make('course.title')
                    ->label('Курс')
                    ->limit(20)
                    // ИСПРАВЛЕНИЕ: Используем ?-> для безопасного доступа, если курс удален
                    ->tooltip(fn (Order $record) => $record->course?->title ?? 'Курс удален'),

                // ТАРИФ
                Tables\Columns\TextColumn::make('tariff.name')
                    ->label('Тариф')
                    ->badge() 
                    ->color('gray')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Сумма')
                    ->money('rub')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'new',
                        'primary' => 'processing',
                        'success' => 'paid',
                        'danger' => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('manager.name')
                    ->label('Менеджер')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->label('Дата'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'Новые',
                        'paid' => 'Оплаченные',
                        'cancelled' => 'Отмена',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()->hasRole('Super Admin')),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderNotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; 
    }
}