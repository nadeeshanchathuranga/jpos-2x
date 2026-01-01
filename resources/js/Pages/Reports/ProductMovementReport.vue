<script setup>
import { computed, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const page = usePage();

const props = defineProps({
    movements: { type: Object, default: () => ({}) },
    summaryByType: { type: Array, default: () => [] },
    summaryByProduct: { type: Array, default: () => [] },
    totals: { type: Object, default: () => ({}) },
    products: { type: Array, default: () => [] },
    selectedProductId: { type: [String, Number, null], default: null },
    selectedMovementType: { type: [String, Number, null], default: null },
    startDate: { type: String, default: '' },
    endDate: { type: String, default: '' },
});

const startDate = ref(props.startDate);
const endDate = ref(props.endDate);
const selectedProductId = ref(props.selectedProductId);
const selectedMovementType = ref(props.selectedMovementType);
const expandedProduct = ref(null);

const movementTypes = [
    { id: 0, name: 'Purchase (GRN)' },
    { id: 1, name: 'Purchase Return (PRN)' },
    { id: 2, name: 'Transfer (PTR)' },
    { id: 3, name: 'Sale' },
    { id: 4, name: 'Sale Return' },
    { id: 5, name: 'GRN Return' },
    { id: 6, name: 'Stock Transfer Return' },
];

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
    if (selectedMovementType.value !== null && selectedMovementType.value !== '') {
        params.movement_type = selectedMovementType.value;
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
    selectedMovementType.value = null;
    router.get(route('reports.product-movements'), {}, { preserveScroll: true });
};

const inboundMovements = computed(() => props.movements.data ? props.movements.data.filter(m => [0, 4, 5].includes(m.movement_type_id)) : []);
const outboundMovements = computed(() => props.movements.data ? props.movements.data.filter(m => [1, 2, 3, 6].includes(m.movement_type_id)) : []);

const exportLinks = computed(() => {
    const params = new URLSearchParams();
    if (startDate.value) params.append('start_date', startDate.value);
    if (endDate.value) params.append('end_date', endDate.value);
    if (selectedProductId.value) params.append('product_id', selectedProductId.value);
    if (selectedMovementType.value !== null && selectedMovementType.value !== '') params.append('movement_type', selectedMovementType.value);
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
                            <select
                                v-model="selectedMovementType"
                                class="px-3 py-1.5 bg-slate-700 text-white text-sm rounded focus:ring-2 focus:ring-indigo-500 flex-1"
                            >
                                <option value="">All Movement Types</option>
                                <option v-for="type in movementTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
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

               

                <!-- Detailed Movements List -->
                <div class="bg-slate-800 rounded-lg p-6 shadow-lg">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-white">All Movements</h3>
                        <div class="flex gap-2">
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
                                    <th class="px-4 py-3 text-left font-semibold text-slate-300">Date</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-300">Product Name</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-300">Movement Type</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-300">Quantity</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-300">Unit</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                <tr
                                    v-for="movement in movements.data"
                                    :key="movement.id"
                                    class="hover:bg-slate-700/50 transition"
                                >
                                    <td class="px-4 py-3 text-slate-300">{{ movement.date }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-slate-200">{{ movement.product_name }}</div>
                                        <div class="text-xs text-slate-400">{{ movement.product_code }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-lg">{{ getMovementIcon(movement.movement_type_id) }}</span>
                                            <span class="text-slate-200">{{ movement.movement_type }}</span>
                                        </div>
                                        <div class="text-xs text-slate-400 mt-1">Ref: {{ movement.reference }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-lg font-bold" :class="[0, 4, 5].includes(movement.movement_type_id) ? 'text-green-400' : 'text-red-400'">
                                            {{ [0, 4, 5].includes(movement.movement_type_id) ? '+' : '-' }}{{ movement.quantity }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-300">{{ movement.unit || 'Units' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="movements.data && movements.data.length === 0" class="text-center text-slate-400 py-8">
                        No movements found for the selected criteria
                    </div>

                    <!-- Pagination -->
                    <div v-if="movements.data && movements.data.length > 0" class="flex justify-between items-center mt-6 pt-4 border-t border-slate-700">
                        <div class="text-sm text-slate-400">
                            Showing {{ movements.from }} to {{ movements.to }} of {{ movements.total }} movements
                        </div>
                        <div class="flex gap-2">
                            <button
                                v-for="link in movements.links"
                                :key="link.label"
                                @click="link.url ? $inertia.get(link.url) : null"
                                :disabled="!link.url"
                                :class="[
                                    'px-4 py-2 rounded text-sm font-semibold transition',
                                    link.active 
                                        ? 'bg-indigo-600 text-white' 
                                        : link.url 
                                            ? 'bg-slate-700 text-slate-300 hover:bg-slate-600' 
                                            : 'bg-slate-800 text-slate-600 cursor-not-allowed'
                                ]"
                                v-html="link.label"
                            ></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
