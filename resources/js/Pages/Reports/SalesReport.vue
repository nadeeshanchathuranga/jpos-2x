<template>
    <Head title="Sales Report" />

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
                            <h1 class="text-3xl font-bold text-white">💰 Sales Report</h1>
                        </div>
                        <p class="text-gray-400">Sales summary by type with income details</p>
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

                    <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm mb-1">Total Sales</p>
                                <h2 class="text-3xl font-bold text-white">{{ totalSalesCount }}</h2>
                            </div>
                            <div class="text-5xl">🛒</div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-600 to-purple-700 rounded-lg p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm mb-1">Net After Returns</p>
                                <h2 class="text-3xl font-bold text-white">{{ page.props.currency || '' }} {{ netAfterReturns }}</h2>
                            </div>
                            <div class="text-5xl">💵</div>
                        </div>
                    </div>
                </div>

                <!-- Income by Payment Type -->
                <div class="bg-gray-800 rounded-lg p-6 shadow-lg mb-6">
                    <h3 class="text-xl font-semibold text-white mb-4">Income by Payment Type</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div v-for="income in incomeSummary" :key="income.payment_type"
                            :class="getPaymentTypeColor(income.payment_type)"
                            class="rounded-lg p-6 text-white">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-lg font-semibold">{{ income.payment_type_name }}</h4>
                                <span class="text-2xl">
                                    {{ income.payment_type === 0 ? '💵' : income.payment_type === 1 ? '💳' : '📝' }}
                                </span>
                            </div>
                            <p class="text-2xl font-bold mb-1">{{ page.props.currency || '' }} {{ income.total_amount }}</p>
                            <p class="text-sm opacity-80">{{ income.transaction_count }} transactions</p>
                        </div>
                    </div>
                    <div v-if="incomeSummary.length === 0" class="text-center text-gray-400 py-8">
                        No income data for selected date range
                    </div>
                </div>

                <!-- Sales by Type -->
                <div class="bg-gray-800 rounded-lg p-6 shadow-lg mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-white">Sales by Type</h3>
                        <div class="flex gap-2">
                            <button 
                                @click="exportSalesPdf" 
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition flex items-center gap-2"
                            >
                                📄 Export PDF
                            </button>
                            <button 
                                @click="exportSalesExcel" 
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
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">Type</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Sales Count</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Gross Total</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Discount</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Net Total</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Returns</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Net After Returns</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Balance</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr v-for="sale in salesSummary" :key="sale.type" class="text-gray-300">
                                    <td class="px-4 py-3">
                                        <span :class="getSaleTypeColor(sale.type)" class="px-3 py-1 rounded-full text-white text-sm font-medium">
                                            {{ sale.type_name }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold">{{ sale.total_sales }}</td>
                                    <td class="px-4 py-3 text-right">{{ page.props.currency || '' }} {{ sale.gross_total }}</td>
                                    <td class="px-4 py-3 text-right text-red-400">{{ page.props.currency || '' }} {{ sale.total_discount }}</td>
                                    <td class="px-4 py-3 text-right text-green-400 font-semibold">{{ page.props.currency || '' }} {{ sale.net_total }}</td>
                                    <td class="px-4 py-3 text-right text-orange-400">{{ page.props.currency || '' }} {{ sale.total_returns }}</td>
                                    <td class="px-4 py-3 text-right text-cyan-400 font-bold">{{ page.props.currency || '' }} {{ sale.net_total_after_returns }}</td>
                                    <td class="px-4 py-3 text-right text-yellow-400">{{ page.props.currency || '' }} {{ sale.total_balance }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="salesSummary.length === 0" class="text-center text-gray-400 py-8">
                        No sales data for selected date range
                    </div>
                </div>

                <!-- Product Sales & Returns Report -->
                <div class="bg-gray-800 rounded-lg p-6 shadow-lg mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-white">Product Sales & Returns Report</h3>
                        <div class="flex gap-2">
                            <button 
                                @click="exportProductPdf" 
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition flex items-center gap-2"
                            >
                                📄 Export PDF
                            </button>
                            <button 
                                @click="exportProductExcel" 
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
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">Barcode</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">Sales Date</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Sales Qty</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Sales Amount</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">Sales Return Date</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Returns Qty</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Returns Amount</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Net Sales Qty</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Net Sales Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr v-for="product in productSalesReport" :key="product.id" class="text-gray-300">
                                    <td class="px-4 py-3 font-medium">{{ product.name }}</td>
                                    <td class="px-4 py-3 text-gray-400">{{ product.barcode }}</td>
                                    <td class="px-4 py-3">{{ product.sales_date || '-' }}</td>
                                    <td class="px-4 py-3 text-right text-blue-400 font-semibold">{{ product.sales_quantity }}</td>
                                    <td class="px-4 py-3 text-right text-green-400">{{ page.props.currency || '' }} {{ product.sales_amount }}</td>
                                    <td class="px-4 py-3">{{ product.returns_date || '-' }}</td>
                                    <td class="px-4 py-3 text-right text-orange-400 font-semibold">{{ product.returns_quantity }}</td>
                                    <td class="px-4 py-3 text-right text-red-400">{{ page.props.currency || '' }} {{ product.returns_amount }}</td>
                                    <td class="px-4 py-3 text-right text-cyan-400 font-bold">{{ product.net_sales_quantity }}</td>
                                    <td class="px-4 py-3 text-right text-green-500 font-bold">{{ page.props.currency || '' }} {{ product.net_sales_amount }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="productSalesReport.length === 0" class="text-center text-gray-400 py-8">
                        No product sales or returns data for selected date range
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
    incomeSummary: Array,
    salesSummary: Array,
    productSalesReport: Array,
    totalIncome: String,
    totalSalesCount: Number,
    startDate: String,
    endDate: String,
});

const startDate = ref(props.startDate);
const endDate = ref(props.endDate);

const page = usePage();

const netAfterReturns = computed(() => {
    const total = props.salesSummary.reduce((sum, sale) => {
        const value = parseFloat(sale.net_total_after_returns.replace(/,/g, ''));
        return sum + (isNaN(value) ? 0 : value);
    }, 0);
    return total.toFixed(2);
});

const exportPdfUrl = computed(() => {
    return route('reports.export.pdf', {
        start_date: startDate.value,
        end_date: endDate.value,
        currency: page.props.currency || ''
    });
});

const exportExcelUrl = computed(() => {
    return route('reports.export.excel', {
        start_date: startDate.value,
        end_date: endDate.value,
        currency: page.props.currency || ''
    });
});

const exportSalesPdf = async () => {
    await logActivity('create', 'sales_report', {
        action: 'export_pdf',
        start_date: startDate.value,
        end_date: endDate.value,
        report_type: 'sales_by_type'
    });
    window.location.href = exportPdfUrl.value;
};

const exportSalesExcel = async () => {
    await logActivity('create', 'sales_report', {
        action: 'export_excel',
        start_date: startDate.value,
        end_date: endDate.value,
        report_type: 'sales_by_type'
    });
    window.location.href = exportExcelUrl.value;
};

const filterReports = () => {
    router.get(route('reports.sales'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilter = () => {
    router.get(route('reports.sales'), {}, {
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

const getSaleTypeColor = (type) => {
    return type === 1 ? 'bg-blue-600' : 'bg-purple-600';
};

const exportProductPdf = async () => {
    await logActivity('create', 'sales_report', {
        action: 'export_pdf',
        start_date: startDate.value,
        end_date: endDate.value,
        report_type: 'product_sales'
    });
    window.location.href = route('reports.export.product-sales.pdf', {
        start_date: startDate.value,
        end_date: endDate.value,
        currency: page.props.currency || ''
    });
};

const exportProductExcel = async () => {
    await logActivity('create', 'sales_report', {
        action: 'export_excel',
        start_date: startDate.value,
        end_date: endDate.value,
        report_type: 'product_sales'
    });
    window.location.href = route('reports.export.product-sales.excel', {
        start_date: startDate.value,
        end_date: endDate.value,
        currency: page.props.currency || ''
    });
};
</script>
