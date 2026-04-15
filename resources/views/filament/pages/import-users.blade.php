<x-filament-panels::page>
    <x-filament-panels::form wire:submit="import">
        {{ $this->form }}

        <div class="flex justify-end mt-4">
            <x-filament::button type="submit" color="success" icon="heroicon-o-arrow-up-on-square">
                Запустить импорт
            </x-filament::button>
        </div>
    </x-filament-panels::form>

    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 mt-6">
        <div class="fi-section-content p-6">
            <div class="flex items-center gap-x-3 mb-4">
                <div class="p-2 bg-primary-50 dark:bg-primary-900/50 rounded-lg text-primary-600 dark:text-primary-400">
                    <x-filament::icon icon="heroicon-o-document-text" class="h-6 w-6" />
                </div>
                <h3 class="text-lg font-bold text-gray-950 dark:text-white">
                    Как работает умный импорт?
                </h3>
            </div>

            <div class="prose prose-sm dark:prose-invert max-w-none text-gray-600 dark:text-gray-400">
                <p>Вам не нужно редактировать CSV файл вручную. Загружайте полную выгрузку с GetCourse.</p>
                
                <ul class="list-disc list-inside space-y-2 mt-2">
                    <li>Система сама найдет колонку <strong>Email</strong> (по названию в заголовке).</li>
                    <li>Система найдет колонку <strong>Имя</strong> (Name, First Name).</li>
                    <li>Система найдет <strong>Телефон</strong> (Phone).</li>
                    <li>Остальные колонки будут проигнорированы.</li>
                </ul>

                <p class="mt-4 text-xs text-gray-500 bg-gray-50 dark:bg-white/5 p-3 rounded">
                    * Если выгрузка содержит "кракозябры" (кодировка Windows-1251), попробуйте сохранить файл в формате "CSV UTF-8" в Excel перед загрузкой, хотя система постарается прочитать его и так.
                </p>
            </div>
        </div>
    </div>
</x-filament-panels::page>