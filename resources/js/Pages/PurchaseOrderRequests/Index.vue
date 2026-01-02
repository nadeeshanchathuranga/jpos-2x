<template>
  <AppLayout>
    <!-- Main Container -->
    <div class="min-h-screen bg-gray-50 p-6">
      <!-- Header Section with Navigation and Actions -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
          <!-- Back to Dashboard Button -->
          <button
            @click="$inertia.visit(route('dashboard'))"
            class="px-6 py-2.5 rounded-full font-medium text-sm bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 hover:border-gray-300 transition-all duration-200"
          >
            ← Back
          </button>
          <h1 class="text-4xl font-bold text-gray-800">Purchase Order Requests</h1>
        </div>
        <!-- Add New Button -->
        <button
          @click="openCreateModal"
          class="px-6 py-2.5 rounded-full font-medium text-sm bg-blue-600 text-white hover:bg-blue-700 transition-all duration-200"
        >
          + Add Purchase Order Request
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
                  <span :class="getStatusClass(purchaseOrderRequest.status)">
                    {{ (
                        purchaseOrderRequest.status === 'active' ? 'Active' :
                        purchaseOrderRequest.status === 'approved' ? 'Processing' :
                        purchaseOrderRequest.status === 'rejected' ? 'Completed' :
                        purchaseOrderRequest.status === 'completed' ? 'Completed' :
                        purchaseOrderRequest.status === 'inactive' ? 'Cancelled' :
                        purchaseOrderRequest.status
                    ) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center space-x-2">
                  <button
                    @click="openViewModal(purchaseOrderRequest)"
                    class="px-3 py-1 text-white bg-blue-500 rounded hover:bg-blue-600"
                  >
                    View
                  </button>
                  <button
                    v-if="purchaseOrderRequest.status === 'active'"
                    @click="cancelPurchaseOrder(purchaseOrderRequest)"
                    class="px-3 py-1 text-white bg-red-500 rounded hover:bg-red-600"
                  >
                    Cancel
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
        <div
          class="flex items-center justify-between px-6 py-4 mt-4"
          v-if="purchaseOrderRequests.links && purchaseOrderRequests.links.length > 3"
        >
          <div class="text-sm text-gray-600">
            Showing {{ purchaseOrderRequests.from }} to {{ purchaseOrderRequests.to }} of
            {{ purchaseOrderRequests.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in purchaseOrderRequests.links"
              :key="link.label"
              @click="link.url ? router.visit(link.url) : null"
              :disabled="!link.url"
              :class="[
                'px-3 py-1 rounded-lg text-sm font-medium transition-all duration-200',
                link.active
                  ? 'bg-blue-600 text-white'
                  : link.url
                  ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                  : 'bg-gray-100 text-gray-400 cursor-not-allowed',
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
      :all-products="allProducts"
      :measurementUnits="measurementUnits"
      :users="users"
      :orderNumber="orderNumber"
    />

    <!-- View Modal -->
    <PurchaseOrderRequestViewModel
      v-model:open="isViewModalOpen"
      :por="selectedPurchaseOrderRequest"
      v-if="selectedPurchaseOrderRequest"
    />

    <!-- Delete Modal -->
    <PurchaseOrderRequestDeleteModal
      v-model:open="isDeleteModalOpen"
      :por="selectedPurchaseOrderRequest"
      v-if="selectedPurchaseOrderRequest"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import { logActivity } from "@/composables/useActivityLog";
import PurchaseOrderRequestCreateModal from "./Components/PurchaseOrderRequestCreateModal.vue";
import PurchaseOrderRequestViewModel from "./Components/PurchaseOrderRequestViewModel.vue";
import PurchaseOrderRequestEditModal from "./Components/PurchaseOrderRequestEditModal.vue";

defineProps({
  purchaseOrderRequests: Object,
  products: Array,
  allProducts: Array,
  measurementUnits: Array,
  users: Array,
  orderNumber: String,
});

const isCreateModalOpen = ref(false);
const isViewModalOpen = ref(false);
const selectedPurchaseOrderRequest = ref(null);

const openCreateModal = () => {
  isCreateModalOpen.value = true;
};

const openViewModal = async (purchaseOrderRequest) => {
  selectedPurchaseOrderRequest.value = purchaseOrderRequest;
  isViewModalOpen.value = true;

  // Log view activity
  await logActivity("view", "purchase_orders", {
    order_id: purchaseOrderRequest.id,
    order_number: purchaseOrderRequest.order_number,
    order_date: purchaseOrderRequest.order_date,
    user: purchaseOrderRequest.user?.name || "N/A",
    status: purchaseOrderRequest.status,
  });
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
};

const formatNumber = (number) => {
  return parseFloat(number).toLocaleString("en-US", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const getStatusClass = (status) => {
  const classes = {
    'active': 'bg-green-500 text-white px-3 py-1 rounded',
    'approved': 'bg-yellow-500 text-white px-3 py-1 rounded',
    'processing': 'bg-yellow-500 text-white px-3 py-1 rounded',
    'completed': 'bg-blue-500 text-white px-3 py-1 rounded',
    'rejected': 'bg-blue-500 text-white px-3 py-1 rounded',
    'inactive': 'bg-gray-600 text-white px-3 py-1 rounded'
  };
  return (
    classes[status] ||
    "bg-gray-500 text-white px-4 py-1.5 rounded-full font-medium text-xs"
  );
};

const updateStatus = (purchaseOrderRequest, newStatus) => {
  router.patch(
    `/purchase-order-requests/${purchaseOrderRequest.id}/status`,
    { status: newStatus },
    {
      onSuccess: () => {
        // Status updated successfully
      },
      onError: () => {
        // Error occurred, revert to previous status
      },
    }
  );
};

const cancelPurchaseOrder = (purchaseOrderRequest) => {
    if (purchaseOrderRequest.status !== 'active') {
        alert('Only active purchase orders can be cancelled');
        return;
    }
    
    if (confirm('Are you sure you want to cancel this purchase order?')) {
        router.patch(`/purchase-order-requests/${purchaseOrderRequest.id}/status`, { status: 'inactive' }, {
            onSuccess: () => {
                // Log cancellation activity
                logActivity('cancel', 'purchase_orders', {
                    order_id: purchaseOrderRequest.id,
                    order_number: purchaseOrderRequest.order_number,
                    previous_status: 'active',
                    new_status: 'inactive'
                });
            },
            onError: (error) => {
                alert('Failed to cancel purchase order: ' + (error.message || 'Unknown error'));
            }
        });
    }
};
</script>

<style scoped>
/* Tailwind CSS handles all styling */
</style>
