<template>
    <Head title="Income Report" />

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
                            <h1 class="text-3xl font-bold text-white">💵 Income Report</h1>
                        </div>
                        <p class="text-gray-400">Income summary and breakdown by payment type</p>
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

                <!-- Summary Card -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-lg p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm mb-1">Total Income</p>
                                <h2 class="text-3xl font-bold text-white">Rs. {{ totalIncome }}</h2>
                            </div>
                            <div class="text-5xl">💰</div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm mb-1">Total Transactions</p>
                                <h2 class="text-3xl font-bold text-white">{{ totalTransactions }}</h2>
                            </div>
                            <div class="text-5xl">📝</div>
                        </div>
                    </div>
                </div>

                <!-- Income by Payment Type -->
                <div class="bg-gray-800 rounded-lg p-6 shadow-lg mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-white">Income by Payment Type</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div v-for="income in incomeSummary" :key="income.payment_type"
                            :class="getPaymentTypeColor(income.payment_type)"
                            class="rounded-lg p-6 text-white">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-lg font-semibold">{{ income.payment_type_name }}</h4>
                                <span class="text-2xl">
                                    {{ income.payment_type === 0 ? '💵' : income.payment_type === 1 ? '💳' : '📝' }}
                                </span>
                            </div>
                            <p class="text-2xl font-bold mb-1">Rs. {{ income.total_amount }}</p>
                            <p class="text-sm opacity-80">{{ income.transaction_count }} transactions</p>
                            <div class="mt-3 pt-3 border-t border-white/20">
                                <p class="text-xs opacity-70">Percentage of Total</p>
                                <p class="text-lg font-semibold">{{ calculatePercentage(income.total_amount) }}%</p>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="incomeSummary.length === 0" class="text-center text-gray-400 py-8">
                        No income data for selected date range
                    </div>

                    <!-- Income Details Table -->
                    <div class="mt-6" v-if="incomeList && incomeList.length > 0">
                        <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-semibold text-white mb-3">Income Transactions</h4>
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
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">ID</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">Source</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">Sale ID</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">Payment Type</th>
                                        <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Amount</th>
                                        <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    <tr v-for="income in incomeList" :key="income.id" class="text-gray-300">
                                        <td class="px-4 py-3">{{ income.id }}</td>
                                        <td class="px-4 py-3">{{ income.source || 'N/A' }}</td>
                                        <td class="px-4 py-3 text-gray-400">{{ income.sale_id || 'N/A' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="px-3 py-1 rounded-full text-white text-sm font-medium"
                                                :class="getPaymentTypeColor(income.payment_type)">
                                                {{ income.payment_type_name }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right text-green-400 font-semibold">Rs. {{ income.amount }}</td>
                                        <td class="px-4 py-3 text-center">{{ formatDate(income.income_date) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { logActivity } from '@/composables/useActivityLog';

const props = defineProps({
    incomeSummary: Array,
    incomeList: Array,
    totalIncome: String,
    startDate: String,
    endDate: String,
});

const startDate = ref(props.startDate);
const endDate = ref(props.endDate);

const totalTransactions = computed(() => {
    return props.incomeSummary.reduce((sum, income) => sum + income.transaction_count, 0);
});

const averageIncome = computed(() => {
    if (totalTransactions.value === 0) return '0.00';
    const total = parseFloat(props.totalIncome.replace(/,/g, ''));
    const avg = total / totalTransactions.value;
    return avg.toFixed(2);
});

const calculatePercentage = (amount) => {
    const total = parseFloat(props.totalIncome.replace(/,/g, ''));
    if (total === 0) return '0.00';
    const value = parseFloat(amount.replace(/,/g, ''));
    return ((value / total) * 100).toFixed(2);
};

const filterReports = () => {
    router.get(route('reports.income'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilter = () => {
    router.get(route('reports.income'), {}, {
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
    await logActivity('create', 'income_report', {
        action: 'export_pdf',
        start_date: startDate.value,
        end_date: endDate.value,
        total_income: props.totalIncome
    });
    window.location.href = route('reports.export.income.pdf', {
        start_date: startDate.value,
        end_date: endDate.value,
    });
};

const exportExcel = async () => {
    await logActivity('create', 'income_report', {
        action: 'export_excel',
        start_date: startDate.value,
        end_date: endDate.value,
        total_income: props.totalIncome
    });
    window.location.href = route('reports.export.income.excel', {
        start_date: startDate.value,
        end_date: endDate.value,
    });
};
</script>
