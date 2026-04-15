<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PaymentSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Настройки ЮKassa / P2P';
    protected static ?string $title = 'Платежные системы';
    protected static ?string $navigationGroup = 'Настройки системы';
    
    protected static string $view = 'filament.pages.payment-settings';

    public ?array $data = [];

    public function mount(): void
    {
        // Загружаем все настройки платежей
        $keys = [
            'yookassa_shop_id', 'yookassa_secret_key', 'yookassa_enabled',
            'yoomoney_p2p_account', 'yoomoney_p2p_secret', 'yoomoney_p2p_enabled'
        ];

        $settings = SystemSetting::whereIn('key', $keys)->pluck('payload', 'key');
        
        $this->form->fill([
            'yookassa_shop_id' => $settings['yookassa_shop_id'] ?? '',
            'yookassa_secret_key' => $settings['yookassa_secret_key'] ?? '',
            'yookassa_enabled' => (bool) ($settings['yookassa_enabled'] ?? false),
            
            // Новые поля P2P
            'yoomoney_p2p_account' => $settings['yoomoney_p2p_account'] ?? '',
            'yoomoney_p2p_secret' => $settings['yoomoney_p2p_secret'] ?? '',
            'yoomoney_p2p_enabled' => (bool) ($settings['yoomoney_p2p_enabled'] ?? false),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // ЮКАССА (Официальная)
                Section::make('ЮKassa (Для ИП и ООО)')
                    ->description('Прием карт, SBP, кредитование через официальный договор.')
                    ->schema([
                        Toggle::make('yookassa_enabled')
                            ->label('Включить ЮKassa'),

                        TextInput::make('yookassa_shop_id')
                            ->label('Shop ID'),

                        TextInput::make('yookassa_secret_key')
                            ->label('Секретный ключ')
                            ->password()
                            ->revealable(),
                    ])
                    ->collapsible(),

                // ЮMONEY P2P (Физлицо)
                Section::make('ЮMoney P2P (Личный кошелек)')
                    ->description('Прямые переводы на кошелек ЮMoney (для физлиц и самозанятых).')
                    ->schema([
                        Toggle::make('yoomoney_p2p_enabled')
                            ->label('Включить прием на кошелек'),

                        TextInput::make('yoomoney_p2p_account')
                            ->label('Номер кошелька')
                            ->placeholder('410010000000000')
                            ->numeric()
                            ->required(fn ($get) => $get('yoomoney_p2p_enabled')),

                        TextInput::make('yoomoney_p2p_secret')
                            ->label('Секрет для уведомлений (HTTP)')
                            ->password()
                            ->revealable()
                            ->helperText('В настройках кошелька на yoomoney.ru: "Сбор денег" -> "Настройка уведомлений" -> "Показать секрет".')
                            ->required(fn ($get) => $get('yoomoney_p2p_enabled')),
                    ])
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['group' => 'payments', 'payload' => $value]
            );
        }

        Notification::make()->title('Настройки сохранены')->success()->send();
    }
    
    protected function getViewData(): array
    {
        return [
            'webhookUrl' => url('/api/webhooks/yookassa'),
            'p2pWebhookUrl' => url('/api/webhooks/yoomoney-p2p'),
        ];
    }
}