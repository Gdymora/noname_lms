<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonResource\Pages;
use App\Models\Lesson;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Управление контентом';
    protected static ?string $navigationLabel = 'Уроки';
    protected static ?string $modelLabel = 'Урок';
    protected static ?string $pluralModelLabel = 'Уроки';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('Super Admin')) {
            return $query;
        }

        if (auth()->user()->hasRole('Teacher')) {
            return $query->whereHas('module.course', function ($q) {
                $q->where('teacher_id', auth()->id());
            });
        }

        return $query->where('id', -1);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('LessonTabs')
                    ->tabs([
                        // === ВКЛАДКА 1: ОСНОВНОЕ ===
                        Forms\Components\Tabs\Tab::make('Основное')
                            ->icon('heroicon-o-link')
                            ->schema([
                                Forms\Components\Section::make('Привязка к курсу')
                                    ->description('Выберите, где будет находиться этот урок')
                                    ->schema([
                                        // 1. КУРС
                                        Forms\Components\Select::make('course_id')
                                            ->label('Курс')
                                            ->options(function () {
                                                $query = \App\Models\Course::query();
                                                if (!auth()->user()->hasRole('Super Admin')) {
                                                    $query->where('teacher_id', auth()->id());
                                                }
                                                return $query->pluck('title', 'id');
                                            })
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function (Forms\Set $set) {
                                                $set('module_id', null);
                                                $set('tariffs', []); 
                                            })
                                            ->dehydrated(false)
                                            ->afterStateHydrated(function (Forms\Components\Select $component, ?\App\Models\Lesson $record) {
                                                if ($record && $record->module) {
                                                    $component->state($record->module->course_id);
                                                }
                                            }),

                                        // 2. МОДУЛЬ
                                        Forms\Components\Select::make('module_id')
                                            ->label('Модуль')
                                            ->options(function (Forms\Get $get) {
                                                $courseId = $get('course_id');
                                                if (!$courseId) return [];
                                                return \App\Models\CourseModule::where('course_id', $courseId)->pluck('title', 'id');
                                            })
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->disabled(fn (Forms\Get $get) => !$get('course_id'))
                                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                                $set('tariffs', []); 
                                                if ($state) {
                                                    $module = \App\Models\CourseModule::with('tariffs')->find($state);
                                                    // Наследуем тарифы модуля
                                                    if ($module && $module->tariffs->isNotEmpty()) {
                                                        $set('tariffs', $module->tariffs->pluck('id')->toArray());
                                                    }
                                                }
                                            }),

                                        // 3. ТАРИФЫ
                                        Forms\Components\Select::make('tariffs')
                                            ->relationship('tariffs', 'name')
                                            ->label('Доступно на тарифах')
                                            ->multiple()
                                            ->preload()
                                            ->options(function (Forms\Get $get) {
                                                $moduleId = $get('module_id');
                                                $courseId = $get('course_id');

                                                if (!$moduleId) return [];

                                                $module = \App\Models\CourseModule::with('tariffs')->find($moduleId);
                                                
                                                // Если модуль ограничен — показываем только эти тарифы
                                                if ($module && $module->tariffs->isNotEmpty()) {
                                                    return $module->tariffs->pluck('name', 'id');
                                                }

                                                return \App\Models\Tariff::where('course_id', $courseId)->pluck('name', 'id');
                                            })
                                            ->helperText('Список ограничен тарифами, доступными для выбранного модуля.')
                                            ->columnSpanFull(),
                                    ])->columns(2),
                                    
                                Forms\Components\Section::make('Название')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('Тема урока')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                    ]),
                            ]),

                        // === ВКЛАДКА 2: КОНТЕНТ ===
                        Forms\Components\Tabs\Tab::make('Контент урока')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\Repeater::make('blocks')
                                    ->relationship()
                                    ->hiddenLabel()
                                    ->label('Блоки контента')
                                    ->orderColumn('sort_order')
                                    ->schema([
                                        Forms\Components\Select::make('type')
                                            ->label('Тип блока')
                                            ->options([
                                                'text'      => 'Текст (HTML)',
                                                'audio'     => '🎧 Аудио / Подкаст',
                                                'buttons'   => '🔗 Кнопки / Ссылки',
                                                'image'     => 'Изображение',
                                                'file'      => 'Файл для скачивания',
                                                'separator' => '--- Разделитель ---',
                                                'quiz'      => '⚡ Тест / Квиз',
                                                'video_youtube'   => 'Видео: YouTube',
                                                'video_rutube'    => 'Видео: RuTube',
                                                'video_vk'        => 'Видео: VK Видео',
                                                'video_kinescope' => 'Видео: Kinescope',
                                            ])
                                            ->default('text')
                                            ->live()
                                            ->required()
                                            ->columnSpanFull(),
                                        
                                        // 1. ТЕКСТ
                                        Forms\Components\RichEditor::make('content.html')
                                            ->label('Текст')
                                            ->visible(fn (Forms\Get $get) => $get('type') === 'text')
                                            ->required(fn (Forms\Get $get) => $get('type') === 'text')
                                            ->columnSpanFull(),

                                        // 2. АУДИО
                                        Forms\Components\FileUpload::make('content.audio_path')
                                            ->label('Аудиофайл (MP3, WAV, M4A)')
                                            ->directory('lesson-audio')
                                            ->acceptedFileTypes(['audio/mpeg', 'audio/wav', 'audio/x-wav', 'audio/mp4', 'audio/ogg', 'audio/x-m4a'])
                                            ->maxSize(51200)
                                            ->visible(fn (Forms\Get $get) => $get('type') === 'audio')
                                            ->required(fn (Forms\Get $get) => $get('type') === 'audio')
                                            ->columnSpanFull(),

                                        // 3. КНОПКИ
                                        Forms\Components\Group::make()
                                            ->visible(fn (Forms\Get $get) => $get('type') === 'buttons')
                                            ->schema([
                                                Forms\Components\Repeater::make('content.buttons')
                                                    ->label('Список кнопок')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('label')->label('Текст')->required(),
                                                        Forms\Components\TextInput::make('url')->label('Ссылка')->url()->required(),
                                                        Forms\Components\Select::make('color')
                                                            ->options(['primary'=>'Синяя','success'=>'Зеленая','danger'=>'Красная','gray'=>'Серая'])
                                                            ->default('primary')->required(),
                                                        Forms\Components\Toggle::make('is_blank')->label('Новая вкладка')->default(true),
                                                    ])->columns(2)->addActionLabel('Добавить кнопку')
                                            ])->columnSpanFull(),

                                        // 4. ВИДЕО
                                        Forms\Components\TextInput::make('content.url')
                                            ->label(fn (Forms\Get $get) => match($get('type')) {
                                                'video_kinescope' => 'ID видео или Ссылка',
                                                default => 'Ссылка на видео'
                                            })
                                            ->visible(fn (Forms\Get $get) => str_starts_with($get('type') ?? '', 'video_'))
                                            ->required(fn (Forms\Get $get) => str_starts_with($get('type') ?? '', 'video_'))
                                            ->columnSpanFull(),

                                        // 5. КАРТИНКА
                                        Forms\Components\FileUpload::make('content.image_path')
                                            ->label('Изображение')
                                            ->image()
                                            ->directory('lesson-images')
                                            ->visible(fn (Forms\Get $get) => $get('type') === 'image')
                                            ->columnSpanFull(),

                                        // 6. ФАЙЛ
                                        Forms\Components\Grid::make(2)
                                            ->visible(fn (Forms\Get $get) => $get('type') === 'file')
                                            ->schema([
                                                Forms\Components\FileUpload::make('content.file_path')
                                                    ->label('Файл')
                                                    ->directory('lesson-files')
                                                    ->required(),
                                                Forms\Components\TextInput::make('content.file_name')
                                                    ->label('Название файла')
                                                    ->required(),
                                            ]),

                                        // 7. ТЕСТ
                                        Forms\Components\Group::make()
                                            ->visible(fn (Forms\Get $get) => $get('type') === 'quiz')
                                            ->schema([
                                                Forms\Components\Section::make('Конструктор теста')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('content.min_score')
                                                            ->label('Минимальный % прохождения')
                                                            ->numeric()->default(70)->required(),
                                                        Forms\Components\Repeater::make('content.questions')
                                                            ->label('Вопросы')
                                                            ->schema([
                                                                Forms\Components\TextInput::make('question')->label('Вопрос')->required(),
                                                                Forms\Components\Repeater::make('answers')
                                                                    ->label('Ответы')
                                                                    ->schema([
                                                                        Forms\Components\TextInput::make('text')->label('Ответ')->required(),
                                                                        Forms\Components\Toggle::make('is_correct')->label('Верный')->default(false),
                                                                    ])->minItems(2)->columns(2),
                                                            ])->itemLabel(fn (array $state): ?string => $state['question'] ?? null)->collapsed(),
                                                    ]),
                                            ]),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => match($state['type'] ?? '') {
                                        'text' => 'Текст',
                                        'audio' => 'Аудио',
                                        'buttons' => 'Кнопки',
                                        'quiz' => 'Тест',
                                        'video_youtube' => 'YouTube',
                                        'video_rutube' => 'RuTube',
                                        'video_vk' => 'VK Video',
                                        'video_kinescope' => 'Kinescope',
                                        'image' => 'Картинка',
                                        'file' => 'Файл',
                                        default => 'Блок'
                                    }),
                            ]),

                        // === ВКЛАДКА 3: НАСТРОЙКИ ===
                        Forms\Components\Tabs\Tab::make('Настройки публикации')
                            ->icon('heroicon-o-cog')
                            ->schema([
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('URL-адрес урока'),

                                Forms\Components\TextInput::make('duration_minutes')
                                    ->label('Длительность (мин)')
                                    ->numeric()
                                    ->default(15),

                                Forms\Components\DateTimePicker::make('available_at')
                                    ->label('Отложенная публикация')
                                    ->helperText('Урок откроется автоматически в это время'),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Toggle::make('is_published')
                                            ->label('Урок опубликован')
                                            ->default(true)
                                            ->onColor('success')
                                            ->offColor('danger'),

                                        Forms\Components\Toggle::make('is_stop_lesson')
                                            ->label('Стоп-урок')
                                            ->helperText('Не пускать дальше без выполнения ДЗ')
                                            ->default(false),
                                    ]),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('module.course.title')
                    ->label('Курс')
                    ->sortable()
                    ->limit(20)
                    ->color('gray'),

                Tables\Columns\TextColumn::make('module.title')
                    ->label('Модуль')
                    ->limit(20),

                Tables\Columns\TextColumn::make('tariffs.name')
                    ->label('Тарифы')
                    ->badge()
                    ->color('success')
                    ->placeholder('Все'),

                // БЫСТРЫЕ ПЕРЕКЛЮЧАТЕЛИ
                Tables\Columns\ToggleColumn::make('is_published')
                    ->label('Вкл'),

                Tables\Columns\ToggleColumn::make('is_stop_lesson')
                    ->label('Стоп'),

                Tables\Columns\TextColumn::make('blocks_count')
                    ->counts('blocks')
                    ->label('Блоков')
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('module.course', 'title')
                    ->label('Фильтр по курсу'),
                
                Tables\Filters\Filter::make('is_published')
                    ->query(fn ($query) => $query->where('is_published', true))
                    ->label('Только опубликованные'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLessons::route('/'),
            'create' => Pages\CreateLesson::route('/create'),
            'edit' => Pages\EditLesson::route('/{record}/edit'),
        ];
    }
}