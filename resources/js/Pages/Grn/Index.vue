<template>
  <AppLayout title="Goods Received Notes">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
          <button
            @click="$inertia.visit(route('dashboard'))"
            class="px-4 py-2 text-white bg-accent rounded hover:bg-accent"
          >
            Back
          </button>
          <h1 class="text-3xl font-bold text-white">Goods Received Notes</h1>
        </div>
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-accent rounded hover:bg-accent"
        >
          Add New GRN
        </button>
      </div>

      <div class="overflow-hidden bg-dark border-4 border-accent rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-accent">
              <tr>
                <th class="px-6 py-3">GRN Number</th>
                <th class="px-6 py-3">Supplier</th>
                <th class="px-6 py-3">Date</th>
                <th class="px-6 py-3">Products</th>
                <th class="px-6 py-3">Discount</th>
                <th class="px-6 py-3">Tax</th>
                <th class="px-6 py-3 text-center">Status</th>
                <th class="px-6 py-3 text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="grn in grns.data"
                :key="grn.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
                <td class="px-6 py-4">
                  <span class="font-semibold">{{ grn.grn_no }}</span>
                </td>
                <td class="px-6 py-4">{{ grn.supplier?.name || 'N/A' }}</td>
                <td class="px-6 py-4">{{ formatDate(grn.grn_date) }}</td>
                <td class="px-6 py-4">
                  <span class="text-sm">{{ grn.grn_products?.length || 0 }} items</span>
                </td>
                <td class="px-6 py-4">Rs. {{ formatNumber(grn.discount) }}</td>
                <td class="px-6 py-4">Rs. {{ formatNumber(grn.tax_total) }}</td>
                <td class="px-6 py-4 text-center">
                  <select
                    :value="grn.status"
                    @change="updateStatus(grn, $event.target.value)"
                    :class="getStatusClass(grn.status)"
                    class="px-2 py-1 rounded text-white cursor-pointer"
                  >
                    <option value="0">INACTIVE</option>
                    <option value="1">ACTIVE</option>
                    <option value="2">DEFAULT</option>
                  </select>
                </td>
                <td class="px-6 py-4 text-center">
                  <button
                    @click="openViewModal(grn)"
                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600 mr-2"
                  >
                    View
                  </button>
                  <button
                    @click="openEditModal(grn)"
                    class="px-4 py-2 text-white bg-accent rounded hover:bg-accent mr-2"
                  >
                    Edit
                  </button>
                  <button
                    @click="openDeleteModal(grn)"
                    class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <tr v-if="!grns.data || grns.data.length === 0">
                <td colspan="8" class="px-6 py-4 text-center text-gray-400">
                  No Goods Received Notes found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="grns.links && grns.links.length > 3">
          <div class="text-sm text-gray-400">
            Showing {{ grns.from }} to {{ grns.to }} of {{ grns.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in grns.links"
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
    <GrnCreateModal 
      v-model:open="isCreateModalOpen"
      :suppliers="suppliers"
      :purchase-orders="purchaseOrders"
      :products="products"
      :available-products="availableProducts"
      :grnNumber="grnNumber"
      :measurementUnits="measurementUnits"
    />

    <!-- View Modal -->
    <GrnViewModel
      v-model:open="isViewModalOpen"
      :grn="selectedGrn"
      :products="products"
      v-if="selectedGrn"
    />

    <!-- Edit Modal -->
    <GrnEditModal
      v-model:open="isEditModalOpen"
      :grn="selectedGrn"
      :products="products"
      :suppliers="suppliers"
      :purchase-orders="purchaseOrders"
      v-if="selectedGrn"
    />

    <!-- Delete Modal -->
    <GrnDeleteModal
      v-model:open="isDeleteModalOpen"
      :grn="selectedGrn"
      v-if="selectedGrn"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import GrnCreateModal from './Components/GrnCreateModal.vue';
import GrnViewModel from './Components/GrnViewModel.vue';
import GrnEditModal from './Components/GrnEditModal.vue';
import GrnDeleteModal from './Components/GrnDeleteModal.vue';

defineProps({
     products: Array,
    grns: Object,
    suppliers: Array,
    purchaseOrders: Array,
    availableProducts: Array,
    grnNumber: String,
    measurementUnits: String,
});

const isCreateModalOpen = ref(false);
const isViewModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedGrn = ref(null);

const openCreateModal = () => {
    isCreateModalOpen.value = true;
};

const openViewModal = (grn) => {
    selectedGrn.value = grn;
    isViewModalOpen.value = true;
};

const openEditModal = (grn) => {
    selectedGrn.value = grn;
    isEditModalOpen.value = true;
};

const openDeleteModal = (grn) => {
    selectedGrn.value = grn;
    isDeleteModalOpen.value = true;
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

const formatNumber = (number) => {
    return parseFloat(number || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const getStatusClass = (status) => {
    const classes = {
        '0': 'bg-red-500 text-white px-3 py-1 rounded',
        '1': 'bg-green-500 text-white px-3 py-1 rounded',
        '2': 'bg-gray-500 text-white px-3 py-1 rounded'
    };
    return classes[status] || 'bg-gray-500 text-white px-3 py-1 rounded';
};

const updateStatus = (grn, newStatus) => {
    router.patch(route('grn.update-status', grn.id), { status: newStatus }, {
        onSuccess: () => {
            // Status updated successfully
        },
        onError: () => {
            // Error occurred
        }
    });
};
</script>

<style scoped>
/* Tailwind CSS handles all styling */
</style>