<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    phone: '', // Добавили телефон (раз уж мы его добавили в базу)
    password: '',
    password_confirmation: '',
    // Галочки
    agree_policy: false,
    agree_marketing: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Регистрация" />

        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Создать аккаунт</h2>
            <p class="text-sm text-gray-500 mt-1">Присоединяйтесь к обучению прямо сейчас</p>
        </div>

        <form @submit.prevent="submit">
            <!-- Имя -->
            <div>
                <InputLabel for="name" value="Имя" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div class="mt-4">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>
            
            <!-- Телефон (Опционально, но полезно) -->
            <div class="mt-4">
                <InputLabel for="phone" value="Телефон (необязательно)" />
                <TextInput
                    id="phone"
                    type="tel"
                    class="mt-1 block w-full"
                    v-model="form.phone"
                    autocomplete="tel"
                />
                <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <!-- Пароль -->
            <div class="mt-4">
                <InputLabel for="password" value="Пароль" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Повторите пароль" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>
            
            <!-- ГАЛОЧКИ СОГЛАСИЯ -->
            <div class="mt-6 space-y-3">
                <!-- Политика (Обязательно) -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input 
                            id="agree_policy" 
                            type="checkbox" 
                            v-model="form.agree_policy" 
                            required
                            class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-indigo-300"
                        >
                    </div>
                    <label for="agree_policy" class="ms-2 text-sm font-medium text-gray-900">
                        Я согласен с <a href="/p/privacy" target="_blank" class="text-indigo-600 hover:underline">Политикой конфиденциальности</a>
                    </label>
                </div>
                <InputError class="mt-1" :message="form.errors.agree_policy" />

                <!-- Маркетинг (Необязательно) -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input 
                            id="agree_marketing" 
                            type="checkbox" 
                            v-model="form.agree_marketing" 
                            class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-indigo-300"
                        >
                    </div>
                    <label for="agree_marketing" class="ms-2 text-sm font-medium text-gray-500">
                        Я хочу получать новости о курсах и акциях (не чаще раза в неделю)
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end mt-6">
                <PrimaryButton 
                    class="w-full justify-center py-3 text-base" 
                    :class="{ 'opacity-25': form.processing }" 
                    :disabled="form.processing"
                >
                    Зарегистрироваться
                </PrimaryButton>
            </div>

            <div class="mt-6 text-center text-sm text-gray-600">
                Уже есть аккаунт?
                <Link :href="route('login')" class="font-bold text-indigo-600 hover:underline">
                    Войти
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>