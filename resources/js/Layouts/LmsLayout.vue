<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const user = usePage().props.auth.user;
// Получаем роли, переданные из Middleware
const roles = usePage().props.auth.roles || [];

// Проверяем, есть ли доступ к админке
// Логика: Если есть любая роль КРОМЕ 'Student' (или если это Super Admin)
const hasAdminAccess = computed(() => {
    if (!roles.length) return false;
    return roles.some(role => role !== 'Student') || roles.includes('Super Admin');
});

const navLinks = [
    { name: 'Каталог курсов', route: 'courses.index', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' },
    { name: 'Мое обучение', route: 'my.learning', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
    { name: 'Мои заказы', route: 'my.orders', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { name: 'Профиль', route: 'profile.edit', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
];

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">
        
        <!-- SIDEBAR (Desktop) -->
        <aside class="hidden md:flex flex-col w-64 bg-white border-r border-gray-100 fixed h-full z-20">
            <!-- Логотип -->
            <div class="h-16 flex items-center px-8 border-b border-gray-50">
                <Link :href="route('courses.index')" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-lg">
                        {{ $page.props.appName ? $page.props.appName[0] : 'L' }}
                    </div>
                    <span class="text-xl font-bold text-gray-800 tracking-tight truncate">
                        {{ $page.props.appName || 'LMS Core' }}
                    </span>
                </Link>
            </div>

            <!-- Навигация -->
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <Link 
                    v-for="link in navLinks" 
                    :key="link.name"
                    :href="route(link.route)"
                    :class="[
                        route().current(link.route) 
                            ? 'bg-indigo-50 text-indigo-700' 
                            : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                        'group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200'
                    ]"
                >
                    <!-- Иконка SVG -->
                    <svg 
                        class="mr-3 flex-shrink-0 h-6 w-6 transition-colors duration-200" 
                        :class="route().current(link.route) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-600'"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" :d="link.icon" />
                    </svg>
                    {{ link.name }}
                </Link>
            </nav>

            <!-- КНОПКА АДМИНКИ (Видна только сотрудникам) -->
            <div v-if="hasAdminAccess" class="px-4 pb-2">
                <a 
                    href="/admin" 
                    class="flex items-center px-4 py-3 text-sm font-bold text-white bg-gray-900 rounded-xl hover:bg-gray-800 transition-all shadow-lg shadow-gray-200 group"
                >
                    <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Панель управления
                </a>
            </div>

            <!-- Юзер внизу -->
            <div class="p-4 border-t border-gray-50 flex items-center justify-between">
                <div class="flex items-center gap-3 overflow-hidden">
                    <img 
                        :src="user.avatar_url ? '/storage/' + user.avatar_url : 'https://ui-avatars.com/api/?name=' + user.name + '&background=random'" 
                        class="w-10 h-10 rounded-full border-2 border-white shadow-sm object-cover flex-shrink-0" 
                        alt=""
                    >
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ user.name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ user.email }}</p>
                    </div>
                </div>

                <button 
                    @click="logout" 
                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    title="Выйти"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 md:ml-64 relative w-full">
            <slot />
        </main>
    </div>
</template>