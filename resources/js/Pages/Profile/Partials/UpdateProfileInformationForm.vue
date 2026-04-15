<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;
const photoInput = ref(null);
const photoPreview = ref(null);

const form = useForm({
    _method: 'PATCH', // Хитрость для отправки файлов
    name: user.name,
    email: user.email,
    avatar: null, // Поле для файла
});

// Логика предпросмотра фото при выборе файла
const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (!photo) return;

    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(photo);
    
    // Кладем файл в форму
    form.avatar = photo;
};

// Клик по кнопке вызывает клик по скрытому инпуту
const selectNewPhoto = () => {
    photoInput.value.click();
};

const submit = () => {
    // Используем POST с forceFormData, так как шлем файл
    form.post(route('profile.update'), {
        forceFormData: true, 
        preserveScroll: true,
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Информация профиля</h2>
            <p class="mt-1 text-sm text-gray-600">
                Обновите данные вашего аккаунта и фото профиля.
            </p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
            
            <div class="col-span-6 sm:col-span-4">
                <input
                    ref="photoInput"
                    type="file"
                    class="hidden"
                    @change="updatePhotoPreview"
                />

                <InputLabel for="photo" value="Фото" />

                <div class="mt-2 flex items-center gap-4">
                    <div v-show="!photoPreview" class="relative">
                        <img
                            :src="user.avatar_url ? '/storage/' + user.avatar_url : 'https://ui-avatars.com/api/?name=' + user.name + '&color=7F9CF5&background=EBF4FF'"
                            :alt="user.name"
                            class="rounded-full h-20 w-20 object-cover border-2 border-gray-200"
                        />
                    </div>

                    <div v-show="photoPreview" class="relative">
                        <span
                            class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center border-2 border-indigo-200"
                            :style="'background-image: url(\'' + photoPreview + '\');'"
                        />
                    </div>

                    <button
                        type="button"
                        class="px-4 py-2 bg-white text-indigo-700 border border-indigo-200 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-50 hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shadow-sm transition ease-in-out duration-150"
                        @click.prevent="selectNewPhoto"
                    >
                        Изменить фото
                    </button>
                </div>
            </div>

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

            <div>
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

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm mt-2 text-gray-800">
                    Ваш email не подтвержден.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Нажмите здесь для отправки письма.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 font-medium text-sm text-green-600"
                >
                    Новая ссылка отправлена на вашу почту.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Сохранить</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Сохранено.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>