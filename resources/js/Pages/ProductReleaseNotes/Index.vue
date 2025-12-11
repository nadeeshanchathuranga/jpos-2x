<template>
  <AppLayout title="Pro Notes">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
          <button
            @click="$inertia.visit(route('dashboard'))"
            class="px-4 py-2 text-white bg-accent rounded hover:bg-accent"
          >
            Back
          </button>
          <h1 class="text-3xl font-bold text-white">Product Release Notes</h1>
        </div>
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-accent rounded hover:bg-accent"
        >
          Add New Product Release Note
        </button>
      </div>

      <div class="overflow-hidden bg-dark border-4 border-accent rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-accent">
              <tr>
                
                <th class="px-6 py-3">Date</th>
               
                <th class="px-6 py-3">Products</th>
                <th class="px-6 py-3">Total Amount</th> 
                <th class="px-6 py-3 text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="productReleaseNote in productReleaseNotes.data"
                :key="productReleaseNote.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
               
                <td class="px-6 py-4">{{ formatDate(productReleaseNote.release_date) }}</td>
                 <td class="px-6 py-4">
                  <span class="text-sm">{{ productReleaseNote.product_release_note_products?.length || 0 }} items</span>
                </td>
                <td class="px-6 py-4">Rs. {{ calculateTotal(productReleaseNote) }}</td>
               
                <td class="px-6 py-4 text-center">
                  <button
                    @click="openViewModal(productReleaseNote)"
                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600"
                  >
                    View
                  </button>
                </td>
              </tr>
              <tr v-if="!productReleaseNotes.data || productReleaseNotes.data.length === 0">
                <td colspan="7" class="px-6 py-4 text-center text-gray-400">
                  No Product Release Notes found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="productReleaseNotes.links && productReleaseNotes.links.length > 3">
          <div class="text-sm text-gray-400">
            Showing {{ productReleaseNotes.from }} to {{ productReleaseNotes.to }} of {{ productReleaseNotes.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in productReleaseNotes.links"
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
    <ProductReleaseNoteCreateModal 
      v-model:open="isCreateModalOpen"
      :products="products"
      :productTransferRequests="productTransferRequests"
      :users="users"
      :availableProducts="products"
    />

    <!-- View Modal -->
    <ProductReleaseNoteViewModel
      v-model:open="isViewModalOpen"
      :product-release-note="selectedProductReleaseNote"
      v-if="selectedProductReleaseNote"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { logActivity } from '@/composables/useActivityLog';
import ProductReleaseNoteCreateModal from './Components/ProductReleaseNoteCreateModal.vue';
import ProductReleaseNoteViewModel from './Components/ProductReleaseNoteViewModel.vue';

defineProps({
  products: Array,
  productReleaseNotes: Object,
  productTransferRequests: Array,
  users: Array,
});

const isCreateModalOpen = ref(false);
const isViewModalOpen = ref(false);
const selectedProductReleaseNote = ref(null);

const openCreateModal = () => {
  isCreateModalOpen.value = true;
};

const openViewModal = async (productReleaseNote) => {
  selectedProductReleaseNote.value = productReleaseNote;
  isViewModalOpen.value = true;

  // Log view activity
  await logActivity('view', 'product_release_notes', {
    release_note_id: productReleaseNote.id,
    release_note_number: productReleaseNote.product_release_note_no,
    release_date: productReleaseNote.product_release_date,
    user: productReleaseNote.user?.name || 'N/A',
    status: productReleaseNote.status,
  });
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

const calculateTotal = (productReleaseNote) => {
  if (!productReleaseNote.product_release_note_products || productReleaseNote.product_release_note_products.length === 0) {
    return formatNumber(0);
  }
  
  const productsTotal = productReleaseNote.product_release_note_products.reduce((sum, item) => {
    return sum + (parseFloat(item.total) || 0);
  }, 0);
  
  return formatNumber(productsTotal);
};
</script>

<style scoped>
/* Tailwind CSS handles all styling */
</style>