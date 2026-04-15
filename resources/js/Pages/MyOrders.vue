<script setup>
import LmsLayout from '@/Layouts/LmsLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    orders: Array,
});

const formatPrice = (val) => {
    return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', minimumFractionDigits: 0 }).format(val);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
        day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

const getStatusColor = (status) => {
    switch(status) {
        case 'paid': return 'bg-green-100 text-green-800';
        case 'new': return 'bg-yellow-100 text-yellow-800';
        case 'processing': return 'bg-blue-100 text-blue-800';
        case 'cancelled': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getStatusLabel = (status) => {
    switch(status) {
        case 'paid': return 'Оплачен';
        case 'new': return 'Ожидает оплаты';
        case 'processing': return 'В обработке';
        case 'cancelled': return 'Отменен';
        case 'refund': return 'Возврат';
        default: return status;
    }
};
</script>

<template>
    <Head title="История заказов" />

    <LmsLayout>
        <!-- Hero -->
        <div class="bg-white border-b border-gray-200 px-8 py-8">
            <h1 class="text-2xl font-bold text-gray-900">История заказов</h1>
            <p class="text-gray-500 mt-1">Здесь отображаются все ваши покупки и счета.</p>
        </div>

        <div class="p-8 max-w-5xl">
            
            <div v-if="orders.length === 0" class="text-center py-12 bg-white rounded-xl border border-gray-200 border-dashed">
                <div class="text-4xl mb-2">🧾</div>
                <h3 class="text-lg font-medium text-gray-900">Список заказов пуст</h3>
                <p class="text-gray-500 mb-4">Вы еще ничего не заказывали.</p>
                <Link :href="route('courses.index')" class="text-indigo-600 font-bold hover:underline">Перейти в каталог</Link>
            </div>

            <div v-else class="space-y-4">
                <div v-for="order in orders" :key="order.id" class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-md transition flex flex-col md:flex-row md:items-center justify-between gap-4">
                    
                    <!-- Информация о заказе -->
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="font-mono text-xs text-gray-400">#{{ order.id }}</span>
                            <span class="text-xs font-medium px-2.5 py-0.5 rounded" :class="getStatusColor(order.status)">
                                {{ getStatusLabel(order.status) }}
                            </span>
                            <span class="text-xs text-gray-400">{{ formatDate(order.created_at) }}</span>
                        </div>
                        
                        <h3 class="font-bold text-lg text-gray-900">
                            {{ order.course?.title || 'Удаленный курс' }}
                        </h3>
                        
                        <p v-if="order.tariff" class="text-sm text-indigo-600 font-medium mt-1">
                            Тариф: {{ order.tariff.name }}
                        </p>

                        <p class="text-lg font-bold text-gray-900 mt-2">
                            {{ formatPrice(order.amount) }}
                        </p>
                    </div>

                    <!-- Действие -->
                    <div>
                        <!-- Если НЕ ОПЛАЧЕНО -> Кнопка Оплатить -->
                        <Link 
                            v-if="['new', 'processing'].includes(order.status)" 
                            :href="route('payment.checkout', order.id)" 
                            class="inline-flex justify-center items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Оплатить заказ
                        </Link>

                        <!-- Если ОПЛАЧЕНО -> Кнопка В курс -->
                        <Link 
                            v-else-if="order.status === 'paid' && order.course" 
                            :href="route('learning.lesson', order.course.slug)" 
                            class="inline-flex justify-center items-center px-6 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Перейти к обучению
                        </Link>
                    </div>

                </div>
            </div>

        </div>
    </LmsLayout>
</template>