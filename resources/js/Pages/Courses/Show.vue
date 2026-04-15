<script setup>
import LmsLayout from '@/Layouts/LmsLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    course: Object,
    syllabus: Array,
    userOrder: Object,      // Заказ (если есть)
    telegramLinks: Object,  // Ссылки на чаты (если есть)
});

const user = computed(() => usePage().props.auth.user);

// === ЛОГИКА ПОКУПКИ (МОДАЛКА) ===
const showModal = ref(false);
const selectedTariffId = ref(null);
const needPassword = ref(false);

const form = useForm({
    name: user.value ? user.value.name : '',
    email: user.value ? user.value.email : '',
    password: '',
    tariff_id: null,
    agree_offer: false,
    agree_policy: false,
    agree_marketing: false,
});

// Проверка недостающих согласий
const missingConsents = computed(() => {
    const u = user.value;
    if (!u) return { offer: true, policy: true, marketing: true };
    return {
        offer: !u.accepted_offer_at,
        policy: !u.accepted_policy_at,
        marketing: !u.accepted_marketing_at,
    };
});

const openBuyModal = (tariffId = null) => {
    form.tariff_id = tariffId;

    // Если юзер авторизован и всё принял -> сразу заказ
    if (user.value && !missingConsents.value.offer && !missingConsents.value.policy) {
        submitOrder();
        return;
    }

    selectedTariffId.value = tariffId;
    form.reset('password');
    needPassword.value = false;
    showModal.value = true;
};

const submitOrder = () => {
    form.post(route('public.course.fast_order', props.course.id), {
        preserveScroll: true,
        onError: (errors) => {
            if (errors.password_required) needPassword.value = true;
        },
        onSuccess: () => showModal.value = false
    });
};

// === ХЕЛПЕРЫ ===
const formatPrice = (price) => {
    if (price === 0) return 'Бесплатно';
    return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', minimumFractionDigits: 0 }).format(price);
};

const getDisplayPrice = () => {
    if (props.course.tariffs_min_price !== null && props.course.tariffs_min_price !== undefined) {
        return 'от ' + formatPrice(props.course.tariffs_min_price);
    }
    return formatPrice(props.course.price);
};

const getTotalLessons = () => {
    let count = 0;
    props.syllabus.forEach(m => {
        count += m.lessons.length;
        if (m.children) m.children.forEach(c => count += c.lessons.length);
    });
    return count;
};
</script>

<template>
    <Head :title="course.title" />

    <LmsLayout>
        <!-- HERO SECTION -->
        <div class="relative bg-gray-900 text-white overflow-hidden">
            <!-- ФОН -->
            <div class="absolute inset-0 opacity-30">
                <img v-if="course.thumbnail_url" :src="'/storage/' + course.thumbnail_url" class="w-full h-full object-cover blur-sm scale-105">
                <div v-else class="w-full h-full bg-gradient-to-r from-indigo-900 to-purple-900"></div>
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/80 to-transparent"></div>

            <div class="relative max-w-6xl mx-auto px-6 py-20 flex flex-col md:flex-row gap-12 items-start">
                
                <!-- ЛЕВАЯ КОЛОНКА: ИНФО -->
                <div class="flex-1 space-y-6">
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 rounded-full text-xs font-bold uppercase tracking-wide">Онлайн-курс</span>
                        <span class="flex items-center text-gray-300 text-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ getTotalLessons() }} уроков
                        </span>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight">{{ course.title }}</h1>
                    <div class="flex items-center gap-4 pt-2">
                        <img :src="course.teacher?.avatar_url ? '/storage/' + course.teacher.avatar_url : 'https://ui-avatars.com/api/?name=' + (course.teacher?.name || 'T')" class="w-10 h-10 rounded-full border border-gray-600 object-cover">
                        <div>
                            <p class="text-sm text-gray-400">Автор курса</p>
                            <p class="text-white font-medium">{{ course.teacher?.name }}</p>
                        </div>
                    </div>
                </div>

                <!-- ПРАВАЯ КОЛОНКА: ДЕЙСТВИЕ -->
                <div class="w-full md:w-96 space-y-6">
                    
                    <!-- ВАРИАНТ 1: УЖЕ КУПЛЕНО -->
                    <div v-if="userOrder" class="space-y-4">
                        <!-- Зеленая карточка доступа -->
                        <div class="bg-green-900/80 backdrop-blur-md border border-green-500/50 rounded-2xl p-6 shadow-2xl">
                            <div class="mb-6 text-center">
                                <div class="w-16 h-16 bg-green-500 text-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <h3 class="text-xl font-bold text-white mb-1">Доступ открыт</h3>
                                <p class="text-green-200 text-sm">
                                    Ваш тариф: <span class="font-bold text-white">{{ userOrder.tariff ? userOrder.tariff.name : 'Стандарт' }}</span>
                                </p>
                            </div>

                            <!-- КНОПКА В ПЛЕЕР -->
                            <Link 
                                :href="route('learning.lesson', course.slug)" 
                                class="block w-full bg-white text-green-700 hover:bg-green-50 font-bold py-3 px-6 rounded-xl text-center transition shadow-lg"
                            >
                                К обучению →
                            </Link>
                        </div>

                        <!-- TELEGRAM КНОПКИ (Если есть) -->
                        <div v-if="telegramLinks && (telegramLinks.channel || telegramLinks.chat)" class="grid grid-cols-1 gap-3">
                            <a v-if="telegramLinks.channel" :href="telegramLinks.channel" target="_blank" class="flex items-center gap-4 p-4 bg-white border border-blue-100 rounded-xl hover:border-blue-300 hover:shadow-md transition group">
                                <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M21.928 2.523c-.706-.705-1.871-.79-2.647-.22l-16.5 11.5c-.672.49-.79 1.43-.24 2.06.52.59 1.43.67 2.05.18l2.9-2.18 3.5 3.5c.39.39 1.02.39 1.41 0l8.5-8.5c.39-.39.39-1.02 0-1.41-.39-.39-1.02-.39-1.41 0l-7.79 7.79-2.8-2.8 13.02-9.12c.43-.32.49-.91.12-1.29z"/><path d="M5.7 13.3L2.5 15.5c-.5.35-.5 1.08 0 1.43l4.5 3.15c.45.31 1.06.13 1.28-.37l1.5-3.5-4.08-2.91z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 group-hover:text-blue-600 transition">Канал курса</p>
                                    <p class="text-xs text-gray-500">Новости и анонсы</p>
                                </div>
                            </a>
                            <a v-if="telegramLinks.chat" :href="telegramLinks.chat" target="_blank" class="flex items-center gap-4 p-4 bg-white border border-blue-100 rounded-xl hover:border-blue-300 hover:shadow-md transition group">
                                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 group-hover:text-blue-600 transition">Чат участников</p>
                                    <p class="text-xs text-gray-500">Общение и вопросы</p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- ВАРИАНТ 2: ЕЩЕ НЕ КУПЛЕНО -->
                    <div v-else class="space-y-6">
                        <!-- Цена -->
                        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-6 shadow-xl">
                            <p class="text-gray-300 text-sm mb-1">Стоимость обучения</p>
                            <p class="text-3xl font-bold text-white">{{ getDisplayPrice() }}</p>
                        </div>

                        <!-- Тарифы -->
                        <div v-if="course.tariffs && course.tariffs.length > 0" class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4">
                            <h3 class="font-bold text-white text-lg mb-4">Выберите тариф</h3>
                            <div class="space-y-3">
                                <div v-for="tariff in course.tariffs" :key="tariff.id" class="bg-gray-900/50 border border-indigo-500/30 rounded-lg p-4 hover:border-indigo-400 transition group cursor-pointer" @click="openBuyModal(tariff.id)">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="font-bold text-indigo-200 group-hover:text-white transition">{{ tariff.name }}</span>
                                        <span class="font-bold text-white">{{ formatPrice(tariff.price) }}</span>
                                    </div>
                                    <button class="w-full py-2 text-sm font-bold rounded bg-indigo-600 hover:bg-indigo-500 text-white transition">Выбрать</button>
                                </div>
                            </div>
                        </div>

                        <!-- Кнопка -->
                        <button v-else @click="openBuyModal(null)" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-4 px-6 rounded-xl transition shadow-lg shadow-indigo-900/50 flex items-center justify-center gap-2">
                            <span>{{ course.price === 0 ? 'Начать бесплатно' : 'Записаться на курс' }}</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-4 4m4-4H6"></path></svg>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- SYLLABUS (Программа) -->
        <div class="max-w-4xl mx-auto px-6 py-16">
            <h3 class="text-2xl font-bold text-gray-900 mb-8 text-center">Программа обучения</h3>
            <div class="space-y-4">
                <details v-for="(module, index) in syllabus" :key="module.id" class="group bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm open:ring-2 open:ring-indigo-100 transition-all" :open="index === 0">
                    <summary class="flex items-center justify-between px-6 py-4 cursor-pointer bg-gray-50 hover:bg-gray-100 transition select-none">
                        <div class="flex items-center gap-4 overflow-hidden">
                            <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-indigo-100 text-indigo-600 rounded-lg font-bold text-sm">{{ index + 1 }}</div>
                            <div class="flex flex-col">
                                <h4 class="font-bold text-gray-800">{{ module.title }}</h4>
                                <div v-if="module.tariffs && module.tariffs.length > 0" class="flex flex-wrap gap-1 mt-1">
                                    <span v-for="t in module.tariffs" :key="t.id" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-purple-100 text-purple-800 border border-purple-200">{{ t.name }}</span>
                                </div>
                                <p v-else class="text-xs text-gray-500 mt-0.5">{{ module.lessons.length + (module.children?.reduce((acc, child) => acc + child.lessons.length, 0) || 0) }} уроков</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </summary>
                    
                    <div class="border-t border-gray-100">
                        <div v-if="module.lessons.length > 0" class="divide-y divide-gray-100">
                            <div v-for="lesson in module.lessons" :key="lesson.id" class="px-6 py-3 flex items-center justify-between hover:bg-gray-50 transition group/lesson">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <svg class="w-4 h-4 text-gray-400 group-hover/lesson:text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div class="flex flex-col">
                                        <span class="text-sm text-gray-700 font-medium">{{ lesson.title }}</span>
                                        <div v-if="lesson.tariffs && lesson.tariffs.length > 0" class="flex flex-wrap gap-1 mt-0.5">
                                            <span v-for="t in lesson.tariffs" :key="t.id" class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium bg-blue-50 text-blue-700 border border-blue-100">{{ t.name }}</span>
                                        </div>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-400 flex-shrink-0 ml-2" v-if="lesson.duration_minutes > 0">{{ lesson.duration_minutes }} мин</span>
                            </div>
                        </div>

                        <div v-if="module.children && module.children.length > 0" class="bg-gray-50/50 px-6 py-4 space-y-3">
                            <div v-for="submodule in module.children" :key="submodule.id">
                                <div class="flex items-center gap-2 mb-2">
                                    <h5 class="text-xs font-bold uppercase tracking-wider text-gray-400 pl-2 border-l-2 border-indigo-200">{{ submodule.title }}</h5>
                                    <div v-if="submodule.tariffs && submodule.tariffs.length > 0" class="flex flex-wrap gap-1">
                                        <span v-for="t in submodule.tariffs" :key="t.id" class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium bg-purple-50 text-purple-700 border border-purple-100">{{ t.name }}</span>
                                    </div>
                                </div>
                                <div class="bg-white rounded-lg border border-gray-100 divide-y divide-gray-100">
                                    <div v-for="subLesson in submodule.lessons" :key="subLesson.id" class="px-4 py-3 flex items-center gap-3 hover:bg-gray-50 transition">
                                        <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <div class="flex flex-col">
                                            <span class="text-sm text-gray-600">{{ subLesson.title }}</span>
                                            <div v-if="subLesson.tariffs && subLesson.tariffs.length > 0" class="flex flex-wrap gap-1 mt-0.5">
                                                <span v-for="t in subLesson.tariffs" :key="t.id" class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium bg-blue-50 text-blue-700 border border-blue-100">{{ t.name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </details>
            </div>
        </div>

        <!-- MODAL WINDOW -->
        <div v-if="showModal" class="fixed inset-0 z-[60] flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showModal = false"></div>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-gray-800">Оформление заявки</h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <form @submit.prevent="submitOrder" class="p-6 space-y-4">
                    <div v-if="!user">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ваше Имя</label>
                        <input type="text" v-model="form.name" required class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Иван Иванов">
                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                    </div>
                    <div v-if="!user">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" v-model="form.email" required class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="ivan@example.com">
                        <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                    </div>
                    <div v-if="needPassword" class="bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                        <p class="text-sm text-indigo-800 mb-3 font-medium">Мы не нашли такого пользователя. Придумайте пароль для создания личного кабинета:</p>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Пароль</label>
                        <input type="password" v-model="form.password" required class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                        <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                    </div>
                    
                    <!-- CONSENTS -->
                    <div class="space-y-3 pt-2">
                        <div v-if="missingConsents.offer" class="flex items-start">
                            <input id="agree_offer" type="checkbox" v-model="form.agree_offer" required class="mt-1 w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <label for="agree_offer" class="ml-2 text-sm text-gray-600">Я принимаю условия <a href="/p/offer" target="_blank" class="text-indigo-600 hover:underline">Публичной оферты</a></label>
                        </div>
                        <div v-if="missingConsents.policy" class="flex items-start">
                            <input id="agree_policy" type="checkbox" v-model="form.agree_policy" required class="mt-1 w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <label for="agree_policy" class="ml-2 text-sm text-gray-600">Я согласен с <a href="/p/privacy" target="_blank" class="text-indigo-600 hover:underline">Политикой обработки персональных данных</a></label>
                        </div>
                        <div v-if="missingConsents.marketing" class="flex items-start">
                            <input id="agree_marketing" type="checkbox" v-model="form.agree_marketing" class="mt-1 w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <label for="agree_marketing" class="ml-2 text-sm text-gray-600">Я согласен на получение рекламных и информационных рассылок</label>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" :disabled="form.processing" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-200 disabled:opacity-50">
                            {{ form.processing ? 'Обработка...' : (needPassword ? 'Создать аккаунт и оформить' : 'Оформить заказ') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </LmsLayout>
</template>