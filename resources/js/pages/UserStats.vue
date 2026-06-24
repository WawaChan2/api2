<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { stats } from '@/routes';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface OrderItem {
    product_id: number;
    quantity: number;
    unit_price: number;
    product_name: string;
}

interface Order {
    order_id: number;
    status: string;
    created_at: string;
    total_amount: string;
    items: OrderItem[];
}

interface Stats {
    totalOrders: number;
    activeOrders: number;
    totalSpent: string;
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Stats',
                href: stats(),
            },
        ],
    },
});

const page = usePage();
const statsData = computed(() => (page.props.stats as Stats) || { totalOrders: 0, activeOrders: 0, totalSpent: '$0.00' });
const purchaseHistory = computed(() => (page.props.purchaseHistory as Order[]) || []);
const user = computed(() => page.props.auth?.user || {});
</script>

<template>
    <Head title="User Stats" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
        <!-- User Profile Section -->
        <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-gray-700 dark:bg-[#161920]">
            <div class="mb-6">
                <h2 class="text-lg font-semibold">User Profile</h2>
            </div>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Email</p>
                    <p class="font-medium">{{ user.email }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Name</p>
                    <p class="font-medium">{{ user.name }}</p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid gap-4 md:grid-cols-3">
            <!-- Total Orders -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-gray-700 dark:bg-[#161920]">
                <p class="text-sm text-gray-600 dark:text-gray-400">📦 Total Orders</p>
                <p class="mt-2 text-3xl font-bold">{{ statsData.totalOrders || 0 }}</p>
            </div>

            <!-- Active Orders -->
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-6 dark:border-blue-900 dark:bg-blue-950">
                <p class="text-sm text-blue-600 dark:text-blue-400 flex items-center gap-2">
                    <span class="icon font-bold">✓</span> Active Orders
                </p>
                <p class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-300">{{ statsData.activeOrders || 0 }}</p>
            </div>

            <!-- Total Spent -->
            <div class="rounded-xl border border-green-200 bg-green-50 p-6 dark:border-green-900 dark:bg-green-950">
                <p class="text-sm text-green-600 dark:text-green-400">💰 Total Spent</p>
                <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-300">{{ statsData.totalSpent || '$0.00' }}</p>
            </div>
        </div>

        <!-- Purchase History -->
        <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-gray-700 dark:bg-[#161920]">
            <h3 class="mb-4 text-lg font-semibold">Purchase History</h3>
            <div v-if="purchaseHistory.length === 0" class="empty-state">
                <div class="empty-icon">📦</div>
                <p>No orders yet</p>
            </div>
            <div v-else class="space-y-4">
                <div v-for="order in purchaseHistory" :key="order.order_id" class="border border-gray-200 rounded-lg p-4 dark:border-gray-700 dark:bg-[#23262e]">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="font-semibold">Order #{{ order.order_id }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ order.created_at }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold">{{ order.total_amount }}</p>
                            <span :class="[
                                'inline-block px-2 py-1 text-xs font-semibold rounded',
                                order.status === 'DELIVERED' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' :
                                order.status === 'SHIPPED' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' :
                                order.status === 'PENDING' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' :
                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                            ]">
                                {{ order.status }}
                            </span>
                        </div>
                    </div>
                    <div v-if="order.items.length > 0" class="text-sm text-gray-600 dark:text-gray-400">
                        <p>Items: {{ order.items.length }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
