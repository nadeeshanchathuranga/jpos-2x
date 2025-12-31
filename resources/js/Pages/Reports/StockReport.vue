<template>
    <Head title="Stock Report" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-4 mb-2">
                            <button
                                @click="$inertia.visit(route('dashboard'))"
                                class="px-4 py-2 bg-accent hover:bg-accent text-white rounded-lg transition flex items-center gap-2"
                            >
                                Back
                            </button>
                            <h1 class="text-3xl font-bold text-white">📊 Products Stock Report</h1>
                        </div>
                        <p class="text-gray-400">Current inventory status and stock levels</p>
                    </div>
                </div>

                

                <!-- Products Stock Report -->
                <div class="bg-gray-800 rounded-lg p-6 shadow-lg mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-white">Products Stock Details</h3>
                        <div class="flex gap-2">
                            <button
                                @click="exportStockPdf"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition flex items-center gap-2"
                            >
                                📄 Export PDF
                            </button>
                            <button
                                @click="exportStockExcel"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition flex items-center gap-2"
                            >
                                📊 Export Excel
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">Product Name</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">Shop Qty</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">Store Qty</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr v-for="product in productsStock.data" :key="product.id" class="text-gray-300 hover:bg-gray-900">
                                    <td class="px-4 py-3">{{ product.name }}</td>
                                    <td class="px-4 py-3 text-center font-semibold">{{ product.shop_qty_display }}</td>
                                    <td class="px-4 py-3 text-center font-semibold">{{ product.store_qty_display }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="!productsStock.data || productsStock.data.length === 0" class="text-center text-gray-400 py-8">
                        No products found
                    </div>

                    <!-- Pagination -->
                    <div v-if="productsStock.data?.length > 0" class="mt-6 flex justify-between items-center">
                        <div class="text-sm text-gray-400">
                            Showing {{ productsStock.from }} to {{ productsStock.to }} of {{ productsStock.total }} products
                        </div>
                        <div class="flex gap-2">
                            <template v-for="(link, index) in productsStock.links" :key="index">
                                <a
                                    v-if="link.url"
                                    :href="link.url"
                                    @click.prevent="router.visit(link.url, { preserveState: true, preserveScroll: true })"
                                    :class="[
                                        'px-3 py-2 text-sm rounded-lg transition',
                                        link.active 
                                            ? 'bg-blue-600 text-white font-semibold' 
                                            : 'bg-gray-700 text-gray-300 hover:bg-gray-600'
                                    ]"
                                    v-html="link.label"
                                ></a>
                                <span
                                    v-else
                                    :class="[
                                        'px-3 py-2 text-sm rounded-lg',
                                        'bg-gray-800 text-gray-600 cursor-not-allowed'
                                    ]"
                                    v-html="link.label"
                                ></span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { logActivity } from '@/composables/useActivityLog';

const props = defineProps({
    productsStock: Object,
});

const page = usePage();

const inStockCount = computed(() => {
    if (!props.productsStock.data) return 0;
    return props.productsStock.data.filter(p => p.shop_quantity > 0 || p.store_quantity > 0).length;
});

const lowStockCount = computed(() => {
    if (!props.productsStock.data) return 0;
    return props.productsStock.data.filter(p => (p.shop_quantity > 0 && p.shop_quantity < 10) || (p.store_quantity > 0 && p.store_quantity < 10)).length;
});

const outOfStockCount = computed(() => {
    if (!props.productsStock.data) return 0;
    return props.productsStock.data.filter(p => p.shop_quantity === 0 && p.store_quantity === 0).length;
});

const exportProductStockPdfUrl = computed(() => {
    return route('reports.export.product-stock.pdf', {
        currency: page.props.currency || ''
    });
});

const exportProductStockExcelUrl = computed(() => {
    return route('reports.export.product-stock.excel', {
        currency: page.props.currency || ''
    });
});

const exportStockPdf = async () => {

    await logActivity('create', 'stock_report', {
        action: 'export_pdf',
        total_products: props.productsStock.total || 0
    });
    window.location.href = exportProductStockPdfUrl.value;
};

const exportStockExcel = async () => {
    await logActivity('create', 'stock_report', {
        action: 'export_excel',
        total_products: props.productsStock.total || 0
    });
    window.location.href = exportProductStockExcelUrl.value;
};

const getStockStatusColor = (status) => {
    if (status === 'Out of Stock') return 'text-red-500';
    if (status === 'Low Stock') return 'text-orange-500';
    return 'text-green-500';
};
</script>
