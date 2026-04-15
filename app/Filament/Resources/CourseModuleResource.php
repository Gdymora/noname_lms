<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseModuleResource\Pages;
use App\Models\CourseModule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseModuleResource extends Resource
{
    protected static ?string $model = CourseModule::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationGroup = 'Управление контентом';
    protected static ?string $navigationLabel = 'Модули';
    protected static ?string $modelLabel = 'Модуль';
    protected static ?string $pluralModelLabel = 'Модули';

    // Глобальный фильтр (Scope)
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('Super Admin')) {
            return $query;
        }

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
                // ЛЕВАЯ КОЛОНКА: Основные данные
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Параметры модуля')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Название')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Например: Введение в PHP'),

                                Forms\Components\Textarea::make('description')
                                    ->label('Описание (для себя)')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Порядок сортировки')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Чем меньше число, тем выше модуль в списке.'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                // ПРАВАЯ КОЛОНКА: Привязки
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Структура')
                            ->icon('heroicon-o-link')
                            ->schema([
                                // 1. КУРС
                                Forms\Components\Select::make('course_id')
                                    ->relationship('course', 'title')
                                    ->label('Курс')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn (Forms\Set $set) => $set('parent_id', null)),

                                // 2. РОДИТЕЛЬ
                                Forms\Components\Select::make('parent_id')
                                    ->label('Родительский модуль')
                                    ->options(function (Get $get, ?CourseModule $record) {
                                        $courseId = $get('course_id');
                                        if (!$courseId) return [];

                                        $query = CourseModule::query()->where('course_id', $courseId);

                                        // Исключаем самого себя, чтобы не создать цикл
                                        if ($record) {
                                            $query->where('id', '!=', $record->id);
                                        }

                                        return $query->pluck('title', 'id');
                                    })
                                    ->searchable()
                                    ->placeholder('Это корневой модуль (без родителя)')
                                    ->disabled(fn (Get $get) => !$get('course_id')),
                            ]),

                        Forms\Components\Section::make('Доступ')
                            ->schema([
                                Forms\Components\Select::make('tariffs')
                                    ->relationship('tariffs', 'name')
                                    ->label('Ограничить тарифами')
                                    ->multiple()
                                    ->preload()
                                    ->options(function (Forms\Get $get) {
                                        $courseId = $get('course_id');
                                        if (!$courseId) return [];
                                        
                                        return \App\Models\Tariff::where('course_id', $courseId)
                                            ->pluck('name', 'id');
                                    })
                                    ->helperText('Если пусто — доступно всем.'),
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
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(40),
                
                Tables\Columns\TextColumn::make('course.title')
                    ->label('Курс')
                    ->sortable()
                    ->color('gray')
                    ->limit(20),

                Tables\Columns\TextColumn::make('parent.title')
                    ->label('Вложенность')
                    ->placeholder('📁 Корневой') // Красивая заглушка
                    ->badge()
                    ->color(fn ($state) => $state ? 'gray' : 'info'),

                // Счетчик уроков
                Tables\Columns\TextColumn::make('lessons_count')
                    ->counts('lessons')
                    ->label('Уроков')
                    ->alignCenter(),

                // Счетчик подмодулей
                Tables\Columns\TextColumn::make('children_count')
                    ->counts('children')
                    ->label('Подпапок')
                    ->color('gray')
                    ->alignCenter(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'title')
                    ->label('Курс'),
                
                // Фильтр: только корневые или вложенные
                Tables\Filters\Filter::make('root_only')
                    ->query(fn (Builder $query) => $query->whereNull('parent_id'))
                    ->label('Только корневые папки'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // Кнопка перехода к урокам этого модуля
                Tables\Actions\Action::make('view_lessons')
                    ->label('Уроки')
                    ->icon('heroicon-o-list-bullet')
                    ->url(fn (CourseModule $record) => route('filament.admin.resources.lessons.index', [
                        'tableFilters[course][value]' => $record->course_id,
                        // Можно добавить фильтр по тексту, но в Filament v3 передать фильтр 
                        // сложнее через URL, поэтому просто переходим в список уроков курса
                    ]))
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseModules::route('/'),
            'create' => Pages\CreateCourseModule::route('/create'),
            'edit' => Pages\EditCourseModule::route('/{record}/edit'),
        ];
    }
}