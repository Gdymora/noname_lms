# PROJECT.md — Документация проекта LMS Core (v1.0)

## 1. Общее описание
**LMS Core** — это платформа для управления обучением (Learning Management System), разработанная как "коробочное" решение.
Система разделена на два интерфейса:
1.  **Админ-панель (FilamentPHP):** Для создания контента, управления пользователями и продажами.
2.  **Кабинет студента (Vue 3 + Inertia):** SPA-приложение для прохождения курсов.

---

## 2. Технологический стек
* **Backend:** PHP 8.2+, Laravel 11.
* **Database:** PostgreSQL (активное использование `JSONB`).
* **Frontend (Student):** Vue.js 3 (Composition API), Inertia.js, TailwindCSS.
* **Frontend (Admin):** FilamentPHP v3 (Livewire).
* **Среда:** Docker (Laravel Sail), WSL2.

---

## 3. База данных и Модели (ERD)

### Основные сущности

#### `User` (Пользователи)
* **Роли:** Студент, Админ, Менеджер, Куратор (реализовано через Filament/RBAC в будущем).
* **Связи:**
    * `hasMany Course` (как преподаватель).
    * `belongsToMany Lesson` (pivot `lesson_user` — прогресс обучения).
    * `hasMany HomeworkSubmission` (сданные работы).
    * `hasMany Order` (покупки).

#### `Course` (Курсы)
* **Поля:** `title`, `slug`, `price` (в копейках), `is_published`, `thumbnail_url`.
* **Связи:**
    * `belongsTo User` (Teacher).
    * `hasMany CourseModule` (структура).
    * `hasMany Order`.

#### `CourseModule` (Модули — Рекурсивная структура)
* **Особенность:** Бесконечная вложенность через `parent_id`.
* **Связи:**
    * `belongsTo Course`.
    * `belongsTo CourseModule` (Parent).
    * `hasMany CourseModule` (Children).
    * `hasMany Lesson`.

#### `Lesson` (Уроки)
* **Поля:** `title`, `slug`, `duration_minutes`, `is_stop_lesson`.
* **Связи:**
    * `belongsTo CourseModule`.
    * `hasMany ContentBlock` (контент).
    * `hasOne Homework` (задание).
    * `belongsToMany User` (через `lesson_user` для трекинга прохождения).

#### `ContentBlock` (Контент урока)
* **Хранение:** Поле `content` типа `JSONB`.
* **Типы блоков:** `text` (HTML), `video_youtube`, `video_vk`, `video_rutube`, `image`, `file`.
* **Связи:** `belongsTo Lesson`.

#### `Homework` (Конфигурация ДЗ)
* **Хранение:** Поле `submission_fields` (`JSONB`) хранит конструктор формы ответа (какие поля требовать от студента).
* **Связи:** `belongsTo Lesson`.

#### `HomeworkSubmission` (Ответы студентов)
* **Хранение:** Поле `content` (`JSONB`) хранит ответы студента.
* **Поля:** `status` (pending, approved, rejected, revision), `grade_percent`.
* **Связи:**
    * `belongsTo Homework`.
    * `belongsTo User` (Student).
    * `belongsTo User` (Curator).

#### `Order` (Заказы)
* **Поля:** `amount`, `status` (new, paid), `history_log` (JSON).
* **Связи:** `belongsTo User`, `belongsTo Course`.

#### `SystemSetting`
* Хранение глобальных настроек (SMTP, Логотип) в формате "Ключ-Значение".

---

## 4. Контроллеры и Логика (Backend)

### Namespace: `App\Http\Controllers\Student`

| Контроллер | Метод | Описание |
| :--- | :--- | :--- |
| **CourseController** | `index` | Каталог курсов (Landing page). |
| | `show($slug)` | Страница курса с программой (дерево модулей). |
| | `myCourses` | Личный кабинет ("Мое обучение") с расчетом % прогресса. |
| **OrderController** | `enroll` | Запись на курс. Если бесплатно — авто-доступ. Если платно — создание заявки. |
| **LearningController** | `show` | **Плеер урока.** Проверка доступа, загрузка дерева модулей, контента и ДЗ. |
| | `markAsComplete` | Отметка урока как пройденного + авто-переход к следующему (алгоритм Flatten Tree). |
| **HomeworkController** | `submit` | Прием ДЗ. Обработка файлов, сохранение JSON-ответа. |

---

## 5. Админ-панель (Filament Resources)

Расположение: `App\Filament\Resources`

1.  **CourseResource:**
    * CRUD курсов.
    * `RelationManager`: Управление корневыми модулями.
2.  **CourseModuleResource:**
    * Управление деревом модулей.
    * Логика выбора `parent_id` (фильтрация по текущему курсу).
3.  **LessonResource:**
    * **Конструктор контента (`Repeater`):** Визуальное добавление блоков (Текст, Видео, Файлы).
    * Поддержка Embed-ссылок (VK, Rutube, YouTube).
4.  **HomeworkResource:**
    * **Конструктор формы (`Repeater`):** Админ собирает форму ответа (Текст, Файл, Ссылка) для студента.
5.  **HomeworkSubmissionResource:**
    * Интерфейс проверки ДЗ куратором.
    * Выставление оценки (Grade %) и смена статуса.

---

## 6. Frontend (Vue + Inertia)

### Layouts
* `LmsLayout.vue`: Современный макет с боковым меню (Sidebar), шапкой и аватаром пользователя.

### Pages (`resources/js/Pages`)
1.  **Courses/Index.vue:**
    * Каталог курсов.
    * Hero-баннер.
    * Карточки с ценами и авторами.
2.  **Courses/Show.vue:**
    * Лендинг курса.
    * Компонент `Accordion` для программы курса (поддержка вложенности).
    * Кнопка Enrollment (Записаться/Купить).
3.  **MyLearning.vue:**
    * Список купленных курсов.
    * **Прогресс-бар:** Визуализация `(completed / total) * 100`.
4.  **Learning/Show.vue (Плеер):**
    * Сайдбар с навигацией по урокам (подсветка активного).
    * Рендеринг контента (Tailwind Typography для текста, Iframe для видео).
    * Кнопка "Завершить и далее".
    * Виджет Домашнего задания.

### Components
* `HomeworkWidget.vue`:
    * Динамическая форма на основе JSON-конфига.
    * Отправка файлов через `forceFormData`.
    * Отображение статуса проверки (цветовая индикация) и оценки.

---

## 7. Ключевые бизнес-процессы (Workflows)

### 1. Прохождение урока
1.  Студент открывает урок (`LearningController@show`).
2.  Изучает контент (Блоки).
3.  Нажимает "Завершить и далее" (`LearningController@markAsComplete`).
4.  Система ставит отметку в `lesson_user`.
5.  Система ищет следующий урок в дереве модулей и редиректит на него.

### 2. Сдача ДЗ
1.  Админ настраивает поля (например: "Ссылка на GitHub" + "Скриншот").
2.  Студент видит форму в уроке.
3.  Заполняет данные -> Отправка (`HomeworkController@submit`).
4.  Данные сохраняются в `homework_submissions` (файлы в `storage/app/public/homeworks`).
5.  Статус заявки: `pending`.
6.  Куратор в админке проверяет -> Ставит `approved` и оценку `100%`.
7.  Студент видит зеленый статус и оценку в уроке.

### 3. Запись на курс
1.  Студент жмет "Купить" (`OrderController@enroll`).
2.  Если цена 0 -> Создается `Order` (status: `paid`) -> Доступ открыт.
3.  Если цена > 0 -> Создается `Order` (status: `new`) -> Ожидание оплаты (в v1.0 ручная смена статуса админом).