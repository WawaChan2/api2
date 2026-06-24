<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { dashboard } from '@/routes';

// ── Types ──────────────────────────────────────────────────────────────────────
interface Kpis {
    totalProducts: number;
    totalUsers: number;
    totalOrders: number;
    pendingOrders: number;
    totalRevenue: string;
}

interface RecentOrder {
    order_id: number;
    user_name: string;
    user_email: string;
    status: 'PENDING' | 'SHIPPED' | 'DELIVERED' | 'CANCELLED';
    total: string;
    created_at: string;
}

interface TopProduct {
    product_id: number;
    product_name: string;
    units_sold: number;
    revenue: string;
}

interface LowStockItem {
    product_id: number;
    product_name: string;
    quantity: number;
}

interface Warehouse {
    warehouse_id: number;
    warehouse_name: string;
    location: string;
    city: string;
    capacity: number;
    current_stock: number;
    utilization: number;
}

// ── Props from Inertia ─────────────────────────────────────────────────────────
defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

const page = usePage();
const kpis             = computed(() => (page.props.kpis as Kpis) || {});
const orderBreakdown   = computed(() => (page.props.orderStatusBreakdown as Record<string, number>) || {});
const recentOrders     = computed(() => (page.props.recentOrders as RecentOrder[]) || []);
const topProducts      = computed(() => (page.props.topProducts as TopProduct[]) || []);
const lowStock         = computed(() => (page.props.lowStockItems as LowStockItem[]) || []);
const warehouses       = computed(() => (page.props.warehouses as Warehouse[]) || []);
const monthlyRevenue   = computed(() => (page.props.monthlyRevenue as Record<string, number>) || {});

// ── Tab state ─────────────────────────────────────────────────────────────────
const activeTab = ref<'orders'>('orders');

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

const maxTopUnits = computed(() => Math.max(...topProducts.value.map(p => p.units_sold), 1));

// ── Status order for breakdown chart ──────────────────────────────────────────
const STATUSES = ['PENDING', 'SHIPPED', 'DELIVERED', 'CANCELLED'];
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="flex flex-col gap-6 p-5 pb-10">

        <!-- ── KPI Row ─────────────────────────────────────────────────────── -->
        <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
            <!-- Revenue -->
            <div class="col-span-2 md:col-span-1 flex flex-col gap-1 rounded-2xl border border-sidebar-border/70 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-[#161920]">
                <span class="text-xs font-semibold uppercase tracking-widest text-gray-400">Revenue <span class="text-xs">(RM)</span></span>
                <span class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ kpis.totalRevenue }}</span>
                <span class="text-xs text-gray-400">all non-cancelled</span>
            </div>
            <!-- Total Orders -->
            <div class="flex flex-col gap-1 rounded-2xl border border-sidebar-border/70 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-[#161920]">
                <span class="text-xs font-semibold uppercase tracking-widest text-gray-400">Orders</span>
                <span class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ kpis.totalOrders }}</span>
                <span class="text-xs text-amber-500 font-medium">{{ kpis.pendingOrders }} pending</span>
            </div>
            <!-- Products -->
            <div class="flex flex-col gap-1 rounded-2xl border border-sidebar-border/70 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-[#161920]">
                <span class="text-xs font-semibold uppercase tracking-widest text-gray-400">Products</span>
                <span class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ kpis.totalProducts }}</span>
                <span class="text-xs text-gray-400">in catalog</span>
            </div>
            <!-- Users -->
            <div class="flex flex-col gap-1 rounded-2xl border border-sidebar-border/70 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-[#161920]">
                <span class="text-xs font-semibold uppercase tracking-widest text-gray-400">Users</span>
                <span class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ kpis.totalUsers }}</span>
                <span class="text-xs text-gray-400">registered</span>
            </div>
            <!-- Low Stock -->
            <div class="flex flex-col gap-1 rounded-2xl border border-red-200 bg-red-50 p-5 shadow-sm dark:border-red-900/60 dark:bg-red-950/30">
                <span class="text-xs font-semibold uppercase tracking-widest text-red-400">Low Stock</span>
                <span class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">{{ lowStock.length }}</span>
                <span class="text-xs text-red-400">items &lt; 20 units</span>
            </div>
        </div>

        <!-- ── Revenue Chart + Order Breakdown ────────────────────────────── -->
        <div class="grid gap-4 md:grid-cols-3">

            <!-- Revenue Sparkline -->
            <div class="md:col-span-2 rounded-2xl border border-sidebar-border/70 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-[#161920]">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Monthly Revenue (last 6 months)
                </h2>
                <div v-if="chartMonths.length">
                    <svg viewBox="0 0 560 150" class="w-full" style="height:150px">
                        <!-- Grid lines -->
                        <line x1="0" y1="0"   x2="560" y2="0"   stroke="currentColor" stroke-width="0.5" class="text-gray-200 dark:text-gray-700"/>
                        <line x1="0" y1="40"  x2="560" y2="40"  stroke="currentColor" stroke-width="0.5" class="text-gray-200 dark:text-gray-700"/>
                        <line x1="0" y1="80"  x2="560" y2="80"  stroke="currentColor" stroke-width="0.5" class="text-gray-200 dark:text-gray-700"/>
                        <line x1="0" y1="120" x2="560" y2="120" stroke="currentColor" stroke-width="0.5" class="text-gray-200 dark:text-gray-700"/>

                        <!-- Area fill -->
                        <defs>
                            <linearGradient id="revGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#6366f1" stop-opacity="0.3"/>
                                <stop offset="100%" stop-color="#6366f1" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        <polygon
                            v-if="chartValues.length"
                            :points="`0,120 ${polylinePoints} 560,120`"
                            fill="url(#revGrad)"
                        />
                        <!-- Line -->
                        <polyline
                            :points="polylinePoints"
                            fill="none"
                            stroke="#6366f1"
                            stroke-width="2.5"
                            stroke-linejoin="round"
                            stroke-linecap="round"
                        />
                        <!-- Dots + labels -->
                        <g v-for="(val, i) in chartValues" :key="i">
                            <circle
                                :cx="chartValues.length > 1 ? Math.round(i * (560 / (chartValues.length - 1))) : 280"
                                :cy="chartY(val)"
                                r="4"
                                fill="#6366f1"
                                stroke="white"
                                stroke-width="2"
                            />
                            <text
                                :x="chartValues.length > 1 ? Math.round(i * (560 / (chartValues.length - 1))) : 280"
                                y="145"
                                text-anchor="middle"
                                font-size="11"
                                class="fill-gray-400"
                            >{{ shortMonth(chartMonths[i]) }}</text>
                        </g>
                    </svg>
                </div>
                <div v-else class="flex h-32 items-center justify-center text-sm text-gray-400">
                    No revenue data yet.
                </div>
            </div>

            <!-- Order Status Breakdown -->
            <div class="rounded-2xl border border-sidebar-border/70 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-[#161920]">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Order Status
                </h2>
                <div class="space-y-3">
                    <div v-for="status in STATUSES" :key="status">
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300 capitalize">{{ status.toLowerCase() }}</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ orderBreakdown[status] ?? 0 }}</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-gray-100 dark:bg-gray-800">
                            <div
                                class="h-2 rounded-full transition-all duration-500"
                                :class="{
                                    'bg-amber-400':  status === 'PENDING',
                                    'bg-blue-500':   status === 'SHIPPED',
                                    'bg-green-500':  status === 'DELIVERED',
                                    'bg-red-400':    status === 'CANCELLED',
                                }"
                                :style="{ width: barWidth(orderBreakdown[status] ?? 0) + '%' }"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Top Products + Warehouses ──────────────────────────────────── -->
        <div class="grid gap-4 md:grid-cols-2">

            <!-- Top Products -->
            <div class="rounded-2xl border border-sidebar-border/70 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-[#161920]">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Top 5 Products by Units Sold
                </h2>
                <div v-if="topProducts.length" class="space-y-3">
                    <div v-for="(p, i) in topProducts" :key="p.product_id" class="flex items-center gap-3">
                        <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300">
                            {{ i + 1 }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between mb-0.5">
                                <span class="truncate text-sm font-medium text-gray-800 dark:text-gray-200">{{ p.product_name }}</span>
                                <span class="ml-2 flex-shrink-0 text-xs text-gray-500 dark:text-gray-400">{{ p.units_sold }} units · RM {{ p.revenue }}</span>
                            </div>
                            <div class="h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-800">
                                <div
                                    class="h-1.5 rounded-full bg-indigo-500 transition-all duration-500"
                                    :style="{ width: Math.round((p.units_sold / maxTopUnits) * 100) + '%' }"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-sm text-gray-400">No order data yet.</div>
            </div>

            <!-- Warehouse Utilisation -->
            <div class="rounded-2xl border border-sidebar-border/70 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-[#161920]">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Warehouse Utilisation
                </h2>
                <div v-if="warehouses.length" class="space-y-3">
                    <div v-for="wh in warehouses" :key="wh.warehouse_id">
                        <div class="mb-1 flex items-center justify-between">
                            <div>
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ wh.warehouse_name }}</span>
                                <span class="ml-1.5 text-xs text-gray-400">· {{ wh.city }}</span>
                            </div>
                            <span class="text-xs font-semibold"
                                :class="{
                                    'text-red-500': wh.utilization >= 85,
                                    'text-amber-500': wh.utilization >= 60 && wh.utilization < 85,
                                    'text-green-500': wh.utilization < 60,
                                }"
                            >{{ wh.utilization }}%</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-gray-100 dark:bg-gray-800">
                            <div
                                class="h-2 rounded-full transition-all duration-500"
                                :class="{
                                    'bg-red-500':   wh.utilization >= 85,
                                    'bg-amber-400': wh.utilization >= 60 && wh.utilization < 85,
                                    'bg-green-500': wh.utilization < 60,
                                }"
                                :style="{ width: Math.min(wh.utilization, 100) + '%' }"
                            />
                        </div>
                        <div class="mt-0.5 text-xs text-gray-400">{{ wh.current_stock.toLocaleString() }} / {{ wh.capacity.toLocaleString() }} units</div>
                    </div>
                </div>
                <div v-else class="text-sm text-gray-400">No warehouse data.</div>
            </div>
        </div>

        <!-- ── Tabbed Lower Section ────────────────────────────────────────── -->
        <div class="rounded-2xl border border-sidebar-border/70 bg-white shadow-sm dark:border-gray-700 dark:bg-[#161920]">
            <!-- Tab headers -->
            <div class="flex border-b border-gray-100 dark:border-gray-800">
                <div class="px-6 py-3.5 text-sm font-medium border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400">
                    Recent Orders
                </div>
            </div>

            <!-- Recent Orders -->
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
                            <td class="px-5 py-3 text-right font-semibold text-gray-800 dark:text-gray-200">RM {{ order.total }}</td>
                            <td class="px-5 py-3 text-xs text-gray-400">{{ order.created_at }}</td>
                        </tr>
                    </tbody>
                </table>
                <div v-else class="flex h-32 items-center justify-center text-sm text-gray-400">
                    No orders yet.
                </div>
            </div>

            <!-- Low-stock Alert banner inside table section -->
            <div v-if="lowStock.length" class="border-t border-amber-100 bg-amber-50 px-5 py-3 dark:border-amber-900/30 dark:bg-amber-950/20">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">⚠ Low-stock alerts</p>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="item in lowStock"
                        :key="item.product_id"
                        class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-white px-3 py-0.5 text-xs font-medium text-amber-700 dark:border-amber-800 dark:bg-amber-950/50 dark:text-amber-300"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500" />
                        {{ item.product_name }} — {{ item.quantity }} units
                    </span>
                </div>
            </div>
        </div>

    </div>
</template>