<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { orders as ordersRoute } from '@/routes';

interface OrderItem{
    product_id: number;
    product_name: string;
    quantity: number;
    unit_price: number;
}

interface Order{
    order_id:number;
    status: string;
    user_email:string;
    created_at: string,
    total_amount: number;
    items: OrderItem[];
}

const props = defineProps<{
    orders: Order[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Orders', href: ordersRoute() },
        ],
    },
});

const selectedOrder = ref<Order | null>(null);
const openOrderDetails = (order: Order) => {
    selectedOrder.value = order;
};
const closeOrderDetails = () => {
    selectedOrder.value = null;
};

const searchQuery = ref('');
const statusFilter = ref('');
const startDate = ref('');
const endDate = ref('');

const resetFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
    startDate.value = '';
    endDate.value = '';
};

const filteredOrders = computed(() => {
    return props.orders.filter(order => {

        if (searchQuery.value && !order.order_id.toString().includes(searchQuery.value)) {
            return false;
        }

        if (statusFilter.value && order.status !== statusFilter.value) {
            return false;
        }

        if (startDate.value || endDate.value) {
            const orderDate = new Date(order.created_at).getTime();

            if (startDate.value) {
                const start = new Date(startDate.value).getTime();

                if (orderDate < start){
                    return false;
                }
            }

            if (endDate.value) {
                const end = new Date(endDate.value);
                end.setHours(23, 59, 59, 999);

                if (orderDate > end.getTime()) {
                    return false;
                }
            }
        }

        return true;

});
});

const formatDateTime = (dateStr: string) => {
    const d = new Date(dateStr);

    return `${d.toLocaleDateString('en-US')} ${d.toLocaleTimeString('en-US')}`;
};
const formatMoney = (amount: number | string) => Number(amount).toLocaleString('en-MY', { minimumFractionDigits: 2 });

</script>

<template>
    <Head title="Order History" />

    <div class="flex h-full flex-1 flex-col p-4">

        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-1 mb-2">Order History</h1>

        <div class="bg-gray-200 dark:bg-[#202024] p-3 rounded-xl flex flex-wrap items-center gap-3 mb-4 mt-2">

            <input
                v-model="searchQuery"
                type="text"
                placeholder="Search by Order ID"
                class="px-4 py-2 rounded-lg border-none focus:ring-2 focus:ring-blue-500 text-sm bg-white dark:bg-[#18181b] dark:text-white w-48 shadow-sm"
            />

            <select
                v-model="statusFilter"
                class="px-4 py-2 rounded-lg border-none focus:ring-2 focus:ring-blue-500 text-sm bg-white dark:bg-[#18181b] dark:text-white min-w-[140px] shadow-sm cursor-pointer"
            >
                <option value="">All Status</option>
                <option value="PENDING">Pending</option>
                <option value="SHIPPED">Shipped</option>
                <option value="DELIVERED">Delivered</option>
                <option value="CANCELLED">Cancelled</option>
            </select>

            <input
                v-model="startDate"
                type="date"
                class="px-4 py-2 rounded-lg border-none focus:ring-2 focus:ring-blue-500 text-sm bg-white dark:bg-[#18181b] dark:text-white shadow-sm cursor-pointer"
            />

            <input
                v-model="endDate"
                type="date"
                class="px-4 py-2 rounded-lg border-none focus:ring-2 focus:ring-blue-500 text-sm bg-white dark:bg-[#18181b] dark:text-white shadow-sm cursor-pointer"
            />

            <button
                @click="resetFilters"
                class="px-5 py-2 bg-purple-600 hover:bg-indigo-600 text-white rounded-lg text-sm font-medium transition-colors shadow-sm ml-auto"
            >
                Refresh
            </button>

        </div>

        <div class="text-sm text-gray-500 mb-4">
            Showing {{ filteredOrders.length }} of {{ orders.length }} orders
        </div>

        <div v-if="filteredOrders && filteredOrders.length > 0" class="grid auto-rows-min gap-6 md:grid-cols-2 lg:grid-cols-3">

            <div
                v-for="order in filteredOrders"
                :key="order.order_id"
                @click="openOrderDetails(order)"
                class="rounded-xl border border-gray-200 bg-white shadow-sm p-5 dark:border-gray-700 dark:bg-[#18181b] cursor-pointer hover:border-blue-500 hover:ring-1 hover:ring-blue-500 transition-all flex flex-col justify-between"
            >
                <div>
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-1">Order #{{ order.order_id }}</h3>
                        <span class="px-2 py-1 text-[10px] font-bold rounded-full uppercase tracking-wider"
                              :class="{
                                  'bg-orange-100 text-orange-600': order.status === 'PENDING',
                                  'bg-blue-100 text-blue-600': order.status === 'SHIPPED',
                                  'bg-green-100 text-green-600': order.status === 'DELIVERED',
                                  'bg-gray-100 text-gray-600': order.status === 'CANCELLED'
                              }">
                            {{ order.status }}
                        </span>
                    </div>

                    <div class="mb-2">
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ order.user_email }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ formatDateTime(order.created_at) }}</p>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center">
                    <div class="text-gray-800 dark:text-gray-200 text-sm">
                        Total: <span class="font-bold text-green-600 text-base">RM {{ formatMoney(order.total_amount) }}</span>
                    </div>
                    <span class="text-blue-500 text-xs font-medium hover:underline">View Details</span>
                </div>
            </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center p-12 bg-white dark:bg-[#18181b] rounded-xl border border-gray-200 dark:border-gray-800 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">No orders found</h3>
            <p class="text-gray-500 text-sm mt-1">Try adjusting your filters or search query.</p>
        </div>

    </div>

    <div v-if="selectedOrder"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         @click="closeOrderDetails">

        <div @click.stop class="bg-white dark:bg-[#18181b] rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh] border border-gray-200 dark:border-gray-700">

            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50 dark:bg-[#202024]">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Order #{{ selectedOrder.order_id }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ formatDateTime(selectedOrder.created_at) }}</p>
                </div>
                <button @click="closeOrderDetails" class="p-1.5 text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="p-5 overflow-y-auto">
                <div class="mb-5">
                    <h4 class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Customer</h4>
                    <p class="text-sm text-gray-800 dark:text-gray-200">{{ selectedOrder.user_email }}</p>
                </div>

                <h4 class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Order Items</h4>
                <div class="bg-gray-50 dark:bg-[#202024] rounded-lg p-3 space-y-2.5 border border-gray-100 dark:border-gray-800">

                    <div v-for="item in selectedOrder.items" :key="item.product_id" class="flex justify-between items-center text-sm">
                        <div>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ item.product_name }}</span>
                            <span class="text-gray-400 text-xs ml-1.5">× {{ item.quantity }}</span>
                        </div>
                        <div class="text-gray-500 text-xs">
                            RM {{ formatMoney(item.unit_price) }}
                        </div>
                    </div>

                </div>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50 dark:bg-[#202024]">
                <div>
                    <span class="text-gray-500 text-xs block mb-0.5">Total Amount</span>
                    <p class="font-bold text-green-600 text-xl">RM {{ formatMoney(selectedOrder.total_amount) }}</p>
                </div>

                <button
                    v-if="selectedOrder.status === 'PENDING'"
                    class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold py-2 px-4 rounded transition-colors shadow-sm"
                >
                    Cancel Order
                </button>
                <span v-else class="px-3 py-1.5 bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded text-xs font-medium">
                    {{ selectedOrder.status }}
                </span>
            </div>

        </div>
    </div>
</template>
