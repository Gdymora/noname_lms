<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\SystemSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class TildaIntegration extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Платежи / Тильда';
    protected static ?string $title = 'Интеграция с Tilda';
    protected static ?string $navigationGroup = 'Настройки системы';
    
    protected static string $view = 'filament.pages.tilda-integration';

    public ?array $data = [];

    public function mount(): void
    {
        $secret = SystemSetting::where('key', 'tilda_secret')->value('payload');
        $this->form->fill(['tilda_secret' => $secret]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Настройки безопасности')
                    ->description('Этот ключ нужно вставить в настройках блока на Тильде (поле secret)')
                    ->schema([
                        TextInput::make('tilda_secret')
                            ->label('Секретный ключ (Secret)')
                            ->password()
                            ->revealable()
                            ->required()
                            // === КНОПКА ГЕНЕРАЦИИ ===
                            ->suffixAction(
                                Action::make('generate')
                                    ->icon('heroicon-m-arrow-path')
                                    ->color('gray')
                                    ->tooltip('Сгенерировать сложный ключ')
                                    ->action(function (Set $set) {
                                        // Генерируем случайную строку и вставляем в поле
                                        $set('tilda_secret', Str::random(32));
                                        
                                        Notification::make()
                                            ->title('Новый ключ сгенерирован')
                                            ->success()
                                            ->send();
                                    })
                            ),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        SystemSetting::updateOrCreate(
            ['key' => 'tilda_secret'],
            ['group' => 'payments', 'payload' => $state['tilda_secret']]
        );

        Notification::make()->title('Настройки сохранены')->success()->send();
    }

    protected function getViewData(): array
    {
        return [
            'courses' => Course::with('tariffs')->get(),
            'webhookUrl' => url('/api/webhooks/tilda'),
        ];
    }
}