<template>
  <div v-if="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-900 rounded-lg p-6 w-full max-w-4xl max-h-screen overflow-y-auto">
      
      <h2 class="text-2xl font-bold text-white mb-4">Create New GRN Return</h2>

      <form @submit.prevent="submitForm">

        <!-- GRN DETAILS -->
        <div class="mb-6">
          <h3 class="text-lg font-semibold text-white mb-4">GRN Details</h3>

          <div class="grid grid-cols-2 gap-4 mb-4">

            <div>
              <label class="block text-white mb-2">GRN Number *</label>
              <select v-model="form.grn_id" @change="onGrnSelect" class="w-full px-3 py-2 bg-gray-800 text-white rounded" required>
                <option value="">Select GRN Number</option>
                <option v-for="g in grns" :key="g.id" :value="g.id">{{ g.grn_no }}</option>
              </select>
            </div>

            <div>
              <label class="block text-white mb-2"> Date *</label>
              <input v-model="form.grn_date" type="date" class="w-full px-3 py-2 bg-gray-800 text-white rounded" required />
            </div> 
          </div>
        </div>

         <!-- PRODUCTS SECTION -->
        <div class="mb-6">

          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Products *</h3>
            <button type="button" @click="addProduct"
                    class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
              + Add Product
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-white text-sm">
              <thead class="bg-blue-600">
                <tr>
                  <th class="px-4 py-2">Product</th>
                  <th class="px-4 py-2">Unit</th>
                  <th class="px-4 py-2">Qty</th>
                  <th class="px-4 py-2">Purchase Price</th>
                  <th class="px-4 py-2">Discount</th>
                  <th class="px-4 py-2">Total</th>
                  <th class="px-4 py-2">Return Qty </th>
                  <th class="px-4 py-2">Action</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="(product, index) in products" :key="index" class="border-b border-gray-700">

                  <td class="px-1 py-2">
                    <div class="text-white">{{ product.product_name || (availableProducts.find(p => p.id === product.product_id)?.name) || 'N/A' }}</div>
                  </td>

                  <td class="px-1 py-2">
                    <div class="text-white">{{ getUnitName(product) }}</div>
                  </td>

                  <td class="px-4 py-2">
                    <div class="text-white">{{ formatNumber(product.qty) }}</div>
                  </td>

                  <td class="px-8 py-2">
                    <div class="text-white">Rs. {{ formatNumber(product.purchase_price) }}</div>
                  </td>

                  <td class="px-4 py-2">
                    <div class="text-white">Rs. {{ formatNumber(product.discount) }}</div>
                  </td>

                  <td class="px-4 py-2">
                    <span class="font-semibold">
                      Rs. {{ formatNumber(product.total) }}
                    </span>
                  </td>

                  <td class="px-4 py-2">
                    <input v-model.number="product.returnQty" type="number" step="0.01" min="0.01"
                           @input="calculateTotal(index)"
                           class="w-full px-2 py-1 bg-gray-800 text-white rounded" />
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
            Create GRN Return
          </button>
        </div>

      </form>
    </div>
  </div>
</template>


 <script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  open: Boolean,
  suppliers: Array,
  purchaseOrders: Array,
  availableProducts: Array,
  measurementUnits: Array,
  grns: Array,
  user: Object,
})

const emit = defineEmits(['update:open'])

const form = ref({
  grn_id: '',
  grn_date: new Date().toISOString().split('T')[0],
  por_id: '',
  discount: 0,
  tax_total: 0,
  remarks: '',
  user_id: props.user?.id || null,
})

const products = ref([])
const formErrors = ref({})

const grandTotal = computed(() => {
  return products.value.reduce((sum, product) => sum + (parseFloat(product.total) || 0), 0)
})

const close = () => {
  emit('update:open', false)
  resetForm()
}

const resetForm = () => {
  form.value = {
    grn_id: '',
    grn_date: new Date().toISOString().split('T')[0],
    por_id: '',
    discount: 0,
    tax_total: 0,
    remarks: '',
    user_id: props.user?.id || null,
  }
  products.value = []
}

const addProduct = () => {
  products.value.push({
    product_id: null,
    product_name: '',
    qty: 1,
    purchase_price: 0,
    discount: 0,
    unit_id: null,
    unit_name: '',
    total: 0,
  })
}

const loadPOData = () => {
    if (!form.value.por_id) {
        products.value = [];
        return;
    }

    router.get(`/po/${form.value.por_id}/details`, {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page) => {
            const poProducts = page.props.poProducts || [];

            if (poProducts.length === 0) {
                console.warn('No products found in this PO');
                return;
            }

            products.value = poProducts.map(item => {
                const qty = parseFloat(item.quantity) || 1;
                const purchasePrice = parseFloat(item.price) || parseFloat(item.unit_price) || 0;
                const total = qty * purchasePrice;
                
                return {
                    product_id: item.product_id,
                    qty: qty,
                    purchase_price: purchasePrice,
                    discount: 0,
                    unit: item.unit || '',
                    total: total,
                };
            });

            console.log('Loaded PO products:', products.value.length);
        },
        onError: (errors) => {
            console.error('Failed to load PO data:', errors);
            alert('Failed to load Purchase Order details');
        },
    });
};

const onGrnSelect = () => {
  console.log('onGrnSelect called, grn_id=', form.value.grn_id)
  console.log('props.grns length:', (props.grns || []).length)

  if (!form.value.grn_id) {
    products.value = [];
    return;
  }

  // Find selected GRN from props (we eager-loaded grnProducts in controller)
  const selectedGrn = props.grns.find(g => Number(g.id) === Number(form.value.grn_id));

  if (!selectedGrn) {
    products.value = [];
    return;
  }

  // relation may be available as `grnProducts` or `grn_products` depending on serialization
  const grnProducts = selectedGrn.grnProducts || selectedGrn.grn_products || [];

  if (!grnProducts.length) {
    products.value = [];
    return;
  }

  products.value = grnProducts.map(item => {
    // item may include nested `product`
    const nestedProduct = item.product || item.product || {};
    const qty = parseFloat(item.qty ?? item.quantity ?? 1) || 1;
    const purchasePrice = parseFloat(item.purchase_price ?? item.price ?? nestedProduct.price ?? 0) || 0;
    const total = qty * purchasePrice;

    const discount = parseFloat(item.discount ?? nestedProduct.discount ?? 0) || 0;

    return {
      product_id: item.product_id ?? nestedProduct.id ?? null,
      product_name: nestedProduct.name ?? nestedProduct.product_name ?? null,
      qty: qty,
      purchase_price: purchasePrice,
      discount: discount,
      unit_id: item.unit_id ?? nestedProduct.purchase_unit_id ?? null,
      unit_name: item.unit_name ?? nestedProduct.purchaseUnit?.name ?? nestedProduct.measurement_unit?.name ?? nestedProduct.measurementUnit?.name ?? '',
      total: total - discount,
      returnQty: 0,
    };
  });

  if (selectedGrn.grn_date) {
    form.value.grn_date = selectedGrn.grn_date;
  }

  console.log('Loaded GRN products from props:', products.value.length);
  console.log('measurementUnits (props):', props.measurementUnits)
};

const removeProduct = (index) => {
  products.value.splice(index, 1)
}

const onProductSelect = (index) => {
  const product = products.value[index]
  const selectedProduct = props.availableProducts.find(p => p.id === product.product_id)

  if (selectedProduct) {
    product.purchase_price = selectedProduct.price || selectedProduct.purchase_price || 0
    product.unit_id = selectedProduct.purchase_unit_id ?? null
    product.unit_name = selectedProduct.purchaseUnit?.name ?? ''
    product.product_name = selectedProduct.name || selectedProduct.product_name || ''
    calculateTotal(index)
  }
}

const calculateTotal = (index) => {
  const p = products.value[index]
  const qty = parseFloat(p.qty) || 0
  const price = parseFloat(p.purchase_price) || 0
  const discount = parseFloat(p.discount) || 0

  p.total = (qty * price) - discount
}

const getUnitName = (product) => {
  // Prefer explicit unit_name if already present
  if (product.unit_name && String(product.unit_name).trim() !== '') return product.unit_name

  // Resolve by unit_id against measurementUnits (allow string/number mismatches)
  const uid = product.unit_id ?? product.unitId ?? null
  if (uid != null && Array.isArray(props.measurementUnits)) {
    const asNumber = Number(uid)
    const found = props.measurementUnits.find(u => {
      // handle case where u.id may be string or number
      return (u.id == uid) || (Number(u.id) === asNumber)
    })
    if (found && (found.name || found.unit_name)) return found.name || found.unit_name
  }

  // Try nested product data shapes (some payloads have nested purchaseUnit / measurementUnit)
  if (product.product) {
    const nested = product.product
    if (nested.purchaseUnit?.name) return nested.purchaseUnit.name
    if (nested.measurementUnit?.name) return nested.measurementUnit.name
    if (nested.measurement_unit?.name) return nested.measurement_unit.name
  }

  return 'N/A'
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
      console.log('CreateModal opened — measurementUnits prop:', props.measurementUnits)
        }
    }
)

const submitForm = () => {
  // Build payload with keys that match backend validation (note: backend expects `date`, not `grn_date`)
  const payload = {
    grn_id: form.value.grn_id,
    date: form.value.grn_date,
    user_id: form.value.user_id,
    remarks: form.value.remarks,
    // send products with `qty` set to the return quantity if provided
    products: products.value.map(p => ({
      product_id: p.product_id,
      qty: p.returnQty && Number(p.returnQty) > 0 ? Number(p.returnQty) : Number(p.qty || 0),
      remarks: p.remarks || null,
    })),
  }

  console.log('Submitting GRN Return payload:', payload)

  router.post(route('grn-returns.store'), payload, {
    onSuccess: () => {
      formErrors.value = {}
      close()
    },
    onError: (errors) => {
      // errors may be an object of validation errors (field => [msgs]) or an Error-like object
      formErrors.value = errors || {}
      try {
        console.error('GRN return create error (details):', errors ? JSON.parse(JSON.stringify(errors)) : errors)
      } catch (err) {
        console.error('GRN return create error (raw):', errors)
      }
      // show a simple alert so the user notices (optional)
      if (errors && typeof errors === 'object' && Object.keys(errors).length) {
        const firstKey = Object.keys(errors)[0]
        const firstMsg = Array.isArray(errors[firstKey]) ? errors[firstKey][0] : errors[firstKey]
        alert('Failed to create GRN Return: ' + (firstMsg || 'See console for details'))
      } else {
        alert('Failed to create GRN Return. See console for details.')
      }
    },
  })
}
</script>
