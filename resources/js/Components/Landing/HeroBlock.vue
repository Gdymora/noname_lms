<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ data: Object });
</script>

<template>
    <div class="relative bg-gray-900 text-white py-24 px-6 overflow-hidden">
        <img v-if="data.image" :src="'/storage/' + data.image" class="absolute inset-0 w-full h-full object-cover opacity-40">
        <div class="absolute inset-0 bg-gradient-to-r from-gray-900 via-gray-900/80 to-transparent"></div>
        
        <div class="relative max-w-7xl mx-auto z-10">
            <h1 class="text-4xl md:text-6xl font-extrabold mb-6 leading-tight max-w-3xl">{{ data.title }}</h1>
            <p class="text-xl text-gray-300 mb-8 max-w-xl">{{ data.subtitle }}</p>
            <a :href="data.button_url" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-8 rounded-xl shadow-lg transition transform hover:-translate-y-1">
                {{ data.button_text }}
            </a>
        </div>
    </div>
</template>