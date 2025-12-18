<script setup>
/**
 * Dashboard Component Script
 *
 * Main dashboard for POS system users
 * Uses AppLayout for consistent navigation
 */
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();
const pageTitle = computed(() => {
  const appName = page.props.appSettings?.app_name || "POS";
  return appName;
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
          class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2"
        >
          POS System Dashboard
        </h1>
        <p class="text-gray-600">Manage your inventory, purchases, and sales</p>
      </div>

      <!-- Inventory Section -->
      <div class="mb-10 bg-white rounded-2xl p-8 shadow-sm border border-gray-200">
        <h3
          class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b-2 border-indigo-200 flex items-center gap-3"
          v-if="[0, 1, 3].includes($page.props.auth.user.role)"
        >
          <span>📦</span> Inventory Management
        </h3>
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-5">
          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('products.index')"
            class="group bg-gradient-to-br from-gray-50 to-white hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border-2 border-gray-200 hover:border-indigo-400 transition-all duration-300 transform hover:scale-105 hover:shadow-lg"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              📦
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Products</div>
            <div class="text-sm text-gray-600">Manage products</div>
          </Link>

          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('brands.index')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              🏷️
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Brands</div>
            <div class="text-sm text-gray-600">Manage brands</div>
          </Link>

          <Link
            v-if="[0, 1, 3].includes($page.props.auth.user.role)"
            :href="route('categories.index')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              📂
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Categories</div>
            <div class="text-sm text-gray-600">Manage categories</div>
          </Link>

          <Link
            v-if="[0, 1, 3].includes($page.props.auth.user.role)"
            :href="route('types.index')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              🔹
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Types</div>
            <div class="text-sm text-gray-600">Manage types</div>
          </Link>

          <Link
            v-if="[0, 1, 3].includes($page.props.auth.user.role)"
            :href="route('measurement-units.index')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              📏
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Units</div>
            <div class="text-sm text-gray-600">Measurement units</div>
          </Link>
        </div>
      </div>

      <!-- Purchase & Stock Section -->
      <div class="mb-10 bg-white rounded-2xl p-8 shadow-sm border border-gray-200">
        <h3
          class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b-2 border-indigo-200 flex items-center gap-3"
          v-if="[0, 1].includes($page.props.auth.user.role)"
        >
          <span>🛒</span> Purchasing & Stock
        </h3>
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-5">
          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('purchase-order-requests.index')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              📋
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Purchase Orders</div>
            <div class="text-sm text-gray-600">Create & manage PORs</div>
          </Link>

          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('good-receive-notes.index')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              📦
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Goods Received</div>
            <div class="text-sm text-gray-600">Track received goods</div>
          </Link>

          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('good-receive-note-returns.index')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              📦
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">
              Goods Received Notes Return
            </div>
            <div class="text-sm text-gray-600">Track goods return notes</div>
          </Link>

          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('purchase-expenses.index')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              💸
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Expenses</div>
            <div class="text-sm text-gray-600">Manage expenses</div>
          </Link>

          <Link
            v-if="[0, 1, 3].includes($page.props.auth.user.role)"
            :href="route('suppliers.index')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              🏢
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Suppliers</div>
            <div class="text-sm text-gray-600">Manage suppliers</div>
          </Link>

          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('product-transfer-requests.index')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              📤
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">
              Product Transfer Request
            </div>
            <div class="text-sm text-gray-600">Transfer products</div>
          </Link>

          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('product-release-notes.index')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              📝
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">
              Product Release Notes
            </div>
            <div class="text-sm text-gray-600">Manage pro notes</div>
          </Link>

          <a
            v-if="[0, 1].includes($page.props.auth.user.role)"
            href="/stock-transfer-returns"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl block"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              🔄
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Stock Returns</div>
            <div class="text-sm text-gray-600">Shop → Store returns</div>
          </a>
        </div>
      </div>

      <!-- Sales Section -->
      <div class="mb-10 bg-white rounded-2xl p-8 shadow-sm border border-gray-200">
        <h3
          class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b-2 border-indigo-200 flex items-center gap-3"
        >
          <span>💰</span> Sales Management
        </h3>
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-5">
          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('customers.index')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              👥
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Customers</div>
            <div class="text-sm text-gray-600">Manage customers</div>
          </Link>

          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('discounts.index')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              🏷️
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Discounts</div>
            <div class="text-sm text-gray-600">Manage discounts</div>
          </Link>

          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('taxes.index')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              📊
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Taxes</div>
            <div class="text-sm text-gray-600">Manage tax rates</div>
          </Link>

          <Link
            v-if="[0, 1, 2].includes($page.props.auth.user.role)"
            :href="route('sales.index')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              💳
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Sales</div>
            <div class="text-sm text-gray-600">Manage sales transactions</div>
          </Link>

          <Link
            v-if="[0, 1, 2].includes($page.props.auth.user.role)"
            :href="route('sales.all')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              �
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Sales History</div>
            <div class="text-sm text-gray-600">View all sales records</div>
          </Link>

          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('return.index')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              ↩️
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Product Return</div>
            <div class="text-sm text-gray-600">Manage product returns</div>
          </Link>
        </div>
      </div>

      <!-- Report Management -->
      <div class="mb-10 bg-white rounded-2xl p-8 shadow-sm border border-gray-200">
        <h3
          class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b-2 border-indigo-200 flex items-center gap-3"
          v-if="[0, 1].includes($page.props.auth.user.role)"
        >
          <span>📊</span> Report Management
        </h3>
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-5">
          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('reports.sales')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              💰
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Sales Report</div>
            <div class="text-sm text-gray-600">Sales, income & product-wise analysis</div>
          </Link>

          <Link
            v-if="[0, 1, 3].includes($page.props.auth.user.role)"
            :href="route('reports.stock')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              📊
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Stock Report</div>
            <div class="text-sm text-gray-600">Current inventory status</div>
          </Link>

          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('reports.activity-log')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              📝
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Activity Log</div>
            <div class="text-sm text-gray-600">User activity & audit trail</div>
          </Link>

          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('reports.sync')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              🔄
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Sync Report</div>
            <div class="text-sm text-gray-600">View sync activity logs</div>
          </Link>

          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('reports.expenses')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              💸
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Expenses Report</div>
            <div class="text-sm text-gray-600">Expense details & summary</div>
          </Link>

          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('reports.income')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              💵
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Income Report</div>
            <div class="text-sm text-gray-600">Income by payment type</div>
          </Link>
          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('reports.product-release')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              📦
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Product Release</div>
            <div class="text-sm text-gray-600">Release notes report</div>
          </Link>

          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('reports.stock-transfer-return')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              🔄
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Stock Return</div>
            <div class="text-sm text-gray-600">Transfer return report</div>
          </Link>

          <Link
            v-if="[0, 1, 3].includes($page.props.auth.user.role)"
            :href="route('reports.low-stock')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              ⚠️
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Low Stock Report</div>
            <div class="text-sm text-gray-600">Products low in shop or store</div>
          </Link>
          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('reports.grn')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              📥
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">
              Goods Received Notes Report
            </div>
            <div class="text-sm text-gray-600">All inbound receipts and totals</div>
          </Link>
          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('reports.grn-returns')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              ↩️
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">
              Goods Received Note Return Report
            </div>
            <div class="text-sm text-gray-600">Returned receipts and quantities</div>
          </Link>
          <Link
            v-if="[0, 1, 3].includes($page.props.auth.user.role)"
            :href="route('reports.product-movements')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              🔀
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">
              Product Movement Report
            </div>
            <div class="text-sm text-gray-600">Track inbound/outbound stock flows</div>
          </Link>
        </div>
      </div>

      <div class="mb-10 bg-white rounded-2xl p-8 shadow-sm border border-gray-200">
        <h3
          class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b-2 border-indigo-200 flex items-center gap-3"
          v-if="[0, 1].includes($page.props.auth.user.role)"
        >
          <span>⚙️</span> System Management
        </h3>
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-5">
          <Link
            v-if="[0, 1].includes($page.props.auth.user.role)"
            :href="route('users.index')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              👤
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Users</div>
            <div class="text-sm text-gray-600">Manage system users</div>
          </Link>
        </div>
      </div>

      <!-- Settings -->
      <div
        v-if="![1, 2, 3].includes($page.props.auth.user.role)"
        class="mb-10 bg-white rounded-2xl p-8 shadow-sm border border-gray-200"
      >
        <h3
          class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b-2 border-indigo-200 flex items-center gap-3"
        >
          <span>🔧</span> Settings
        </h3>
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-5">
          <Link
            :href="route('settings.company')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              🏢
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Company Info</div>
            <div class="text-sm text-gray-600">Company information & settings</div>
          </Link>
          <Link
            :href="route('settings.app')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              ⚙️
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">App Settings</div>
            <div class="text-sm text-gray-600">
              Application preferences & configuration
            </div>
          </Link>
          <Link
            :href="route('settings.smtp')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              📧
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">SMTP Settings</div>
            <div class="text-sm text-gray-600">Email server configuration</div>
          </Link>
          <Link
            :href="route('settings.sync')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              🔄
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Sync Setting</div>
            <div class="text-sm text-gray-600">Synchronization configuration</div>
          </Link>
          <Link
            :href="route('settings.bill')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              🧾
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Bill Setting</div>
            <div class="text-sm text-gray-600">Bill logo, company info, print size</div>
          </Link>
          <Link
            :href="route('backup.settings')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              🧾
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Database Backup</div>
            <div class="text-sm text-gray-600">Bill logo, company info, print size</div>
          </Link>
          <Link
            :href="route('settings.sync')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              🔄
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Sync Setting</div>
            <div class="text-sm text-gray-600">Configure sync options</div>
          </Link>
          <Link
            :href="route('settings.bill')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
              🧾
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1">Bill Setting</div>
            <div class="text-sm text-gray-600">Configure bill options</div>
          </Link>
          <Link
            :href="route('import-export')"
            class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 p-6 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
          >
            <div
              class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300"
            >
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
  @apply transition-all duration-300 ease-in-out;
}
</style>
