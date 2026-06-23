<script setup>
import { Head } from '@inertiajs/vue3';
import { LucideShoppingBasket } from '@lucide/vue';
import { catalog } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Product Catalog',
                href: catalog(),
            },
        ],
    },
});

const props = defineProps({
    products: Array,
});
</script>

<template>
    <Head title="Product Catalog" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="grid gap-5 sm:grid-cols-2 md:grid-cols-3">
            <div
                v-for="product in products"
                :key="product.id"
                class="group relative flex flex-col rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-[#161920]"
            >

                <!-- Product Image -->
                <div class="mb-4 flex h-44 items-center justify-center overflow-hidden rounded-xl bg-gray-50 dark:bg-[#26292e]">
                    <img
                        v-if="product.image_path"
                        :src="product.image_path"
                        :alt="product.product_name"
                        class="h-full w-full object-contain p-4 transition-transform duration-300 group-hover:scale-105"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center text-gray-300 dark:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="flex flex-1 flex-col gap-1">
                    <p class="text-sm text-gray-400 dark:text-gray-500">
                        {{ product.category?.category_name ?? '-' }}
                    </p>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                        {{ product.product_name }}
                    </h3>

                    <!-- Price Row -->
                    <div class="mt-2 flex items-center justify-between">
                        <div class="flex items-baseline gap-2">
                            <span class="text-lg font-bold text-gray-900 dark:text-white">
                                ${{ Number(product.price).toFixed(2) }}
                            </span>
                            <span
                                v-if="product.original_price"
                                class="text-sm text-gray-400 line-through dark:text-gray-500"
                            >
                                ${{ Number(product.original_price).toFixed(2) }}
                            </span>
                        </div>

                        <!-- Action Buttons -->
                        <!-- <div class="flex items-center gap-2">
                            <button
                                class="flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white transition-colors hover:bg-blue-700 active:bg-blue-800"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Buy ${{ Number(product.price).toFixed(2) }}
                            </button>
                            <button
                                class="rounded-lg border border-blue-600 px-3 py-1.5 text-sm font-semibold text-blue-600 transition-colors hover:bg-blue-50 active:bg-blue-100 dark:border-blue-500 dark:text-blue-400 dark:hover:bg-blue-900/30"
                            >
                                <LucideShoppingBasket class="h-5 w-5" />
                            </button>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div
            v-if="!products || products.length === 0"
            class="flex flex-col items-center justify-center py-24 text-gray-400 dark:text-gray-600"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="mb-4 h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p class="text-lg font-medium">No products found</p>
            <p class="mt-1 text-sm">Add some products to see them here.</p>
        </div>
    </div>
</template>