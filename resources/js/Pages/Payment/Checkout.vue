<script setup>
import LmsLayout from '@/Layouts/LmsLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
    methods: Object,
});

const form = useForm({
    // Выбираем первый доступный метод по умолчанию
    method: props.methods.yookassa ? 'yookassa' : (props.methods.yoomoney_p2p ? 'yoomoney_p2p' : ''),
});

const submit = () => {
    form.post(route('payment.pay', props.order.id));
};

const formatPrice = (val) => {
    return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', minimumFractionDigits: 0 }).format(val);
};
</script>

<template>
    <Head title="Оплата заказа" />

    <LmsLayout>
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12 -mt-16">
            
            <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                
                <div class="bg-gray-50 px-6 py-8 text-center border-b border-gray-100">
                    <h2 class="text-gray-500 text-sm font-medium uppercase tracking-wider mb-2">Сумма к оплате</h2>
                    <p class="text-4xl font-extrabold text-gray-900">{{ formatPrice(order.amount) }}</p>
                    <p class="mt-2 text-gray-600 text-sm">Заказ #{{ order.id }} • {{ order.course.title }}</p>
                    <div v-if="order.tariff" class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        Тариф: {{ order.tariff.name }}
                    </div>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Выберите способ оплаты</label>
                        
                        <div class="space-y-3">
                            <!-- ЮKassa -->
                            <div v-if="methods.yookassa" 
                                class="relative flex items-center p-4 border rounded-xl cursor-pointer transition-all group"
                                :class="form.method === 'yookassa' ? 'border-indigo-600 ring-1 ring-indigo-600 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'"
                                @click="form.method = 'yookassa'"
                            >
                                <input type="radio" name="method" value="yookassa" v-model="form.method" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                <div class="ml-3 flex-1">
                                    <span class="block text-sm font-medium text-gray-900">ЮKassa (Карты РФ)</span>
                                    <span class="block text-xs text-gray-500">Официальный эквайринг</span>
                                </div>
                            </div>

                            <!-- ЮMoney P2P -->
                            <div v-if="methods.yoomoney_p2p" 
                                class="relative flex items-center p-4 border rounded-xl cursor-pointer transition-all group"
                                :class="form.method === 'yoomoney_p2p' ? 'border-purple-600 ring-1 ring-purple-600 bg-purple-50' : 'border-gray-200 hover:border-gray-300'"
                                @click="form.method = 'yoomoney_p2p'"
                            >
                                <input type="radio" name="method" value="yoomoney_p2p" v-model="form.method" class="h-4 w-4 text-purple-600 border-gray-300 focus:ring-purple-500">
                                <div class="ml-3 flex-1">
                                    <span class="block text-sm font-medium text-gray-900">ЮMoney (P2P)</span>
                                    <span class="block text-xs text-gray-500">Перевод на кошелек / карту</span>
                                </div>
                                <div class="w-6 h-6 bg-purple-600 text-white rounded-full flex items-center justify-center text-xs font-bold">Ю</div>
                            </div>

                            <div v-if="!methods.yookassa && !methods.yoomoney_p2p" class="text-center text-gray-500 text-sm py-4">
                                Нет доступных методов оплаты.
                            </div>
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="form.processing || (!methods.yookassa && !methods.yoomoney_p2p)"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition disabled:opacity-50"
                    >
                        <span v-if="form.processing">Переход к оплате...</span>
                        <span v-else>Оплатить {{ formatPrice(order.amount) }}</span>
                    </button>
                </form>
            </div>
            
            <div class="mt-6">
                <a :href="route('courses.show', order.course.slug)" class="text-sm text-gray-500 hover:text-gray-900">← Вернуться к описанию курса</a>
            </div>
        </div>
    </LmsLayout>
</template>