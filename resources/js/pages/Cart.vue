<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { cart } from '@/routes';
import { router } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Cart',
                href: cart(),
            },
        ],
    },
});

interface Category {
    category_name: string;
}

interface Product {
    product_id: number;
    product_name: string;
    price: number;
    category: Category;
    image_path: string;
}

interface CartItem {
    cart_item_id: number;
    product: Product;
    quantity: number;
    unit_price: number;
}

const props = defineProps<{
    cartItems: CartItem[];
}>();

// Local reactive state
const items = ref(
    (props.cartItems ?? []).map((item) => ({
        ...item,
        quantity: item.quantity,
        selected: true,
    }))
);

function increment(index: number) {
    items.value[index].quantity++;
    router.patch(`/cart/${items.value[index].cart_item_id}/update`, {
        quantity: items.value[index].quantity,
    }, { preserveScroll: true });
}

function decrement(index: number) {
    if (items.value[index].quantity > 1) {
        items.value[index].quantity--;
        router.patch(`/cart/${items.value[index].cart_item_id}/update`, {
            quantity: items.value[index].quantity,
        }, { preserveScroll: true });
    }
    else removeItem(index);
}

function removeItem(index: number) {
    const cartItemId = items.value[index].cart_item_id;
    items.value.splice(index, 1);
    router.delete(`/cart/${cartItemId}/destroy`, { preserveScroll: true });
}

const allSelected = computed(() => items.value.every((item) => item.selected));

function toggleAll() {
    const next = !allSelected.value;
    items.value.forEach((i) => (i.selected = next));
}

const selectedItems = computed(() => items.value.filter((item) => item.selected));

const total = computed(() =>
    selectedItems.value.reduce(
        (sum, item) => sum + item.product.price * item.quantity,
        0
    )
);

function formatPrice(value: number) {
    return value.toLocaleString('en-MY', { style: 'currency', currency: 'MYR' });
}

function handleCheckout() {
    router.post('/checkout/store', {
        items: selectedItems.value.map((item) => ({
            product_id: item.product.product_id,
            quantity: item.quantity,
            price: item.product.price,
        })),
    });

    const selectedIndices = selectedItems.value
        .map((item) => items.value.findIndex((i) => i.cart_item_id === item.cart_item_id))
        .filter((index) => index !== -1)
        .sort((a, b) => b - a);

    selectedIndices.forEach((index) => removeItem(index));

    alert("Order placed successfully!");
}
</script>

<template>
    <Head title="Cart" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">

        <!-- Empty state -->
        <div
            v-if="items.length === 0"
            class="flex flex-1 flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-sidebar-border py-24 text-center dark:border-sidebar-border"
        >
            <svg class="h-12 w-12 text-muted-foreground/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.673-7.014a1.08 1.08 0 0 0-1.05-1.32H5.106L4.5 4.5M7.5 14.25 5.106 5.25M12 10.5h.008v.008H12V10.5Zm0 3h.008v.008H12v-.008Z" />
            </svg>
            <p class="text-sm font-medium text-muted-foreground">Your cart is empty</p>
            <p class="text-xs text-muted-foreground/60">Add some products to get started.</p>
        </div>

        <!-- Cart content -->
        <template v-else>
            <!-- Header row -->
            <div class="flex items-center gap-3 border-b border-sidebar-border pb-3 dark:border-sidebar-border">
                <input
                    type="checkbox"
                    :checked="allSelected"
                    @change="toggleAll"
                    class="h-4 w-4 rounded border-gray-300 accent-primary cursor-pointer"
                    title="Select all"
                />
                <span class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                    Select all ({{ items.length }} item{{ items.length !== 1 ? 's' : '' }})
                </span>
            </div>

            <!-- Items list -->
            <div class="flex flex-col gap-3">
                <div
                    v-for="(item, index) in items"
                    :key="index"
                    class="flex items-center gap-4 rounded-xl border border-sidebar-border/70 bg-white px-4 py-4 shadow-sm transition-opacity dark:border-sidebar-border dark:bg-sidebar"
                    :class="{ 'opacity-50': !item.selected }"
                >
                    <!-- Product image -->
                    <img
                        :src="item.product.image_path"
                        :alt="item.product.product_name"
                        @click="router.visit(`/catalog/${item.product.product_id}`)"
                        class="h-14 w-14 shrink-0 rounded-lg object-cover transition hover:opacity-80"
                    />

                    <!-- Checkbox -->
                    <input
                        type="checkbox"
                        v-model="item.selected"
                        class="h-4 w-4 shrink-0 rounded border-gray-300 accent-primary cursor-pointer"
                    />

                    <!-- Product info -->
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-foreground">
                            {{ item.product.product_name }}
                        </p>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            {{ item.product.category.category_name }}
                        </p>
                    </div>

                    <!-- Price per unit -->
                    <div class="hidden text-right sm:block">
                        <p class="text-xs text-muted-foreground">Unit price</p>
                        <p class="text-sm font-medium text-foreground">
                            {{ formatPrice(item.product.price) }}
                        </p>
                    </div>

                    <!-- Quantity stepper -->
                    <div class="flex items-center gap-1 rounded-lg border border-sidebar-border/70 dark:border-sidebar-border">
                        <button
                            @click="decrement(index)"
                            :disabled="item.quantity <= 1"
                            class="flex h-8 w-8 items-center justify-center rounded-l-lg text-muted-foreground transition hover:bg-muted disabled:cursor-not-allowed disabled:opacity-30"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                            </svg>
                        </button>
                        <span class="w-8 text-center text-sm font-semibold tabular-nums text-foreground">
                            {{ item.quantity }}
                        </span>
                        <button
                            @click="increment(index)"
                            class="flex h-8 w-8 items-center justify-center rounded-r-lg text-muted-foreground transition hover:bg-muted"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                            </svg>
                        </button>
                    </div>

                    <!-- Subtotal -->
                    <div class="w-24 text-right">
                        <p class="text-xs text-muted-foreground">Subtotal</p>
                        <p class="text-sm font-semibold text-foreground">
                            {{ formatPrice(item.product.price * item.quantity) }}
                        </p>
                    </div>

                    <!-- Remove -->
                    <button
                        @click="removeItem(index)"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-muted-foreground/50 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-950/30"
                        title="Remove item"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Summary footer -->
            <div class="mt-auto rounded-xl border border-sidebar-border/70 bg-white px-6 py-5 dark:border-sidebar-border dark:bg-sidebar">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-1">
                        <p class="text-xs text-muted-foreground">
                            {{ selectedItems.length }} of {{ items.length }} item{{ items.length !== 1 ? 's' : '' }} selected
                        </p>
                        <div class="flex items-baseline gap-2">
                            <span class="text-sm text-muted-foreground">Total:</span>
                            <span class="text-2xl font-bold tracking-tight text-foreground">
                                {{ formatPrice(total) }}
                            </span>
                        </div>
                    </div>

                    <button
                        @click="handleCheckout"
                        :disabled="selectedItems.length === 0"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-8 py-3 text-sm font-semibold text-primary-foreground shadow-sm transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.673-7.014a1.08 1.08 0 0 0-1.05-1.32H5.106L4.5 4.5" />
                        </svg>
                        Checkout ({{ selectedItems.length }})
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>