<script setup>
defineProps({ data: Object });
</script>

<template>
    <section class="py-20 bg-white px-6">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-bold text-gray-900 mb-12 text-center">{{ data.title }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <div v-for="(expert, i) in data.items" :key="i" class="text-center">
                    <img :src="'/storage/' + expert.photo" class="w-32 h-32 rounded-full mx-auto mb-4 object-cover border-4 border-indigo-50">
                    <h3 class="text-xl font-bold">{{ expert.name }}</h3>
                    <p class="text-indigo-600 font-medium">{{ expert.role }}</p>
                </div>
            </div>
        </div>
    </section>
</template>