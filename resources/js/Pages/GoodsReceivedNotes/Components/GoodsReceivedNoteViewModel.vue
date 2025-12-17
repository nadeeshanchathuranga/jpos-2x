<template>
  <div v-if="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-900 rounded-lg p-6 w-full max-w-4xl max-h-screen overflow-y-auto">
      <h2 class="text-2xl font-bold text-white mb-4">GRN Details</h2>
      <div class="bg-gray-800 p-4 rounded mb-4">
        <div class="grid grid-cols-2 gap-4">
          <div><span class="text-gray-400">GRN Number:</span><span class="text-white ml-2">{{ grn.goods_received_note_no }}</span></div>
          <div><span class="text-gray-400">Supplier:</span><span class="text-white ml-2">{{ grn.supplier?.name }}</span></div>
          <div><span class="text-gray-400">Date:</span><span class="text-white ml-2">{{ formatDate(grn.goods_received_note_date) }}</span></div>
          <div><span class="text-gray-400">Status:</span><span class="text-white ml-2">{{ getStatusText(grn.status) }}</span></div>
          <div><span class="text-gray-400">Discount:</span><span class="text-white ml-2"> {{ formatNumber(grn.discount) }}</span></div>
          <div><span class="text-gray-400">Tax:</span><span class="text-white ml-2"> {{ formatNumber(grn.tax_total) }}</span></div>
        </div>
      </div>
      <h3 class="text-xl font-bold text-white mb-2">Products</h3>
      <div class="overflow-x-auto mb-4">
        <table class="w-full text-white text-sm">
          <thead class="bg-blue-600">
            <tr>
              <th class="px-4 py-2">Product</th>
              <th class="px-4 py-2">Qty</th>
              <th class="px-4 py-2">Unit</th>
              <th class="px-4 py-2">Purchase Price ({{ page.props.currency || '' }})</th>
              <th class="px-4 py-2">Total
 ({{ page.props.currency || '' }})

              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="product in grn.goods_received_note_products" :key="product.id" class="border-b border-gray-700">
              <td class="px-4 py-2">{{ product.product?.name }}</td>
              <td class="px-4 py-2">{{ product.quantity }}</td>
              <td class="px-4 py-2">{{ product.product?.measurement_unit?.name || product.unit || 'No' }}</td>
              <td class="px-4 py-2"> {{ formatNumber(product.purchase_price) }}</td>
              <td class="px-4 py-2"> {{ formatNumber(product.total) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex justify-end mb-4">
        <div class="text-white font-bold">
          Total Set: {{ formatNumber(grandTotal) }} ({{ page.props.currency || '' }})
        </div>
      </div>

      <div class="flex justify-end">
        <button @click="close" class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
const page = usePage()

const props = defineProps({
    open: Boolean,
    grn: Object,
});

const emit = defineEmits(['update:open']);

const close = () => {
    emit('update:open', false);
};

const grandTotal = computed(() => {
  const items = props.grn?.goods_received_note_products || []
  return items.reduce((sum, p) => sum + (parseFloat(p.total) || 0), 0)
})

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

const getStatusText = (status) => {
    const statuses = { '0': 'INACTIVE', '1': 'ACTIVE', '2': 'DEFAULT' };
    return statuses[status] || 'UNKNOWN';
};
</script>
