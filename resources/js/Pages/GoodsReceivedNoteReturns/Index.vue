<template>
  <AppLayout >
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-white">GRN Returns</h1>
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
        >
          Add New GRN Return
        </button>      </div>

      <div class="overflow-hidden bg-black border-4 border-blue-600 rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-blue-600">
              <tr>
                <th class="px-6 py-3">Return #</th>
                <th class="px-6 py-3">GRN</th>
                <th class="px-6 py-3">Date</th>
                <th class="px-6 py-3">User</th>
                <th class="px-6 py-3">Products</th>
                <th class="px-6 py-3">Return Qty</th>
                <th class="px-6 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in returns.data" :key="r.id" class="border-b border-gray-700 hover:bg-gray-900">
                <td class="px-6 py-4">{{ r.id }}</td>
                <td class="px-6 py-4">{{ r.grn?.grn_no || 'N/A' }}</td>
                <td class="px-6 py-4">{{ formatDate(r.date) }}</td>
                <td class="px-6 py-4">{{ r.user?.name || 'N/A' }}</td>
                <td class="px-6 py-4">{{ r.grn_return_products?.length || r.products?.length || 0 }} items</td>
                <td class="px-6 py-4">{{ sumReturnQty(r) }} </td>
                <td class="px-6 py-4 text-center">
                  <button
                    @click="openViewModal(r)"
                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600 mr-2"
                  >
                    View
                  </button>
                  <button
                    @click="openDeleteModal(r)"
                    class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <tr v-if="!returns.data || returns.data.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-400">No returns found</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="returns.links && returns.links.length > 3">
          <div class="text-sm text-gray-400">Showing {{ returns.from }} to {{ returns.to }} of {{ returns.total }} results</div>
          <div class="flex space-x-2">
            <button v-for="link in returns.links" :key="link.label" @click="link.url ? router.visit(link.url) : null" :disabled="!link.url" :class="[ 'px-3 py-1 rounded', link.active ? 'bg-blue-600 text-white' : link.url ? 'bg-gray-700 text-white hover:bg-gray-600' : 'bg-gray-800 text-gray-500 cursor-not-allowed' ]" v-html="link.label"></button>
          </div>
        </div>
      </div>
    </div>

     <!-- Create Modal -->
    <GoodsReceivedNoteReturnCreateModal 
      v-model:open="isCreateModalOpen"
      :suppliers="suppliers"
      :purchase-orders="purchaseOrders"
      :products="products"
      :available-products="availableProducts"
      :measurement-units="measurementUnits"
      :grnNumber="grnNumber"
      :grns="grns"
      :user="user"
    />

    <!-- View Modal -->
    <GoodsReceivedNoteReturnViewModal
      v-model:open="isViewModalOpen"
      :ret="selectedReturn"
      :measurement-units="measurementUnits"
      v-if="selectedReturn"
    />

    <!-- Delete Modal -->
    <GoodsReceivedNoteReturnDeleteModal
      v-model:open="isDeleteModalOpen"
      :grn="selectedReturn"
      v-if="selectedReturn"
      @deleted="handleReturnDeleted"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import GoodsReceivedNoteReturnCreateModal from './components/GoodsReceivedNoteReturnCreateModal.vue';
import GoodsReceivedNoteReturnViewModal from './components/GoodsReceivedNoteReturnViewModal.vue';
import GoodsReceivedNoteReturnDeleteModal from './components/GoodsReceivedNoteReturnDeleteModal.vue';

const props = defineProps({
  returns: Object,
  suppliers: { type: Array, default: () => [] },
  purchaseOrders: { type: Array, default: () => [] },
  products: { type: Array, default: () => [] },
  availableProducts: { type: Array, default: () => [] },
  grnNumber: { type: String, default: '' },
  grns: { type: Array, default: () => [] },
  measurementUnits: { type: Array, default: () => [] },
  user: { type: Object, default: null },
});

// Debug: show grns arriving from server
try {
  console.log('GrnReturns Index props.grns:', props.grns)
  if (!props.grns || props.grns.length === 0) console.warn('Index.vue: `grns` is empty')
} catch (e) {
  console.error('Failed to read props.grns in Index.vue', e)
}

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const sumReturnQty = (r) => {
  const rows = r.grn_return_products || r.products || [];
  if (!Array.isArray(rows) || rows.length === 0) return 0;
  return rows.reduce((sum, item) => {
    const qty = Number(item.qty ?? item.returnQty ?? 0) || 0;
    return sum + qty;
  }, 0);
};

const isCreateModalOpen = ref(false);
const isViewModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedReturn = ref(null);

const openCreateModal = () => {
    isCreateModalOpen.value = true;
};

const openViewModal = (r) => {
    selectedReturn.value = r;
    isViewModalOpen.value = true;
};

const openDeleteModal = (r) => {
    selectedReturn.value = r;
    isDeleteModalOpen.value = true;
};

const handleReturnSaved = (payload) => {
  // optimistic update: update selectedReturn products so UI reflects change immediately
  try {
    if (!selectedReturn.value) return;
    const id = selectedReturn.value.id;
    const mappedProducts = (payload.products || []).map(p => ({
      products_id: p.product_id,
      qty: p.qty,
      remarks: p.remarks ?? null,
      product: (props.products || []).find(prod => Number(prod.id) === Number(p.product_id)) || null,
    }));

    // update selectedReturn
    selectedReturn.value.grn_return_products = mappedProducts;

    // try to update the table entry if present
    const idx = returns.data.findIndex(x => x.id === id);
    if (idx !== -1) {
      returns.data[idx].grn_return_products = mappedProducts;
    }
  } catch (e) {
    console.error('Failed optimistic update for GRN return:', e);
  }
};

const handleReturnDeleted = (id) => {
  try {
    if (!id) return;
    // remove from paginated data if present
    if (returns && Array.isArray(returns.data)) {
      const idx = returns.data.findIndex(x => x.id === id);
      if (idx !== -1) returns.data.splice(idx, 1);
    }
    if (selectedReturn.value?.id === id) {
      selectedReturn.value = null;
      isDeleteModalOpen.value = false;
    }
  } catch (e) {
    console.error('Failed optimistic delete update:', e);
  }
};
</script>

<style scoped></style>
