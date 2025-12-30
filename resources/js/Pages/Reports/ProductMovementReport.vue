<script setup>
import { computed, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const page = usePage();

const props = defineProps({
    movements: { type: Array, default: () => [] },
    summaryByType: { type: Array, default: () => [] },
    summaryByProduct: { type: Array, default: () => [] },
    totals: { type: Object, default: () => ({}) },
    products: { type: Array, default: () => [] },
    selectedProductId: { type: [String, Number, null], default: null },
    startDate: { type: String, default: '' },
    endDate: { type: String, default: '' },
});

const startDate = ref(props.startDate);
const endDate = ref(props.endDate);
const selectedProductId = ref(props.selectedProductId);
const expandedProduct = ref(null);

const formatCurrency = (value) =>
    new Intl.NumberFormat('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(
        Number(value ?? 0),
    );

const getMovementColor = (typeId) => {
    const colors = {
        0: 'bg-green-600/20 text-green-300 border-l-4 border-green-500',
        1: 'bg-orange-600/20 text-orange-300 border-l-4 border-orange-500',
        2: 'bg-blue-600/20 text-blue-300 border-l-4 border-blue-500',
        3: 'bg-red-600/20 text-red-300 border-l-4 border-red-500',
        4: 'bg-amber-600/20 text-amber-300 border-l-4 border-amber-500',
        5: 'bg-purple-600/20 text-purple-300 border-l-4 border-purple-500',
        6: 'bg-pink-600/20 text-pink-300 border-l-4 border-pink-500',
    };
    return colors[typeId] || 'bg-slate-600/20 text-slate-300 border-l-4 border-slate-500';
};

const getMovementIcon = (typeId) => {
    const icons = {
        0: '📥',
        1: '📤',
        2: '🔄',
        3: '🛒',
        4: '↩️',
        5: '🔙',
        6: '⚠️',
    };
    return icons[typeId] || '📦';
};

const filterReport = () => {
    const params = {
        start_date: startDate.value,
        end_date: endDate.value,
    };
    if (selectedProductId.value) {
        params.product_id = selectedProductId.value;
    }
    router.get(
        route('reports.product-movements'),
        params,
        { preserveScroll: true, preserveState: true },
    );
};

const resetFilter = () => {
    startDate.value = props.startDate;
    endDate.value = props.endDate;
    selectedProductId.value = null;
    router.get(route('reports.product-movements'), {}, { preserveScroll: true });
};

const inboundMovements = computed(() => props.movements.filter(m => [0, 4, 5].includes(m.movement_type_id)));
const outboundMovements = computed(() => props.movements.filter(m => [1, 2, 3, 6].includes(m.movement_type_id)));

const exportLinks = computed(() => {
    const params = new URLSearchParams();
    if (startDate.value) params.append('start_date', startDate.value);
    if (endDate.value) params.append('end_date', endDate.value);
    if (selectedProductId.value) params.append('product_id', selectedProductId.value);
    const query = params.toString();
    return {
        pdf: '/reports/export/product-movements/pdf' + (query ? `?${query}` : ''),
        excel: '/reports/export/product-movements/excel' + (query ? `?${query}` : ''),
    };
});

const logExportActivity = async (type) => {
    try {
        await axios.post('/products/log-activity', {
            action: 'export',
            module: 'product movement report',
            details: {
                export_type: type,
                start_date: startDate.value,
                end_date: endDate.value,
                product_id: selectedProductId.value,
            },
        });
    } catch (e) {
        // Optionally handle/log error
        console.error('Activity log failed', e);
    }
};
</script>

<template>
    <Head title="Product Movements Report" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header with Date & Product Filter -->
                <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-4 mb-2">
                            <button
                                @click="$inertia.visit(route('dashboard'))"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition flex items-center gap-2"
                            >
                                Back
                            </button>
                            <h1 class="text-3xl font-bold text-white">📦 Product Movements Report</h1>
                        </div>
                        <p class="text-gray-400">Track all inventory movements - purchases, sales, transfers, returns</p>
                    </div>

                    <!-- Filters -->
                    <div class="flex flex-col gap-3 bg-slate-800 rounded-lg p-4 shadow-lg min-w-fit">
                        <div class="flex items-center gap-2">
                            <input
                                type="date"
                                v-model="startDate"
                                class="px-3 py-1.5 bg-slate-700 text-white text-sm rounded focus:ring-2 focus:ring-indigo-500"
                            />
                            <span class="text-gray-400">to</span>
                            <input
                                type="date"
                                v-model="endDate"
                                class="px-3 py-1.5 bg-slate-700 text-white text-sm rounded focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                        <div class="flex gap-2">
                            <button
                                @click="filterReport"
                                class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded transition flex-1"
                            >
                                Apply
                            </button>
                            <button
                                @click="resetFilter"
                                class="px-4 py-1.5 bg-slate-600 hover:bg-slate-700 text-white text-sm font-semibold rounded transition flex-1"
                            >
                                Reset
                            </button>
                        </div>

                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-lg p-4 shadow-lg">
                        <p class="text-green-100 text-sm mb-1">Total Inbound</p>
                        <h2 class="text-2xl font-bold text-white">{{ formatCurrency(totals.total_quantity_in) }}</h2>
                        <p class="text-green-50 text-xs mt-1">Units received</p>
                    </div>

                    <div class="bg-gradient-to-br from-red-600 to-red-700 rounded-lg p-4 shadow-lg">
                        <p class="text-red-100 text-sm mb-1">Total Outbound</p>
                        <h2 class="text-2xl font-bold text-white">{{ formatCurrency(totals.total_quantity_out) }}</h2>
                        <p class="text-red-50 text-xs mt-1">Units shipped</p>
                    </div>

                    <div class="bg-gradient-to-br from-cyan-600 to-cyan-700 rounded-lg p-4 shadow-lg">
                        <p class="text-cyan-100 text-sm mb-1">Movements</p>
                        <h2 class="text-2xl font-bold text-white">{{ totals.total_movements }}</h2>
                        <p class="text-cyan-50 text-xs mt-1">Total transactions</p>
                    </div>

                    <div class="bg-gradient-to-br from-purple-600 to-purple-700 rounded-lg p-4 shadow-lg">
                        <p class="text-purple-100 text-sm mb-1">Products</p>
                        <h2 class="text-2xl font-bold text-white">{{ totals.unique_products }}</h2>
                        <p class="text-purple-50 text-xs mt-1">Unique items</p>
                    </div>
                </div>

                <!-- Movement Type Summary -->
                <div class="bg-slate-800 rounded-lg p-6 shadow-lg mb-6">
                    <h3 class="text-xl font-semibold text-white mb-4">Movements by Type</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div
                            v-for="summary in summaryByType"
                            :key="summary.type"
                            class="bg-slate-700 rounded-lg p-4"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-sm font-semibold text-slate-200">{{ summary.type }}</h4>
                                <span class="text-xs text-slate-400 bg-slate-600 px-2 py-1 rounded">{{ summary.count }}</span>
                            </div>
                            <p class="text-2xl font-bold text-indigo-300">{{ formatCurrency(summary.quantity) }}</p>
                        </div>
                    </div>
                    <div v-if="summaryByType.length === 0" class="text-center text-slate-400 py-4">
                        No movements recorded for selected criteria
                    </div>
                </div>

                <!-- Product Summary -->
                <div class="bg-slate-800 rounded-lg p-6 shadow-lg mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                            <h3 class="text-xl font-semibold text-white">Summary by Product</h3>
                            <select
                                v-model="selectedProductId"
                                class="min-w-[180px] w-auto px-3 py-1.5 bg-slate-700 text-white text-sm rounded focus:ring-2 focus:ring-indigo-500"
                            >
                                <option value="">All Products</option>
                                <option v-for="product in products" :key="product.id" :value="product.id">
                                    {{ product.name }}
                                </option>
                            </select>
                        </div>
                        <div class="flex gap-2 sm:justify-end">
                            <a
                                :href="exportLinks.pdf"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition flex items-center gap-2"
                                @click="logExportActivity('pdf')"
                            >
                                📄 Export PDF
                            </a>
                            <a
                                :href="exportLinks.excel"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition flex items-center gap-2"
                                @click="logExportActivity('excel')"
                            >
                                📊 Export Excel
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-700 border-b border-slate-600">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-300">Product</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-300">Inbound</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-300">Outbound</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-300">Net Balance</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                <tr
                                    v-for="product in summaryByProduct"
                                    :key="product.product_id"
                                    class="hover:bg-slate-700/50 transition cursor-pointer"
                                    @click="expandedProduct = expandedProduct === product.product_id ? null : product.product_id"
                                >
                                    <td class="px-4 py-3 text-slate-200">
                                        <div class="font-medium">{{ product.product_name }}</div>
                                        <div class="text-xs text-slate-400">{{ product.product_code }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-right text-green-400 font-semibold">{{ formatCurrency(product.inbound) }}</td>
                                    <td class="px-4 py-3 text-right text-red-400 font-semibold">{{ formatCurrency(product.outbound) }}</td>
                                    <td class="px-4 py-3 text-right font-bold" :class="product.net >= 0 ? 'text-cyan-400' : 'text-orange-400'">
                                        {{ formatCurrency(product.net) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="summaryByProduct.length === 0" class="text-center text-slate-400 py-6">
                        No products with movements
                    </div>
                </div>

                <!-- Detailed Movements List -->
                <div class="bg-slate-800 rounded-lg p-6 shadow-lg">
                    <h3 class="text-xl font-semibold text-white mb-4">All Movements</h3>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <div
                            v-for="movement in movements"
                            :key="movement.id"
                            :class="['rounded-lg p-4 border-l-4 transition', getMovementColor(movement.movement_type_id)]"
                        >
                            <div class="flex justify-between items-start gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xl">{{ getMovementIcon(movement.movement_type_id) }}</span>
                                        <span class="font-semibold">{{ movement.product_name }}</span>
                                        <span class="text-xs text-slate-400 bg-slate-700 px-2 py-1 rounded">{{ movement.product_code }}</span>
                                    </div>
                                    <div class="text-sm text-slate-300 mb-1">{{ movement.movement_type }}</div>
                                    <div class="flex justify-between text-xs text-slate-400">
                                        <span>Ref: {{ movement.reference }}</span>
                                        <span>{{ movement.date }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold" :class="[0, 4, 5].includes(movement.movement_type_id) ? 'text-green-400' : 'text-red-400'">
                                        {{ [0, 4, 5].includes(movement.movement_type_id) ? '+' : '-' }}{{ movement.quantity }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="movements.length === 0" class="text-center text-slate-400 py-8">
                        No movements found for the selected criteria
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
