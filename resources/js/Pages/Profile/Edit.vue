<script setup>
import LmsLayout from '@/Layouts/LmsLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Настройки профиля" />

    <LmsLayout>
        <div class="bg-indigo-900 text-white pt-12 pb-24 px-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-indigo-500 opacity-20 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-72 h-72 rounded-full bg-pink-500 opacity-20 blur-3xl"></div>

            <div class="max-w-4xl mx-auto relative z-10">
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-4">
                    Настройки профиля
                </h1>
                <p class="text-indigo-200 text-lg max-w-2xl">
                    Управляйте личными данными, безопасностью и настройками вашего аккаунта.
                </p>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-6 -mt-16 pb-20 relative z-20 space-y-8">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-xl">
                <div class="p-8">
                    <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Личные данные</h2>
                            <p class="text-sm text-gray-500">Имя, Email и фото профиля</p>
                        </div>
                    </div>
                    
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-xl">
                <div class="p-8">
                    <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Безопасность</h2>
                            <p class="text-sm text-gray-500">Обновление пароля</p>
                        </div>
                    </div>

                    <UpdatePasswordForm class="max-w-xl" />
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-red-100 overflow-hidden transition-all duration-300 hover:shadow-xl hover:border-red-200">
                <div class="p-8">
                    <div class="flex items-center gap-3 mb-6 border-b border-red-100 pb-4">
                        <div class="p-2 bg-red-50 rounded-lg text-red-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-red-600">Удаление аккаунта</h2>
                            <p class="text-sm text-gray-500">Безвозвратное удаление данных</p>
                        </div>
                    </div>

                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>

        </div>
    </LmsLayout>
</template>