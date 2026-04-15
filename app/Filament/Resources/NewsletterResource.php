<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterResource\Pages;
use App\Jobs\SendNewsletterJob;
use App\Models\Course;
use App\Models\Newsletter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class NewsletterResource extends Resource
{
    protected static ?string $model = Newsletter::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'Маркетинг';
    protected static ?string $navigationLabel = 'Рассылки';

    protected static ?string $modelLabel = 'Рассылку';
protected static ?string $pluralModelLabel = 'Рассылки';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ЛЕВАЯ КОЛОНКА: Контент
                Forms\Components\Section::make('Содержание')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('Тема письма')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\RichEditor::make('content')
                            ->label('Текст рассылки')
                            ->required(),
                    ])->columnSpan(2),

                // ПРАВАЯ КОЛОНКА: Настройки
                Forms\Components\Section::make('Настройки отправки')
                    ->schema([
                        // Фильтр: Выбор курсов
                        Forms\Components\Select::make('recipients_filter.course_id')
                            ->label('Только студентам курсов')
                            ->options(Course::all()->pluck('title', 'id'))
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('Оставьте пустым, чтобы отправить всем подписчикам.'),

                        // Фильтр: Выбор ролей
                        Forms\Components\Select::make('recipients_filter.roles')
                            ->label('Только ролям')
                            ->options([
                                'Student' => 'Студенты',
                                'Teacher' => 'Учителя',
                            ])
                            ->multiple(),

                        // Дата отправки
                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Запланировать отправку')
                            ->helperText('Оставьте пустым для мгновенной отправки.')
                            ->minDate(now()),
                            
                        Forms\Components\Placeholder::make('status')
                            ->label('Текущий статус')
                            ->content(fn (?Newsletter $record) => $record ? $record->status : 'Черновик'),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->label('Тема')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'scheduled',
                        'primary' => 'processing',
                        'success' => 'sent',
                    ]),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Запланировано')
                    ->dateTime('d.m.Y H:i'),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Отправлено')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // КНОПКА: Отправить сейчас / Запланировать
                Tables\Actions\Action::make('send')
                    ->label('Запустить рассылку')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn (Newsletter $record) => $record->status === 'draft')
                    ->action(function (Newsletter $record) {
                        
                        // Если есть дата - ставим статус Scheduled
                        if ($record->scheduled_at && $record->scheduled_at > now()) {
                            $record->update(['status' => 'scheduled']);
                            
                            // Используем задержку Laravel Queue
                            SendNewsletterJob::dispatch($record)->delay($record->scheduled_at);
                            
                            Notification::make()->title('Рассылка запланирована')->success()->send();
                        } 
                        // Иначе отправляем сразу
                        else {
                            SendNewsletterJob::dispatch($record);
                            
                            Notification::make()->title('Рассылка запущена в обработку')->success()->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsletters::route('/'),
            'create' => Pages\CreateNewsletter::route('/create'),
            'edit' => Pages\EditNewsletter::route('/{record}/edit'),
        ];
    }
}