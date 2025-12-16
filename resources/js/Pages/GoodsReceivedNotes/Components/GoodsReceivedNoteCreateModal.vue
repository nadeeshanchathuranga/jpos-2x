<template>
  <div v-if="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-900 rounded-lg p-6 w-full max-w-4xl max-h-screen overflow-y-auto">
      
      <h2 class="text-2xl font-bold text-white mb-4">Create New GRN</h2>

      <form @submit.prevent="submitForm">

        <!-- GRN DETAILS -->
        <div class="mb-6">
          <h3 class="text-lg font-semibold text-white mb-4">GRN Details</h3>

          <div class="grid grid-cols-2 gap-4 mb-4">

            <div>
              <label class="block text-white mb-2">GRN Number *</label>
              <input v-model="form.goods_received_note_no" type="text" class="w-full px-3 py-2 bg-gray-800 text-white rounded" required />
            </div>

            <div>
              <label class="block text-white mb-2">Supplier *</label>
              <select v-model="form.supplier_id" class="w-full px-3 py-2 bg-gray-800 text-white rounded" required>
                <option value="">Select Supplier</option>
                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                  {{ supplier.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-white mb-2">GRN Date *</label>
              <input v-model="form.goods_received_note_date" type="date" class="w-full px-3 py-2 bg-gray-800 text-white rounded" required />
            </div>

            <div>
              <label class="block text-white mb-2">Purchase Order</label>
                <select 
                    v-model="form.purchase_order_request_id" 
                    @change="loadPOData"
                    class="w-full px-3 py-2 bg-gray-800 text-white rounded"
                  >
                      <option value="">Select PO (Optional)</option>
                  <option v-for="po in filteredPurchaseOrders" :key="po.id" :value="po.id">
                    {{ po.order_number }}
                  </option>
                  </select>
            </div>

            <div>
              <label class="block text-white mb-2">Discount</label>
              <input v-model.number="form.discount" type="number" step="0.01" class="w-full px-3 py-2 bg-gray-800 text-white rounded" />
            </div>

            <div>
              <label class="block text-white mb-2">Tax Total</label>
              <input v-model.number="form.tax_total" type="number" step="0.01" class="w-full px-3 py-2 bg-gray-800 text-white rounded" />
            </div>

            <div>
             
              
            </div>

          </div>

          <div>
            <label class="block text-white mb-2">Remarks</label>
            <textarea v-model="form.remarks" rows="3" class="w-full px-3 py-2 bg-gray-800 text-white rounded"></textarea>
          </div>
        </div>

        <!-- PRODUCTS SECTION -->
        <div class="mb-6">
          <div class="overflow-x-auto">
            <table class="w-full text-white text-sm">
              <thead class="bg-blue-600">
                <tr>
                  <th class="px-4 py-2">Product</th>
                  <th class="px-4 py-2">Unit</th>
                  <th class="px-4 py-2">Requested Qunatity</th>
                  <th class="px-4 py-2">Issued Qunatity</th>
                  <th class="px-4 py-2">Purchase Price</th>
                  <th class="px-4 py-2">Discount</th>
                  <th class="px-4 py-2">Total</th>
                  <th class="px-4 py-2">Action</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="(product, index) in products" :key="index" class="border-b border-gray-700">

                  <td class="px-4 py-2">

                    
                    <select v-model.number="product.product_id" @change="onProductSelect(index)"
                            class="w-full px-2 py-1 bg-gray-800 text-white rounded">
                      <option :value="null">Select Product</option>
                      <option v-for="prod in availableProducts" :key="prod.id" :value="prod.id">
                        {{ prod.name }}  
                      </option>
                    </select>
                  </td>

                <td class="px-4 py-2">
                  <select 
                    v-model="product.measurement_unit_id" 
                    class="w-full px-2 py-1 bg-gray-800 text-white rounded"
                  >
                    <option value="">Select Unit</option>
                    <option 
                      v-for="unit in measurementUnits" 
                      :key="unit.id" 
                      :value="unit.id"
                    >
                      {{ unit.name }}
                    </option>
                  </select>
                </td>

                  <td class="px-4 py-2">
                    <input v-model.number="product.requested_quantity" type="number" step="0.01" min="0.01"
                           
                           class="w-full px-2 py-1 bg-gray-800 text-white rounded" />
                  </td>

                  <td class="px-4 py-2">
                    <input v-model.number="product.issued_quantity" type="number" step="0.01" min="0.01"
                    @input="calculateTotal(index)"
                           class="w-full px-2 py-1 bg-gray-800 text-white rounded" />
                  </td>

                  <td class="px-4 py-2">
                    <input v-model.number="product.purchase_price" type="number" step="0.01" min="0"
                           @input="calculateTotal(index)"
                           class="w-full px-2 py-1 bg-gray-800 text-white rounded" />
                  </td>

                  <td class="px-4 py-2">
                    <input v-model.number="product.discount" type="number" step="0.01" min="0"
                           @input="calculateTotal(index)"
                           class="w-full px-2 py-1 bg-gray-800 text-white rounded" />
                  </td>

                  <td class="px-4 py-2">
                    <span class="font-semibold">
                      Rs. {{ formatNumber(product.total) }}
                    </span>
                  </td>

                  <td class="px-4 py-2">
                    <button type="button" @click="removeProduct(index)"
                            class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                      Remove
                    </button>
                  </td>

                </tr>

                <tr v-if="products.length === 0">
                  <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                    No products added yet. Click "Add Product" to start.
                  </td>
                </tr>

              </tbody>

              <tfoot v-if="products.length > 0" class="bg-gray-800">
                <tr>
                  <td colspan="5" class="px-4 py-3 text-right font-semibold">Grand Total:</td>
                  <td class="px-4 py-3 font-bold text-lg">
                    Rs. {{ formatNumber(grandTotal) }}
                  </td>
                  <td></td>
                </tr>
              </tfoot>

            </table>
          </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="flex justify-end gap-2">
          <button type="button" @click="close"
                  class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">
            Cancel
          </button>

          <button type="submit" :disabled="products.length === 0"
                  class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
            Create GRN
          </button>
        </div>

      </form>
    </div>
  </div>
</template>


 <script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { logActivity } from '@/composables/useActivityLog'

const props = defineProps({
  open: Boolean,
  suppliers: Array,
     measurementUnits: Array,

  purchaseOrders: Array,
  availableProducts: Array,
  grnNumber: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['update:open'])

const form = ref({
    goods_received_note_no: props.grnNumber,
    supplier_id: '',
    goods_received_note_date: new Date().toISOString().split('T')[0],
    purchase_order_request_id: '',
    discount: 0,
    tax_total: 0,
    remarks: '',
})

const products = ref([])

const grandTotal = computed(() => {
  return products.value.reduce((sum, product) => sum + (parseFloat(product.total) || 0), 0)
})

// Filter out completed purchase orders so they don't appear in the GRN dropdown
const filteredPurchaseOrders = computed(() => {
  const list = props.purchaseOrders || [];
  return list.filter(po => (po.status || '').toString().toLowerCase() !== 'completed')
})

const close = () => {
  emit('update:open', false)
  resetForm()
}

const resetForm = () => {
  form.value = {
    goods_received_note_no: props.grnNumber,
    supplier_id: '',
    goods_received_note_date: new Date().toISOString().split('T')[0],
    purchase_order_request_id: '',
    discount: 0,
    tax_total: 0,
    remarks: '',
  }
  products.value = []
}

const loadPOData = async () => {
    if (!form.value.purchase_order_request_id) {
        products.value = [];
        return;
    }

    try {
        const response = await fetch(`/po/${form.value.purchase_order_request_id}/details`);
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Failed to load PO details');
        }

        const poProducts = data.purchaseOrderProducts || [];

        if (poProducts.length === 0) {
          console.warn('No products found in this PO');
          return;
        }

        // The backend now returns `requested_quantity` as the remaining amount
        // (original requested - already issued). Initialize `issued_quantity` to 0
        // so the user can enter the actual received amount for this GRN.
        products.value = poProducts.map(item => {
          const remainingRequested = parseFloat(item.requested_quantity) || 0;
          const purchasePrice = parseFloat(item.price) || 0;

          return {
            product_id: item.product_id,
            measurement_unit_id: item.measurement_unit_id,
            requested_quantity: remainingRequested,
            // Start with 0 received for this GRN; user will input the issued_quantity
            issued_quantity: 0,
            purchase_price: purchasePrice,
            discount: 0,
            unit: item.name || '',
            total: 0,
          };
        });

    } catch (error) {
        console.error('Failed to load PO data:', error);
        alert('Failed to load Purchase Order details: ' + error.message);
    }
};

const removeProduct = (index) => {
  products.value.splice(index, 1)
}

const onProductSelect = (index) => {
  const product = products.value[index]
  const selectedProduct = props.availableProducts.find(p => p.id === product.product_id)

  if (selectedProduct) {
    product.purchase_price = selectedProduct.price || 0
    product.measurement_unit_id = selectedProduct.measurement_unit_id
    product.unit = selectedProduct.purchaseUnit?.name || selectedProduct.measurementUnit?.name || 'N/A'
    calculateTotal(index)
  }
}

const calculateTotal = (index) => {
  const p = products.value[index]
  // Use issued_quantity for GRN line totals (actual received amount), fallback to requested_quantity
  const qty = parseFloat(p.issued_quantity ?? p.requested_quantity) || 0
  const price = parseFloat(p.purchase_price) || 0
  const discount = parseFloat(p.discount) || 0

  p.total = (qty * price) - discount
}

const formatNumber = (number) => {
  return parseFloat(number || 0).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

// 🔥 FIXED WATCH — no form.reset()
watch(
    () => props.open,
    (newVal) => {
        if (newVal) {
            resetForm()
        }
    }
)

const submitForm = () => {
  const payload = {
    ...form.value,
    products: products.value,
  }

  router.post(route('good-receive-notes.store'), payload, {
    onSuccess: async () => {
      // Log create activity
      await logActivity('create', 'goods_received_notes', {
        grn_number: form.value.goods_received_note_no,
        grn_date: form.value.goods_received_note_date,
        supplier_id: form.value.supplier_id,
        purchase_order_id: form.value.purchase_order_request_id,
        products_count: products.value.length,
      });
      
      close();
    },
    onError: (e) => console.error('GRN create error:', e),
  })
}
</script>
