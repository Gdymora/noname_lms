<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeworkSubmissionResource\Pages;
use App\Models\HomeworkSubmission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HomeworkSubmissionResource extends Resource
{
    protected static ?string $model = HomeworkSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationGroup = 'Работа со студентами'; // Новая группа
    protected static ?string $navigationLabel = 'Проверка ДЗ';

    protected static ?string $modelLabel = 'Проверка ДЗ';
protected static ?string $pluralModelLabel = 'Проверка ДЗ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация')
                    ->schema([
                        Forms\Components\Placeholder::make('student_name')
                            ->label('Студент')
                            ->content(fn (HomeworkSubmission $record) => $record->student->name ?? 'Unknown'),
                        
                        Forms\Components\Placeholder::make('homework_title')
                            ->label('Урок')
                            ->content(fn (HomeworkSubmission $record) => $record->homework->lesson->title ?? '-'),
                    ])->columns(2),

                Forms\Components\Section::make('Ответ студента')
                    ->schema([
                        // Пока показываем как JSON, позже сделаем красивый View
                        Forms\Components\KeyValue::make('content')
                            ->label('Содержимое ответа')
                            ->disabled() // Куратор не может менять ответ студента
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Вердикт')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Ожидает',
                                'approved' => 'Принято',
                                'rejected' => 'Отклонено',
                                'revision' => 'На доработку',
                            ])
                            ->required(),

                        // Оценка с точностью до сотых (твое требование про рейтинг)
                        Forms\Components\TextInput::make('grade_percent')
                            ->label('Оценка (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->helperText('Для рейтинга важны сотые доли. 98.55 > 98.50'),

                        Forms\Components\Textarea::make('curator_comment')
                            ->label('Комментарий студенту')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Студент')
                    ->searchable(),

                Tables\Columns\TextColumn::make('homework.lesson.title')
                    ->label('Урок')
                    ->limit(30),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'gray' => 'revision',
                    ]),

                Tables\Columns\TextColumn::make('grade_percent')
                    ->label('Оценка')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Сдано')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc') // Свежие сверху
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Ожидает проверки',
                        'approved' => 'Принято',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Проверить'),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomeworkSubmissions::route('/'),
            'edit' => Pages\EditHomeworkSubmission::route('/{record}/edit'),
        ];
    }
    
    // Убираем кнопку "Создать", так как ДЗ создают студенты, а не админ
    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // 1. Супер-Админ видит всё
        if ($user->hasRole('Super Admin')) {
            return $query;
        }

        // 2. Учитель видит всё по своим курсам
        if ($user->hasRole('Teacher')) {
            return $query->whereHas('homework.lesson.module.course', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            });
        }

        // 3. Куратор видит всё по НАЗНАЧЕННЫМ курсам
        if ($user->hasRole('Curator')) {
            return $query->whereHas('homework.lesson.module.course.curators', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        return $query->where('id', -1);
    }
}