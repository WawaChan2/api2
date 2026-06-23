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

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <div class="grid gap-5 sm:grid-cols-2 md:grid-cols-3">
            <div
                v-for="product in products"
                :key="product.id"
                class="group relative flex flex-col rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-[#161920]"
            >
                <!-- Product Image -->
                <div
                    class="mb-4 flex h-44 items-center justify-center overflow-hidden rounded-xl bg-gray-50 dark:bg-[#26292e]"
                >
                    <img
                        v-if="product.image_path"
                        :src="product.image_path"
                        :alt="product.product_name"
                        class="h-full w-full object-contain p-4 transition-transform duration-300 group-hover:scale-105"
                    />
                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center text-gray-300 dark:text-gray-600"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-16 w-16"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="flex flex-1 flex-col gap-1">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                        {{ product.product_name }}
                    </h3>

                    <div class="relative mt-2 h-14 overflow-hidden">
                        <!-- Category & Price: slides out to the left on hover -->
                        <div class="absolute inset-0 flex flex-col justify-start transition-transform duration-300 ease-in-out translate-y-0 group-hover:-translate-y-full">
                            <p class="text-sm text-gray-400 dark:text-gray-500">
                                {{ product.category?.category_name ?? '-' }}
                            </p>
                            <span class="mt-1 text-lg font-bold text-gray-900 dark:text-white">
                                ${{ Number(product.price).toFixed(2) }}
                            </span>
                        </div>

                        <!-- Action Buttons: slides in from the right on hover -->
                        <div class="absolute inset-0 flex items-center gap-3 transition-transform duration-300 ease-in-out translate-y-full group-hover:translate-y-0">
                            <button
                                class="flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white transition-colors hover:bg-blue-700 active:bg-blue-800"
                            >
                                Buy ${{ Number(product.price).toFixed(2) }}
                            </button>
                            <button
                                class="flex items-center justify-center rounded-lg border border-blue-600 p-1.5 text-blue-600 transition-colors hover:bg-blue-50 active:bg-blue-100 dark:border-blue-500 dark:text-blue-400 dark:hover:bg-blue-900/30"
                            >
                                <LucideShoppingBasket class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div
            v-if="!products || products.length === 0"
            class="flex flex-col items-center justify-center py-24 text-gray-400 dark:text-gray-600"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="mb-4 h-16 w-16"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                />
            </svg>
            <p class="text-lg font-medium">No products found</p>
            <p class="mt-1 text-sm">Add some products to see them here.</p>
        </div>
    </div>
</template>