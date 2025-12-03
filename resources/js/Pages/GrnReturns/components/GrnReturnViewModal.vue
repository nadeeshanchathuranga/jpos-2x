<template>
  <div v-if="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-900 rounded-lg p-6 w-full max-w-4xl max-h-screen overflow-y-auto">
      <h2 class="text-2xl font-bold text-white mb-4">GRN Return Details</h2>
      <div class="bg-gray-800 p-4 rounded mb-4">
        <div class="grid grid-cols-2 gap-4">
          <div><span class="text-gray-400">GRN Number:</span><span class="text-white ml-2">{{ ret?.grn?.grn_no || 'N/A' }}</span></div>
          <div><span class="text-gray-400">Date:</span><span class="text-white ml-2">{{ formatDate(ret?.date) }}</span></div>
        </div>
      </div>
      <h3 class="text-xl font-bold text-white mb-2">Returned Products</h3>
      <div class="overflow-x-auto mb-4">
        <table class="w-full text-white text-sm">
          <thead class="bg-blue-600">
            <tr>
              <th class="px-4 py-2">Product</th>
              <th class="px-4 py-2">Unit</th>
              <th class="px-4 py-2">Qty</th>
              <th class="px-4 py-2">Return Qty</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in ret?.grn_return_products || []" :key="item.id" class="border-b border-gray-700">
              <td class="px-4 py-2">{{ item.product?.name || item.products?.name || 'N/A' }}</td>
              <td class="px-4 py-2">{{ getUnitName(item) }}</td>
              <td class="px-4 py-2">{{ getOriginalQty(item) ?? 'N/A' }}</td>
              <td class="px-4 py-2">{{ item.qty }}</td>
            </tr>
            <tr v-if="!(ret?.grn_return_products && ret.grn_return_products.length)">
              <td colspan="3" class="px-4 py-4 text-center text-gray-400">No returned products</td>
            </tr>
          </tbody>
        </table>
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
const props = defineProps({
  open: Boolean,
  ret: Object,
  measurementUnits: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:open']);

const close = () => {
    emit('update:open', false);
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

const getOriginalQty = (item) => {
  try {
    const grn = props.ret?.grn || {};
    const grnProducts = grn.grnProducts || grn.grn_products || [];
    const pid = item.products_id ?? item.product_id ?? null;
    if (!pid) return null;
    const match = (Array.isArray(grnProducts) ? grnProducts : (grnProducts.data || [])).find(gp => Number(gp.product_id) === Number(pid));
    return match ? (match.qty ?? null) : null;
  } catch (e) {
    return null;
  }
}

const getUnitName = (item) => {
  // 1. Try direct unit on item (if exists)
  if (item.unit?.name) return item.unit.name
  if (item.measurement_units?.name) return item.measurement_units.name

  // 2. Try product-level unit ID lookup
  const unitId = item.product?.purchase_unit_id ?? item.product?.measurement_unit_id
  if (unitId && Array.isArray(props.measurementUnits)) {
    const found = props.measurementUnits.find(u => Number(u.id) === Number(unitId))
    if (found) return found.name
  }

  // fallback
  return 'N/A'
}


const getStatusText = (status) => {
    const statuses = { '0': 'INACTIVE', '1': 'ACTIVE', '2': 'DEFAULT' };
    return statuses[status] || 'UNKNOWN';
};
</script>
