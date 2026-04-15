<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-900 relative overflow-hidden">
        
        <!-- Декоративный фон -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 -mt-20 w-[800px] h-[800px] rounded-full bg-indigo-600/20 blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 -mb-20 w-[600px] h-[600px] rounded-full bg-purple-600/20 blur-3xl pointer-events-none"></div>

        <!-- Логотип и Название -->
        <div class="relative z-10 mb-6">
            <Link href="/" class="flex items-center gap-3 text-white hover:text-indigo-200 transition">
                <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-2xl shadow-lg shadow-indigo-500/30">
                    <!-- Первая буква названия -->
                    {{ page.props.appName ? page.props.appName[0] : 'L' }}
                </div>
                <!-- Динамическое название из конфига -->
                <span class="text-3xl font-bold tracking-tight">
                    {{ page.props.appName }}
                </span>
            </Link>
        </div>

        <!-- Карточка -->
        <div class="relative z-10 w-full sm:max-w-md mt-6 px-8 py-10 bg-white shadow-2xl overflow-hidden sm:rounded-2xl border border-gray-100/10">
            <slot />
        </div>

        <!-- Футер -->
        <div class="relative z-10 mt-8 text-gray-500 text-sm">
            &copy; {{ new Date().getFullYear() }} {{ page.props.appName }}. Все права защищены.
        </div>
    </div>
</template>