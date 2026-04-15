<x-filament-panels::page>
    
    {{-- 1. ФОРМА НАСТРОЕК --}}
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}
        
        <div class="flex justify-end">
            <x-filament::button type="submit">
                Сохранить ключ
            </x-filament::button>
        </div>
    </x-filament-panels::form>

    {{-- 2. ИНСТРУКЦИЯ --}}
    <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 mt-6">
        <h3 class="text-lg font-bold text-gray-950 dark:text-white flex items-center gap-2">
            <span>🚀</span> Как настроить интеграцию?
        </h3>
        
        <div class="mt-4 space-y-4 text-sm text-gray-600 dark:text-gray-400">
            <p>1. В Тильде в настройках формы добавьте сервис <strong>Webhook</strong>.</p>
            
            <p class="flex items-center gap-2">
                2. Укажите адрес (Webhook URL): 
                <code class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded text-primary-600 font-mono select-all cursor-pointer"
                      onclick="navigator.clipboard.writeText('{{ $webhookUrl }}'); alert('URL скопирован!')">
                    {{ $webhookUrl }}
                </code>
            </p>

            <div>
                3. В форму добавьте <strong>Hidden Fields (Скрытые поля)</strong> с именами переменных:
                <ul class="list-disc list-inside mt-2 ml-2 space-y-1">
                    <li><code>secret</code> — Ваш секретный ключ (из формы выше)</li>
                    <li><code>course_id</code> — ID курса (из таблицы ниже)</li>
                    <li><code>tariff_id</code> — ID тарифа (если нужно разделить доступы)</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- 3. ТАБЛИЦА КУРСОВ (Красивая) --}}
    <div class="mt-8">
        <h3 class="text-lg font-bold mb-4 text-gray-950 dark:text-white">📋 ID для вставки в Тильду</h3>

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-white/5 text-gray-950 dark:text-white border-b border-gray-200 dark:border-white/10">
                        <tr>
                            <th class="px-6 py-4 font-medium">Название курса</th>
                            <th class="px-6 py-4 font-medium">Основной ID</th>
                            <th class="px-6 py-4 font-medium">Тарифы (ID)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @forelse($courses as $course)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                <td class="px-6 py-4 font-medium text-gray-950 dark:text-white">
                                    {{ $course->title }}
                                    <div class="text-xs text-gray-500 font-normal mt-1">{{ $course->slug }}</div>
                                </td>
                                
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-500">course_id =</span>
                                        <button 
                                            onclick="navigator.clipboard.writeText('{{ $course->id }}')"
                                            class="px-2 py-1 bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400 rounded-md font-mono font-bold hover:bg-primary-100 transition"
                                            title="Нажмите, чтобы скопировать"
                                        >
                                            {{ $course->id }}
                                        </button>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    @if($course->tariffs->count() > 0)
                                        <div class="flex flex-col gap-2">
                                            @foreach($course->tariffs as $tariff)
                                                <div class="flex items-center justify-between gap-4 p-2 rounded-lg border border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/5">
                                                    <span class="text-gray-700 dark:text-gray-300">{{ $tariff->name }}</span>
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs text-gray-400">tariff_id:</span>
                                                        <code class="font-mono font-bold text-gray-900 dark:text-white">{{ $tariff->id }}</code>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic text-xs">Нет тарифов (доступ по course_id)</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                    Курсы пока не созданы.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</x-filament-panels::page>