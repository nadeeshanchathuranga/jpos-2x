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
                <h3 class="text-2xl font-bold text-white mb-4 pb-2 border-b border-blue-600">
                    📦 Inventory Management
                </h3>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <Link 
                        :href="route('products.index')" 
                        class="group  bg-primary hover:bg-primary p-6 rounded-lg text-bl transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📦</div>
                        <div class="font-semibold text-lg">Products</div>
                        <div class="text-sm text-black group-hover:text-black">Manage products</div>
                    </Link>
                    
                    <Link 
                        :href="route('brands.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-black transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">🏷️</div>
                        <div class="font-semibold text-lg">Brands</div>
                        <div class="text-sm text-black group-hover:text-black">Manage brands</div>
                    </Link>
                    
                    <Link 
                        :href="route('categories.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-black transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📂</div>
                        <div class="font-semibold text-lg">Categories</div>
                        <div class="text-sm text-black group-hover:text-black">Manage categories</div>
                    </Link>
                    
                    <Link 
                        :href="route('types.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-black transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">🔹</div>
                        <div class="font-semibold text-lg">Types</div>
                        <div class="text-sm text-black group-hover:text-black">Manage types</div>
                    </Link>
                    
                    <Link 
                        :href="route('measurement-units.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-black transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📏</div>
                        <div class="font-semibold text-lg">Units</div>
                        <div class="text-sm text-black group-hover:text-black">Measurement units</div>
                    </Link>
                </div>
            </div>

            <!-- Purchase & Stock Section -->
            <div class="mb-10">
                <h3 class="text-2xl font-bold text-white mb-4 pb-2 border-b border-gray-600">
                    🛒 Purchasing & Stock
                </h3>
                <div class="grid gap-4 md:grid-cols-3">
                    <Link 
                        :href="route('por.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-black transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📋</div>
                        <div class="font-semibold text-lg">Purchase Orders</div>
                        <div class="text-sm text-black group-hover:text-black">Create & manage PORs</div>
                    </Link>
                    
                    <Link 
                        :href="route('grn.index')" 
                        class="group  bg-primary hover:bg-primary p-6 rounded-lg text-black transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📦</div>
                        <div class="font-semibold text-lg">Goods Received</div>
                        <div class="text-sm text-black group-hover:text-black">Track received goods</div>
                    </Link>
                    
                    <Link 
                        :href="route('suppliers.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-black transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">🏢</div>
                        <div class="font-semibold text-lg">Suppliers</div>
                        <div class="text-sm text-black group-hover:text-black">Manage suppliers</div>
                    </Link>

                    <Link 
                        :href="route('ptr.index')" 
                        class="group  bg-primary hover:bg-primary p-6 rounded-lg text-black transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📤</div>
                        <div class="font-semibold text-lg">Product Transfer</div>
                        <div class="text-sm text-black group-hover:text-black">Transfer products</div>
                    </Link>
                </div>
            </div>

            <!-- Sales Section -->
            <div class="mb-10">
                <h3 class="text-2xl font-bold text-white mb-4 pb-2 border-b border-emerald-600">
                    💰 Sales Management
                </h3>
                <div class="grid gap-4 md:grid-cols-3">
                    <Link 
                        :href="route('customers.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-black transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">👥</div>
                        <div class="font-semibold text-lg">Customers</div>
                        <div class="text-sm text-black group-hover:text-black">Manage customers</div>
                    </Link>
                    
                    <Link 
                        :href="route('discounts.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-black transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">🏷️</div>
                        <div class="font-semibold text-lg">Discounts</div>
                        <div class="text-sm text-black group-hover:text-black">Manage discounts</div>
                    </Link>
                    
                    <Link 
                        :href="route('taxes.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-black transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">📊</div>
                        <div class="font-semibold text-lg">Taxes</div>
                        <div class="text-sm text-black group-hover:text-black">Manage tax rates</div>
                    </Link>
                </div>
            </div>
<!--    <div v-if="$page.props.auth.user.user_type >= 2"> -->
            <!-- System Management -->
            <div>
                <h3 class="text-2xl font-bold text-white mb-4 pb-2 border-b border-slate-600">
                    ⚙️ System Management
                </h3>
                <div class="grid gap-4 md:grid-cols-1 w-full md:w-1/3">
                    <Link 
                        :href="route('users.index')" 
                        class="group bg-primary hover:bg-primary p-6 rounded-lg text-black transition transform hover:scale-105 shadow-lg"
                    >
                        <div class="text-3xl mb-2">👤</div>
                        <div class="font-semibold text-lg">Users</div>
                        <div class="text-sm text-black group-hover:text-black">Manage system users</div>
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


