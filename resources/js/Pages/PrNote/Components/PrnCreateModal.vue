<template>
  <div v-if="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-900 rounded-lg p-6 w-full max-w-4xl max-h-screen overflow-y-auto">
      
      <h2 class="text-2xl font-bold text-white mb-4">Create New PRN</h2>

      <form @submit.prevent="submitForm">

        <!-- PRN DETAILS -->
        <div class="mb-6">
          <h3 class="text-lg font-semibold text-white mb-4">PRN Details</h3>

          <div class="grid grid-cols-2 gap-4 mb-4">
 
            <div>
              <label class="block text-white mb-2">PTR *</label>
              <select v-model.number="form.ptr_id" class="w-full px-3 py-2 bg-gray-800 text-white rounded" required>
                <option :value="null">Select PTR</option>
                <option v-for="ptr in ptrs" :key="ptr.id" :value="ptr.id">
                  {{ ptr.transfer_no }} 
                </option>
              </select>
            </div>

            <div>
              <label class="block text-white mb-2">Release Date *</label>
              <input v-model="form.release_date" type="date" class="w-full px-3 py-2 bg-gray-800 text-white rounded" required />
            </div>

            <div>
              <label class="block text-white mb-2">Status</label>
              <select v-model="form.status" class="w-full px-3 py-2 bg-gray-800 text-white rounded">
                <option value="draft">Draft</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
              </select>
            </div>

            <div>
              <label class="block text-white mb-2">User</label>
              <select v-model.number="form.user_id" class="w-full px-3 py-2 bg-gray-800 text-white rounded">
                <option :value="null">Select User</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
            </div>

          </div>

          <div>
            <label class="block text-white mb-2">Remark</label>
            <textarea v-model="form.remark" rows="3" class="w-full px-3 py-2 bg-gray-800 text-white rounded"></textarea>
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
                        {{ prod.name }} - Rs. {{ formatNumber(prod.price) }}
                      </option>
                    </select>
                  </td>

                  <td class="px-4 py-2">
                    <span class="text-gray-300">
                      {{ product.unit || 'N/A' }}
                    </span>
                  </td>

                  <td class="px-4 py-2">
                    <input v-model.number="product.qty" type="number" step="0.01" min="0.01"
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
            Create PRN
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  open: Boolean,
  suppliers: Array,
  availableProducts: Array,
  ptrs: Array,
  users: Array,
})

const emit = defineEmits(['update:open'])

const form = ref({
  ptr_id: null,
  user_id: null,
  release_date: new Date().toISOString().split('T')[0],
  status: 'draft',
  remark: '',
})

const products = ref([])

const grandTotal = computed(() => {
  return products.value.reduce((sum, product) => sum + (parseFloat(product.total) || 0), 0)
})

const close = () => {
  emit('update:open', false)
  resetForm()
}

const resetForm = () => {
  form.value = {
    ptr_id: null,
    user_id: null,
    release_date: new Date().toISOString().split('T')[0],
    status: 'draft',
    remark: '',
  }
  products.value = []
}

const addProduct = () => {
  products.value.push({
    product_id: null,
    qty: 1,
    purchase_price: 0,
    discount: 0,
    unit: '',
    total: 0,
  })
}

const removeProduct = (index) => {
  products.value.splice(index, 1)
}

const onProductSelect = (index) => {
  const product = products.value[index]
  const selectedProduct = props.availableProducts.find(p => p.id === product.product_id)

  if (selectedProduct) {
    product.purchase_price = selectedProduct.price || 0
    product.unit = selectedProduct.measurementUnit?.name || 'N/A'
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

const formatNumber = (number) => {
  return parseFloat(number || 0).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

const submitForm = () => {
  const payload = {
    ...form.value,
    products: products.value.map(p => ({
      product_id: p.product_id,
      qty: p.qty,
      purchase_price: p.purchase_price,
      discount: p.discount,
    })),
  }

  router.post(route('prn.store'), payload, {
    onSuccess: () => close(),
    onError: (e) => console.error('PRN create error:', e),
  })
}
</script>
