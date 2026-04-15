<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Настройки системы';
    protected static ?string $navigationLabel = 'Пользователи';

    protected static ?string $modelLabel = 'Пользователь';
    protected static ?string $pluralModelLabel = 'Пользователи';

    // === ГЛОБАЛЬНАЯ ФИЛЬТРАЦИЯ (КТО КОГО ВИДИТ) ===
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        if (auth()->user()->hasRole('Super Admin')) {
            return $query;
        }

        if (auth()->user()->hasRole('Teacher')) {
            // Учитель видит только тех, кто купил его курсы + себя
            return $query->whereHas('orders.course', function ($q) {
                $q->where('teacher_id', auth()->id());
            })
            ->orWhere('id', auth()->id());
        }

        return $query->where('id', -1);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // === ЛЕВАЯ КОЛОНКА (ОСНОВНОЕ) ===
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Профиль')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Имя')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),

                                Forms\Components\TextInput::make('phone')
                                    ->label('Телефон')
                                    ->tel()
                                    ->maxLength(20),

                                Forms\Components\TextInput::make('password')
                                    ->label('Пароль')
                                    ->password()
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->visible(fn () => auth()->user()->hasRole('Super Admin')),
                            ])->columns(2),

                        // Секция: Маркетинг (UTM)
                        Forms\Components\Section::make('Маркетинговая информация')
                            ->icon('heroicon-o-chart-bar')
                            ->description('Параметры перехода при регистрации.')
                            ->schema([
                                Forms\Components\KeyValue::make('utm_data')
                                    ->label('UTM Метки')
                                    ->keyLabel('Параметр')
                                    ->valueLabel('Значение')
                                    ->disabled(), // Только чтение
                            ])
                            ->collapsible()
                            ->collapsed() // Свернуто по умолчанию
                            ->visible(fn (?User $record) => $record && !empty($record->utm_data)),
                    ])
                    ->columnSpan(['lg' => 2]),

                // === ПРАВАЯ КОЛОНКА (НАСТРОЙКИ) ===
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Доступ и Роли')
                            ->schema([
                                Forms\Components\Select::make('roles')
                                    ->label('Роль')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->visible(fn () => auth()->user()->hasRole('Super Admin')),
                                
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Активен')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->default(true)
                                    ->visible(fn () => auth()->user()->hasRole('Super Admin')),
                            ]),

                        Forms\Components\Section::make('Метаданные')
                            ->schema([
                                Forms\Components\Placeholder::make('created_at')
                                    ->label('Зарегистрирован')
                                    ->content(fn (User $record): ?string => $record->created_at?->diffForHumans()),
                                
                                Forms\Components\Placeholder::make('updated_at')
                                    ->label('Последнее обновление')
                                    ->content(fn (User $record): ?string => $record->updated_at?->diffForHumans()),
                            ])
                            ->hidden(fn (string $operation): bool => $operation === 'create'),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('Фото')
                    ->circular()
                    ->defaultImageUrl('https://ui-avatars.com/api/?background=random'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Роль')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Super Admin' => 'danger',
                        'Teacher' => 'warning',
                        'Manager' => 'info',
                        'Curator' => 'primary',
                        'Student' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('utm_data')
                    ->label('UTM')
                    ->boolean()
                    ->trueIcon('heroicon-o-chart-bar')
                    ->falseIcon('')
                    ->getStateUsing(fn (User $record) => !empty($record->utm_data))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Регистрация')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Роль')
                    ->relationship('roles', 'name'),
                
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (User $record) => auth()->user()->hasRole('Super Admin') && $record->id !== auth()->id()),
                
                Tables\Actions\RestoreAction::make()
                    ->visible(fn () => auth()->user()->hasRole('Super Admin')),

                Tables\Actions\ForceDeleteAction::make()
                    ->visible(fn () => auth()->user()->hasRole('Super Admin')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole('Super Admin')),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole('Super Admin')),
                    Tables\Actions\RestoreBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole('Super Admin')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}