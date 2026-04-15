<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\Order;
use App\Models\Tariff;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Mail\ImportedUserCredentials;
use Illuminate\Support\Facades\Mail;

class ImportUsers extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-on-square-stack';
    protected static ?string $navigationLabel = 'Импорт студентов';
    protected static ?string $title = 'Импорт из CSV (GetCourse)';
    protected static ?string $navigationGroup = 'Настройки системы';
    
    protected static string $view = 'filament.pages.import-users';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Загрузка данных')
                    ->description('Загрузите выгрузку из GetCourse "как есть". Мы сами найдем колонки Email, Имя и Телефон.')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->schema([
                        FileUpload::make('file')
                            ->label('CSV файл')
                            ->disk('local')
                            ->directory('imports')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                            ->required()
                            ->columnSpanFull(),

                        \Filament\Forms\Components\Group::make()
                            ->schema([
                                Select::make('course_id')
                                    ->label('Курс')
                                    ->options(Course::all()->pluck('title', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live(),

                                Select::make('tariff_id')
                                    ->label('Тариф (необязательно)')
                                    ->options(function (\Filament\Forms\Get $get) {
                                        $courseId = $get('course_id');
                                        if (!$courseId) return [];
                                        return Tariff::where('course_id', $courseId)->pluck('name', 'id');
                                    })
                                    ->disabled(fn (\Filament\Forms\Get $get) => !$get('course_id'))
                                    ->searchable()
                                    ->preload(),
                            ])->columns(2)->columnSpanFull(),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function import(): void
    {
        $data = $this->form->getState();
        $filePath = Storage::disk('local')->path($data['file']);
        
        $course = Course::find($data['course_id']);
        $tariffId = $data['tariff_id'] ?? null;
        
        if (!file_exists($filePath)) {
            Notification::make()->title('Файл не найден')->danger()->send();
            return;
        }

        $handle = fopen($filePath, 'r');
        $firstLine = fgets($handle);
        rewind($handle);
        
        $delimiter = ';';
        if (substr_count($firstLine, ',') > substr_count($firstLine, ';')) {
            $delimiter = ',';
        }

        $headers = fgetcsv($handle, 0, $delimiter);
        $headers = array_map(function($h) {
            return mb_strtolower(trim(preg_replace('/[\x{FEFF}]/u', '', $h)));
        }, $headers);

        $emailIdx = $this->findHeaderIndex($headers, ['email', 'e-mail', 'mail', 'почта']);
        $nameIdx = $this->findHeaderIndex($headers, ['name', 'имя', 'first name', 'fistname']);
        $phoneIdx = $this->findHeaderIndex($headers, ['phone', 'телефон', 'number', 'tel']);

        if ($emailIdx === false) {
            Notification::make()->title('Ошибка: Не найдена колонка Email')->danger()->send();
            return;
        }

        $importedCount = 0;
        $newUsersCount = 0; // Считаем, скольким отправили письма
        $errors = 0;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rawEmail = $row[$emailIdx] ?? '';
            $email = trim($rawEmail);

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors++;
                continue;
            }

            $name = ($nameIdx !== false && isset($row[$nameIdx])) ? trim($row[$nameIdx]) : 'Student';
            if (empty($name)) $name = 'Student';
            $phone = ($phoneIdx !== false && isset($row[$phoneIdx])) ? trim($row[$phoneIdx]) : null;

            // --- ЛОГИКА СОЗДАНИЯ ПОЛЬЗОВАТЕЛЯ ---
            $user = User::where('email', $email)->first();
            
            // Если пользователя НЕТ - создаем и шлем пароль
            if (!$user) {
                $rawPassword = Str::random(12); // Генерируем пароль (12 символов)
                
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => Hash::make($rawPassword), // В базу пишем хэш
                    'email_verified_at' => now(),
                    'accepted_offer_at' => now(), // Считаем, что при переносе оферта принята
                    'accepted_policy_at' => now(),
                ]);
                
                $user->assignRole('Student');
                
                // ВАЖНО: Отправляем письмо в очередь
                Mail::to($user)->queue(new ImportedUserCredentials($user, $rawPassword));
                
                $newUsersCount++;
            }

            // --- ВЫДАЧА ДОСТУПА ---
            $exists = Order::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->where('status', 'paid')
                ->exists();

            if (!$exists) {
                Order::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'tariff_id' => $tariffId,
                    'amount' => 0,
                    'status' => 'paid',
                    'paid_at' => now(),
                    'history_log' => ['source' => 'import_getcourse', 'admin_id' => auth()->id()]
                ]);
                $importedCount++;
            }
        }

        fclose($handle);
        Storage::disk('local')->delete($data['file']);

        Notification::make()
            ->title('Импорт завершен')
            ->body("Выдан доступ: {$importedCount}. Отправлено писем новым студентам: {$newUsersCount}.")
            ->success()
            ->send();
            
        $this->form->fill();
    }

    // Вспомогательная функция для поиска заголовка
    private function findHeaderIndex(array $headers, array $keywords)
    {
        foreach ($headers as $index => $header) {
            foreach ($keywords as $keyword) {
                // Ищем точное совпадение или вхождение (для надежности)
                if (str_contains($header, $keyword)) {
                    return $index;
                }
            }
        }
        return false;
    }
}