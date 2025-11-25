<template>
  <AppLayout title="Purchase Order Requests">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-white">Purchase Order Requests</h1>
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
        >
          Add New PTR
        </button>
      </div>
 
      <div class="overflow-hidden bg-black border-4 border-blue-600 rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-blue-600">
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
                v-for="ptr in ptrs.data"
                :key="ptr.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
                <td class="px-6 py-4">
                  <span class="font-semibold">{{ ptr.transfer_no }}</span>
                </td>
                <td class="px-6 py-4">{{ formatDate(ptr.request_date) }}</td>
                <td class="px-6 py-4">{{ ptr.user?.name || 'N/A' }}</td>
                <td class="px-6 py-4">
                  <div class="text-sm">
                    <div v-if="ptr.ptr_products && ptr.ptr_products.length > 0">
                      <span v-for="(product, idx) in ptr.ptr_products.slice(0, 2)" :key="idx" class="block">
                        {{ product.product?.name }} ({{ product.requested_qty }} {{ product.measurement_unit?.code }})
                      </span>
                      <span v-if="ptr.ptr_products.length > 2" class="text-gray-400">
                        +{{ ptr.ptr_products.length - 2 }} more
                      </span>
                    </div>
                    <span v-else class="text-gray-400">No products</span>
                  </div>
                </td>
                
                <td class="px-6 py-4 text-center">
                  <select
                    :value="ptr.status"
                    @change="updateStatus(ptr, $event.target.value)"
                    :class="getStatusClass(ptr.status)"
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
                    @click="openViewModal(ptr)"
                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600 mr-2"
                  >
                    View
                  </button>
                  <button
                    @click="openEditModal(ptr)"
                    :disabled="ptr.status !== 'pending'"
                    class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700 mr-2 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Edit
                  </button>
                  <button
                    @click="openDeleteModal(ptr)"
                    :disabled="ptr.status !== 'pending'"
                    class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <tr v-if="!ptrs.data || ptrs.data.length === 0">
                <td colspan="7" class="px-6 py-4 text-center text-gray-400">
                  No Purchase Order Requests found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="ptrs.links && ptrs.links.length > 3">
          <div class="text-sm text-gray-400">
            Showing {{ ptrs.from }} to {{ ptrs.to }} of {{ ptrs.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in ptrs.links"
              :key="link.label"
              @click="link.url ? router.visit(link.url) : null"
              :disabled="!link.url"
              :class="[
                'px-3 py-1 rounded',
                link.active
                  ? 'bg-blue-600 text-white'
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
    <PtrCreateModal 
      v-model:open="isCreateModalOpen"
      :products="products"
      :measurement-units="measurementUnits"
      :users="users"
      :transferNo="transfer_no"
    />

    <!-- View Modal -->
    <PtrViewModel
      v-model:open="isViewModalOpen"
      :ptr="selectedPtr"
      v-if="selectedPtr"
    />

    <!-- Edit Modal -->
    <PtrEditModal
      v-model:open="isEditModalOpen"
      :ptr="selectedPtr"
      :users="users"
      :products="products"
      :measurement-units="measurementUnits"
      v-if="selectedPtr"
    />

    <!-- Delete Modal -->
    <PtrDeleteModal
      v-model:open="isDeleteModalOpen"
      :ptr="selectedPtr"
      v-if="selectedPtr"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import PtrCreateModal from './Components/PtrCreateModal.vue';
import PtrViewModel from './Components/PtrViewModel.vue';
import PtrEditModal from './Components/PtrEditModal.vue';
import PtrDeleteModal from './Components/PtrDeleteModal.vue';

defineProps({
    ptrs: Object,
    products: Array,
    measurementUnits: Array,
    users: Array,
    transfer_no: String
});

const isCreateModalOpen = ref(false);
const isViewModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedPtr = ref(null);

const openCreateModal = () => {
    isCreateModalOpen.value = true;
};

const openViewModal = (ptr) => {
    selectedPtr.value = ptr;
    isViewModalOpen.value = true;
};

const openEditModal = (ptr) => {
    selectedPtr.value = ptr;
    isEditModalOpen.value = true;
};

const openDeleteModal = (ptr) => {
    selectedPtr.value = ptr;
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

const updateStatus = (ptr, newStatus) => {
    router.patch(`/ptr/${ptr.id}/status`, { status: newStatus }, {
        onSuccess: () => {
            // Status updated successfully
        },
        onError: () => {
            // Error occurred, revert to previous status
        }
    });
};

const calculateTotal = (ptr) => {
    if (!ptr.ptr_products || ptr.ptr_products.length === 0) return 0;
    return ptr.ptr_products.reduce((total, item) => {
        return total + (item.requested_qty * (item.unit_price || 0));
    }, 0);
};
</script>

<style scoped>
/* Tailwind CSS handles all styling */
</style>