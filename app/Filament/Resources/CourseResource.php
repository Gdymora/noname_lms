<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Управление контентом';
    protected static ?string $navigationLabel = 'Курсы';
    protected static ?string $modelLabel = 'Курс';
    protected static ?string $pluralModelLabel = 'Курсы';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('Super Admin')) {
            return $query;
        }

        if (auth()->user()->hasRole('Teacher')) {
            return $query->where('teacher_id', auth()->id());
        }
        
        return $query->where('id', -1);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // === ЛЕВАЯ КОЛОНКА (Контент - 2/3 ширины) ===
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Основная информация')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Название курса')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('slug')
                                    ->label('Ссылка (URL)')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->prefix(url('/c/').'/')
                                    ->columnSpanFull(),

                                // Заменили Textarea на RichEditor для красоты
                                Forms\Components\RichEditor::make('description')
                                    ->label('Описание курса')
                                    ->columnSpanFull()
                                    ->toolbarButtons([
                                        'bold', 'italic', 'bulletList', 'orderedList', 'link', 'h2', 'h3',
                                    ]),
                            ]),

                        Forms\Components\Section::make('Команда курса')
                            ->schema([
                                Forms\Components\Select::make('teacher_id')
                                    ->relationship('teacher', 'name')
                                    ->label('Главный преподаватель')
                                    ->disabled(fn () => !auth()->user()->hasRole('Super Admin'))
                                    ->dehydrated()
                                    ->required()
                                    ->default(auth()->id())
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('curators')
                                    ->label('Кураторы')
                                    ->relationship('curators', 'name', function ($query) {
                                        return $query->role('Curator');
                                    })
                                    ->multiple()
                                    ->preload()
                                    ->searchable(),
                            ])->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),

                // === ПРАВАЯ КОЛОНКА (Настройки - 1/3 ширины) ===
                Forms\Components\Group::make()
                    ->schema([
                        
                        // Карточка статуса
                        Forms\Components\Section::make('Статус')
                            ->schema([
                                Forms\Components\Toggle::make('is_published')
                                    ->label('Опубликован')
                                    ->helperText('Виден в каталоге')
                                    ->onColor('success')
                                    ->offColor('gray')
                                    ->default(false),
                                
                                Forms\Components\DateTimePicker::make('starts_at')
                                    ->label('Старт потока')
                                    ->native(false),

                                Forms\Components\DateTimePicker::make('ends_at')
                                    ->label('Конец потока')
                                    ->native(false),
                            ]),

                        // Карточка цены
                        Forms\Components\Section::make('Стоимость')
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label('Базовая цена')
                                    ->numeric()
                                    ->prefix('₽')
                                    ->default(0)
                                    ->helperText('Если 0 — бесплатно (или действуют тарифы).'),
                            ]),

                        // Карточка медиа
                        Forms\Components\Section::make('Медиа')
                            ->schema([
                                Forms\Components\FileUpload::make('thumbnail_url')
                                    ->label('Обложка')
                                    ->image()
                                    ->directory('course-thumbnails')
                                    ->visibility('public')
                                    ->imageEditor(), // Можно обрезать картинку прямо в админке!
                            ]),

                        // Карточка ссылок
                        Forms\Components\Section::make('Ссылки')
                            ->schema([
                                Forms\Components\TextInput::make('public_link')
                                    ->label('Лендинг')
                                    ->formatStateUsing(fn (?Course $record) => $record ? route('public.course.show', $record->slug) : null)
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffixAction(
                                        \Filament\Forms\Components\Actions\Action::make('copy')
                                            ->icon('heroicon-m-clipboard')
                                            ->action(function ($state, $livewire) {
                                                // Копирование в буфер (JS) через нативный метод Filament
                                                $livewire->js("window.navigator.clipboard.writeText('{$state}')");
                                                \Filament\Notifications\Notification::make()->title('Скопировано')->success()->send();
                                            })
                                    ),
                                    
                                Forms\Components\TextInput::make('telegram_channel_link')
                                    ->label('Канал TG')
                                    ->prefixIcon('heroicon-o-paper-airplane')
                                    ->url(),
                                    
                                Forms\Components\TextInput::make('telegram_chat_link')
                                    ->label('Чат TG')
                                    ->prefixIcon('heroicon-o-chat-bubble-left-right')
                                    ->url(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3); // Используем сетку из 3 колонок
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_url')
                    ->label('Фото')
                    ->circular(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(30),

                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Автор')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('rub')
                    ->sortable(),

                // Используем ToggleColumn для быстрого переключения
                Tables\Columns\ToggleColumn::make('is_published')
                    ->label('Публикация'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('published')
                    ->query(fn ($query) => $query->where('is_published', true))
                    ->label('Только опубликованные'),
            ])
            ->actions([
                // Кнопка "Открыть на сайте"
                Tables\Actions\Action::make('visit')
                    ->label('На сайт')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Course $record) => route('public.course.show', $record->slug))
                    ->openUrlInNewTab()
                    ->color('gray'),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ModulesRelationManager::class,
            RelationManagers\TariffsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}