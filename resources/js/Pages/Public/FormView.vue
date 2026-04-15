<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    form: Object,
});

// Используем computed для безопасного доступа
const user = computed(() => usePage().props.auth?.user);

const formData = {};
props.form.schema.forEach(field => {
    if (user.value) {
        if (field.type === 'email') formData[field.name] = user.value.email;
        else if (field.type === 'phone') formData[field.name] = user.value.phone;
        else formData[field.name] = '';
    } else {
        formData[field.name] = '';
    }
});

const formState = useForm(formData);

const submit = () => {
    formState.post(route('public.form.submit', props.form.id), {
        preserveScroll: true,
        onSuccess: () => formState.reset(),
    });
};
</script>

<template>
    <Head :title="form.title" />

    <PublicLayout>
        <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
            <div class="max-w-2xl w-full space-y-8 bg-white p-10 rounded-2xl shadow-xl border border-gray-100">
                
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-gray-900">{{ form.title }}</h2>
                    <p v-if="user" class="mt-2 text-sm text-green-600">
                        Вы вошли как {{ user.name }}. Ваши данные будут привязаны к аккаунту.
                    </p>
                </div>

                <!-- ИСПРАВЛЕНИЕ: Добавили знак вопроса (?.) -->
                <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-lg text-center font-bold">
                    {{ $page.props.flash.success }}
                </div>

                <form v-else @submit.prevent="submit" class="space-y-6 mt-8">
                    
                    <div v-for="(field, index) in form.schema" :key="index">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ field.label }}
                            <span v-if="field.required" class="text-red-500">*</span>
                        </label>

                        <div v-if="['text', 'email', 'phone'].includes(field.type)">
                            <input 
                                :type="field.type === 'phone' ? 'tel' : 'text'" 
                                v-model="formState[field.name]"
                                class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                                :placeholder="field.type === 'phone' ? '+7 (999) 000-00-00' : ''"
                                :required="field.required"
                                :disabled="user && field.type === 'email'" 
                            >
                        </div>

                        <div v-else-if="field.type === 'textarea'">
                            <textarea 
                                v-model="formState[field.name]"
                                rows="3"
                                class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                                :required="field.required"
                            ></textarea>
                        </div>

                        <div v-else-if="field.type === 'select'">
                            <select 
                                v-model="formState[field.name]"
                                class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                                :required="field.required"
                            >
                                <option value="" disabled>Выберите вариант</option>
                                <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                        </div>

                        <div v-if="formState.errors[field.name]" class="text-red-500 text-xs mt-1">
                            {{ formState.errors[field.name] }}
                        </div>
                    </div>

                    <div class="pt-4">
                        <button 
                            type="submit" 
                            :disabled="formState.processing"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition disabled:opacity-50"
                        >
                            {{ form.settings?.submit_text || 'Отправить' }}
                        </button>
                    </div>

                    <p class="text-xs text-center text-gray-400">
                        Нажимая кнопку, вы соглашаетесь с <a href="/p/privacy" class="underline hover:text-gray-600" target="_blank">Политикой конфиденциальности</a>.
                    </p>
                </form>

            </div>
        </div>
    </PublicLayout>
</template>