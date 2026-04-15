<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    block: Object,
    result: Object, // Результат из БД (только для чтения)
});

// 1. Локальное состояние результата (чтобы мы могли его сбрасывать)
const localResult = ref(props.result);

// 2. Если с сервера придут новые данные (например, после отправки формы), обновляем локальное состояние
watch(() => props.result, (newVal) => {
    localResult.value = newVal;
});

const passed = computed(() => localResult.value?.is_passed);
const score = computed(() => localResult.value?.score_percent);

const form = useForm({
    answers: {},
});

const submit = () => {
    form.post(route('learning.test.check', props.block.id), {
        preserveScroll: true,
        onSuccess: () => {
            // При успехе Inertia обновит props.result, 
            // сработает watch выше, и localResult обновится сам.
        }
    });
};

// Логика пересдачи
const retake = () => {
    localResult.value = null; // Скрываем результат
    form.reset(); // Очищаем выбранные радио-кнопки
    form.clearErrors();
};
</script>

<template>
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm my-8">
        <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
            <span class="bg-indigo-100 text-indigo-700 p-1 rounded text-xs font-bold uppercase">Тест</span>
            Проверка знаний
        </h3>

        <!-- 1. ПОКАЗЫВАЕМ РЕЗУЛЬТАТ (используем localResult) -->
        <div v-if="localResult" class="mb-6 p-4 rounded-lg border" :class="passed ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-bold text-lg" :class="passed ? 'text-green-800' : 'text-red-800'">
                        {{ passed ? 'Тест сдан!' : 'Тест не сдан' }}
                    </p>
                    <p class="text-sm opacity-80">Ваш результат: {{ score }}%</p>
                </div>
                
                <!-- Иконка статуса -->
                <div v-if="passed" class="bg-white p-2 rounded-full shadow-sm">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div v-else class="bg-white p-2 rounded-full shadow-sm">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </div>
            </div>
            
            <!-- Кнопка пересдачи -->
            <button 
                v-if="!passed" 
                @click="retake" 
                class="mt-4 px-4 py-2 bg-white border border-red-200 text-red-700 text-sm font-bold rounded hover:bg-red-50 transition"
            >
                Попробовать снова
            </button>
        </div>

        <!-- 2. ФОРМА ТЕСТА (показываем, если нет результата) -->
        <form v-else @submit.prevent="submit" class="space-y-6">
            <div v-for="(q, qIndex) in block.content.questions" :key="qIndex">
                <p class="font-medium text-gray-900 mb-3">{{ qIndex + 1 }}. {{ q.question }}</p>
                
                <div class="space-y-2">
                    <div 
                        v-for="(ans, aIndex) in q.answers" 
                        :key="aIndex"
                        class="flex items-center"
                    >
                        <input 
                            type="radio" 
                            :name="'q_' + block.id + '_' + qIndex"
                            :id="'ans_' + block.id + '_' + qIndex + '_' + aIndex"
                            :value="aIndex"
                            v-model="form.answers[qIndex]"
                            class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 cursor-pointer"
                        >
                        <label 
                            :for="'ans_' + block.id + '_' + qIndex + '_' + aIndex"
                            class="ml-3 text-sm text-gray-700 cursor-pointer select-none w-full py-1"
                        >
                            {{ ans.text }}
                        </label>
                    </div>
                </div>
            </div>
            <div class="text-sm text-gray-500 bg-gray-50 p-3 rounded-lg border border-gray-100">
                ℹ️ Для успешной сдачи необходимо набрать минимум 
                <span class="font-bold text-gray-800">{{ block.content.min_score || 70 }}%</span>.
            </div>    
            <div class="pt-4 border-t border-gray-100">
                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-indigo-700 transition disabled:opacity-50 shadow-md shadow-indigo-200"
                >
                    {{ form.processing ? 'Проверка...' : 'Отправить ответы' }}
                </button>
            </div>
        </form>
    </div>
</template>