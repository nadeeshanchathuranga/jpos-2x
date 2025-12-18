<template>
  <div v-if="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-900 rounded-lg p-6 w-full max-w-4xl max-h-screen overflow-y-auto">
      <h2 class="text-2xl font-bold text-white mb-4">GRN Return Details</h2>
      
      <div class="bg-gray-800 p-4 rounded mb-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <span class="text-gray-400">GRN Number:</span>
            <span class="text-white ml-2">{{ getGrnNumber() }}</span>
          </div>
          <div>
            <span class="text-gray-400">Date:</span>
            <span class="text-white ml-2">{{ formatDate(ret?.date) }}</span>
          </div>
        </div>
      </div>
      
      <h3 class="text-xl font-bold text-white mb-2">Returned Products</h3>
      
      <div class="overflow-x-auto mb-4">
        <table class="w-full text-white text-sm">
          <thead class="bg-blue-600">
            <tr>
              <th class="px-4 py-2">Product</th>
              <th class="px-4 py-2">Unit</th>
              <th class="px-4 py-2">Original Qty</th>
              <th class="px-4 py-2">Return Qty</th>
            </tr>
          </thead>
          <tbody class="text-center">
            <tr 
              v-for="item in getReturnProducts()" 
              :key="item.id" 
              class="border-b border-gray-700"
            >
              <td class="px-4 py-2">{{ getProductName(item) }}</td>
              <td class="px-4 py-2">{{ getUnitName(item) }}</td>
              <td class="px-4 py-2">{{ formatNumber(getOriginalQty(item)) }}</td>
              <td class="px-4 py-2">{{ formatNumber(item.qty || item.quantity) }}</td>
            </tr>
            <tr v-if="getReturnProducts().length === 0">
              <td colspan="4" class="px-4 py-4 text-center text-gray-400">
                No returned products
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <div class="flex justify-end">
        <button 
          @click="close" 
          class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600"
        >
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
  const num = parseFloat(number || 0);
  if (isNaN(num)) return 'N/A';
  return num.toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
};

// Get GRN number robustly from all possible property names and fallback to goods_received_note_no
const getGrnNumber = () => {
  const ret = props.ret || {};
  // Try nested objects
  return (
    ret.goods_received_note?.grn_no ||
    ret.goods_received_note?.goods_received_note_no ||
    ret.goodsReceivedNote?.grn_no ||
    ret.goodsReceivedNote?.goods_received_note_no ||
    ret.grn?.grn_no ||
    ret.grn?.goods_received_note_no ||
    ret.goods_received_note_no ||
    ret.grn_no ||
    'N/A'
  );
};

// Get return products from various possible property names
const getReturnProducts = () => {
  const products = props.ret?.goods_received_note_return_products 
    || props.ret?.goodsReceivedNoteReturnProducts 
    || props.ret?.grn_return_products 
    || [];
  
  return Array.isArray(products) ? products : (products.data || []);
};

// Get product name from item
const getProductName = (item) => {
  return item.product?.name || item.products?.name || 'N/A';
};

// Get original quantity from GRN
const getOriginalQty = (item) => {
  try {
    // Get GRN from various possible property names
    const grn = props.ret?.goods_received_note 
      || props.ret?.goodsReceivedNote 
      || props.ret?.grn 
      || {};
    
    // Get GRN products from various possible property names
    const grnProducts = grn.goods_received_note_products 
      || grn.goodsReceivedNoteProducts 
      || grn.grnProducts 
      || grn.grn_products 
      || [];
    
    // Get product ID from item
    const productId = item.products_id 
      || item.product_id 
      || item.product?.id 
      || item.products?.id;
    
    if (!productId) return null;
    
    // Find matching product in GRN
    const productsArray = Array.isArray(grnProducts) 
      ? grnProducts 
      : (grnProducts.data || []);
    
    const match = productsArray.find(gp => 
      Number(gp.product_id) === Number(productId)
    );
    
    return match ? (match.qty || match.quantity || null) : null;
  } catch (e) {
    console.error('Error getting original quantity:', e);
    return null;
  }
};

// Get unit name for product
const getUnitName = (item) => {
  // 1. Try direct unit relationship on item
  if (item.measurement_unit?.name) return item.measurement_unit.name;
  if (item.measurementUnit?.name) return item.measurementUnit.name;
  if (item.unit?.name) return item.unit.name;
  
  // 2. Try product-level unit relationship
  const productUnit = item.product?.measurement_unit?.name 
    || item.product?.measurementUnit?.name 
    || item.products?.measurement_unit?.name 
    || item.products?.measurementUnit?.name;
  
  if (productUnit) return productUnit;
  
  // 3. Try unit ID lookup in measurementUnits prop
  const unitId = item.measurement_unit_id 
    || item.product?.measurement_unit_id 
    || item.product?.purchase_unit_id 
    || item.products?.measurement_unit_id 
    || item.products?.purchase_unit_id;
  
  if (unitId && Array.isArray(props.measurementUnits)) {
    const found = props.measurementUnits.find(u => 
      Number(u.id) === Number(unitId)
    );
    if (found) return found.name;
  }
  
  // Fallback
  return 'N/A';
};
</script>