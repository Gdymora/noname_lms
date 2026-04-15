<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ data: Object });

const formatPrice = (val) => new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', minimumFractionDigits: 0 }).format(val);
const getPriceLabel = (c) => c.tariffs_min_price ? 'от ' + formatPrice(c.tariffs_min_price) : (c.price === 0 ? 'Бесплатно' : formatPrice(c.price));
</script>

<template>
    <section id="courses" class="py-20 bg-gray-50 px-6">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-bold text-gray-900 mb-12 text-center">{{ data.title }}</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div v-for="course in data.courses" :key="course.id" class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition overflow-hidden flex flex-col">
                    <div class="h-48 bg-gray-200 relative">
                        <img v-if="course.thumbnail_url" :src="'/storage/' + course.thumbnail_url" class="w-full h-full object-cover">
                        <div class="absolute top-4 right-4 bg-white/90 px-3 py-1 rounded-full text-xs font-bold">{{ getPriceLabel(course) }}</div>
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <h3 class="text-xl font-bold mb-2">{{ course.title }}</h3>
                        <div class="mt-auto pt-4 border-t border-gray-100 flex justify-between items-center">
                            <span class="text-sm text-gray-500">{{ course.teacher?.name }}</span>
                            <Link :href="route('public.course.show', course.slug)" class="text-indigo-600 font-bold hover:underline">Подробнее →</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>