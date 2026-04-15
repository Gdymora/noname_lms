<script setup>
defineProps({ data: Object });
</script>

<template>
    <section class="py-20 bg-white px-6 border-b border-gray-100">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center gap-12">
            
            <!-- Левая колонка: Текст -->
            <div class="flex-1">
                <!-- ЗАГОЛОВОК -->
                <h2 v-if="data.title" class="text-3xl font-extrabold text-gray-900 mb-6 leading-tight">
                    {{ data.title }}
                </h2>
                
                <!-- Контент (HTML) -->
                <div class="prose prose-lg prose-indigo text-gray-600 leading-relaxed" v-html="data.content"></div>
            </div>

            <!-- Правая колонка: Картинка -->
            <div class="flex-1 w-full" v-if="data.image">
                <img 
                    :src="'/storage/' + data.image" 
                    class="rounded-2xl shadow-2xl w-full h-auto object-cover transform hover:scale-[1.02] transition duration-500"
                    alt="About us"
                >
            </div>
            
        </div>
    </section>
</template>