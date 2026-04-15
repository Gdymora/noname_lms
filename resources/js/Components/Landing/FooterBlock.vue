<script setup>
defineProps({ data: Object });
const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="bg-gray-900 text-gray-400 py-12 px-6 border-t border-gray-800">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-12 text-sm">
            
            <!-- 1. Контакты и Реквизиты -->
            <div>
                <h4 class="text-white font-bold mb-6 text-lg">Контакты</h4>
                <div class="space-y-3">
                    <p v-if="data.phone" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        {{ data.phone }}
                    </p>
                    <p v-if="data.email" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        {{ data.email }}
                    </p>
                </div>

                <!-- Юридическая информация -->
                <div class="mt-8 pt-6 border-t border-gray-800 space-y-2 text-xs opacity-60">
                    <p v-if="data.legal_name" class="font-bold text-gray-300 text-sm">
                        {{ data.legal_name }}
                    </p>
                    <div class="flex flex-wrap gap-x-3 gap-y-1">
                        <span v-if="data.inn">ИНН {{ data.inn }}</span>
                        <span v-if="data.ogrn">ОГРНИП {{ data.ogrn }}</span>
                    </div>
                    <p v-if="data.license">
                        Образовательная лицензия: <br> {{ data.license }}
                    </p>
                </div>
            </div>

            <!-- 2. Документы (Динамические) -->
            <div>
                <h4 class="text-white font-bold mb-6 text-lg">Информация</h4>
                <ul class="space-y-3">
                    <!-- Если админ добавил документы -->
                    <li v-for="(doc, i) in data.documents" :key="i">
                        <a :href="doc.url" class="hover:text-white transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            {{ doc.label }}
                        </a>
                    </li>

                    <!-- Фоллбэк (если список пуст, но есть старые ссылки) -->
                    <li v-if="!data.documents || data.documents.length === 0">
                        <a href="/p/offer" class="hover:text-white">Публичная оферта</a>
                    </li>
                </ul>
            </div>

            <!-- 3. Соцсети и Копирайт -->
            <div>
                <div class="flex gap-3 mb-6">
                    <a v-for="soc in data.socials" :key="soc.url" :href="soc.url" target="_blank" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-indigo-600 hover:text-white transition text-xs font-bold uppercase">
                        {{ soc.icon ? soc.icon[0] : 'L' }}
                    </a>
                </div>
                <p class="opacity-60">&copy; {{ currentYear }} {{ data.copyright || 'LMS Core' }}</p>
            </div>

        </div>
    </footer>
</template>