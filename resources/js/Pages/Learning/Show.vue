<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import HomeworkWidget from '@/Components/HomeworkWidget.vue';
import QuizBlock from '@/Components/QuizBlock.vue';

const props = defineProps({
    course: Object,
    syllabus: Array,
    lesson: Object,
    homework: Object,
    submission: Object,
    prevLessonUrl: String,
    canComplete: Boolean,
    isAdminView: Boolean,
});

const isSidebarOpen = ref(true);
const isCurrentLesson = (lessonId) => props.lesson.id === lessonId;

const nextLesson = () => {
    if (!props.canComplete) return;
    router.post(route('learning.complete', props.lesson.id));
};
</script>

<template>
    <Head :title="lesson.title" />

    <div class="flex h-screen bg-gray-100 overflow-hidden">
        
        <!-- SIDEBAR -->
        <aside 
            class="bg-white w-80 border-r border-gray-200 flex-shrink-0 flex flex-col transition-all duration-300"
            :class="{ '-ml-80': !isSidebarOpen }"
        >
            <div class="h-16 flex items-center px-4 border-b border-gray-100">
                <Link :href="route('my.learning')" class="flex items-center text-gray-500 hover:text-gray-900 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span class="text-sm font-medium">Назад к курсам</span>
                </Link>
            </div>

            <div v-if="isAdminView" class="px-4 py-2 bg-amber-50 border-b border-amber-100 text-xs text-amber-800">
                👁 <strong>Режим Админа:</strong> Вы видите все уроки и даты.
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                <div v-for="module in syllabus" :key="module.id">
                    <div class="flex items-center justify-between mb-2 px-2">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ module.title }}</h3>
                    </div>

                    <ul class="space-y-1 mb-2">
                        <li v-for="l in module.lessons" :key="l.id">
                            <div v-if="l.is_locked_by_date" class="flex flex-col px-3 py-2 rounded-md text-sm text-gray-400 bg-gray-50 border border-transparent cursor-not-allowed">
                                <div class="flex items-center"><svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg><span>{{ l.title }}</span></div>
                                <span class="text-[10px] text-orange-500 ml-6 mt-1 font-medium uppercase tracking-wide">{{ l.locked_message }}</span>
                            </div>
                            <Link v-else :href="route('learning.lesson', [course.slug, l.slug])" class="flex items-center px-3 py-2 rounded-md text-sm transition-colors" :class="isCurrentLesson(l.id) ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-gray-700 hover:bg-gray-50'">
                                <svg v-if="isCurrentLesson(l.id)" class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" /></svg>
                                <svg v-else class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ l.title }}
                            </Link>
                        </li>
                    </ul>

                    <div v-if="module.children && module.children.length > 0" class="pl-4 border-l-2 border-gray-100 ml-2 space-y-4">
                        <div v-for="child in module.children" :key="child.id">
                             <div class="flex items-center justify-between mb-1">
                                <h4 class="text-xs font-semibold text-gray-500">{{ child.title }}</h4>
                             </div>
                             <ul class="space-y-1">
                                <li v-for="cl in child.lessons" :key="cl.id">
                                    <div v-if="cl.is_locked_by_date" class="flex flex-col px-3 py-2 rounded-md text-sm text-gray-400 bg-gray-50 border border-transparent cursor-not-allowed">
                                        <div class="flex items-center"><svg class="w-3 h-3 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>{{ cl.title }}</div>
                                        <span class="text-[10px] text-orange-500 ml-5 mt-0.5 font-medium uppercase tracking-wide">{{ cl.locked_message }}</span>
                                    </div>
                                    <Link v-else :href="route('learning.lesson', [course.slug, cl.slug])" class="block px-3 py-2 rounded-md text-sm hover:bg-gray-50 truncate transition-colors" :class="isCurrentLesson(cl.id) ? 'text-indigo-700 font-medium' : 'text-gray-600'">{{ cl.title }}</Link>
                                </li>
                             </ul>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- MAIN AREA -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 flex-shrink-0">
                <button @click="isSidebarOpen = !isSidebarOpen" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <h1 class="text-lg font-bold text-gray-800 truncate ml-4 flex-1">{{ lesson.title }}</h1>
            </header>

            <div class="flex-1 overflow-y-auto bg-gray-50 p-6 lg:p-10">
                <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-8 min-h-full">
                    
                    <div class="space-y-8">
                        <div v-for="block in lesson.blocks" :key="block.id" class="content-block">
                            <!-- Text -->
                            <div v-if="block.type === 'text'" class="prose prose-indigo max-w-none" v-html="block.content.html"></div>
                            
                            <!-- AUDIO (НОВЫЙ БЛОК) -->
                            <div v-else-if="block.type === 'audio'" class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <audio controls class="w-full focus:outline-none">
                                    <source :src="'/storage/' + block.content.audio_path">
                                    Ваш браузер не поддерживает аудио элемент.
                                </audio>
                            </div>

                            <!-- Buttons -->
                            <div v-else-if="block.type === 'buttons'" class="flex flex-wrap gap-4">
                                <a v-for="(btn, index) in block.content.buttons" :key="index" :href="btn.url" :target="btn.is_blank ? '_blank' : '_self'" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-bold rounded-xl shadow-sm text-white transition-all transform active:scale-95" :class="{'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200': btn.color === 'primary', 'bg-green-600 hover:bg-green-700 shadow-green-200': btn.color === 'success', 'bg-red-600 hover:bg-red-700 shadow-red-200': btn.color === 'danger', 'bg-gray-600 hover:bg-gray-700 shadow-gray-200': btn.color === 'gray'}">{{ btn.label }}</a>
                            </div>
                            
                            <!-- Quiz -->
                            <div v-else-if="block.type === 'quiz'"><QuizBlock :block="block" :result="block.test_results?.[0]" /></div>
                            
                            <!-- Video -->
                            <div v-else-if="block.type.startsWith('video_')" class="rounded-xl overflow-hidden bg-black aspect-video shadow-lg">
                                <iframe :src="block.content.url" class="w-full h-full" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
                            </div>
                            
                            <!-- Image -->
                            <div v-else-if="block.type === 'image'" class="flex justify-center"><img :src="'/storage/' + block.content.image_path" class="rounded-lg shadow-md max-h-[500px]"></div>
                            
                            <!-- File -->
                            <div v-else-if="block.type === 'file'" class="flex items-center p-4 bg-indigo-50 border border-indigo-100 rounded-lg">
                                <svg class="w-8 h-8 text-indigo-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                <div class="flex-1"><p class="font-medium text-indigo-900">{{ block.content.file_name }}</p><p class="text-xs text-indigo-500">Файл для скачивания</p></div>
                                <a :href="'/storage/' + block.content.file_path" download class="px-4 py-2 bg-white text-indigo-600 text-sm font-bold rounded border border-indigo-200 hover:bg-indigo-50 transition">Скачать</a>
                            </div>
                            
                            <!-- Separator -->
                            <hr v-else-if="block.type === 'separator'" class="my-8 border-gray-200">
                        </div>

                        <div v-if="lesson.blocks.length === 0" class="text-center text-gray-400 italic py-10">Этот урок пока пуст.</div>
                    </div>

                    <div v-if="homework" class="max-w-3xl mx-auto mt-16"><HomeworkWidget :homework="homework" :submission="submission" /></div>

                    <div class="mt-16 flex justify-between pt-6 border-t border-gray-100 items-center">
                        <div v-if="prevLessonUrl"><Link :href="prevLessonUrl" class="text-gray-500 hover:text-gray-900 font-medium flex items-center">← Предыдущий</Link></div>
                        <div v-else class="text-gray-300 font-medium cursor-not-allowed">← Предыдущий</div>
                        
                        <button @click="nextLesson" :disabled="!canComplete" class="px-6 py-3 rounded-lg font-bold shadow-lg transition flex items-center gap-2" :class="canComplete ? 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-200' : 'bg-gray-300 text-gray-500 cursor-not-allowed shadow-none opacity-70'">
                            <span>Завершить и далее</span>
                            <svg v-if="!canComplete" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>