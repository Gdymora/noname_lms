<x-filament-panels::page>
    {{-- Подключаем Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Хедер статистики --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm text-gray-500 font-medium">Всего ответов</h3>
            <p class="text-3xl font-bold text-indigo-600 mt-2">{{ $totalSubmissions }}</p>
        </div>
        
        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 col-span-2 flex items-center justify-between">
            <div>
                <h3 class="text-sm text-gray-500 font-medium">Анкета</h3>
                <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $record->title }}</p>
            </div>
            <x-filament::button
                tag="a"
                href="{{ route('filament.admin.resources.forms.edit', $record->id) }}"
                color="gray"
                icon="heroicon-m-pencil-square"
            >
                Редактировать анкету
            </x-filament::button>
        </div>
    </div>

    @if($totalSubmissions === 0)
        <div class="text-center py-12 text-gray-500">
            Пока нет данных для анализа.
        </div>
    @else
        <h2 class="text-lg font-bold mb-4">Статистика ответов</h2>
        
        {{-- Сетка графиков --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($charts as $chart)
                <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <h4 class="text-base font-semibold mb-4 text-center">{{ $chart['label'] }}</h4>
                    
                    <div class="relative h-64 w-full flex justify-center">
                        <canvas id="{{ $chart['id'] }}"></canvas>
                    </div>

                    {{-- Инициализация графика --}}
                    <script>
                        document.addEventListener('livewire:navigated', () => {
                            initChart('{{ $chart['id'] }}', @json($chart['labels']), @json($chart['data']), @json($chart['colors']));
                        });
                        // Для первой загрузки
                        setTimeout(() => {
                            initChart('{{ $chart['id'] }}', @json($chart['labels']), @json($chart['data']), @json($chart['colors']));
                        }, 100);
                    </script>
                </div>
            @endforeach
        </div>
        
        {{-- Общий скрипт инициализации --}}
        <script>
            function initChart(id, labels, data, colors) {
                const ctx = document.getElementById(id);
                if (!ctx) return;
                
                // Уничтожаем старый график, если есть (при навигации)
                if (window[id] instanceof Chart) {
                    window[id].destroy();
                }

                window[id] = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: colors,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            }
                        }
                    }
                });
            }
        </script>

        <hr class="my-8 border-gray-200 dark:border-gray-700">

        {{-- Список последних ответов (просто таблица текстом) --}}
        <h2 class="text-lg font-bold mb-4">Последние заявки</h2>
        
        @php
            // Получаем последние 10 заявок
            $latestSubs = $record->submissions()->latest()->take(10)->get();
        @endphp

        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-xl">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">Дата</th>
                        <th class="px-6 py-3">Email / User</th>
                        <th class="px-6 py-3">Ответы</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($latestSubs as $sub)
                        <tr class="bg-white border-b dark:bg-gray-900 dark:border-gray-700">
                            <td class="px-6 py-4">{{ $sub->created_at->format('d.m.Y H:i') }}</td>
                            <td class="px-6 py-4">
                                {{ $sub->user ? $sub->user->email : ($sub->data['email'] ?? 'Гость') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    @foreach($sub->data as $key => $value)
                                        @if(!in_array($key, ['email', 'phone', 'name'])) 
                                            <span class="text-xs">
                                                <span class="font-bold">{{ $key }}:</span> {{ is_array($value) ? implode(', ', $value) : $value }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-2 text-center">
            <a href="{{ route('filament.admin.resources.forms.edit', $record->id) }}" class="text-sm text-indigo-600 hover:underline">Смотреть все ответы в таблице →</a>
        </div>
    @endif
</x-filament-panels::page>