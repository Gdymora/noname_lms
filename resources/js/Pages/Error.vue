<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    status: Number,
});

const title = computed(() => {
    return {
        503: 'Технические работы',
        500: 'Ошибка сервера',
        404: 'Страница не найдена',
        403: 'Доступ запрещен',
    }[props.status] || 'Ошибка';
});

const description = computed(() => {
    return {
        503: 'Мы проводим плановое обслуживание. Загляните чуть позже.',
        500: 'Что-то пошло не так на наших серверах. Мы уже чиним.',
        404: 'Похоже, вы забрели в неизведанные земли. Такой страницы не существует.',
        403: 'У вас нет прав для просмотра этой страницы.',
    }[props.status] || 'Произошла неизвестная ошибка.';
});
</script>

<template>
    <Head :title="title" />

    <PublicLayout>
        <div class="relative flex items-center justify-center min-h-[70vh] bg-gray-50 overflow-hidden">
            
            <!-- Фоновая огромная цифра -->
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none select-none overflow-hidden">
                <span class="text-[20rem] font-black text-gray-100 opacity-80 transform -translate-y-10 scale-150 blur-sm">
                    {{ status }}
                </span>
            </div>

            <div class="relative z-10 text-center px-4 max-w-2xl">
                <div class="mb-6 inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-xl border border-gray-100">
                    <span class="text-4xl" v-if="status === 404">🔍</span>
                    <span class="text-4xl" v-else-if="status === 403">🔒</span>
                    <span class="text-4xl" v-else-if="status === 503">🛠</span>
                    <span class="text-4xl" v-else>🔥</span>
                </div>

                <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight text-gray-900 mb-4">
                    {{ title }}
                </h1>
                
                <p class="text-lg text-gray-500 mb-10">
                    {{ description }}
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <!-- Кнопка Назад -->
                    <button 
                        @click="$event.view.history.back()" 
                        class="px-8 py-3 bg-white border border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition shadow-sm"
                    >
                        ← Вернуться назад
                    </button>

                    <!-- Кнопка Домой -->
                    <Link 
                        href="/" 
                        class="px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-200 flex items-center justify-center gap-2"
                    >
                        На главную
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>