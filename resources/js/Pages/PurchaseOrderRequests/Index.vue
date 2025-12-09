<template>
  <AppLayout title="Purchase Order Requests">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
          <button
            @click="$inertia.visit(route('dashboard'))"
            class="px-4 py-2 text-white bg-accent rounded hover:bg-accent"
          >
            Back
          </button>
          <h1 class="text-3xl font-bold text-white">Purchase Order Requests</h1>
        </div>
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-accent rounded hover:bg-accent"
        >
          Add New Purchase Order Request
        </button>
      </div>

      <div class="overflow-hidden bg-dark border-4 border-accent rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-accent">
              <tr>
                <th class="px-6 py-3">Order Number</th>
                <th class="px-6 py-3">Date</th>
                <th class="px-6 py-3">User</th> 
                <th class="px-6 py-3 text-center">Status</th>
                <th class="px-6 py-3 text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="purchaseOrderRequest in purchaseOrderRequests.data"
                :key="purchaseOrderRequest.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
                <td class="px-6 py-4">
                  <span class="font-semibold">{{ purchaseOrderRequest.order_number }}</span>
                </td>
                <td class="px-6 py-4">{{ formatDate(purchaseOrderRequest.order_date) }}</td>
                <td class="px-6 py-4">{{ purchaseOrderRequest.user?.name || 'N/A' }}</td>
                <td class="px-6 py-4 text-center">
                  <select
                    :value="purchaseOrderRequest.status"
                    @change="updateStatus(purchaseOrderRequest, $event.target.value)"
                    :class="getStatusClass(purchaseOrderRequest.status)"
                    class="px-2 py-1 rounded text-white cursor-pointer"
                  >
                    <option value="pending">PENDING</option>
                    <option value="approved">APPROVED</option>
                    <option value="rejected">REJECTED</option>
                    <option value="completed">COMPLETED</option>
                  </select>
                </td>
                <td class="px-6 py-4 text-center">
                  <button
                    @click="openViewModal(purchaseOrderRequest)"
                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600 mr-2"
                  >
                    View
                  </button>
                  <button
                    @click="openEditModal(purchaseOrderRequest)"
                    :disabled="purchaseOrderRequest.status !== 'pending'"
                    class="px-4 py-2 text-white bg-accent rounded hover:bg-accent mr-2 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Edit
                  </button>
                  <button
                    @click="openDeleteModal(purchaseOrderRequest)"
                    :disabled="purchaseOrderRequest.status !== 'pending'"
                    class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <tr v-if="!purchaseOrderRequests.data || purchaseOrderRequests.data.length === 0">
                <td colspan="5" class="px-6 py-4 text-center text-gray-400">
                  No Purchase Order Requests found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="purchaseOrderRequests.links && purchaseOrderRequests.links.length > 3">
          <div class="text-sm text-gray-400">
            Showing {{ purchaseOrderRequests.from }} to {{ purchaseOrderRequests.to }} of {{ purchaseOrderRequests.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in purchaseOrderRequests.links"
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
    <PurchaseOrderRequestCreateModal 
      v-model:open="isCreateModalOpen"
      :products="products"
      :measurementUnits="measurementUnits"
      :users="users"
      :orderNumber="orderNumber"
    />

    <!-- View Modal -->
    <PurchaseOrderRequestViewModel
      v-model:open="isViewModalOpen"
      :purchase-order-request="selectedPurchaseOrderRequest"
      v-if="selectedPurchaseOrderRequest"
    />

    <!-- Edit Modal -->
    <PurchaseOrderRequestEditModal
      v-model:open="isEditModalOpen"
      :purchase-order-request="selectedPurchaseOrderRequest"
      :users="users"
      :products="products"
      :measurement-units="measurementUnits"
      v-if="selectedPurchaseOrderRequest"
    />

    <!-- Delete Modal -->
    <PurchaseOrderRequestDeleteModal
      v-model:open="isDeleteModalOpen"
      :purchase-order-request="selectedPurchaseOrderRequest"
      v-if="selectedPurchaseOrderRequest"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import PurchaseOrderRequestCreateModal from './Components/PurchaseOrderRequestCreateModal.vue';
import PurchaseOrderRequestViewModel from './Components/PurchaseOrderRequestViewModel.vue';
import PurchaseOrderRequestEditModal from './Components/PurchaseOrderRequestEditModal.vue';
import PurchaseOrderRequestDeleteModal from './Components/PurchaseOrderRequestDeleteModal.vue';

defineProps({
    purchaseOrderRequests: Object,
    products: Array,
    measurementUnits: Array,
    users: Array,
    orderNumber: String
});

const isCreateModalOpen = ref(false);
const isViewModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedPurchaseOrderRequest = ref(null);

const openCreateModal = () => {
    isCreateModalOpen.value = true;
};

const openViewModal = (purchaseOrderRequest) => {
    selectedPurchaseOrderRequest.value = purchaseOrderRequest;
    isViewModalOpen.value = true;
};

const openEditModal = (purchaseOrderRequest) => {
    selectedPurchaseOrderRequest.value = purchaseOrderRequest;
    isEditModalOpen.value = true;
};

const openDeleteModal = (purchaseOrderRequest) => {
    selectedPurchaseOrderRequest.value = purchaseOrderRequest;
    isDeleteModalOpen.value = true;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

const formatNumber = (number) => {
    return parseFloat(number).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const getStatusClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-500 text-white px-3 py-1 rounded',
        'approved': 'bg-green-500 text-white px-3 py-1 rounded',
        'rejected': 'bg-red-500 text-white px-3 py-1 rounded',
        'completed': 'bg-blue-500 text-white px-3 py-1 rounded'
    };
    return classes[status] || 'bg-gray-500 text-white px-3 py-1 rounded';
};

const updateStatus = (purchaseOrderRequest, newStatus) => {
    router.patch(`/purchase-order-requests/${purchaseOrderRequest.id}/status`, { status: newStatus }, {
        onSuccess: () => {
            // Status updated successfully
        },
        onError: () => {
            // Error occurred, revert to previous status
        }
    });
};
</script>

<style scoped>
/* Tailwind CSS handles all styling */
</style>