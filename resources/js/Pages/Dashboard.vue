
<script setup>
/**
 * Dashboard Component Script
 * 
 * Main dashboard for POS system users
 * Uses AppLayout for consistent navigation
 */
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const pageTitle = computed(() => {
    const appName = page.props.appSettings?.app_name || 'POS';
    return appName;
});
</script>

<template>
    <!-- Page Title for Browser Tab -->
    <Head :title="pageTitle" />

    <AppLayout>
        <div class="min-h-screen bg-secondary p-6">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-white mb-2">POS System Dashboard</h1>
                <p class="text-white">Manage your inventory, purchases, and sales</p>
            </div>

            <!-- Inventory Section -->
            <div class="mb-10">
                <h3 class="text-2xl font-bold text-white mb-4 pb-2 border-b border-gray-600">
                    📦 Inventory Management
                </h3>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <Link 
                        :href="route('products.index')" 
                        class="group  bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📦</div>
                        <div class="font-semibold text-lg">Products</div>
                        <div class="text-sm text-white group-hover:text-white">Manage products</div>
                    </Link>
                    
                    <Link 
                        :href="route('brands.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">🏷️</div>
                        <div class="font-semibold text-lg">Brands</div>
                        <div class="text-sm text-white group-hover:text-white">Manage brands</div>
                    </Link>
                    
                    <Link 
                        :href="route('categories.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📂</div>
                        <div class="font-semibold text-lg">Categories</div>
                        <div class="text-sm text-white group-hover:text-white">Manage categories</div>
                    </Link>
                    
                    <Link 
                        :href="route('types.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">🔹</div>
                        <div class="font-semibold text-lg">Types</div>
                        <div class="text-sm text-white group-hover:text-white">Manage types</div>
                    </Link>
                    
                    <Link 
                        :href="route('measurement-units.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📏</div>
                        <div class="font-semibold text-lg">Units</div>
                        <div class="text-sm text-white group-hover:text-white">Measurement units</div>
                    </Link>
                </div>
            </div>

            <!-- Purchase & Stock Section -->
            <div class="mb-10">
                <h3 class="text-2xl font-bold text-white mb-4 pb-2 border-b border-gray-600">
                    🛒 Purchasing & Stock
                </h3>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <Link 
                        :href="route('purchase-order-requests.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📋</div>
                        <div class="font-semibold text-lg">Purchase Orders</div>
                        <div class="text-sm text-white group-hover:text-white">Create & manage PORs</div>
                    </Link>
                    
                    <Link 
                        :href="route('goods-received-notes.index')" 
                        class="group  bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📦</div>
                        <div class="font-semibold text-lg">Goods Received</div>
                        <div class="text-sm text-white group-hover:text-white">Track received goods</div>
                    </Link>
                    
                    <Link 
                        :href="route('purchase-expenses.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">💸</div>
                        <div class="font-semibold text-lg">Expenses</div>
                        <div class="text-sm text-white group-hover:text-white">Manage expenses</div>
                    </Link>
                    
                    <Link 
                        :href="route('suppliers.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">🏢</div>
                        <div class="font-semibold text-lg">Suppliers</div>
                        <div class="text-sm text-white group-hover:text-white">Manage suppliers</div>
                    </Link>

                    <Link 
                        :href="route('product-transfer-requests.index')" 
                        class="group  bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📤</div>
                        <div class="font-semibold text-lg">Product Transfer Request</div>
                        <div class="text-sm text-white group-hover:text-white">Transfer products</div>
                    </Link>

                    <Link 
                        :href="route('product-release-notes.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📝</div>
                        <div class="font-semibold text-lg">Product Release Notes</div>
                        <div class="text-sm text-white group-hover:text-white">Manage pro notes</div>
                    </Link>
                </div>
            </div>

            <!-- Sales Section -->
            <div class="mb-10">
                <h3 class="text-2xl font-bold text-white mb-4 pb-2 border-b border-gray-600">
                    💰 Sales Management
                </h3>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <Link 
                        :href="route('customers.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">👥</div>
                        <div class="font-semibold text-lg">Customers</div>
                        <div class="text-sm text-white group-hover:text-white">Manage customers</div>
                    </Link>
                    
                    <Link 
                        :href="route('discounts.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">🏷️</div>
                        <div class="font-semibold text-lg">Discounts</div>
                        <div class="text-sm text-white group-hover:text-white">Manage discounts</div>
                    </Link>
                    
                    <Link 
                        :href="route('taxes.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📊</div>
                        <div class="font-semibold text-lg">Taxes</div>
                        <div class="text-sm text-white group-hover:text-white">Manage tax rates</div>
                    </Link>
 
                     <Link 
                        :href="route('sales.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">💳</div>
                        <div class="font-semibold text-lg">Sales</div>
                        <div class="text-sm text-white group-hover:text-white">Manage sales transactions</div>
                    </Link>

                    <Link 
                        :href="route('return.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">↩️</div>
                        <div class="font-semibold text-lg">Product Return</div>
                        <div class="text-sm text-white group-hover:text-white">Manage product returns</div>
                    </Link>
                </div>
            </div>

            <!-- Report Management -->
            <div class="mb-10">
                <h3 class="text-2xl font-bold text-white mb-4 pb-2 border-b border-gray-600">
                    📊 Report Management
                </h3>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <Link 
                        :href="route('reports.sales')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">💰</div>
                        <div class="font-semibold text-lg">Sales Report</div>
                        <div class="text-sm text-white group-hover:text-white">Sales, income & product-wise analysis</div>
                    </Link>

                    <Link 
                        :href="route('reports.stock')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📊</div>
                        <div class="font-semibold text-lg">Stock Report</div>
                        <div class="text-sm text-white group-hover:text-white">Current inventory status</div>
                    </Link>

                    <Link 
                        :href="route('reports.expenses')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">💸</div>
                        <div class="font-semibold text-lg">Expenses Report</div>
                        <div class="text-sm text-white group-hover:text-white">Expense details & summary</div>
                    </Link>

                    <Link 
                        :href="route('reports.income')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">💵</div>
                        <div class="font-semibold text-lg">Income Report</div>
                        <div class="text-sm text-white group-hover:text-white">Income by payment type</div>
                    </Link>
                </div>
            </div>
<!--    <div v-if="$page.props.auth.user.user_type >= 2"> -->
            <!-- System Management -->
            <div class="mb-10">
                <h3 class="text-2xl font-bold text-white mb-4 pb-2 border-b border-slate-600">
                    ⚙️ System Management
                </h3>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <Link 
                        :href="route('users.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">👤</div>
                        <div class="font-semibold text-lg">Users</div>
                        <div class="text-sm text-white group-hover:text-white">Manage system users</div>
                    </Link>
                </div>
            </div>

            <!-- Settings -->
            <div>
                <h3 class="text-2xl font-bold text-white mb-4 pb-2 border-b border-slate-600">
                    🔧 Settings
                </h3>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <Link 
                        :href="route('settings.company')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">🏢</div>
                        <div class="font-semibold text-lg">Company Info</div>
                        <div class="text-sm text-white group-hover:text-white">Company information & settings</div>
                    </Link>
                    <Link 
                        :href="route('settings.app')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">⚙️</div>
                        <div class="font-semibold text-lg">App Settings</div>
                        <div class="text-sm text-white group-hover:text-white">Application preferences & configuration</div>
                    </Link>
                    <Link 
                        :href="route('settings.smtp')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📧</div>
                        <div class="font-semibold text-lg">SMTP Settings</div>
                        <div class="text-sm text-white group-hover:text-white">Email server configuration</div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Smooth transitions */
a {
    @apply transition-all duration-300 ease-in-out;
}
</style>


