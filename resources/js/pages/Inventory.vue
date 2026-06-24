<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// ── Types ──────────────────────────────────────────────────────────────────────
interface StockItem {
    product_id: number;
    product_name: string;
    quantity: number;
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
        breadcrumbs: [
            { title: 'Inventory', href: '/inventory' },
        ],
    },
});

const page       = usePage();
const LOW_STOCK_THRESHOLD = 20;

const stockItems    = computed(() => (page.props.stockLevels as StockItem[]) || []);
const movements     = computed(() => (page.props.movements as Movement[]) || []);
const lowStockItems = computed(() => stockItems.value.filter(i => i.quantity < LOW_STOCK_THRESHOLD));

// ── Restock form state ─────────────────────────────────────────────────────────
const selectedProduct = ref<StockItem | null>(null);
const restockQty      = ref<number>(10);
const submitting      = ref(false);

function selectProduct(item: StockItem) {
    selectedProduct.value = item;
    restockQty.value = 10;
}

function submitRestock() {
    if (!selectedProduct.value || restockQty.value < 1) return;
    submitting.value = true;
    router.post(
        '/inventory/restock',
        {
            product_id: selectedProduct.value.product_id,
            quantity: restockQty.value,
        },
        {
            onFinish: () => {
                submitting.value = false;
                // Keep selected product visible so user sees updated stock
            },
            preserveScroll: true,
        }
    );
}

// ── Stock level colour ─────────────────────────────────────────────────────────
function dotClass(qty: number) {
    if (qty <= 0)                    return 'bg-red-500';
    if (qty < LOW_STOCK_THRESHOLD)   return 'bg-amber-400';
    return 'bg-green-500';
}

// ── Movement helpers ───────────────────────────────────────────────────────────
const MOVEMENT_STYLES: Record<string, string> = {
    ORDER:              'bg-red-100   text-red-700   dark:bg-red-900/40   dark:text-red-300',
    ORDER_CANCELLATION: 'bg-blue-100  text-blue-700  dark:bg-blue-900/40  dark:text-blue-300',
    GOODS_RECEIPT:      'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
    ADJUSTMENT:         'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
};

const MOVEMENT_LABELS: Record<string, string> = {
    ORDER:              'sale',
    ORDER_CANCELLATION: 'cancellation',
    GOODS_RECEIPT:      'receipt',
    ADJUSTMENT:         'adjustment',
};

function movementLabel(type: string) { return MOVEMENT_LABELS[type] ?? type.toLowerCase(); }
function movementStyle(type: string) { return MOVEMENT_STYLES[type] ?? ''; }
</script>

<template>
    <Head title="Inventory Management" />

    <div class="flex flex-col gap-6 p-5 pb-10">

        <!-- ── Header ──────────────────────────────────────────────────────── -->
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Inventory Management</h1>
        </div>

        <!-- ── Low-Stock Alert ──────────────────────────────────────────────── -->
        <div
            v-if="lowStockItems.length"
            class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-700/40 dark:bg-amber-900/20"
        >
            <div class="mb-3 flex items-center gap-2">
                <span class="text-lg">⚠️</span>
                <h2 class="text-sm font-bold text-amber-800 dark:text-amber-300">
                    Low Stock Alert — {{ lowStockItems.length }} product{{ lowStockItems.length > 1 ? 's' : '' }} below threshold ({{ LOW_STOCK_THRESHOLD }} units)
                </h2>
            </div>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="item in lowStockItems"
                    :key="item.product_id"
                    @click="selectProduct(item)"
                    class="flex items-center gap-2 rounded-lg border border-amber-200 bg-white px-3 py-1.5 transition-colors hover:border-amber-400 hover:bg-amber-50 dark:border-amber-700/30 dark:bg-amber-900/30 dark:hover:bg-amber-900/50"
                >
                    <span class="inline-block h-2 w-2 rounded-full" :class="dotClass(item.quantity)"></span>
                    <span class="text-sm font-medium text-amber-900 dark:text-amber-200">{{ item.product_name }}</span>
                    <span class="rounded-full bg-amber-100 px-1.5 py-0.5 text-xs font-bold text-amber-700 dark:bg-amber-800/50 dark:text-amber-300">
                        {{ item.quantity }} left
                    </span>
                </button>
            </div>
        </div>

        <!-- ── Top Section: Stock Levels + Restock Panel ───────────────────── -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            <!-- Stock Levels -->
            <div class="rounded-2xl border border-sidebar-border/70 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-[#161920]">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Current Stock Levels
                </h2>

                <div class="flex flex-col divide-y divide-gray-100 dark:divide-gray-800">
                    <div
                        v-for="item in stockItems"
                        :key="item.product_id"
                        class="flex items-center justify-between py-3"
                    >
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-gray-200">{{ item.product_name }}</p>
                            <p class="mt-0.5 flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                                <span class="inline-block h-2 w-2 rounded-full" :class="dotClass(item.quantity)"></span>
                                {{ item.quantity }} units
                            </p>
                        </div>
                        <button
                            @click="selectProduct(item)"
                            class="rounded-lg px-4 py-1.5 text-sm font-medium transition-colors"
                            :class="selectedProduct?.product_id === item.product_id
                                ? 'bg-indigo-600 text-white shadow-sm hover:bg-indigo-700'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
                        >
                            Restock
                        </button>
                    </div>

                    <div v-if="!stockItems.length" class="flex h-24 items-center justify-center text-sm text-gray-400">
                        No inventory data found.
                    </div>
                </div>
            </div>

            <!-- Restock Panel -->
            <div class="rounded-2xl border border-sidebar-border/70 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-[#161920]">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Restock Product
                </h2>

                <!-- No product selected -->
                <div
                    v-if="!selectedProduct"
                    class="flex h-36 items-center justify-center rounded-xl border border-dashed border-gray-200 dark:border-gray-700"
                >
                    <p class="text-sm text-gray-400">Select a product to restock</p>
                </div>

                <!-- Product selected -->
                <div v-else class="flex flex-col gap-5">
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/40">
                        <p class="text-xs font-medium text-gray-400 dark:text-gray-500">Selected Product</p>
                        <p class="mt-1 text-lg font-bold text-gray-800 dark:text-gray-100">{{ selectedProduct.product_name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Current stock: <span class="font-semibold">{{ selectedProduct.quantity }} units</span>
                        </p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Quantity to Add
                        </label>
                        <input
                            v-model.number="restockQty"
                            type="number"
                            min="1"
                            class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-gray-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:focus:ring-indigo-800"
                        />
                    </div>

                    <button
                        @click="submitRestock"
                        :disabled="submitting || restockQty < 1"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <span>📦</span>
                        <span>{{ submitting ? 'Processing…' : 'Restock (Execute Transaction)' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Inventory Movement History ──────────────────────────────────── -->
        <div class="rounded-2xl border border-sidebar-border/70 bg-white shadow-sm dark:border-gray-700 dark:bg-[#161920]">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h2 class="text-base font-bold text-gray-800 dark:text-gray-100">Inventory Movement History</h2>
            </div>

            <div class="overflow-x-auto">
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
                        <tr
                            v-for="(m, i) in movements"
                            :key="i"
                            class="transition-colors hover:bg-gray-50 dark:hover:bg-[#1e2028]"
                        >
                            <td class="px-5 py-3 text-xs text-gray-400">{{ m.created_at }}</td>
                            <td class="px-5 py-3 font-medium text-gray-800 dark:text-gray-200">{{ m.product_name }}</td>
                            <td class="px-5 py-3">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    :class="movementStyle(m.transaction_type)"
                                >
                                    {{ movementLabel(m.transaction_type) }}
                                </span>
                            </td>
                            <td
                                class="px-5 py-3 text-right font-mono font-semibold"
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
                    No inventory movements yet
                </div>
            </div>
        </div>

    </div>
</template>