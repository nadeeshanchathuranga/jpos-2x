<template>
  <AppLayout title="Product Transfer Requests">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
          <button
            @click="$inertia.visit(route('dashboard'))"
            class="px-4 py-2 text-white bg-accent rounded hover:bg-accent"
          >
            Back
          </button>
          <h1 class="text-3xl font-bold text-white">Product Transfer Requests</h1>
        </div>
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-accent rounded hover:bg-accent"
        >
          Add New Transfer Request
        </button>
      </div>

      <div class="overflow-hidden bg-dark border-4 border-accent rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-accent">
              <tr>
                <th class="px-6 py-3">Transfer No</th>
                <th class="px-6 py-3">Date</th>
                <th class="px-6 py-3">User</th>
                <th class="px-6 py-3">Products</th>
                
                <th class="px-6 py-3 text-center">Status</th>
                <th class="px-6 py-3 text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="productTransferRequest in productTransferRequests.data"
                :key="productTransferRequest.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
                <td class="px-6 py-4">
                  <span class="font-semibold">{{ productTransferRequest.transfer_no }}</span>
                </td>
                <td class="px-6 py-4">{{ formatDate(productTransferRequest.request_date) }}</td>
                <td class="px-6 py-4">{{ productTransferRequest.user?.name || 'N/A' }}</td>
                <td class="px-6 py-4">
                  <div class="text-sm">
                    <div v-if="productTransferRequest.product_transfer_request_products && productTransferRequest.product_transfer_request_products.length > 0">
                      <span v-for="(product, idx) in productTransferRequest.product_transfer_request_products.slice(0, 2)" :key="idx" class="block">
                        {{ product.product?.name }} ({{ product.requested_qty }} {{ product.measurement_unit?.code }})
                      </span>
                      <span v-if="productTransferRequest.product_transfer_request_products.length > 2" class="text-gray-400">
                        +{{ productTransferRequest.product_transfer_request_products.length - 2 }} more
                      </span>
                    </div>
                    <span v-else class="text-gray-400">No products</span>
                  </div>
                </td>
                
                <td class="px-6 py-4 text-center">
                  <select
                    :value="productTransferRequest.status"
                    @change="updateStatus(productTransferRequest, $event.target.value)"
                    :class="getStatusClass(productTransferRequest.status)"
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
                    @click="openViewModal(productTransferRequest)"
                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600 mr-2"
                  >
                    View
                  </button>
                  <button
                    @click="openEditModal(productTransferRequest)"
                    :disabled="productTransferRequest.status !== 'pending'"
                    class="px-4 py-2 text-white bg-accent rounded hover:bg-accent mr-2 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Edit
                  </button>
                  <button
                    @click="openDeleteModal(productTransferRequest)"
                    :disabled="productTransferRequest.status !== 'pending'"
                    class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <tr v-if="!productTransferRequests.data || productTransferRequests.data.length === 0">
                <td colspan="7" class="px-6 py-4 text-center text-gray-400">
                  No Product Transfer Requests found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="productTransferRequests.links && productTransferRequests.links.length > 3">
          <div class="text-sm text-gray-400">
            Showing {{ productTransferRequests.from }} to {{ productTransferRequests.to }} of {{ productTransferRequests.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in productTransferRequests.links"
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
    <ProductTransferRequestCreateModal 
      v-model:open="isCreateModalOpen"
      :products="products"
      :measurement-units="measurementUnits"
      :users="users"
      :transfer-number="transferNumber"
    />

    <!-- View Modal -->
    <ProductTransferRequestViewModel
      v-model:open="isViewModalOpen"
      :product-transfer-request="selectedProductTransferRequest"
      v-if="selectedProductTransferRequest"
    />

    <!-- Edit Modal -->
    <ProductTransferRequestEditModal
      v-model:open="isEditModalOpen"
      :product-transfer-request="selectedProductTransferRequest"
      :users="users"
      :products="products"
      :measurement-units="measurementUnits"
      v-if="selectedProductTransferRequest"
    />

    <!-- Delete Modal -->
    <ProductTransferRequestDeleteModal
      v-model:open="isDeleteModalOpen"
      :product-transfer-request="selectedProductTransferRequest"
      v-if="selectedProductTransferRequest"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import ProductTransferRequestCreateModal from './Components/ProductTransferRequestCreateModal.vue';
import ProductTransferRequestViewModel from './Components/ProductTransferRequestViewModel.vue';
import ProductTransferRequestEditModal from './Components/ProductTransferRequestEditModal.vue';
import ProductTransferRequestDeleteModal from './Components/ProductTransferRequestDeleteModal.vue';

defineProps({
    productTransferRequests: Object,
    products: Array,
    measurementUnits: Array,
    users: Array,
    transferNumber: String
});

const isCreateModalOpen = ref(false);
const isViewModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedProductTransferRequest = ref(null);

const openCreateModal = () => {
    isCreateModalOpen.value = true;
};

const openViewModal = (productTransferRequest) => {
    selectedProductTransferRequest.value = productTransferRequest;
    isViewModalOpen.value = true;
};

const openEditModal = (productTransferRequest) => {
    selectedProductTransferRequest.value = productTransferRequest;
    isEditModalOpen.value = true;
};

const openDeleteModal = (productTransferRequest) => {
    selectedProductTransferRequest.value = productTransferRequest;
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

const updateStatus = (productTransferRequest, newStatus) => {
    router.patch(`/product-transfer-requests/${productTransferRequest.id}/status`, { status: newStatus }, {
        onSuccess: () => {
            // Status updated successfully
        },
        onError: () => {
            // Error occurred, revert to previous status
        }
    });
};

const calculateTotal = (productTransferRequest) => {
    if (!productTransferRequest.product_transfer_request_products || productTransferRequest.product_transfer_request_products.length === 0) return 0;
    return productTransferRequest.product_transfer_request_products.reduce((total, item) => {
        return total + (item.requested_qty * (item.unit_price || 0));
    }, 0);
};
</script>

<style scoped>
/* Tailwind CSS handles all styling */
</style>