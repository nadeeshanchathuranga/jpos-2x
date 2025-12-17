<template>
    <Head title="Expenses Report" />

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
                            <h1 class="text-3xl font-bold text-white">💸 Expenses Report</h1>
                        </div>
                        <p class="text-gray-400">Expense details and summary by payment type</p>
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-red-600 to-red-700 rounded-lg p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-100 text-sm mb-1">Total Expenses</p>
                                <h2 class="text-3xl font-bold text-white">{{ page.props.currency || '' }} {{ totalExpenses }}</h2>
                            </div>
                            <div class="text-5xl">💸</div>
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

                    <div class="bg-gradient-to-br from-purple-600 to-purple-700 rounded-lg p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm mb-1">Average Expense</p>
                                <h2 class="text-3xl font-bold text-white">{{ page.props.currency || '' }} {{ averageExpense }}</h2>
                            </div>
                            <div class="text-5xl">📊</div>
                        </div>
                    </div>
                </div>

                <!-- Expenses Summary by Payment Type -->
                <div class="bg-gray-800 rounded-lg p-6 shadow-lg mb-6">
                    <h3 class="text-xl font-semibold text-white mb-4">Expenses by Payment Type</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div v-for="(expense, index) in expensesSummary" :key="index"
                            :class="getPaymentTypeColor(expense.payment_type)"
                            class="rounded-lg p-6 text-white">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-lg font-semibold">{{ expense.payment_type_name }}</h4>
                                <span class="text-2xl">
                                    {{ expense.payment_type === 0 ? '💵' : expense.payment_type === 1 ? '💳' : '📝' }}
                                </span>
                            </div>
                            <p class="text-2xl font-bold mb-1">{{ page.props.currency || '' }} {{ expense.total_amount }}</p>
                            <p class="text-sm opacity-80">{{ expense.transaction_count }} transactions</p>
                        </div>
                    </div>
                    <div v-if="expensesSummary.length === 0" class="text-center text-gray-400 py-8">
                        No expenses data for selected date range
                    </div>
                </div>

                <!-- Expenses Details -->
                <div class="bg-gray-800 rounded-lg p-6 shadow-lg mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-white">Expense Transactions</h3>
                        <div class="flex gap-2">
                            <button 
                                @click="exportExpensesPdf" 
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition flex items-center gap-2"
                            >
                                📄 Export PDF
                            </button>
                            <button 
                                @click="exportExpensesExcel" 
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
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">Title</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">Payment</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">Supplier</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">Reference</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Amount</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr v-for="expense in expensesList" :key="expense.id" class="text-gray-300">
                                    <td class="px-4 py-3">{{ expense.id }}</td>
                                    <td class="px-4 py-3">{{ expense.title }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-3 py-1 rounded-full text-white text-sm font-medium"
                                            :class="getPaymentTypeColor(expense.payment_type)">
                                            {{ expense.payment_type_name }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-400">{{ expense.supplier_name }}</td>
                                    <td class="px-4 py-3 text-gray-400">{{ expense.reference || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-right text-red-400 font-semibold">{{ page.props.currency || '' }} {{ expense.amount }}</td>
                                    <td class="px-4 py-3 text-center">{{ formatDate(expense.expense_date) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="expensesList.length === 0" class="text-center text-gray-400 py-8">
                        No expenses for selected date range
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

const props = defineProps({
    expensesSummary: Array,
    expensesList: Array,
    totalExpenses: String,
    startDate: String,
    endDate: String,
});

const startDate = ref(props.startDate);
const endDate = ref(props.endDate);

const page = usePage();

const totalTransactions = computed(() => {
    return props.expensesList.length;
});

const averageExpense = computed(() => {
    if (props.expensesList.length === 0) return '0.00';
    const total = parseFloat(props.totalExpenses.replace(/,/g, ''));
    const avg = total / props.expensesList.length;
    return avg.toFixed(2);
});

const exportExpensesPdfUrl = computed(() => {
    return route('reports.export.expenses.pdf', {
        start_date: startDate.value,
        end_date: endDate.value,
        currency: page.props.currency || ''
    });
});

const exportExpensesExcelUrl = computed(() => {
    return route('reports.export.expenses.excel', {
        start_date: startDate.value,
        end_date: endDate.value,
        currency: page.props.currency || ''
    });
});

const exportExpensesPdf = async () => {
    await logActivity('create', 'expenses_report', {
        action: 'export_pdf',
        start_date: startDate.value,
        end_date: endDate.value,
        total_expenses: props.totalExpenses
    });
    window.location.href = exportExpensesPdfUrl.value;
};

const exportExpensesExcel = async () => {
    await logActivity('create', 'expenses_report', {
        action: 'export_excel',
        start_date: startDate.value,
        end_date: endDate.value,
        total_expenses: props.totalExpenses
    });
    window.location.href = exportExpensesExcelUrl.value;
};

const filterReports = () => {
    router.get(route('reports.expenses'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilter = () => {
    router.get(route('reports.expenses'), {}, {
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
</script>
