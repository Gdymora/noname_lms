<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\Page as PageModel;
use App\Models\SystemSetting;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group; // <--- ВОТ ЭТОЙ СТРОКИ НЕ ХВАТАЛО
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageLanding extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Конструктор Главной';
    protected static ?string $title = 'Редактирование Главной страницы';
    protected static ?string $navigationGroup = 'Управление контентом';
    
    protected static string $view = 'filament.pages.manage-landing';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SystemSetting::where('key', 'landing_page_blocks')->value('payload');
        $this->form->fill(['blocks' => $settings ?? []]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Builder::make('blocks')
                    ->label('Блоки страницы')
                    ->blocks([
                        // 1. HERO SECTION
                        Builder\Block::make('hero')
                            ->label('Главный экран (Hero)')
                            ->icon('heroicon-o-star')
                            ->schema([
                                TextInput::make('title')->label('Заголовок')->required(),
                                TextInput::make('subtitle')->label('Подзаголовок'),
                                FileUpload::make('image')
                                    ->label('Фоновое изображение')
                                    ->image()
                                    ->directory('landing')
                                    ->visibility('public'),
                                TextInput::make('button_text')->label('Текст кнопки')->default('Выбрать курс'),
                                TextInput::make('button_url')->label('Ссылка кнопки')->default('#courses'),
                            ]),

                        // 2. ВИТРИНА КУРСОВ
                        Builder\Block::make('courses')
                            ->label('Витрина курсов')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                TextInput::make('title')->label('Заголовок секции')->default('Наши курсы'),
                                Select::make('course_ids')
                                    ->label('Выберите курсы для отображения')
                                    ->options(Course::where('is_published', true)->pluck('title', 'id'))
                                    ->multiple()
                                    ->searchable()
                                    ->preload(),
                            ]),

                        // 3. О ШКОЛЕ
                        Builder\Block::make('about')
                            ->label('О школе')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                TextInput::make('title')->label('Заголовок'),
                                RichEditor::make('content')->label('Текст'),
                                FileUpload::make('image')->label('Изображение')->image()->directory('landing'),
                            ]),

                        // 4. ЭКСПЕРТЫ
                        Builder\Block::make('experts')
                            ->label('Команда / Эксперты')
                            ->icon('heroicon-o-users')
                            ->schema([
                                TextInput::make('title')->label('Заголовок секции'),
                                Repeater::make('items')
                                    ->label('Список экспертов')
                                    ->schema([
                                        FileUpload::make('photo')->avatar()->directory('landing'),
                                        TextInput::make('name')->label('Имя')->required(),
                                        TextInput::make('role')->label('Должность / Регалии'),
                                    ])->grid(3),
                            ]),

                        // 5. ПОДВАЛ (FOOTER)
                        Builder\Block::make('footer')
                            ->label('Подвал и Контакты')
                            ->icon('heroicon-o-bars-3-bottom-left')
                            ->schema([
                                // Основные контакты
                                Group::make()
                                    ->schema([
                                        TextInput::make('email')->label('Email'),
                                        TextInput::make('phone')->label('Телефон'),
                                    ])->columns(2),

                                // Юридическая информация (Сворачиваемая секция)
                                Section::make('Юридическая информация')
                                    ->schema([
                                        TextInput::make('legal_name')
                                            ->label('Наименование (ИП / ООО)')
                                            ->placeholder('ИП Иванов И.И.'),
                                        
                                        Group::make()
                                            ->schema([
                                                TextInput::make('inn')->label('ИНН'),
                                                TextInput::make('ogrn')->label('ОГРН / ОГРНИП'),
                                            ])->columns(2),

                                        TextInput::make('license')
                                            ->label('Образовательная лицензия')
                                            ->placeholder('Л035-01255... от 03.07.2023'),
                                    ])->collapsed(),

                                TextInput::make('copyright')->label('Копирайт'),
                                
                                // Соцсети
                                Repeater::make('socials')
                                    ->label('Соцсети')
                                    ->schema([
                                        Select::make('icon')->options([
                                            'telegram' => 'Telegram',
                                            'vk' => 'VK',
                                            'youtube' => 'YouTube'
                                        ]),
                                        TextInput::make('url')->label('Ссылка')->url(),
                                    ])->columns(2),

                                // Документы (Оферта и т.д.)
                                Repeater::make('documents')
                                    ->label('Правовые документы')
                                    ->schema([
                                        Select::make('page_id')
                                            ->label('Страница')
                                            ->options(PageModel::where('is_published', true)->pluck('title', 'id'))
                                            ->required()
                                            ->searchable(),
                                        
                                        TextInput::make('label')
                                            ->label('Название в меню')
                                            ->placeholder('По умолчанию как у страницы'),
                                    ])
                                    ->columns(2)
                                    ->addActionLabel('Добавить документ'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        SystemSetting::updateOrCreate(
            ['key' => 'landing_page_blocks'],
            ['group' => 'landing', 'payload' => $state['blocks']]
        );

        Notification::make()->title('Главная страница обновлена')->success()->send();
    }
}