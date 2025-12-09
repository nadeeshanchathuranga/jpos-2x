<template>
  <Head title="Stock Transfer Returns" />

  <AppLayout title="Stock Transfer Returns">
    <div class="min-h-screen bg-secondary p-6">
      <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-4">
              <button
                @click="$inertia.visit(route('dashboard'))"
                class="px-4 py-2 text-white bg-accent rounded hover:bg-accent/80 transition"
              >
                Back
              </button>
              <div>
                <h1 class="text-3xl font-bold text-white mb-1">🔄 Stock Transfer Returns</h1>
                <p class="text-gray-300">Return damaged products from Shop to Store</p>
              </div>
            </div>
            <div class="text-right">
              <div class="text-sm text-gray-300">Total Returns</div>
              <div class="text-2xl font-bold text-red-400">{{ stockTransferReturns.total || 0 }}</div>
            </div>
          </div>

          <!-- Tabs -->
          <div class="mb-6">
            <div class="flex space-x-1 bg-gray-800 p-1 rounded-lg w-fit">
              <button
                @click="activeTab = 'returns'"
                :class="[
                  'px-4 py-2 rounded-md text-sm font-medium transition-colors',
                  activeTab === 'returns' 
                    ? 'bg-red-600 text-white' 
                    : 'text-gray-400 hover:text-white'
                ]"
              >
                Returns List ({{ stockTransferReturns.total || 0 }})
              </button>
              <button
                @click="activeTab = 'create'"
                :class="[
                  'px-4 py-2 rounded-md text-sm font-medium transition-colors',
                  activeTab === 'create' 
                    ? 'bg-red-600 text-white' 
                    : 'text-gray-400 hover:text-white'
                ]"
              >
                Create Return
              </button>
            </div>
          </div>
        </div>

        <!-- Returns List Tab -->
        <div v-if="activeTab === 'returns'" class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
          <div class="p-4 bg-gray-900">
            <h3 class="text-lg font-semibold text-white">Stock Returns History</h3>
            <p class="text-sm text-gray-400">View all returned products from shop to store</p>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-white">
              <thead class="bg-gray-700">
                <tr>
                  <th class="px-6 py-3 font-semibold">Return No</th>
                  <th class="px-6 py-3 font-semibold">Date</th>
                  <th class="px-6 py-3 font-semibold">Products</th>
                  <th class="px-6 py-3 font-semibold">Reason</th>
                  <th class="px-6 py-3 font-semibold">User</th>
                  <th class="px-6 py-3 font-semibold text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="stockReturn in stockTransferReturns.data"
                  :key="stockReturn.id"
                  class="border-b border-gray-700 hover:bg-gray-700/50 transition"
                >
                  <td class="px-6 py-4">
                    <div class="font-medium text-red-400">{{ stockReturn.return_no }}</div>
                  </td>
                  <td class="px-6 py-4">{{ formatDate(stockReturn.return_date) }}</td>
                  <td class="px-6 py-4">
                    <div v-if="stockReturn.products && stockReturn.products.length > 0">
                      <div v-for="(item, idx) in stockReturn.products" :key="idx" class="mb-1">
                        <div class="font-medium">{{ item.product?.name || 'N/A' }}</div>
                        <div class="text-sm text-gray-400">
                          Qty: {{ item.stock_transfer_quantity }} {{ item.measurement_unit?.name || '' }}
                        </div>
                      </div>
                    </div>
                    <div v-else class="text-gray-400">No products</div>
                  </td>
                  <td class="px-6 py-4">
                    <span class="text-sm text-gray-300">{{ stockReturn.reason || 'N/A' }}</span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="font-medium">{{ stockReturn.user?.name || 'N/A' }}</div>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <span
                      class="px-3 py-1 text-xs rounded font-medium"
                      :class="{
                        'bg-yellow-600 text-white': stockReturn.status === 'pending',
                        'bg-green-600 text-white': stockReturn.status === 'completed',
                        'bg-blue-600 text-white': stockReturn.status === 'approved'
                      }"
                    >
                      {{ stockReturn.status }}
                    </span>
                  </td>
                </tr>
                <tr v-if="stockTransferReturns.data.length === 0">
                  <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                    <div class="text-6xl mb-4">📦</div>
                    <div class="text-xl font-semibold mb-2">No stock transfer returns found</div>
                    <div class="text-sm">Create a new return to get started</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="stockTransferReturns.links && stockTransferReturns.data.length > 0">
            <div class="text-sm text-gray-400">
              Showing {{ stockTransferReturns.from }} to {{ stockTransferReturns.to }} of {{ stockTransferReturns.total }} returns
            </div>
            <div class="flex space-x-2">
              <button
                v-for="(link, index) in stockTransferReturns.links"
                :key="index"
                @click="link.url ? $inertia.visit(link.url) : null"
                :disabled="!link.url"
                :class="[
                  'px-3 py-1 text-sm rounded',
                  link.active
                    ? 'bg-red-600 text-white'
                    : link.url
                    ? 'bg-gray-700 text-gray-300 hover:bg-gray-600'
                    : 'bg-gray-800 text-gray-500 cursor-not-allowed'
                ]"
                v-html="link.label"
              ></button>
            </div>
          </div>
        </div>

        <!-- Create Return Tab -->
        <div v-if="activeTab === 'create'" class="bg-gray-800 rounded-lg shadow-lg p-6">
          <div class="mb-6">
            <h3 class="text-lg font-semibold text-white mb-2">📝 Create New Stock Return</h3>
            <p class="text-sm text-gray-400">Return damaged or defective products from shop back to store</p>
          </div>
          
          <div class="bg-gray-700 rounded-lg p-6">
            <StockTransferReturnCreateModal
              :open="true"
              :products="products"
              :measurementUnits="measurementUnits"
              :users="users"
              :returnNo="returnNo"
              @close="activeTab = 'returns'"
              :inline="true"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StockTransferReturnCreateModal from './Components/StockTransferReturnCreateModal.vue';

defineProps({
  stockTransferReturns: Object,
  products: Array,
  measurementUnits: Array,
  users: Array,
  returnNo: String,
});

const activeTab = ref('returns');

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};
</script>
