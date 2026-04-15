<script setup>
import LmsLayout from '@/Layouts/LmsLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    courses: Array,
    filters: Object,
});

// Базовое форматирование числа в рубли
const formatCurrency = (value) => {
    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB',
        minimumFractionDigits: 0
    }).format(value);
};

// Умная функция для вывода цены на карточке
const getPriceLabel = (course) => {
    // 1. Если у курса есть тарифы (пришло поле tariffs_min_price)
    if (course.tariffs_min_price !== null && course.tariffs_min_price !== undefined) {
        return 'от ' + formatCurrency(course.tariffs_min_price);
    }

    // 2. Если тарифов нет, смотрим обычную цену
    if (course.price === 0) {
        return 'Бесплатно';
    }

    return formatCurrency(course.price);
};

// Поиск
const search = ref(props.filters?.search || '');

const handleSearch = () => {
    router.get(route('courses.index'), { search: search.value }, {
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <Head title="Каталог" />

    <LmsLayout>
        <!-- HERO SECTION -->
        <div class="bg-indigo-900 text-white pt-12 pb-24 px-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-indigo-500 opacity-20 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-72 h-72 rounded-full bg-pink-500 opacity-20 blur-3xl"></div>

            <div class="max-w-5xl mx-auto relative z-10">
                <h1 class="text-4xl font-extrabold tracking-tight mb-4">
                    Что будем учить сегодня?
                </h1>
                <p class="text-indigo-200 text-lg max-w-2xl">
                    Выбирайте из лучших курсов, созданных экспертами. Прокачивайте навыки в удобном темпе.
                </p>
                
                <!-- Поиск -->
                <div class="mt-8 max-w-xl">
                    <div class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-pink-600 to-indigo-600 rounded-lg blur opacity-25 group-hover:opacity-75 transition duration-200"></div>
                        <div class="relative flex items-center bg-white rounded-lg p-2">
                            <svg class="w-6 h-6 text-gray-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <input 
                                type="text" 
                                v-model="search"
                                @keydown.enter="handleSearch"
                                placeholder="Поиск курса..." 
                                class="w-full border-none focus:ring-0 text-gray-800 placeholder-gray-400 h-10 ml-2 focus:outline-none"
                            >
                            <button @click="handleSearch" class="bg-indigo-600 text-white px-6 py-2 rounded-md font-medium hover:bg-indigo-700 transition">Найти</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- СПИСОК КУРСОВ -->
        <div class="max-w-6xl mx-auto px-6 -mt-16 pb-20 relative z-20">
            
            <div v-if="courses.length === 0" class="bg-white rounded-2xl shadow-xl p-10 text-center">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486747.png" class="w-24 h-24 mx-auto opacity-50 mb-4" alt="">
                <h3 class="text-xl font-bold text-gray-700">Пока пусто</h3>
                <p class="text-gray-500">Курсы по вашему запросу не найдены.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <div v-for="course in courses" :key="course.id" class="group bg-white rounded-2xl shadow-sm hover:shadow-2xl transition-all duration-300 flex flex-col overflow-hidden border border-gray-100 transform hover:-translate-y-1">
                    
                    <!-- Обложка -->
                    <div class="h-56 relative overflow-hidden">
                        <img 
                            v-if="course.thumbnail_url" 
                            :src="'/storage/' + course.thumbnail_url" 
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" 
                            alt="Cover"
                        >
                        <div v-else class="w-full h-full bg-gradient-to-br from-indigo-50 to-purple-50 flex items-center justify-center">
                             <svg class="w-16 h-16 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>

                        <!-- Бейдж цены -->
                        <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold text-gray-900 shadow-sm">
                            <!-- ИСПОЛЬЗУЕМ НОВУЮ ФУНКЦИЮ -->
                            {{ getPriceLabel(course) }}
                        </div>
                    </div>

                    <!-- Контент -->
                    <div class="p-6 flex-grow flex flex-col">
                        <div class="flex items-center gap-2 mb-3">
                             <img 
                                :src="course.teacher?.avatar_url ? '/storage/' + course.teacher.avatar_url : 'https://ui-avatars.com/api/?name=' + (course.teacher?.name || 'T') + '&background=random'" 
                                class="w-6 h-6 rounded-full object-cover border border-gray-200" 
                                alt="Author"
                             >
                             <span class="text-xs font-medium text-gray-500">{{ course.teacher?.name }}</span>
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 mb-2 leading-tight group-hover:text-indigo-600 transition-colors">
                            {{ course.title }}
                        </h3>

                        <p class="text-gray-500 text-sm line-clamp-2 mb-4 flex-grow">
                            {{ course.description || 'Краткое описание отсутствует...' }}
                        </p>

                        <div class="pt-4 border-t border-gray-50 mt-auto flex items-center justify-between">
                            <div class="flex items-center gap-1 text-yellow-500 text-sm font-bold">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <span>5.0</span>
                            </div>

                            <Link 
                                :href="route('courses.show', course.slug)" 
                                class="inline-flex items-center text-indigo-600 font-semibold text-sm hover:text-indigo-800 transition-colors"
                            >
                                Подробнее
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </LmsLayout>
</template>