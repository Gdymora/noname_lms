<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    page: Object,
});
</script>

<template>
    <Head :title="page.title" />

    <PublicLayout>
        <div class="py-16 bg-white">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Заголовок -->
                <h1 class="text-3xl font-extrabold text-gray-900 mb-8 sm:text-4xl">
                    {{ page.title }}
                </h1>

                <!-- Контент (HTML из редактора) -->
                <div class="prose prose-indigo prose-lg max-w-none text-gray-600" v-html="page.content"></div>

                <!-- Дата обновления (для юридической точности) -->
                <div class="mt-10 pt-6 border-t border-gray-100 text-sm text-gray-400">
                    Последнее обновление: {{ new Date(page.updated_at).toLocaleDateString() }}
                </div>

            </div>
        </div>
    </PublicLayout>
</template>