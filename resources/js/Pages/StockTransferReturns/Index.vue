<template>
  <Head title="Stock Transfer Returns" />

  <AppLayout title="Stock Transfer Returns">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
          <button
            @click="$inertia.visit(route('dashboard'))"
            class="px-4 py-2 text-white bg-accent rounded hover:bg-accent"
          >
            Back
          </button>
          <h1 class="text-3xl font-bold text-white">Stock Transfer Returns</h1>
        </div>
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-accent rounded hover:bg-accent"
        >
          Add New Stock Transfer Return
        </button>
      </div>

      <div class="overflow-hidden bg-dark border-4 border-accent rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-accent">
              <tr>
                <th class="px-6 py-3">Return Number</th>
                <th class="px-6 py-3">Date</th>
                <th class="px-6 py-3">User</th>
                <th class="px-6 py-3 text-center">Status</th>
                <th class="px-6 py-3 text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="stockReturn in stockTransferReturns.data"
                :key="stockReturn.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
                <td class="px-6 py-4">
                  <span class="font-semibold">{{ stockReturn.return_no }}</span>
                </td>
                <td class="px-6 py-4">{{ formatDate(stockReturn.return_date) }}</td>
                <td class="px-6 py-4">{{ stockReturn.user?.name || 'N/A' }}</td>
                <td class="px-6 py-4 text-center">
                  <select
                    :value="stockReturn.status"
                    @change="updateStatus(stockReturn, $event.target.value)"
                    :class="getStatusClass(stockReturn.status)"
                    class="px-2 py-1 rounded text-white cursor-pointer"
                  >
                    <option value="pending">PENDING</option>
                    <option value="approved">APPROVED</option>
                    <option value="completed">COMPLETED</option>
                  </select>
                </td>
                <td class="px-6 py-4 text-center">
                  <button
                    @click="openViewModal(stockReturn)"
                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600 mr-2"
                  >
                    View
                  </button>
                  <button
                    @click="openDeleteModal(stockReturn)"
                    :disabled="stockReturn.status !== 'pending'"
                    class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <tr v-if="!stockTransferReturns.data || stockTransferReturns.data.length === 0">
                <td colspan="5" class="px-6 py-4 text-center text-gray-400">
                  No Stock Transfer Returns found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="stockTransferReturns.links && stockTransferReturns.links.length > 3">
          <div class="text-sm text-gray-400">
            Showing {{ stockTransferReturns.from }} to {{ stockTransferReturns.to }} of {{ stockTransferReturns.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in stockTransferReturns.links"
              :key="link.label"
              @click="link.url ? router.visit(link.url) : null"
              :disabled="!link.url"
              :class="[
                'px-3 py-1 rounded',
                link.active
                  ? 'bg-accent text-white'
                  : link.url
                  ? 'bg-gray-700 text-white hover:bg-gray-600'
                  : 'bg-gray-800 text-gray-500 cursor-not-allowed'
              ]"
              v-html="link.label"
            ></button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Modal -->
    <StockTransferReturnCreateModal 
      v-model:open="isCreateModalOpen"
      :products="products"
      :measurementUnits="measurementUnits"
      :users="users"
      :returnNo="returnNo"
    />

    <!-- View Modal -->
    <StockTransferReturnViewModel
      v-model:open="isViewModalOpen"
      :stock-transfer-return="selectedStockReturn"
      v-if="selectedStockReturn"
    />



    <!-- Delete Modal -->
    <StockTransferReturnDeleteModal
      v-model:open="isDeleteModalOpen"
      :stock-transfer-return="selectedStockReturn"
      v-if="selectedStockReturn"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StockTransferReturnCreateModal from './Components/StockTransferReturnCreateModal.vue';
import StockTransferReturnViewModel from './Components/StockTransferReturnViewModel.vue';
import StockTransferReturnDeleteModal from './Components/StockTransferReturnDeleteModal.vue';

defineProps({
  stockTransferReturns: Object,
  products: Array,
  measurementUnits: Array,
  users: Array,
  returnNo: String,
});

const isCreateModalOpen = ref(false);
const isViewModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedStockReturn = ref(null);

const openCreateModal = () => {
  isCreateModalOpen.value = true;
};

const openViewModal = (stockReturn) => {
  selectedStockReturn.value = stockReturn;
  isViewModalOpen.value = true;
};

const openDeleteModal = (stockReturn) => {
  selectedStockReturn.value = stockReturn;
  isDeleteModalOpen.value = true;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  });
};

const getStatusClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-500 text-white px-3 py-1 rounded',
    'approved': 'bg-green-500 text-white px-3 py-1 rounded',
    'completed': 'bg-blue-500 text-white px-3 py-1 rounded'
  };
  return classes[status] || 'bg-gray-500 text-white px-3 py-1 rounded';
};

const updateStatus = (stockReturn, newStatus) => {
  router.patch(`/stock-transfer-returns/${stockReturn.id}/status`, { status: newStatus }, {
    onSuccess: () => {
      // Status updated successfully
    },
    onError: () => {
      // Error occurred
    }
  });
};
</script>
