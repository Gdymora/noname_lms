<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import FooterBlock from '@/Components/Landing/FooterBlock.vue'; // Импортируем компонент

const page = usePage();
const user = computed(() => page.props.auth.user);
// Получаем глобальные данные футера
const footerData = computed(() => page.props.footerData || {});
</script>

<template>
    <div class="min-h-screen bg-gray-50 font-sans text-gray-900 flex flex-col">
        <!-- Header -->
        <header class="bg-white border-b border-gray-100 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-lg">
                        {{ page.props.appName ? page.props.appName[0] : 'L' }}
                    </div>
                    <span class="text-xl font-bold text-gray-800 tracking-tight">
                        {{ page.props.appName || 'LMS Core' }}
                    </span>
                </Link>

                <!-- Navigation -->
                <nav class="flex items-center gap-4">
                    <Link v-if="user" :href="route('my.learning')" class="text-sm font-medium text-gray-500 hover:text-gray-900">
                        Мое обучение
                    </Link>
                    <template v-else>
                        <Link :href="route('login')" class="text-sm font-medium text-gray-500 hover:text-gray-900">
                            Войти
                        </Link>
                        <Link :href="route('register')" class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 transition">
                            Регистрация
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Content (растягиваем, чтобы футер прижался к низу) -->
        <main class="flex-grow">
            <slot />
        </main>

        <!-- GLOBAL FOOTER -->
        <!-- Передаем данные из Middleware в наш компонент -->
        <FooterBlock :data="footerData" />
    </div>
</template>