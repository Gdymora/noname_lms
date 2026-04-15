<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';

// Импортируем блоки
import HeroBlock from '@/Components/Landing/HeroBlock.vue';
import CoursesBlock from '@/Components/Landing/CoursesBlock.vue';
import AboutBlock from '@/Components/Landing/AboutBlock.vue';
import ExpertsBlock from '@/Components/Landing/ExpertsBlock.vue';
// FooterBlock здесь импортировать НЕ НУЖНО, он в Layout

defineProps({
    blocks: Array,
});

// Маппинг типа блока к компоненту
const components = {
    hero: HeroBlock,
    courses: CoursesBlock,
    about: AboutBlock,
    experts: ExpertsBlock,
    // footer: FooterBlock, <--- УБРАЛИ ЭТУ СТРОКУ, чтобы не дублировался
};
</script>

<template>
    <Head title="Главная" />

    <PublicLayout>
        <div v-for="(block, index) in blocks" :key="index">
            <!-- Рендерим только если компонент объявлен в списке выше -->
            <component 
                :is="components[block.type]" 
                v-if="components[block.type]" 
                :data="block.data" 
            />
        </div>
    </PublicLayout>
</template>