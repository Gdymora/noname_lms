<script setup>
import LmsLayout from '@/Layouts/LmsLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    course: Object,
});
</script>

<template>
    <Head title="Заказ уже существует" />

    <LmsLayout>
        <div class="min-h-screen flex flex-col items-center justify-center text-center px-4 -mt-16">
            <!-- Иконка часов/ожидания -->
            <div class="w-24 h-24 bg-orange-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-12 h-12 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Заказ уже создан</h1>
            
            <p class="text-lg text-gray-600 max-w-xl mb-8">
                Вы уже оформляли заявку на курс <br>
                <span class="font-bold text-indigo-600 text-xl">"{{ course.title }}"</span>.
                <br><br>
                Вам не нужно создавать новую. Наш менеджер видит вашу заявку и скоро свяжется с вами для уточнения деталей.
            </p>

            <div class="flex flex-col sm:flex-row gap-4">
                <Link 
                    :href="route('courses.index')" 
                    class="px-6 py-3 bg-white border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition"
                >
                    Вернуться в каталог
                </Link>
                <Link 
                    :href="route('my.learning')" 
                    class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition shadow-lg shadow-indigo-200"
                >
                    Мое обучение
                </Link>
            </div>
        </div>
    </LmsLayout>
</template>