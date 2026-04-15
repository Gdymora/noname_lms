<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormResource\Pages;
use App\Filament\Resources\FormResource\RelationManagers;
use App\Models\Form as FormModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FormResource extends Resource
{
    protected static ?string $model = FormModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Маркетинг';
    protected static ?string $navigationLabel = 'Анкеты и Формы';

    protected static ?string $modelLabel = 'Анкету';
    protected static ?string $pluralModelLabel = 'Анкеты';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // === ЛЕВАЯ КОЛОНКА (2/3) - КОНСТРУКТОР ===
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Конструктор формы')
                            ->description('Добавьте необходимые поля для сбора данных.')
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                Forms\Components\Repeater::make('schema')
                                    ->label('Поля')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                // Тип и Обязательность в одну строку
                                                Forms\Components\Select::make('type')
                                                    ->label('Тип поля')
                                                    ->options([
                                                        'text' => '📝 Текст (Строка)',
                                                        'textarea' => '📄 Текст (Абзац)',
                                                        'email' => '📧 Email',
                                                        'phone' => '📱 Телефон',
                                                        'select' => 'u25bc Выпадающий список',
                                                    ])
                                                    ->required()
                                                    ->live(), // Чтобы показывать/скрывать options

                                                Forms\Components\Toggle::make('required')
                                                    ->label('Обязательное')
                                                    ->inline(false)
                                                    ->default(true),
                                            ]),

                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('label')
                                                    ->label('Вопрос / Заголовок')
                                                    ->required()
                                                    ->placeholder('Например: Ваше имя')
                                                    ->live(onBlur: true), // Для обновления заголовка блока

                                                Forms\Components\TextInput::make('name')
                                                    ->label('ID поля (латиница)')
                                                    ->required()
                                                    ->alphaDash() // Только буквы, цифры, дефис
                                                    ->placeholder('full_name')
                                                    ->helperText('Уникальный ключ для базы данных'),
                                            ]),

                                        // Опции (только для Select)
                                        Forms\Components\TagsInput::make('options')
                                            ->label('Варианты ответов')
                                            ->visible(fn (Forms\Get $get) => $get('type') === 'select')
                                            ->placeholder('Введите вариант и нажмите Enter')
                                            ->helperText('Нажмите Enter после каждого варианта')
                                            ->columnSpanFull(),
                                    ])
                                    ->cloneable()   // <-- Кнопка "Дублировать поле"
                                    ->collapsible() // <-- Кнопка "Свернуть"
                                    ->itemLabel(fn (array $state): ?string => 
                                        ($state['label'] ?? 'Новое поле') . 
                                        ' (' . ($state['type'] ?? '-') . ')'
                                    ),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                // === ПРАВАЯ КОЛОНКА (1/3) - НАСТРОЙКИ ===
                Forms\Components\Group::make()
                    ->schema([
                        
                        // Карточка: Основное
                        Forms\Components\Section::make('Основные параметры')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Название')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),

                                Forms\Components\TextInput::make('slug')
                                    ->label('URL-адрес')
                                    ->prefix(url('/f/').'/')
                                    ->required()
                                    ->unique(ignoreRecord: true),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Анкета активна')
                                    ->default(true)
                                    ->onColor('success')
                                    ->offColor('danger'),
                            ]),

                        // Карточка: Тексты и переводы
                        Forms\Components\Section::make('Тексты интерфейса')
                            ->schema([
                                Forms\Components\TextInput::make('settings.submit_text')
                                    ->label('Кнопка отправки')
                                    ->default('Отправить')
                                    ->placeholder('Отправить'),

                                Forms\Components\Textarea::make('settings.success_message')
                                    ->label('Сообщение об успехе')
                                    ->default('Спасибо! Ваша заявка принята.')
                                    ->rows(3),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3); // Общая сетка на 3 колонки
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Ссылка')
                    ->prefix('/f/')
                    ->color('gray')
                    ->copyable(), // Можно скопировать ссылку по клику

                Tables\Columns\TextColumn::make('submissions_count')
                    ->counts('submissions')
                    ->label('Заявок')
                    ->badge()
                    ->color('primary'),

                // Быстрый переключатель
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Активна'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // Кнопка открытия на сайте
                Tables\Actions\Action::make('open')
                    ->label('Открыть')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (FormModel $record) => route('public.form.show', $record->slug))
                    ->openUrlInNewTab()
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
        return [
            RelationManagers\SubmissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListForms::route('/'),
            'create' => Pages\CreateForm::route('/create'),
            'edit' => Pages\EditForm::route('/{record}/edit'),
        ];
    }
}