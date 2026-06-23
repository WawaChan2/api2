<script setup>
import { Head } from '@inertiajs/vue3';
import { LucideShoppingBasket } from '@lucide/vue';
import { catalog } from '@/routes';
import { ref } from 'vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Product Catalog',
                href: catalog(),
            },
            {
                title: 'Product Detail',
            },
        ],
    },
});

const props = defineProps({
    product: Object,
});

console.log(props.product.image_path);

const quantity = ref(1);

const increment = () => quantity.value++;
const decrement = () => { if (quantity.value > 1) quantity.value--; };
</script>

<template>
    <Head title="Product Detail" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="grid gap-4 md:grid-cols-2 md:grid-rows-2">

            <!-- Left: Product Image (spans 2 rows) -->
            <div class="flex items-center justify-center overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 p-8 md:row-span-2 dark:border-gray-700 dark:bg-[#26292e]">
                <img
                    v-if="product.image_path"
                    :src="product.image_path"
                    :alt="product.product_name"
                    class="h-full max-h-96 w-full object-contain"
                />
                <div v-else class="flex flex-col items-center gap-2 text-gray-300 dark:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-sm">No image available</span>
                </div>
            </div>

            <!-- Top Right: Product Info -->
            <div class="flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-[#161920]">
                <div>
                    <p class="mb-1 text-sm font-medium text-blue-500">
                        {{ product.category?.category_name ?? 'Uncategorized' }}
                    </p>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ product.product_name }}
                    </h1>
                </div>
                <p class="leading-relaxed text-gray-500 dark:text-gray-400">
                    {{ product.description ?? 'No description available.' }}
                </p>
            </div>

            <!-- Bottom Right: Price & Actions -->
            <div class="flex flex-col justify-between gap-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-[#161920]">

                <!-- Price -->
                <div>
                    <p class="text-sm text-gray-400 dark:text-gray-500">Price</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">
                        ${{ Number(product.price).toFixed(2) }}
                    </p>
                </div>

                <!-- Quantity -->
                <div class="flex flex-col gap-2">
                    <p class="text-sm text-gray-400 dark:text-gray-500">Quantity</p>
                    <div class="flex items-center gap-3">
                        <button
                            @click="decrement"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-600 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            −
                        </button>
                        <span class="w-8 text-center text-lg font-semibold text-gray-900 dark:text-white">
                            {{ quantity }}
                        </span>
                        <button
                            @click="increment"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-600 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            +
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button
                        class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 active:bg-blue-800"
                    >
                        Buy ${{ (Number(product.price) * quantity).toFixed(2) }}
                    </button>
                    <button
                        class="flex items-center justify-center gap-2 rounded-lg border border-blue-600 px-4 py-2.5 text-sm font-semibold text-blue-600 transition hover:bg-blue-50 active:bg-blue-100 dark:border-blue-500 dark:text-blue-400 dark:hover:bg-blue-900/30"
                    >
                        <LucideShoppingBasket class="h-4 w-4" />
                        Add to Cart
                    </button>
                </div>

            </div>

        </div>
    </div>
</template>