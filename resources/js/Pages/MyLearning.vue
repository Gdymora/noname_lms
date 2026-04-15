<script setup>
import LmsLayout from '@/Layouts/LmsLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ courses: Array });
</script>

<template>
    <Head title="Мое обучение" />

    <LmsLayout>
        <div class="bg-indigo-900 text-white pt-12 pb-24 px-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-indigo-500 opacity-20 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-72 h-72 rounded-full bg-pink-500 opacity-20 blur-3xl"></div>

            <div class="max-w-6xl mx-auto relative z-10">
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-4">
                    Мое обучение
                </h1>
                <p class="text-indigo-200 text-lg max-w-2xl">
                    Возвращайтесь к урокам и отслеживайте свой прогресс. Каждое занятие приближает вас к цели.
                </p>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-6 -mt-16 pb-20 relative z-20">
            
            <div v-if="courses.length === 0" class="bg-white rounded-2xl shadow-xl p-12 text-center border border-gray-100">
                <div class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Вы пока нигде не учитесь</h3>
                <p class="text-gray-500 mb-8">Самое время выбрать новый навык из нашего каталога.</p>
                <Link 
                    :href="route('courses.index')" 
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition"
                >
                    Перейти в каталог
                </Link>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <div v-for="course in courses" :key="course.id" class="group bg-white rounded-2xl shadow-sm hover:shadow-2xl transition-all duration-300 flex flex-col overflow-hidden border border-gray-100 transform hover:-translate-y-1">
                    
                    <div class="h-48 relative overflow-hidden bg-gray-200">
                        <img 
                            v-if="course.thumbnail_url" 
                            :src="'/storage/' + course.thumbnail_url" 
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" 
                            alt="Cover"
                        >
                        <div v-else class="w-full h-full flex items-center justify-center bg-indigo-50">
                             <svg class="w-12 h-12 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>

                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center pl-1 shadow-lg">
                                <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" /></svg>
                            </div>
                        </div>

                        <div 
                            class="absolute top-4 right-4 backdrop-blur-md px-3 py-1 rounded-full text-xs font-bold shadow-sm border border-white/20"
                            :class="course.progress === 100 ? 'bg-green-500/90 text-white' : 'bg-white/90 text-gray-800'"
                        >
                            {{ course.progress === 100 ? 'Завершено' : 'В процессе' }}
                        </div>
                    </div>

                    <div class="p-6 flex-grow flex flex-col">
                        <div class="flex items-center gap-2 mb-3">
                             <img 
                                :src="course.teacher?.avatar_url ? '/storage/' + course.teacher.avatar_url : 'https://ui-avatars.com/api/?name=' + (course.teacher?.name || 'T')" 
                                class="w-6 h-6 rounded-full object-cover border border-gray-100" 
                                alt=""
                             >
                             <span class="text-xs font-medium text-gray-500">{{ course.teacher?.name }}</span>
                        </div>

                        <h3 class="text-lg font-bold text-gray-900 mb-4 line-clamp-2 group-hover:text-indigo-600 transition-colors">
                            {{ course.title }}
                        </h3>

                        <div class="mt-auto">
                            <div class="flex justify-between text-xs font-semibold text-gray-500 mb-2">
                                <span>Прогресс</span>
                                <span :class="course.progress === 100 ? 'text-green-600' : 'text-indigo-600'">{{ course.progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div 
                                    class="h-full rounded-full transition-all duration-1000 ease-out relative" 
                                    :class="course.progress === 100 ? 'bg-green-500' : 'bg-indigo-500'"
                                    :style="{ width: course.progress + '%' }"
                                >
                                    <div class="absolute top-0 right-0 bottom-0 w-full bg-gradient-to-r from-transparent to-white/30"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <Link 
                                :href="route('courses.show', course.slug)" 
                                class="block w-full text-center py-2 rounded-lg font-semibold transition-colors border"
                                :class="course.progress === 100 
                                    ? 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' 
                                    : 'bg-indigo-600 text-white border-transparent hover:bg-indigo-700 shadow-md shadow-indigo-200'"
                            >
                                {{ course.progress === 100 ? 'Страница курса' : 'Продолжить обучение' }}
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </LmsLayout>
</template>