<template>
    <Head title="Sales Income Report" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header with Date Filter -->
                <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-4 mb-2">
                            <button
                                @click="$inertia.visit(route('dashboard'))"
                                class="px-4 py-2 bg-accent hover:bg-accent text-white rounded-lg transition flex items-center gap-2"
                            >
                                Back
                            </button>
                            <h1 class="text-3xl font-bold text-white">💵 Sales Income Report</h1>
                        </div>
                        <p class="text-gray-400">Detailed sales income and returns transactions</p>
                    </div>
                    
                    <!-- Compact Date Filter -->
                    <div class="flex items-center gap-2 bg-gray-800 rounded-lg p-3 shadow-lg">
                        <input 
                            type="date" 
                            v-model="startDate" 
                            class="px-3 py-1.5 bg-gray-700 text-white text-sm rounded focus:ring-2 focus:ring-blue-500"
                        />
                        <span class="text-gray-400">to</span>
                        <input 
                            type="date" 
                            v-model="endDate" 
                            class="px-3 py-1.5 bg-gray-700 text-white text-sm rounded focus:ring-2 focus:ring-blue-500"
                        />
                        <button 
                            @click="filterReports" 
                            class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded transition"
                        >
                            Apply
                        </button>
                        <button 
                            @click="resetFilter" 
                            class="px-4 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold rounded transition"
                        >
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-lg p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm mb-1">Total Income</p>
                                <h2 class="text-3xl font-bold text-white">{{ page.props.currency || '' }} {{ totalIncome }}</h2>
                            </div>
                            <div class="text-5xl">💰</div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-red-600 to-red-700 rounded-lg p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-100 text-sm mb-1">Total Returns</p>
                                <h2 class="text-3xl font-bold text-white">{{ page.props.currency || '' }} {{ totalReturns }}</h2>
                            </div>
                            <div class="text-5xl">↩️</div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm mb-1">Net Income</p>
                                <h2 class="text-3xl font-bold text-white">{{ page.props.currency || '' }} {{ netIncome }}</h2>
                            </div>
                            <div class="text-5xl">💵</div>
                        </div>
                    </div>
                </div>

                <!-- Sales Income Transactions Table -->
                <div class="bg-gray-800 rounded-lg p-6 shadow-lg mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-white">Sales Income Transactions</h3>
                        <div class="flex gap-2">
                            <button 
                                @click="exportPdf" 
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition flex items-center gap-2"
                            >
                                📄 Export PDF
                            </button>
                            <button 
                                @click="exportExcel" 
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
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">Invoice No</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">Income Date</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Amount</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">Type</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">Payment Type</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr v-for="income in salesIncomeList" :key="income.id" class="text-gray-300 hover:bg-gray-900">
                                    <td class="px-4 py-3 font-medium">{{ income.invoice_no || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-center">{{ formatDate(income.income_date) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold"
                                        :class="income.is_return ? 'text-red-400' : 'text-green-400'">
                                        {{ page.props.currency || '' }} {{ income.amount }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-3 py-1 rounded-full text-white text-sm font-medium"
                                            :class="income.is_return ? 'bg-red-600' : 'bg-green-600'">
                                            {{ income.type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-3 py-1 rounded-full text-white text-sm font-medium"
                                            :class="getPaymentTypeColor(income.payment_type)">
                                            {{ income.payment_type_name }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="salesIncomeList.length === 0" class="text-center text-gray-400 py-8">
                        No sales income data for selected date range
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { logActivity } from '@/composables/useActivityLog';

const page = usePage();

const props = defineProps({
    salesIncomeList: Array,
    totalIncome: String,
    totalReturns: String,
    netIncome: String,
    startDate: String,
    endDate: String,
});

const startDate = ref(props.startDate);
const endDate = ref(props.endDate);

const filterReports = () => {
    router.get(route('reports.sales-income'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilter = () => {
    router.get(route('reports.sales-income'), {}, {
        preserveState: false,
        preserveScroll: false,
    });
};

const getPaymentTypeColor = (type) => {
    const colors = {
        0: 'bg-green-600',
        1: 'bg-blue-600',
        2: 'bg-orange-600',
    };
    return colors[type] || 'bg-gray-600';
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const exportPdf = async () => {
    await logActivity('create', 'sales_income_report', {
        action: 'export_pdf',
        start_date: startDate.value,
        end_date: endDate.value,
    });
    window.location.href = route('reports.export.sales-income.pdf', {
        start_date: startDate.value,
        end_date: endDate.value,
        currency: page.props.currency || ''
    });
};

const exportExcel = async () => {
    await logActivity('create', 'sales_income_report', {
        action: 'export_excel',
        start_date: startDate.value,
        end_date: endDate.value,
    });
    window.location.href = route('reports.export.sales-income.excel', {
        start_date: startDate.value,
        end_date: endDate.value,
        currency: page.props.currency || ''
    });
};
</script>
