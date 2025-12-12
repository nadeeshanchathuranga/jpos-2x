<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    grnRows: { type: Array, default: () => [] },
    grnTotals: { type: Object, default: () => ({}) },
    startDate: { type: String, default: '' },
    endDate: { type: String, default: '' },
});

const startDate = ref(props.startDate);
const endDate = ref(props.endDate);

const formatCurrency = (value) =>
    new Intl.NumberFormat('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(
        Number(value ?? 0),
    );

const filterReports = () => {
    router.get(
        route('reports.grn'),
        { start_date: startDate.value, end_date: endDate.value },
        { preserveScroll: true, preserveState: true },
    );
};

const resetFilter = () => {
    startDate.value = props.startDate;
    endDate.value = props.endDate;
    filterReports();
};

const statusBadge = (status) => {
    if (status === 1) return 'bg-green-600/80 text-white';
    if (status === 2) return 'bg-yellow-500/80 text-white';
    return 'bg-gray-600/80 text-white';
};
</script>

<template>
    <Head title="GRN Report" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header with Date Filter -->
                <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-4 mb-2">
                            <button
                                @click="$inertia.visit(route('dashboard'))"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition flex items-center gap-2"
                            >
                                Back
                            </button>
                            <h1 class="text-3xl font-bold text-white">📥 GRN Report</h1>
                        </div>
                        <p class="text-gray-400">Track received inventory within a date range</p>
                    </div>

                    <!-- Date Filter -->
                    <div class="flex items-center gap-2 bg-slate-800 rounded-lg p-3 shadow-lg">
                        <input
                            type="date"
                            v-model="startDate"
                            class="px-3 py-1.5 bg-slate-700 text-white text-sm rounded focus:ring-2 focus:ring-emerald-500"
                        />
                        <span class="text-gray-400">to</span>
                        <input
                            type="date"
                            v-model="endDate"
                            class="px-3 py-1.5 bg-slate-700 text-white text-sm rounded focus:ring-2 focus:ring-emerald-500"
                        />
                        <button
                            @click="filterReports"
                            class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded transition"
                        >
                            Apply
                        </button>
                        <button
                            @click="resetFilter"
                            class="px-4 py-1.5 bg-slate-600 hover:bg-slate-700 text-white text-sm font-semibold rounded transition"
                        >
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-lg p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-emerald-100 text-sm mb-1">Total GRN Value</p>
                                <h2 class="text-3xl font-bold text-white">Rs. {{ grnTotals.net_total ?? '0.00' }}</h2>
                            </div>
                            <div class="text-5xl">📥</div>
                        </div>
                        <p class="text-emerald-50 text-sm mt-2">{{ grnTotals.count ?? 0 }} GRNs</p>
                    </div>

                    <div class="bg-gradient-to-br from-cyan-600 to-cyan-700 rounded-lg p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-cyan-100 text-sm mb-1">Total Items Received</p>
                                <h2 class="text-3xl font-bold text-white">{{ grnTotals.items_count ?? 0 }}</h2>
                            </div>
                            <div class="text-5xl">📦</div>
                        </div>
                        <p class="text-cyan-50 text-sm mt-2">Across all GRNs</p>
                    </div>

                    <div class="bg-gradient-to-br from-slate-700 to-slate-800 rounded-lg p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-slate-100 text-sm mb-1">Date Range</p>
                                <h2 class="text-lg font-semibold text-white">{{ startDate }} → {{ endDate }}</h2>
                            </div>
                            <div class="text-5xl">🗓️</div>
                        </div>
                        <p class="text-slate-200 text-sm mt-2">Filtered period</p>
                    </div>
                </div>

                <!-- GRN Table -->
                <div class="bg-slate-800 rounded-lg p-6 shadow-lg mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-white">Goods Received Notes</h3>
                        <div class="text-sm text-slate-300 flex gap-4">
                            <span>Gross: Rs. {{ grnTotals.gross_total ?? '0.00' }}</span>
                            <span>Tax: Rs. {{ grnTotals.tax_total ?? '0.00' }}</span>
                            <span>Discount: Rs. {{ grnTotals.discount_total ?? '0.00' }}</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-slate-300">GRN No</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-slate-300">Supplier</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-slate-300">Date</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-slate-300">Items</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-slate-300">Gross</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-slate-300">Discount</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-slate-300">Tax</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-slate-300">Net</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-slate-300">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                <tr v-for="row in grnRows" :key="row.id" class="text-slate-200">
                                    <td class="px-4 py-3 font-semibold">{{ row.grn_no }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ row.supplier_name }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ row.date }}</td>
                                    <td class="px-4 py-3 text-right text-emerald-300 font-semibold">{{ row.items_count }}</td>
                                    <td class="px-4 py-3 text-right">Rs. {{ formatCurrency(row.gross_total) }}</td>
                                    <td class="px-4 py-3 text-right text-amber-300">Rs. {{ formatCurrency(row.line_discount + row.header_discount) }}</td>
                                    <td class="px-4 py-3 text-right text-cyan-300">Rs. {{ formatCurrency(row.tax_total) }}</td>
                                    <td class="px-4 py-3 text-right text-green-400 font-semibold">Rs. {{ formatCurrency(row.net_total) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span :class="['px-3 py-1 rounded-full text-xs font-semibold', statusBadge(row.status)]">
                                            {{ row.status === 1 ? 'Active' : row.status === 2 ? 'Closed' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="grnRows.length === 0" class="text-center text-slate-400 py-8">
                        No GRNs found for the selected range.
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
