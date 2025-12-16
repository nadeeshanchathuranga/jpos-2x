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

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm mb-1">Total Products</p>
                                <h2 class="text-3xl font-bold text-white">{{ productsStock.length }}</h2>
                            </div>
                            <div class="text-5xl">📦</div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-lg p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm mb-1">In Stock</p>
                                <h2 class="text-3xl font-bold text-white">{{ inStockCount }}</h2>
                            </div>
                            <div class="text-5xl">✅</div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-orange-600 to-orange-700 rounded-lg p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-orange-100 text-sm mb-1">Low Stock</p>
                                <h2 class="text-3xl font-bold text-white">{{ lowStockCount }}</h2>
                            </div>
                            <div class="text-5xl">⚠️</div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-red-600 to-red-700 rounded-lg p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-100 text-sm mb-1">Out of Stock</p>
                                <h2 class="text-3xl font-bold text-white">{{ outOfStockCount }}</h2>
                            </div>
                            <div class="text-5xl">❌</div>
                        </div>
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
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Current Stock</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Retail Price</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Wholesale Price</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr v-for="product in productsStock" :key="product.id" class="text-gray-300">
                                    <td class="px-4 py-3">{{ product.name }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">{{ product.stock }}</td>
                                    <td class="px-4 py-3 text-right">Rs. {{ product.retail_price }}</td>
                                    <td class="px-4 py-3 text-right">Rs. {{ product.wholesale_price }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span :class="getStockStatusColor(product.stock_status)" class="font-semibold">
                                            {{ product.stock_status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="productsStock.length === 0" class="text-center text-gray-400 py-8">
                        No products found
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { logActivity } from '@/composables/useActivityLog';

const props = defineProps({
    productsStock: Array,
});

const inStockCount = computed(() => {
    return props.productsStock.filter(p => p.stock_status === 'In Stock').length;
});

const lowStockCount = computed(() => {
    return props.productsStock.filter(p => p.stock_status === 'Low Stock').length;
});

const outOfStockCount = computed(() => {
    return props.productsStock.filter(p => p.stock_status === 'Out of Stock').length;
});

const exportProductStockPdfUrl = computed(() => {
    return route('reports.export.product-stock.pdf');
});

const exportProductStockExcelUrl = computed(() => {
    return route('reports.export.product-stock.excel');
});

const exportStockPdf = async () => {
    await logActivity('create', 'stock_report', {
        action: 'export_pdf',
        total_products: props.productsStock.length
    });
    window.location.href = exportProductStockPdfUrl.value;
};

const exportStockExcel = async () => {
    await logActivity('create', 'stock_report', {
        action: 'export_excel',
        total_products: props.productsStock.length
    });
    window.location.href = exportProductStockExcelUrl.value;
};

const getStockStatusColor = (status) => {
    if (status === 'Out of Stock') return 'text-red-500';
    if (status === 'Low Stock') return 'text-orange-500';
    return 'text-green-500';
};
</script>
