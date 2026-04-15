<?php

namespace Database\Seeders;

use App\Models\ContentBlock;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Homework;
use App\Models\Lesson;
use App\Models\Order;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. РОЛИ
        $roles = ['Super Admin', 'Teacher', 'Student', 'Manager', 'Curator'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // 2. ПЕРСОНАЛ
        $admin = User::firstOrCreate(
            ['email' => 'admin@lms.test'],
            ['name' => 'Super Admin', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $admin->assignRole('Super Admin');

        $teacher = User::firstOrCreate(
            ['email' => 'teacher@lms.test'],
            ['name' => 'Иван Преподаватель', 'password' => Hash::make('password'), 'email_verified_at' => now(), 'avatar_url' => null]
        );
        $teacher->assignRole('Teacher');

        $manager = User::firstOrCreate(
            ['email' => 'manager@lms.test'],
            ['name' => 'Ольга Менеджер', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $manager->assignRole('Manager');

        $curator = User::firstOrCreate(
            ['email' => 'curator@lms.test'],
            ['name' => 'Сергей Куратор', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $curator->assignRole('Curator');

        // 3. СТУДЕНТЫ (10 шт)
        $students = User::factory(10)->create();
        foreach ($students as $student) {
            $student->assignRole('Student');
        }

        // 4. КУРС №1: "PHP для начинающих" (С тарифами)
        $coursePhp = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'PHP для профессионалов',
            'slug' => 'php-pro',
            'description' => 'Полный курс по бэкенд-разработке. От синтаксиса до Laravel и Docker.',
            'price' => 0, // Цена определяется тарифами
            'is_published' => true,
        ]);
        
        // Назначаем куратора
        $coursePhp->curators()->sync([$curator->id]);

        // Тарифы
        $tariffBasic = Tariff::create([
            'course_id' => $coursePhp->id,
            'name' => 'Базовый',
            'price' => 1500000, // 15 000 руб (в копейках)
        ]);
        $tariffVip = Tariff::create([
            'course_id' => $coursePhp->id,
            'name' => 'VIP с наставником',
            'price' => 4500000, // 45 000 руб
        ]);

        // Модуль 1
        $mod1 = CourseModule::create([
            'course_id' => $coursePhp->id,
            'title' => 'Введение в PHP',
            'sort_order' => 1,
        ]);
        // Привязываем модуль ко всем тарифам
        $mod1->tariffs()->attach([$tariffBasic->id, $tariffVip->id]);

        // Урок 1.1 (Видео + Текст)
        $lesson1 = Lesson::create([
            'module_id' => $mod1->id,
            'title' => 'Настройка окружения (Docker)',
            'slug' => 'setup-docker',
            'duration_minutes' => 25,
            'is_published' => true,
            'sort_order' => 1,
        ]);
        $lesson1->tariffs()->attach([$tariffBasic->id, $tariffVip->id]);

        ContentBlock::create([
            'lesson_id' => $lesson1->id,
            'type' => 'video_youtube',
            'sort_order' => 1,
            'content' => ['url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ'], // Заглушка
        ]);
        ContentBlock::create([
            'lesson_id' => $lesson1->id,
            'type' => 'text',
            'sort_order' => 2,
            'content' => ['html' => '<p>В этом уроке мы разберем установку Docker Desktop и настройку Laravel Sail.</p>'],
        ]);

        // Урок 1.2 (Тест)
        $lesson2 = Lesson::create([
            'module_id' => $mod1->id,
            'title' => 'Проверка знаний: Основы',
            'slug' => 'quiz-basics',
            'duration_minutes' => 10,
            'is_published' => true,
            'sort_order' => 2,
        ]);
        $lesson2->tariffs()->attach([$tariffBasic->id, $tariffVip->id]);

        ContentBlock::create([
            'lesson_id' => $lesson2->id,
            'type' => 'quiz',
            'sort_order' => 1,
            'content' => [
                'min_score' => 70,
                'questions' => [
                    [
                        'question' => 'Какой знак используется для переменных в PHP?',
                        'answers' => [
                            ['text' => '@', 'is_correct' => false],
                            ['text' => '$', 'is_correct' => true],
                            ['text' => '%', 'is_correct' => false],
                        ]
                    ],
                    [
                        'question' => 'Laravel - это...',
                        'answers' => [
                            ['text' => 'CMS', 'is_correct' => false],
                            ['text' => 'Фреймворк', 'is_correct' => true],
                        ]
                    ]
                ]
            ],
        ]);

        // Модуль 2 (Только для VIP)
        $mod2 = CourseModule::create([
            'course_id' => $coursePhp->id,
            'title' => 'Продвинутые техники (VIP)',
            'sort_order' => 2,
        ]);
        $mod2->tariffs()->attach([$tariffVip->id]); // Доступ только VIP

        $lesson3 = Lesson::create([
            'module_id' => $mod2->id,
            'title' => 'Архитектура сложных систем',
            'slug' => 'architecture',
            'is_published' => true,
            'sort_order' => 1,
        ]);
        $lesson3->tariffs()->attach([$tariffVip->id]);
        
        // ДЗ к уроку 3
        Homework::create([
            'lesson_id' => $lesson3->id,
            'description' => '<p>Спроектируйте БД для интернет-магазина.</p>',
            'is_required' => true,
            'submission_fields' => [
                ['type' => 'text', 'label' => 'Ссылка на схему (DBDiagram)', 'required' => true],
                ['type' => 'file', 'label' => 'Скриншот схемы', 'required' => false],
            ]
        ]);


        // 5. КУРС №2: "Дизайн интерфейсов" (Простой)
        $courseDesign = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'UI/UX Дизайн с нуля',
            'slug' => 'ui-ux-start',
            'description' => 'Научитесь создавать красивые интерфейсы в Figma.',
            'price' => 990000, // 9 900 руб (без тарифов)
            'is_published' => true,
        ]);
        
        // Модуль
        $modDesign = CourseModule::create(['course_id' => $courseDesign->id, 'title' => 'Основы композиции']);
        Lesson::create([
            'module_id' => $modDesign->id, 
            'title' => 'Правило близости', 
            'slug' => 'prox-rule',
            'is_published' => true
        ]);

        // 6. АНКЕТА "Предзапись"
        $form = Form::create([
            'title' => 'Анкета предзаписи',
            'slug' => 'pre-order-list',
            'is_active' => true,
            'schema' => [
                [
                    'type' => 'text',
                    'name' => 'name',
                    'label' => 'Ваше имя',
                    'required' => true
                ],
                [
                    'type' => 'email',
                    'name' => 'email',
                    'label' => 'Ваш Email',
                    'required' => true
                ],
                [
                    'type' => 'select',
                    'name' => 'level',
                    'label' => 'Ваш уровень',
                    'options' => ['Новичок', 'Любитель', 'Профи'],
                    'required' => true
                ]
            ],
            'settings' => [
                'submit_text' => 'Записаться',
                'success_message' => 'Вы в списке!'
            ]
        ]);
        
        // Ответы на анкету
        FormSubmission::create([
            'form_id' => $form->id,
            'data' => ['name' => 'Test User', 'email' => 'test@mail.ru', 'level' => 'Новичок'],
            'utm_data' => ['utm_source' => 'google']
        ]);

        // 7. ЗАКАЗЫ (Раздаем доступы)
        // Студент 1 купил PHP (Базовый)
        Order::create([
            'user_id' => $students[0]->id,
            'course_id' => $coursePhp->id,
            'tariff_id' => $tariffBasic->id,
            'amount' => $tariffBasic->price,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Студент 2 купил PHP (VIP)
        Order::create([
            'user_id' => $students[1]->id,
            'course_id' => $coursePhp->id,
            'tariff_id' => $tariffVip->id,
            'amount' => $tariffVip->price,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Студент 3 купил Дизайн (Простой)
        Order::create([
            'user_id' => $students[2]->id,
            'course_id' => $courseDesign->id,
            'tariff_id' => null,
            'amount' => $courseDesign->price,
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        
        // Студент 4 сделал заказ, но не оплатил (New)
        Order::create([
            'user_id' => $students[3]->id,
            'course_id' => $coursePhp->id,
            'tariff_id' => $tariffBasic->id,
            'amount' => $tariffBasic->price,
            'status' => 'new',
            'manager_id' => $manager->id, // Менеджер взял в работу
        ]);
    }
}