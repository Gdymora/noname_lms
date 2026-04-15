<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}
        <div class="flex justify-end">
            <x-filament::button type="submit">Сохранить настройки</x-filament::button>
        </div>
    </x-filament-panels::form>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <!-- ЮКасса Инструкция -->
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-lg font-bold mb-2">Webhook для ЮKassa</h3>
            <code class="block px-2 py-2 bg-gray-100 dark:bg-gray-800 rounded text-xs font-mono select-all break-all">
                {{ $webhookUrl }}
            </code>
        </div>

        <!-- P2P Инструкция -->
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-lg font-bold mb-2">Webhook для ЮMoney P2P</h3>
            <p class="text-sm text-gray-500 mb-2">Вставьте этот URL в настройках уведомлений кошелька на yoomoney.ru (галочка "Отправлять HTTP-уведомления").</p>
            <code class="block px-2 py-2 bg-gray-100 dark:bg-gray-800 rounded text-xs font-mono select-all break-all">
                {{ $p2pWebhookUrl }}
            </code>
        </div>
    </div>
</x-filament-panels::page>