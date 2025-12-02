<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="POS Dashboard" />

    <AuthenticatedLayout>
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
                        :href="route('por.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📋</div>
                        <div class="font-semibold text-lg">Purchase Orders</div>
                        <div class="text-sm text-white group-hover:text-white">Create & manage PORs</div>
                    </Link>
                    
                    <Link 
                        :href="route('grn.index')" 
                        class="group  bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📦</div>
                        <div class="font-semibold text-lg">Goods Received</div>
                        <div class="text-sm text-white group-hover:text-white">Track received goods</div>
                    </Link>
                    
                    <Link 
                        :href="route('expenses.index')" 
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
                        :href="route('ptr.index')" 
                        class="group  bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📤</div>
                        <div class="font-semibold text-lg">Product Transfer</div>
                        <div class="text-sm text-white group-hover:text-white">Transfer products</div>
                    </Link>

                    <Link 
                        :href="route('prn.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-white transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📝</div>
                        <div class="font-semibold text-lg">Pro Notes</div>
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
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Smooth transitions */
a {
    @apply transition-all duration-300 ease-in-out;
}
</style>


