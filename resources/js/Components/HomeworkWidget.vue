<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    homework: Object,
    submission: Object,
});

// Инициализируем форму
// Важно: для чекбоксов нужно сразу создать пустой массив [], иначе v-model не сработает
const initialFields = {};

if (props.homework.submission_fields) {
    props.homework.submission_fields.forEach(field => {
        if (field.type === 'checkboxes') {
            initialFields[field.label] = []; // Массив для множественного выбора
        } else {
            initialFields[field.label] = ''; // Строка для всего остального
        }
    });
}

const form = useForm({
    fields: initialFields
});

const submit = () => {
    form.post(route('homework.submit', props.homework.id), {
        preserveScroll: true,
        forceFormData: true, // Обязательно для файлов
        onSuccess: () => {
            form.reset();
        }
    });
};

const getStatusColor = (status) => {
    switch(status) {
        case 'approved': return 'bg-green-100 text-green-700 border-green-200';
        case 'rejected': return 'bg-red-100 text-red-700 border-red-200';
        case 'revision': return 'bg-orange-100 text-orange-700 border-orange-200';
        default: return 'bg-blue-50 text-blue-700 border-blue-200';
    }
};

const getStatusLabel = (status) => {
    switch(status) {
        case 'approved': return 'Задание принято';
        case 'rejected': return 'Не принято';
        case 'revision': return 'Нужна доработка';
        default: return 'На проверке';
    }
};

// Хелпер для красивого вывода ответов (массивов) в блоке "Уже сдано"
const formatAnswer = (val) => {
    if (Array.isArray(val)) return val.join(', '); // Для чекбоксов
    if (typeof val === 'object' && val !== null && val.type === 'file') return `Файл: ${val.original_name}`;
    return val;
};
</script>

<template>
    <div class="mt-12 border-t border-gray-200 pt-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            Домашнее задание
        </h2>

        <div class="prose prose-sm text-gray-600 mb-8 bg-gray-50 p-6 rounded-xl border border-gray-100" v-html="homework.description"></div>

        <!-- 1. ЕСЛИ УЖЕ СДАНО -->
        <div v-if="submission" class="rounded-xl border p-6 mb-6" :class="getStatusColor(submission.status)">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="font-bold text-lg mb-1">{{ getStatusLabel(submission.status) }}</h3>
                    <p class="text-sm opacity-80 mb-4">
                        Сдано: {{ new Date(submission.created_at).toLocaleDateString() }}
                    </p>
                    
                    <!-- Показываем ответы пользователя -->
                    <div class="space-y-2 text-sm bg-white/50 p-3 rounded-lg">
                        <div v-for="(val, key) in submission.content" :key="key">
                            <span class="font-bold">{{ key }}:</span> {{ formatAnswer(val) }}
                        </div>
                    </div>

                    <div v-if="submission.grade_percent" class="mt-3 inline-flex items-center px-3 py-1 bg-white/50 rounded-full text-sm font-bold">
                        Оценка: {{ submission.grade_percent }}%
                    </div>
                </div>
                
                <div v-if="submission.status === 'approved'" class="bg-white rounded-full p-2">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
            </div>

            <div v-if="submission.curator_comment" class="mt-4 pt-4 border-t border-black/10">
                <p class="text-xs font-bold uppercase tracking-wide opacity-60 mb-1">Комментарий преподавателя:</p>
                <p class="italic">{{ submission.curator_comment }}</p>
            </div>

            <div v-if="['rejected', 'revision'].includes(submission.status)" class="mt-6">
                <button @click="submission = null" class="text-sm font-bold underline hover:text-gray-900">
                    Отправить новый ответ
                </button>
            </div>
        </div>

        <!-- 2. ФОРМА ОТПРАВКИ -->
        <form v-else @submit.prevent="submit" class="space-y-6 bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
            
            <div v-for="(field, index) in homework.submission_fields" :key="index">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ field.label }} 
                    <span v-if="field.required" class="text-red-500">*</span>
                </label>

                <!-- TEXTAREA -->
                <div v-if="field.type === 'text'">
                    <textarea 
                        v-model="form.fields[field.label]" 
                        rows="4"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                        :placeholder="'Введите ваш ответ...'"
                        :required="field.required"
                    ></textarea>
                </div>

                <!-- INPUT -->
                <div v-else-if="['string', 'url'].includes(field.type)">
                    <input 
                        type="text" 
                        v-model="form.fields[field.label]"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                        :required="field.required"
                    >
                </div>

                <!-- CHECKBOXES (Новое) -->
                <div v-else-if="field.type === 'checkboxes'" class="space-y-2">
                    <div v-for="option in field.options" :key="option" class="flex items-center">
                        <input 
                            type="checkbox" 
                            :value="option"
                            v-model="form.fields[field.label]"
                            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer"
                        >
                        <span class="ml-2 text-sm text-gray-600">{{ option }}</span>
                    </div>
                </div>
                
                <!-- SELECT (Новое) -->
                <div v-else-if="field.type === 'select'">
                    <select 
                        v-model="form.fields[field.label]"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                        :required="field.required"
                    >
                        <option value="" disabled>Выберите вариант</option>
                        <option v-for="option in field.options" :key="option" :value="option">{{ option }}</option>
                    </select>
                </div>

                <!-- FILE -->
                <div v-else-if="field.type === 'file'">
                    <input 
                        type="file" 
                        @input="form.fields[field.label] = $event.target.files[0]"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        :required="field.required"
                    >
                </div>

                <!-- Ошибки валидации -->
                <div v-if="form.errors[`fields.${field.label}`]" class="text-red-500 text-xs mt-1">
                    {{ form.errors[`fields.${field.label}`] }}
                </div>
            </div>

            <div class="pt-4">
                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition disabled:opacity-50"
                >
                    <span v-if="form.processing">Отправка...</span>
                    <span v-else>Отправить на проверку</span>
                </button>
            </div>
        </form>
    </div>
</template>