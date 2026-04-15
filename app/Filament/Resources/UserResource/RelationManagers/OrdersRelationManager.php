<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\Course;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Доступ к курсам (Заказы)';

    public function form(Form $form): Form
    {
        // Форма редактирования существующего заказа (редко нужна, но пусть будет)
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('status')
                    ->options([
                        'new' => 'Новый',
                        'paid' => 'Оплачен (Доступ открыт)',
                        'cancelled' => 'Отменен',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('course.title')
                    ->label('Курс')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Сумма')
                    ->money('rub')
                    // Если сумма 0, пишем "Подарок"
                    ->formatStateUsing(fn (int $state) => $state === 0 ? '🎁 Подарок' : number_format($state, 0, '.', ' ') . ' ₽'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'new',
                        'danger' => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата выдачи')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->headerActions([
                // === НАША СУПЕР-КНОПКА ===
                Tables\Actions\Action::make('grant_access')
                    ->label('Выдать доступ / Подарить')
                    ->icon('heroicon-o-gift')
                    ->form([
                        // 1. Выбор курсов (Множественный)
                        Forms\Components\Select::make('course_ids')
                            ->label('Выберите курсы')
                            ->options(function ($livewire) {
                                // 1. Получаем студента, которому выдаем доступ
                                $student = $livewire->getOwnerRecord();
                                
                                // Если что-то пошло не так и студента нет - возвращаем пустой список
                                if (!$student) return [];

                                $query = Course::query();

                                // 2. Фильтр: Убираем курсы, которые у студента УЖЕ ЕСТЬ
                                $query->whereDoesntHave('orders', function ($q) use ($student) {
                                    $q->where('user_id', $student->id)
                                      ->whereIn('status', ['paid', 'new']);
                                });

                                // 3. Фильтр: Если это Учитель - показываем только ЕГО курсы
                                // ВАЖНО: Проверь в базе данных, совпадают ли teacher_id у курсов и твой ID
                                if (!auth()->user()->hasRole('Super Admin')) {
                                    $query->where('teacher_id', auth()->id());
                                }

                                return $query->pluck('title', 'id');
                            })
                            ->multiple()
                            ->preload() // Загружает список сразу (важно для поиска)
                            ->searchable() // Включает поиск по загруженному списку
                            ->required(),

                        // 2. Галочка "Это подарок?"
                        Forms\Components\Toggle::make('is_gift')
                            ->label('Оформить как подарок')
                            ->helperText('Если включено, цена будет 0 руб., даже если курс платный.')
                            ->default(true),
                    ])
                    ->action(function (array $data, RelationManager $livewire) {
                        $user = $livewire->getOwnerRecord();
                        $adminId = auth()->id();

                        foreach ($data['course_ids'] as $courseId) {
                            $course = Course::find($courseId);
                            
                            // Логика цены: Если подарок -> 0, иначе цена курса
                            $amount = $data['is_gift'] ? 0 : $course->price;
                            
                            // Создаем заказ
                            Order::create([
                                'user_id' => $user->id,
                                'course_id' => $course->id,
                                'manager_id' => $adminId, // Кто выдал
                                'amount' => $amount,
                                'status' => 'paid', // Сразу открываем доступ
                                'paid_at' => now(),
                                'history_log' => [
                                    'action' => 'granted_by_admin',
                                    'admin_id' => $adminId,
                                    'is_gift' => $data['is_gift']
                                ]
                            ]);
                        }

                        // Уведомление об успехе
                        \Filament\Notifications\Notification::make()
                            ->title('Доступ выдан')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                // Позволим аннулировать доступ (удалить заказ)
                Tables\Actions\DeleteAction::make()
                    ->label('Забрать доступ'),
            ]);
    }
}