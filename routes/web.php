<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\CourseController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Главная страница (пока просто редирект на вход)
// Главная страница
Route::get('/', [\App\Http\Controllers\Public\HomeController::class, 'index'])->name('home');
// === ПУБЛИЧНЫЕ СТРАНИЦЫ ===
Route::get('/c/{slug}', [\App\Http\Controllers\Public\PublicCourseController::class, 'show'])->name('public.course.show');
Route::get('/c/{slug}/thank-you', [\App\Http\Controllers\Public\PublicCourseController::class, 'thankYou'])->name('public.course.thankyou');
Route::get('/c/{slug}/exists', [\App\Http\Controllers\Public\PublicCourseController::class, 'orderExists'])->name('public.course.order_exists');
// Статичные страницы (Оферта, Политика)
Route::get('/p/{slug}', [\App\Http\Controllers\Public\PageController::class, 'show'])->name('public.page');

    // Анкеты
Route::get('/f/{slug}', [\App\Http\Controllers\Public\FormController::class, 'show'])->name('public.form.show');
Route::post('/f/{form}', [\App\Http\Controllers\Public\FormController::class, 'submit'])->name('public.form.submit');

// Обработка быстрого заказа
Route::post('/c/{course}/fast-order', [\App\Http\Controllers\Public\FastOrderController::class, 'store'])->name('public.course.fast_order');
// Группа маршрутов для авторизованных студентов
Route::middleware(['auth', 'verified'])->group(function () {

    // Плеер уроков
// URL будет вида: /learning/php-course/lesson-1
Route::get('/learning/{course:slug}/{lessonSlug?}', [\App\Http\Controllers\Student\LearningController::class, 'show'])
    ->name('learning.lesson');

    // Страница выбора оплаты
    Route::get('/payment/{order}/checkout', [\App\Http\Controllers\Student\PaymentController::class, 'checkout'])
        ->name('payment.checkout');

    // Инициализация платежа
    Route::post('/payment/{order}/pay', [\App\Http\Controllers\Student\PaymentController::class, 'pay'])
        ->name('payment.pay');

    // Каталог курсов
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');
    Route::post('/homework/{homework}/submit', [\App\Http\Controllers\Student\HomeworkController::class, 'submit'])
    ->name('homework.submit');

    // Профиль (стандартный Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Личный кабинет (вместо dashboard редиректа можно сразу сюда)
Route::get('/my-learning', [CourseController::class, 'myCourses'])->name('my.learning');

Route::post('/learning/{lesson}/complete', [\App\Http\Controllers\Student\LearningController::class, 'markAsComplete'])
    ->name('learning.complete');

// Логика записи
Route::post('/courses/{course:slug}/enroll', [\App\Http\Controllers\Student\OrderController::class, 'enroll'])->name('courses.enroll');

// Страница "Спасибо за покупку"
Route::get('/courses/{course:slug}/thank-you', [\App\Http\Controllers\Student\CourseController::class, 'thankYou'])
    ->name('courses.thankyou');

    // История заказов
Route::get('/my-orders', [\App\Http\Controllers\Student\OrderController::class, 'index'])
    ->name('my.orders');
    // Страница "Заказ уже существует"
Route::get('/courses/{course:slug}/order-exists', [\App\Http\Controllers\Student\CourseController::class, 'orderExists'])
    ->name('courses.order_exists');

Route::post('/learning/test/{block}/check', [\App\Http\Controllers\Student\LearningController::class, 'checkTest'])
    ->name('learning.test.check');



// Dashboard теперь пусть ведет на My Learning
Route::get('/dashboard', function () {
    return redirect()->route('my.learning');
})->name('dashboard');
});

require __DIR__.'/auth.php';