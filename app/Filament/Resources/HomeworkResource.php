<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeworkResource\Pages;
use App\Models\Homework;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HomeworkResource extends Resource
{
    protected static ?string $model = Homework::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Управление контентом';
    protected static ?string $navigationLabel = 'Домашние задания';

    protected static ?string $modelLabel = 'Домашнее задание';
protected static ?string $pluralModelLabel = 'Домашние задания';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Привязка к уроку')
                    ->schema([
                        // 1. ВЫБОР КУРСА (Фильтр)
                        Forms\Components\Select::make('course_id')
                            ->label('Курс')
                            ->options(function () {
                                $query = \App\Models\Course::query();
                                // Если учитель - показываем только его курсы
                                if (!auth()->user()->hasRole('Super Admin')) {
                                    $query->where('teacher_id', auth()->id());
                                }
                                return $query->pluck('title', 'id');
                            })
                            ->required()
                            ->live() // Обновляет форму при изменении
                            ->afterStateUpdated(function (Forms\Set $set) {
                                // При смене курса сбрасываем модуль и урок
                                $set('module_id', null);
                                $set('lesson_id', null);
                            })
                            ->dehydrated(false) // Не сохраняем в таблицу homeworks
                            // При открытии на редактирование - находим курс через урок
                            ->afterStateHydrated(function (Forms\Components\Select $component, ?\App\Models\Homework $record) {
                                if ($record && $record->lesson && $record->lesson->module) {
                                    $component->state($record->lesson->module->course_id);
                                }
                            }),

                        // 2. ВЫБОР МОДУЛЯ (Фильтр)
                        Forms\Components\Select::make('module_id')
                            ->label('Модуль')
                            ->options(function (Forms\Get $get) {
                                $courseId = $get('course_id');
                                if (!$courseId) return [];
                                
                                // Грузим модули выбранного курса
                                return \App\Models\CourseModule::where('course_id', $courseId)
                                    ->pluck('title', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('lesson_id', null))
                            ->disabled(fn (Forms\Get $get) => !$get('course_id')) // Блокируем, если нет курса
                            ->dehydrated(false)
                            // При редактировании - находим модуль через урок
                            ->afterStateHydrated(function (Forms\Components\Select $component, ?\App\Models\Homework $record) {
                                if ($record && $record->lesson) {
                                    $component->state($record->lesson->module_id);
                                }
                            }),

                        // 3. ВЫБОР УРОКА (Цель)
                        Forms\Components\Select::make('lesson_id')
                            ->label('Урок')
                            ->options(function (Forms\Get $get) {
                                $moduleId = $get('module_id');
                                if (!$moduleId) return [];

                                // Грузим уроки выбранного модуля
                                return \App\Models\Lesson::where('module_id', $moduleId)
                                    ->pluck('title', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn (Forms\Get $get) => !$get('module_id')) // Блокируем, если нет модуля
                            ->helperText('В одном уроке может быть только одно задание'),
                        
                        // ГАЛОЧКА "ОБЯЗАТЕЛЬНОЕ"
                        Forms\Components\Toggle::make('is_required')
                            ->label('Обязательное задание')
                            ->default(true)
                            ->helperText('Стоп-урок не пустит дальше без сдачи')
                            ->inline(false), // Чтобы выравнивание было красивым
                            
                    ])->columns(3), // Выстраиваем 3 селекта в ряд

                Forms\Components\Section::make('Суть задания')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->label('Текст задания')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Форма ответа студента')
                    ->description('Сконструируйте поля, которые должен заполнить студент')
                    ->schema([
                        Forms\Components\Repeater::make('submission_fields')
                            ->label('Поля для ответа')
                            ->schema([
                                // Выбор типа поля
                                Forms\Components\Select::make('type')
                                    ->label('Тип поля')
                                    ->options([
                                        'text' => 'Длинный текст (Editor)',
                                        'string' => 'Короткая строка',
                                        'file' => 'Загрузка файла',
                                        'url' => 'Ссылка (URL)',
                                        'checkboxes' => 'Множественный выбор (Чекбоксы)', 
                                        'select' => 'Выпадающий список (Один вариант)',
                                    ])
                                    ->required()
                                    ->reactive(), // Чтобы менять иконку или настройки динамически

                                // Подпись поля
                                Forms\Components\TextInput::make('label')
                                    ->label('Название поля')
                                    ->placeholder('Например: Ссылка на GitHub')
                                    ->required(),
                                
                                // Обязательно ли?
                                Forms\Components\Toggle::make('required')
                                    ->label('Обязательно')
                                    ->default(true),

                                // === НОВОЕ ПОЛЕ: ВАРИАНТЫ ОТВЕТОВ ===
                                Forms\Components\TagsInput::make('options')
                                    ->label('Варианты ответов')
                                    ->placeholder('Введите вариант и нажмите Enter')
                                    ->visible(fn (Forms\Get $get) => in_array($get('type'), ['select', 'checkboxes']))
                                    ->required(fn (Forms\Get $get) => in_array($get('type'), ['select', 'checkboxes'])),
                                // ====================================
                            ])
                            ->columns(3) // В одну строку для компактности
                            ->addActionLabel('Добавить поле ответа')
                            ->defaultItems(1), // По умолчанию одно текстовое поле
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lesson.module.course.title')
                    ->label('Курс')
                    ->sortable(),

                Tables\Columns\TextColumn::make('lesson.title')
                    ->label('Урок')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_required')
                    ->label('Обязательное')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomework::route('/'),
            'create' => Pages\CreateHomework::route('/create'),
            'edit' => Pages\EditHomework::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('Super Admin')) {
            return $query;
        }

        if (auth()->user()->hasRole('Teacher')) {
            // Показываем только те ДЗ, которые привязаны к курсам этого учителя
            return $query->whereHas('lesson.module.course', function ($q) {
                $q->where('teacher_id', auth()->id());
            });
        }

        return $query->where('id', -1);
    }
}