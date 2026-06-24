<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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

interface Movement {
    product_name: string;
    transaction_type: string;
    quantity_delta: number;
    prev_quantity: number | null;
    new_quantity: number | null;
    transaction_id: number;
    created_at: string;
}

// ── Props from Inertia ─────────────────────────────────────────────────────────
defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

const page = usePage();
const orderBreakdown   = computed(() => (page.props.orderStatusBreakdown as Record<string, number>) || {});
const recentOrders     = computed(() => (page.props.recentOrders as RecentOrder[]) || []);
const movements        = computed(() => (page.props.recentMovements as Movement[]) || []);
const monthlyRevenue   = computed(() => (page.props.monthlyRevenue as Record<string, number>) || {});

// ── Tab state ─────────────────────────────────────────────────────────────────
const activeTab = ref<'orders' | 'inventory'>('orders');

// ── Helpers ───────────────────────────────────────────────────────────────────
const STATUS_STYLES: Record<string, string> = {
    PENDING:   'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    SHIPPED:   'bg-blue-100  text-blue-700  dark:bg-blue-900/40  dark:text-blue-300',
    DELIVERED: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
    CANCELLED: 'bg-red-100   text-red-700   dark:bg-red-900/40   dark:text-red-300',
};

const MOVEMENT_STYLES: Record<string, string> = {
    ORDER:             'bg-red-100   text-red-700   dark:bg-red-900/40   dark:text-red-300',
    ORDER_CANCELLATION:'bg-blue-100  text-blue-700  dark:bg-blue-900/40  dark:text-blue-300',
    GOODS_RECEIPT:     'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
    ADJUSTMENT:        'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
};

const MOVEMENT_LABELS: Record<string, string> = {
    ORDER:             'sale',
    ORDER_CANCELLATION:'cancellation',
    GOODS_RECEIPT:     'receipt',
    ADJUSTMENT:        'adjustment',
};

function movementLabel(type: string) { return MOVEMENT_LABELS[type] ?? type.toLowerCase(); }
function movementStyle(type: string) { return MOVEMENT_STYLES[type] ?? ''; }
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
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="flex flex-col gap-6 p-5 pb-10">

        <!-- ── Tabbed Lower Section ────────────────────────────────────────── -->
        <div class="rounded-2xl border border-sidebar-border/70 bg-white shadow-sm dark:border-gray-700 dark:bg-[#161920]">
            <!-- Tab headers -->
            <div class="flex border-b border-gray-100 dark:border-gray-800">
                <button
                    v-for="tab in [{ key: 'orders', label: 'Recent Orders' }, { key: 'inventory', label: 'Inventory Movements' }]"
                    :key="tab.key"
                    @click="activeTab = tab.key as 'orders' | 'inventory'"
                    class="px-6 py-3.5 text-sm font-medium transition-colors"
                    :class="activeTab === tab.key
                        ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400'
                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                >
                    {{ tab.label }}
                </button>
            </div>

            <!-- Recent Orders -->
            <div v-if="activeTab === 'orders'" class="overflow-x-auto">
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
                        <tr v-for="order in recentOrders" :key="order.order_id" class="hover:bg-gray-50 dark:hover:bg-[#1e2028] transition-colors">
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

            <!-- Inventory Movements -->
            <div v-if="activeTab === 'inventory'" class="overflow-x-auto">
                <table v-if="movements.length" class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Date/Time</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Product</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Type</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Change</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Previous</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">New</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Ref</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        <tr v-for="(m, i) in movements" :key="i" class="hover:bg-gray-50 dark:hover:bg-[#1e2028] transition-colors">
                            <td class="px-5 py-3 text-xs text-gray-400">{{ m.created_at }}</td>
                            <td class="px-5 py-3 font-medium text-gray-800 dark:text-gray-200">{{ m.product_name }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="movementStyle(m.transaction_type)">
                                    {{ movementLabel(m.transaction_type) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-mono font-semibold"
                                :class="m.quantity_delta >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400'"
                            >
                                {{ m.quantity_delta >= 0 ? '+' : '' }}{{ m.quantity_delta }}
                            </td>
                            <td class="px-5 py-3 text-right text-gray-500">{{ m.prev_quantity ?? '-' }}</td>
                            <td class="px-5 py-3 text-right font-bold text-gray-800 dark:text-gray-200">{{ m.new_quantity ?? '-' }}</td>
                            <td class="px-5 py-3 text-xs text-gray-400">#{{ m.transaction_id }}</td>
                        </tr>
                    </tbody>
                </table>
                <div v-else class="flex h-32 items-center justify-center text-sm text-gray-400">
                    No inventory movements yet.
                </div>
            </div>
        </div>

    </div>
</template>
