<script setup>
/**
 * Dashboard Component Script
 *
 * Main dashboard for POS system users
 * Uses AppLayout for consistent navigation
 */
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import { computed, ref, onMounted } from "vue";

const page = usePage();
const pageTitle = computed(() => {
    const appName = page.props.appSettings?.app_name || "POS";
    return appName;
});

// Track active tab
const activeTab = ref("inventory");

// Switch tabs and persist selection
const setActiveTab = (tab) => {
    activeTab.value = tab;
    localStorage.setItem("dashboardActiveTab", tab);
};

// Load saved tab on mount
onMounted(() => {
    const savedTab = localStorage.getItem("dashboardActiveTab");
    if (savedTab) {
        activeTab.value = savedTab;
    }
});
</script>

<template>
    <!-- Page Title for Browser Tab -->

    <Head :title="pageTitle" />

    <AppLayout>
        <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 p-6">
            <!-- Header -->
            <div class="mb-8">
                <h1
                    class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
                    POS System Dashboard
                </h1>
                <p class="text-gray-600">Manage your products, stores, and shops</p>
            </div>

            <!-- Tab Navigation -->
            <div class="mb-8 flex justify-center">
                <div
                    class="inline-flex gap-2 bg-white/40 backdrop-blur-xl rounded-full p-2 shadow-2xl border border-white/60">
                    <button v-if="[0, 1, 3].includes($page.props.auth.user.role)" @click="setActiveTab('products')"
                        :class="[
                            'flex items-center gap-2 px-5 py-2.5 rounded-full font-medium text-sm transition-all duration-300',
                            activeTab === 'products'
                                ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/50 scale-105'
                                : 'bg-white/60 backdrop-blur-sm text-gray-700 hover:bg-white/80 hover:shadow-md',
                        ]">
                        <span class="text-lg">📦</span>
                        <span>Products</span>
                    </button>

                    <button v-if="[0, 1,3].includes($page.props.auth.user.role)" @click="setActiveTab('stores')" :class="[
                        'flex items-center gap-2 px-5 py-2.5 rounded-full font-medium text-sm transition-all duration-300',
                        activeTab === 'stores'
                            ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg shadow-purple-500/50 scale-105'
                            : 'bg-white/60 backdrop-blur-sm text-gray-700 hover:bg-white/80 hover:shadow-md',
                    ]">
                        <span class="text-lg">🛒</span>
                        <span>Stores</span>
                    </button>

                    <button @click="setActiveTab('sales')" :class="[
                        'flex items-center gap-2 px-5 py-2.5 rounded-full font-medium text-sm transition-all duration-300',
                        activeTab === 'sales'
                            ? 'bg-gradient-to-r from-green-500 to-teal-500 text-white shadow-lg shadow-green-500/50 scale-105'
                            : 'bg-white/60 backdrop-blur-sm text-gray-700 hover:bg-white/80 hover:shadow-md',
                    ]">
                        <span class="text-lg">💰</span>
                        <span>Shops</span>
                    </button>

                    <button v-if="[0, 1, 2, 3].includes($page.props.auth.user.role)" @click="setActiveTab('reports')"
                        :class="[
                            'flex items-center gap-2 px-5 py-2.5 rounded-full font-medium text-sm transition-all duration-300',
                            activeTab === 'reports'
                                ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-500/50 scale-105'
                                : 'bg-white/60 backdrop-blur-sm text-gray-700 hover:bg-white/80 hover:shadow-md',
                        ]">
                        <span class="text-lg">📊</span>
                        <span>Reports</span>
                    </button>

                    <button v-if="[0].includes($page.props.auth.user.role)" @click="setActiveTab('system')" :class="[
                        'flex items-center gap-2 px-5 py-2.5 rounded-full font-medium text-sm transition-all duration-300',
                        activeTab === 'system'
                            ? 'bg-gradient-to-r from-gray-600 to-gray-800 text-white shadow-lg shadow-gray-600/50 scale-105'
                            : 'bg-white/60 backdrop-blur-sm text-gray-700 hover:bg-white/80 hover:shadow-md',
                    ]">
                        <span class="text-lg">⚙️</span>
                        <span>System</span>
                    </button>

                    <button v-if="![2].includes($page.props.auth.user.role)" @click="setActiveTab('settings')"
                        :class="[
                            'flex items-center gap-2 px-5 py-2.5 rounded-full font-medium text-sm transition-all duration-300',
                            activeTab === 'settings'
                                ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white shadow-lg shadow-indigo-500/50 scale-105'
                                : 'bg-white/60 backdrop-blur-sm text-gray-700 hover:bg-white/80 hover:shadow-md',
                        ]">
                        <span class="text-lg">🔧</span>
                        <span>Settings</span>
                    </button>
                </div>
            </div>

            <!-- Products Section -->
            <div v-if="activeTab === 'products' && [0, 1, 3].includes($page.props.auth.user.role)"
                class="bg-white/40 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-white/60">
                <h3
                    class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b-2 border-white/40 flex items-center gap-3">
                    <span>📦</span> Inventory Management
                </h3>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-5">
                    <Link v-if="[0, 1].includes($page.props.auth.user.role)" :href="route('products.index')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            📦
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Products</div>
                        <div class="text-sm text-gray-600">Manage the products & information.</div>
                    </Link>

                    <Link v-if="[0, 1].includes($page.props.auth.user.role)" :href="route('brands.index')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            🏷️
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Brands</div>
                        <div class="text-sm text-gray-600">Manage brands</div>
                    </Link>

                    <Link v-if="[0, 1, 3].includes($page.props.auth.user.role)" :href="route('categories.index')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            📂
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Categories</div>
                        <div class="text-sm text-gray-600">Manage categories</div>
                    </Link>

                    <Link v-if="[0, 1, 3].includes($page.props.auth.user.role)" :href="route('types.index')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            🔹
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Types</div>
                        <div class="text-sm text-gray-600">Manage types</div>
                    </Link>

                    <Link v-if="[0, 1].includes($page.props.auth.user.role)" :href="route('measurement-units.index')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            📏
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Units</div>
                        <div class="text-sm text-gray-600">Measurement units</div>
                    </Link>

                    <Link v-if="[3].includes(Number($page.props.auth.user.role))" :href="route('suppliers.index')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">🏢</div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Suppliers</div>
                        <div class="text-sm text-gray-600">Manage suppliers</div>
                    </Link>






                </div>
            </div>


            <!-- Stores Section -->
            <div v-if="activeTab === 'stores' && [0, 1,3, 3].includes($page.props.auth.user.role)"
                class="bg-white/40 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-white/60">
                <h3
                    class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b-2 border-white/40 flex items-center gap-3">
                    <span>🛒</span> Stores
                </h3>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-5">


                    <Link v-if="[0, 1,3].includes($page.props.auth.user.role)"
                        :href="route('purchase-order-requests.index')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            📋
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Purchase Orders</div>
                        <div class="text-sm text-gray-600">Manage the purchase orders</div>
                    </Link>

                    <Link v-if="[0, 1,3].includes($page.props.auth.user.role)" :href="route('good-receive-notes.index')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            📦
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Goods Received Notes</div>
                        <div class="text-sm text-gray-600">Track the received goods from the purchase orders.</div>
                    </Link>

                    <Link v-if="[0, 1,3].includes($page.props.auth.user.role)"
                        :href="route('good-receive-note-returns.index')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            📦
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">
                            Goods Return Notes
                        </div>
                        <div class="text-sm text-gray-600">Track the return goods from the purchase orders.</div>
                    </Link>

                    <Link v-if="[0, 1,3].includes($page.props.auth.user.role)" :href="route('purchase-expenses.index')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            💸
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Supplier Payments</div>
                        <div class="text-sm text-gray-600">Track the supplier payments</div>
                    </Link>

                    <Link v-if="[0, 1,3].includes($page.props.auth.user.role)"
                        :href="route('product-release-notes.index')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            📝
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">
                            Goods Transfer Release Notes
                        </div>
                        <div class="text-sm text-gray-600">Manage goods transfers from stores to shop.</div>
                    </Link>

                </div>
            </div>

            <!-- Sales Section -->
            <div v-if="activeTab === 'sales'"
                class="bg-white/40 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-white/60">
                <h3
                    class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b-2 border-white/40 flex items-center gap-3">
                    <span>💰</span> Sales Management
                </h3>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-5">
                    <Link v-if="[0, 1].includes($page.props.auth.user.role)" :href="route('customers.index')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            👥
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Customers</div>
                        <div class="text-sm text-gray-600">Manage customers</div>
                    </Link>

                    <Link v-if="[0, 1].includes($page.props.auth.user.role)" :href="route('discounts.index')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            🏷️
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Discounts</div>
                        <div class="text-sm text-gray-600">Manage discounts</div>
                    </Link>

                    <Link v-if="[0, 1].includes($page.props.auth.user.role)" :href="route('taxes.index')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            📊
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Taxes</div>
                        <div class="text-sm text-gray-600">Manage tax rates</div>
                    </Link>

                    <Link v-if="[0, 1, 2].includes($page.props.auth.user.role)" :href="route('sales.index')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            💳
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Sales</div>
                        <div class="text-sm text-gray-600">Manage sales transactions</div>
                    </Link>

                    <Link v-if="[0, 1, 2].includes($page.props.auth.user.role)" :href="route('sales.all')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            �
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Sales History</div>
                        <div class="text-sm text-gray-600">View all sales records</div>
                    </Link>

                    <Link v-if="[0, 1, 2].includes($page.props.auth.user.role)" :href="route('quotations.index')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-green-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            📄
                        </div>

                        <div class="font-semibold text-lg text-gray-800 mb-1">
                            Quotations
                        </div>

                        <div class="text-sm text-gray-600">
                            Create and manage quotations
                        </div>
                    </Link>

                    <Link
    v-if="[0, 1, 2].includes($page.props.auth.user.role)"
    :href="route('quotation.edit')"
    class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-green-500/20"
>
    <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
        📄
    </div>

    <div class="font-semibold text-lg text-gray-800 mb-1">
        View Quotations
    </div>

    <div class="text-sm text-gray-600">
        Open and review quotation records
    </div>
</Link>

          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('product-transfer-requests.index')"
            class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              📤
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">
              Goods Transfer Request
            </div>
            <div class="text-sm text-gray-600">Manage the goods transfer request from shop.</div>
          </Link>

                    <a v-if="[0, 1].includes($page.props.auth.user.role)" href="/stock-transfer-returns"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20 block">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            🔄
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Goods Transfer Returns</div>
                        <div class="text-sm text-gray-600">Manage the transfer from shop to store.</div>
                    </a>

                    <Link v-if="[0, 1].includes($page.props.auth.user.role)" :href="route('return.index')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            ↩️
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Sales Return</div>
                        <div class="text-sm text-gray-600">Manage sales returns</div>
                    </Link>
                </div>
            </div>

            <!-- Report Management -->
            <div v-if="activeTab === 'reports' && [0, 1, 2, 3].includes($page.props.auth.user.role)"
                class="bg-white/40 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-white/60">
                <h3
                    class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b-2 border-white/40 flex items-center gap-3">
                    <span>📊</span> Report Management
                </h3>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-5">
                    <Link v-if="[0, 1, 3].includes($page.props.auth.user.role)" :href="route('reports.low-stock')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            ⚠️
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Low Stock Report</div>
                        <div class="text-sm text-gray-600">Products low in shop or store</div>
                    </Link>
                    <Link v-if="[0, 1, 2].includes($page.props.auth.user.role)" :href="route('reports.sales')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            💰
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Sales Report</div>
                        <div class="text-sm text-gray-600">Sales, income & product-wise analysis</div>
                    </Link>


                    <Link v-if="[0, 1, 2, 3].includes($page.props.auth.user.role)" :href="route('reports.stock')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            📊
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Stock Report</div>
                        <div class="text-sm text-gray-600">Current inventory status</div>
                    </Link>

                    <Link v-if="[0, 1, 3].includes($page.props.auth.user.role)" :href="route('reports.expenses')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            💸
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Supplier Payment Report</div>
                        <div class="text-sm text-gray-600">Supplier payment details & summary</div>
                    </Link>

                    <Link v-if="[0, 1, 2].includes($page.props.auth.user.role)" :href="route('reports.income')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            💵
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Sales Income Report</div>
                        <div class="text-sm text-gray-600">Income by payment type</div>
                    </Link>
                    <Link v-if="[0, 1, 3].includes($page.props.auth.user.role)" :href="route('reports.product-release')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            📦
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Goods Transfer Report</div>
                        <div class="text-sm text-gray-600">Release notes report</div>
                    </Link>

                    <Link v-if="[0, 1, 3].includes($page.props.auth.user.role)"
                        :href="route('reports.stock-transfer-return')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            🔄
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Goods Transfer Return Report</div>
                        <div class="text-sm text-gray-600">Transfer return report</div>
                    </Link>


                    <Link v-if="[0, 1, 3].includes($page.props.auth.user.role)" :href="route('reports.grn')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            📥
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">
                            Goods Received Report
                        </div>
                        <div class="text-sm text-gray-600">All inbound receipts and totals</div>
                    </Link>
                    <Link v-if="[0, 1, 3].includes($page.props.auth.user.role)" :href="route('reports.grn-returns')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            ↩️
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">
                            Goods Return Report
                        </div>
                        <div class="text-sm text-gray-600">Returned receipts and quantities</div>
                    </Link>
                    <Link v-if="[0, 1, 3].includes($page.props.auth.user.role)"
                        :href="route('reports.product-movements')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            🔀
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">
                            Product Movement Report
                        </div>
                        <div class="text-sm text-gray-600">Track inbound/outbound stock flows</div>
                    </Link>
                    <Link v-if="[0].includes($page.props.auth.user.role)" :href="route('reports.activity-log')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            📝
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Activity Log</div>
                        <div class="text-sm text-gray-600">User activity & audit trail</div>
                    </Link>

                    <!-- <Link
            v-if="[0].includes($page.props.auth.user.role)"
            :href="route('reports.sync')"
            class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              🔄
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Sync Report</div>
            <div class="text-sm text-gray-600">View sync activity logs</div>
          </Link> -->
                </div>
            </div>

            <!-- System Management -->
            <div v-if="activeTab === 'system' && [0, 1].includes($page.props.auth.user.role)"
                class="bg-white/40 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-white/60">
                <h3
                    class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b-2 border-white/40 flex items-center gap-3">
                    <span>⚙️</span> System Management
                </h3>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-5">
                    <Link v-if="[0, 1].includes($page.props.auth.user.role)" :href="route('users.index')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            👤
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Users</div>
                        <div class="text-sm text-gray-600">Manage system users</div>
                    </Link>
                </div>
            </div>

            <!-- Settings -->
            <div v-if="activeTab === 'settings' && ![1, 2].includes($page.props.auth.user.role)"
                class="bg-white/40 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-white/60">
                <h3
                    class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b-2 border-white/40 flex items-center gap-3">
                    <span>🔧</span> Settings
                </h3>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-5">
                    <Link :href="route('settings.company')"
                    v-if="$page.props.auth.user.role !== 3"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            🏢
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Company Info</div>
                        <div class="text-sm text-gray-600">Company information & settings</div>
                    </Link>
                    <Link :href="route('settings.app')"
                    v-if="$page.props.auth.user.role !== 3"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            ⚙️
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">App Settings</div>
                        <div class="text-sm text-gray-600">
                            Application preferences & configuration
                        </div>
                    </Link>
                    <!-- <Link
            :href="route('settings.smtp')"
            class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              📧
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">SMTP Settings</div>
            <div class="text-sm text-gray-600">Email server configuration</div>
          </Link>-->
          <Link
            :href="route('settings.sync')"
            class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              🔄
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Sync Setting</div>
            <div class="text-sm text-gray-600">Synchronization configuration</div>
          </Link>
          <!-- <Link
            :href="route('settings.bill')"
            class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              🧾
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Bill Setting</div>
            <div class="text-sm text-gray-600">Bill logo, company info, print size</div>
          </Link> -->
          <Link
                    v-if="![1].includes($page.props.auth.user.role)"
            :href="route('backup.settings')"
            class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              🧾
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Database Backup</div>
            <div class="text-sm text-gray-600">Bill logo, company info, print size</div>
          </Link>
          <!-- <Link
                    <Link :href="route('backup.settings')"
                    v-if="$page.props.auth.user.role !== 3"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            🧾
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Database Backup</div>
                        <div class="text-sm text-gray-600">Bill logo, company info, print size</div>
                    </Link>
                    <!-- <Link
            :href="route('settings.sync')"
            class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              🔄
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Sync Setting</div>
            <div class="text-sm text-gray-600">Configure sync options</div>
          </Link> -->
                    <Link :href="route('settings.bill')"
                    v-if="$page.props.auth.user.role !== 3"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            🧾
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Bill Setting</div>
                        <div class="text-sm text-gray-600">Configure bill options</div>
                    </Link>
                    <Link :href="route('import-export')"
                        class="group bg-white/60 backdrop-blur-md hover:bg-white/80 p-6 rounded-2xl border border-white/60 hover:border-white/90 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">
                            🔄
                        </div>
                        <div class="font-semibold text-lg text-gray-800 mb-1">Import & Export</div>
                        <div class="text-sm text-gray-600">Manage data import and export</div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Smooth transitions */
a {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}
</style>
