<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { dashboard } from '@/routes';

// ── Types ──────────────────────────────────────────────────────────────────────
interface RecentOrder {
    order_id: number;
    user_name: string;
    user_email: string;
    status: 'PENDING' | 'SHIPPED' | 'DELIVERED' | 'CANCELLED';
    total: string;
    created_at: string;
}

interface LowStockItem {
    product_id: number;
    product_name: string;
    quantity: number;
}

// ── Props from Inertia ─────────────────────────────────────────────────────────
defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

const page           = usePage();
const orderBreakdown = computed(() => (page.props.orderStatusBreakdown as Record<string, number>) || {});
const recentOrders   = computed(() => (page.props.recentOrders as RecentOrder[]) || []);
const monthlyRevenue = computed(() => (page.props.monthlyRevenue as Record<string, number>) || {});
const lowStockItems  = computed(() => (page.props.lowStockItems as LowStockItem[]) || []);

// ── Helpers ───────────────────────────────────────────────────────────────────
const STATUS_STYLES: Record<string, string> = {
    PENDING:   'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    SHIPPED:   'bg-blue-100  text-blue-700  dark:bg-blue-900/40  dark:text-blue-300',
    DELIVERED: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
    CANCELLED: 'bg-red-100   text-red-700   dark:bg-red-900/40   dark:text-red-300',
};

function statusStyle(s: string) { return STATUS_STYLES[s] ?? ''; }

const totalOrderCount = computed(() =>
    Object.values(orderBreakdown.value).reduce((a, b) => a + b, 0) || 1
);

function barWidth(count: number) {
    return Math.round((count / totalOrderCount.value) * 100);
}

const chartMonths = computed(() => Object.keys(monthlyRevenue.value));
const chartValues = computed(() => Object.values(monthlyRevenue.value).map(Number));
const chartMax    = computed(() => Math.max(...chartValues.value, 1));

function chartY(val: number) {
    const height = 120;
    return height - Math.round((val / chartMax.value) * height);
}

const polylinePoints = computed(() => {
    const vals = chartValues.value;
    if (!vals.length) return '';
    const w = 560;
    const h = 120;
    const step = vals.length > 1 ? w / (vals.length - 1) : w / 2;
    return vals.map((v, i) => `${Math.round(i * step)},${chartY(v)}`).join(' ');
});

function shortMonth(ym: string) {
    const [y, m] = ym.split('-');
    const d = new Date(Number(y), Number(m) - 1, 1);
    return d.toLocaleString('default', { month: 'short' });
}

function dotClass(qty: number) {
    if (qty <= 0) return 'bg-red-500';
    if (qty < 10) return 'bg-red-400';
    return 'bg-amber-400';
}
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="flex flex-col gap-6 p-5 pb-10">

        <!-- ── Low-Stock Restock Alert ────────────────────────────────────── -->
        <div
            v-if="lowStockItems.length"
            class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-700/40 dark:bg-amber-900/20"
        >
            <div class="mb-3 flex items-center gap-2">
                <span class="text-lg">⚠️</span>
                <h2 class="text-sm font-bold text-amber-800 dark:text-amber-300">
                    Low Stock Alert — {{ lowStockItems.length }} product{{ lowStockItems.length > 1 ? 's' : '' }} need restocking
                </h2>
                <a
                    href="/inventory"
                    class="ml-auto rounded-lg bg-amber-500 px-3 py-1 text-xs font-semibold text-white transition-colors hover:bg-amber-600"
                >
                    Go to Inventory →
                </a>
            </div>
            <div class="flex flex-wrap gap-2">
                <div
                    v-for="item in lowStockItems"
                    :key="item.product_id"
                    class="flex items-center gap-2 rounded-lg border border-amber-200 bg-white px-3 py-1.5 dark:border-amber-700/30 dark:bg-amber-900/30"
                >
                    <span class="inline-block h-2 w-2 rounded-full" :class="dotClass(item.quantity)"></span>
                    <span class="text-sm font-medium text-amber-900 dark:text-amber-200">{{ item.product_name }}</span>
                    <span class="rounded-full bg-amber-100 px-1.5 py-0.5 text-xs font-bold text-amber-700 dark:bg-amber-800/50 dark:text-amber-300">
                        {{ item.quantity }} left
                    </span>
                </div>
            </div>
        </div>

        <!-- ── Recent Orders ──────────────────────────────────────────────── -->
        <div class="rounded-2xl border border-sidebar-border/70 bg-white shadow-sm dark:border-gray-700 dark:bg-[#161920]">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h2 class="text-base font-bold text-gray-800 dark:text-gray-100">Recent Orders</h2>
            </div>

            <div class="overflow-x-auto">
                <table v-if="recentOrders.length" class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Order</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Customer</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Status</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Total</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        <tr v-for="order in recentOrders" :key="order.order_id" class="transition-colors hover:bg-gray-50 dark:hover:bg-[#1e2028]">
                            <td class="px-5 py-3 font-mono text-xs font-semibold text-gray-600 dark:text-gray-300">#{{ order.order_id }}</td>
                            <td class="px-5 py-3">
                                <div class="font-medium text-gray-800 dark:text-gray-200">{{ order.user_name }}</div>
                                <div class="text-xs text-gray-400">{{ order.user_email }}</div>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusStyle(order.status)">
                                    {{ order.status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-gray-800 dark:text-gray-200">${{ order.total }}</td>
                            <td class="px-5 py-3 text-xs text-gray-400">{{ order.created_at }}</td>
                        </tr>
                    </tbody>
                </table>
                <div v-else class="flex h-32 items-center justify-center text-sm text-gray-400">
                    No orders yet.
                </div>
            </div>
        </div>

    </div>
</template>