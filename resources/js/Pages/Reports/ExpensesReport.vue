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
                            <h1 class="text-3xl font-bold text-white">💸 Supplier Payment Report</h1>
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
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">Expense Date</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">Supplier Name</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Amount</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">Payment Type</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr v-for="expense in expensesList.data" :key="expense.id" class="text-gray-300 hover:bg-gray-900">
                                    <td class="px-4 py-3 text-center">{{ formatDate(expense.expense_date) }}</td>
                                    <td class="px-4 py-3">{{ expense.supplier_name }}</td>
                                    <td class="px-4 py-3 text-right text-red-400 font-semibold">{{ page.props.currency || '' }} {{ expense.amount }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-3 py-1 rounded-full text-white text-sm font-medium"
                                            :class="getPaymentTypeColor(expense.payment_type)">
                                            {{ expense.payment_type_name }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="!expensesList.data || expensesList.data.length === 0" class="text-center text-gray-400 py-8">
                        No expenses for selected date range
                    </div>

                    <!-- Pagination -->
                    <div v-if="expensesList.data?.length > 0" class="mt-6 flex justify-between items-center">
                        <div class="text-sm text-gray-400">
                            Showing {{ expensesList.from }} to {{ expensesList.to }} of {{ expensesList.total }} expenses
                        </div>
                        <div class="flex gap-2">
                            <template v-for="(link, index) in expensesList.links" :key="index">
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
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { logActivity } from '@/composables/useActivityLog';

const props = defineProps({
    expensesSummary: Array,
    expensesList: Object,
    totalExpenses: String,
    startDate: String,
    endDate: String,
});

const startDate = ref(props.startDate);
const endDate = ref(props.endDate);

const page = usePage();

const totalTransactions = computed(() => {
    return props.expensesList.total || 0;
});

const averageExpense = computed(() => {
    const totalCount = props.expensesList.total || 0;
    if (totalCount === 0) return '0.00';
    const total = parseFloat(props.totalExpenses.replace(/,/g, ''));
    const avg = total / totalCount;
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
