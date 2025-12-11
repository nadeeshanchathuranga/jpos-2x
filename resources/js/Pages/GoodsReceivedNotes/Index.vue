<template>
  <AppLayout title="  ">
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
          Add New Goods Received Note
        </button>
      </div>

      <div class="overflow-hidden bg-dark border-4 border-accent rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-accent">
              <tr>
                <th class="px-6 py-3">Note Number</th>
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
                v-for="goodsReceivedNote in goodsReceivedNotes.data"
                :key="goodsReceivedNote.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
                <td class="px-6 py-4">
                  <span class="font-semibold">{{ goodsReceivedNote.goods_received_note_no }}</span>
                </td>
                <td class="px-6 py-4">{{ goodsReceivedNote.supplier?.name || 'N/A' }}</td>
                <td class="px-6 py-4">{{ formatDate(goodsReceivedNote.goods_received_note_date) }}</td>
                <td class="px-6 py-4">
                  <span class="text-sm">{{ goodsReceivedNote.goods_received_note_products?.length || 0 }} items</span>
                </td>
                <td class="px-6 py-4">Rs. {{ formatNumber(goodsReceivedNote.discount) }}</td>
                <td class="px-6 py-4">Rs. {{ formatNumber(goodsReceivedNote.tax_total) }}</td>
                <td class="px-6 py-4 text-center">
                  <span :class="getStatusClass(goodsReceivedNote.status)" class="px-3 py-1 rounded text-white font-semibold">
                    {{ goodsReceivedNote.status === 0 ? 'INACTIVE' : goodsReceivedNote.status === 1 ? 'ACTIVE' : 'DEFAULT' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <button
                    @click="openViewModal(goodsReceivedNote)"
                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600 mr-2"
                  >
                    View
                  </button>
                  <button
                    v-if="goodsReceivedNote.status !== 0"
                    @click="openDeleteModal(goodsReceivedNote)"
                    class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <tr v-if="!goodsReceivedNotes.data || goodsReceivedNotes.data.length === 0">
                <td colspan="8" class="px-6 py-4 text-center text-gray-400">
                  No Goods Received Notes found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="goodsReceivedNotes.links && goodsReceivedNotes.links.length > 3">
          <div class="text-sm text-gray-400">
            Showing {{ goodsReceivedNotes.from }} to {{ goodsReceivedNotes.to }} of {{ goodsReceivedNotes.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in goodsReceivedNotes.links"
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
    <GoodsReceivedNoteCreateModal 
      v-model:open="isCreateModalOpen"
      :suppliers="suppliers"
      :purchase-orders="purchaseOrders"
      :products="products"
      :available-products="availableProducts"
      :grnNumber="grnNumber"
      :measurementUnits="measurementUnits"
    />

    <!-- View Modal -->
    <GoodsReceivedNoteViewModel
      v-model:open="isViewModalOpen"
      :grn="selectedGoodsReceivedNote"
      v-if="selectedGoodsReceivedNote"
    />

    <!-- Delete Modal -->
    <GoodsReceivedNoteDeleteModal
      v-model:open="isDeleteModalOpen"
      :grn="selectedGoodsReceivedNote"
      v-if="selectedGoodsReceivedNote"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import GoodsReceivedNoteCreateModal from './Components/GoodsReceivedNoteCreateModal.vue';
import GoodsReceivedNoteViewModel from './Components/GoodsReceivedNoteViewModel.vue';
import GoodsReceivedNoteEditModal from './Components/GoodsReceivedNoteEditModal.vue';
import GoodsReceivedNoteDeleteModal from './Components/GoodsReceivedNoteDeleteModal.vue';

defineProps({
     products: Array,
    goodsReceivedNotes: Object,
    suppliers: Array,
    purchaseOrders: Array,
    availableProducts: Array,
    grnNumber: String,
    measurementUnits: Array,
});

const isCreateModalOpen = ref(false);
const isViewModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedGoodsReceivedNote = ref(null);

const openCreateModal = () => {
    isCreateModalOpen.value = true;
};

const openViewModal = (goodsReceivedNote) => {
    selectedGoodsReceivedNote.value = goodsReceivedNote;
    isViewModalOpen.value = true;
};

const openDeleteModal = (goodsReceivedNote) => {
    selectedGoodsReceivedNote.value = goodsReceivedNote;
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

const updateStatus = (goodsReceivedNote, newStatus) => {
    router.patch(route('goods-received-notes.update-status', goodsReceivedNote.id), { status: newStatus }, {
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