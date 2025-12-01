<template>
  <AppLayout title="Pro Notes">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-white">Pro Notes</h1>
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
        >
          Add New Pro Note
        </button>
      </div>

      <div class="overflow-hidden bg-black border-4 border-blue-600 rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-blue-600">
              <tr>
                
                <th class="px-6 py-3">Date</th>
               
                <th class="px-6 py-3">Products</th>
                <th class="px-6 py-3">Total Amount</th> 
                <th class="px-6 py-3 text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="prn in prns.data"
                :key="prn.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
               
                <td class="px-6 py-4">{{ formatDate(prn.release_date) }}</td>
                 <td class="px-6 py-4">
                  <span class="text-sm">{{ prn.prn_products?.length || 0 }} items</span>
                </td>
                <td class="px-6 py-4">Rs. {{ calculateTotal(prn) }}</td>
               
                <td class="px-6 py-4 text-center">
                  <button
                    @click="openViewModal(prn)"
                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600 mr-2"
                  >
                    View
                  </button>
                  <button
                    @click="openEditModal(prn)"
                    class="px-4 py-2 text-white bg-accent rounded hover:bg-accent mr-2"
                  >
                    Edit
                  </button>
                  <button
                    @click="openDeleteModal(prn)"
                    class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <tr v-if="!prns.data || prns.data.length === 0">
                <td colspan="7" class="px-6 py-4 text-center text-gray-400">
                  No Pro Notes found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="prns.links && prns.links.length > 3">
          <div class="text-sm text-gray-400">
            Showing {{ prns.from }} to {{ prns.to }} of {{ prns.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in prns.links"
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
    <PrnCreateModal 
      v-model:open="isCreateModalOpen"
      :products="products"
      :ptrs="ptrs"
      :users="users"
      :availableProducts="products"
    />

    <!-- View Modal -->
    <PrnViewModel
      v-model:open="isViewModalOpen"
      :prn="selectedPrn"
      v-if="selectedPrn"
    />

    <!-- Edit Modal -->
    <PrnEditModal
      v-model:open="isEditModalOpen"
      :prn="selectedPrn"
      :availableProducts="products"
      :users="users"
      v-if="selectedPrn"
    />

    <!-- Delete Modal -->
    <PrnDeleteModal
      v-model:open="isDeleteModalOpen"
      :prn="selectedPrn"
      v-if="selectedPrn"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import PrnCreateModal from './Components/PrnCreateModal.vue';
import PrnViewModel from './Components/PrnViewModel.vue';
import PrnEditModal from './Components/PrnEditModal.vue';
import PrnDeleteModal from './Components/PrnDeleteModal.vue';

defineProps({
  products: Array,
  prns: Object,
  ptrs: Array,
  users: Array,
});

const isCreateModalOpen = ref(false);
const isViewModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedPrn = ref(null);

const openCreateModal = () => {
  isCreateModalOpen.value = true;
};

const openViewModal = (prn) => {
  selectedPrn.value = prn;
  isViewModalOpen.value = true;
};

const openEditModal = (prn) => {
  selectedPrn.value = prn;
  isEditModalOpen.value = true;
};

const openDeleteModal = (prn) => {
  selectedPrn.value = prn;
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

const calculateTotal = (prn) => {
  if (!prn.prn_products || prn.prn_products.length === 0) {
    return formatNumber(0);
  }
  
  const productsTotal = prn.prn_products.reduce((sum, item) => {
    return sum + (parseFloat(item.total) || 0);
  }, 0);
  
  return formatNumber(productsTotal);
};
</script>

<style scoped>
/* Tailwind CSS handles all styling */
</style>