<template>
  <div v-if="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-900 rounded-lg p-6 w-full max-w-4xl max-h-screen overflow-y-auto">

      <h2 class="text-2xl font-bold text-white mb-4">Create New PRN</h2>

      <form @submit.prevent="submitForm">

        <!-- PRN DETAILS -->
        <h3 class="text-lg font-semibold text-white mb-4">PRN Details</h3>

        <div class="grid grid-cols-2 gap-4 mb-4">

          <div>
            <label class="block text-white mb-2">Product Transfer Request *</label>
            <select v-model.number="form.ptr_id" @change="onPtrSelect"
                    class="w-full px-3 py-2 bg-gray-800 text-white rounded" required>
              <option :value="null">Select PTR</option>
              <option v-for="ptr in productTransferRequests" :key="ptr.id" :value="ptr.id">
                {{ ptr.product_transfer_request_no }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-white mb-2">Release Date *</label>
            <input v-model="form.release_date" type="date"
                   class="w-full px-3 py-2 bg-gray-800 text-white rounded" required />
          </div>

          <div>
            <label class="block text-white mb-2">Status</label>
            <select v-model.number="form.status" class="w-full px-3 py-2 bg-gray-800 text-white rounded">
              <option :value="0">Pending</option>
              <option :value="1">Released</option>
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
          <textarea v-model="form.remark" rows="3"
                    class="w-full px-3 py-2 bg-gray-800 text-white rounded"></textarea>
        </div>

        <!-- PRODUCTS -->
        <div class="mt-6">
          <div class="flex justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Products *</h3>
            <button type="button" @click="addProduct"
                    class="px-3 py-1 bg-green-600 text-white rounded">+ Add Product</button>
          </div>

          <table class="w-full text-white text-sm">
            <thead class="bg-blue-600">
              <tr>
                <th class="px-4 py-2">Product</th>
                <th class="px-4 py-2">Unit</th>
                <th class="px-4 py-2">Qty</th>
                <th class="px-4 py-2">Unit Price</th>
                <th class="px-4 py-2">Total</th>
                <th class="px-4 py-2">Action</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="(product, index) in products" :key="index" class="border-b border-gray-700">

                <td class="px-4 py-2">
                  <div v-if="product.product_name && !product.isManual">
                    {{ product.product_name }}
                  </div>
                  <select v-else v-model.number="product.product_id" @change="onProductSelect(index)"
                          class="w-full px-2 py-1 bg-gray-800 text-white rounded">
                    <option :value="null">Select Product</option>
                    <option v-for="prod in availableProducts" :key="prod.id" :value="prod.id">
                      {{ prod.name }}
                    </option>
                  </select>
                </td>

                <td class="px-4 py-2">{{ product.unit }}</td>

                <td class="px-4 py-2">
                  <input v-model.number="product.qty" type="number"
                         @input="calculateTotal(index)"
                         class="w-full px-2 py-1 bg-gray-800 text-white rounded">
                </td>

                <td class="px-4 py-2">
                  <input v-model.number="product.unit_price" type="number" step="0.01"
                         @input="calculateTotal(index)"
                         class="w-full px-2 py-1 bg-gray-800 text-white rounded">
                </td>

                <td class="px-4 py-2">Rs. {{ formatNumber(product.total) }}</td>

                <td class="px-4 py-2">
                  <button @click="removeProduct(index)"
                          class="px-2 py-1 bg-red-600 text-white rounded">Remove</button>
                </td>

              </tr>
            </tbody>

            <tfoot v-if="products.length > 0" class="bg-gray-800">
              <tr>
                <td colspan="4" class="px-4 py-3 text-right font-semibold">Grand Total:</td>
                <td class="px-4 py-3 font-bold text-lg">Rs. {{ formatNumber(grandTotal) }}</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div class="flex justify-end gap-2 mt-6">
          <button @click="close" class="px-4 py-2 bg-gray-700 text-white rounded">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Create PRN</button>
        </div>

      </form>
    </div>
  </div>
</template>


<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { logActivity } from '@/composables/useActivityLog'
import axios from 'axios'

const props = defineProps({
  open: Boolean,
  availableProducts: Array,
  productTransferRequests: Array,
  users: Array,
})

const emit = defineEmits(['update:open'])

const form = ref({
  ptr_id: null,
  user_id: null,
  release_date: new Date().toISOString().split('T')[0],
  status: 0,
  remark: '',
})

const products = ref([])

const close = () => {
  emit('update:open', false)
  resetForm()
}

const resetForm = () => {
  form.value = {
    ptr_id: null,
    user_id: null,
    release_date: new Date().toISOString().split('T')[0],
    status: 0,
    remark: '',
  }
  products.value = []
}

// 🔥 Load PTR products when selected
const onPtrSelect = async () => {
  if (!form.value.ptr_id) {
    products.value = []
    return
  }

  try {
    const response = await axios.get(`/ptr/${form.value.ptr_id}/details`)
    
    console.log('API Response:', response.data)

    const ptrData = response.data

    if (!ptrData.ptrProducts || ptrData.ptrProducts.length === 0) {
      products.value = []
      return
    }

    products.value = ptrData.ptrProducts.map(item => {
      const quantity = Number(item.qty) || 0
      const price = Number(item.price) || 0

      return {
        product_id: item.product_id,
        product_name: item.name,
        qty: quantity,
        unit_price: price,
        unit: item.unit || 'N/A',
        total: quantity * price,
        isManual: false, // Flag to indicate this is from PTR
      }
    })

    console.log('Mapped products:', products.value)

  } catch (error) {
    console.error('Failed to load PTR details:', error)
    products.value = []
  }
}

// Add manual product
const addProduct = () => {
  products.value.push({
    product_id: null,
    product_name: '',
    qty: 1,
    unit_price: 0,
    unit: '',
    total: 0,
    isManual: true, // Flag to indicate this is manually added
  })
}

const removeProduct = (index) => products.value.splice(index, 1)

const onProductSelect = (index) => {
  const p = products.value[index]
  const prod = props.availableProducts.find(a => a.id === p.product_id)

  if (prod) {
    p.product_name = prod.name
    p.unit_price = prod.price
    p.unit = prod.measurementUnit?.name || 'N/A'
    calculateTotal(index)
  }
}

const calculateTotal = (index) => {
  const p = products.value[index]
  p.total = p.qty * p.unit_price
}

const grandTotal = computed(() =>
  products.value.reduce((sum, p) => sum + p.total, 0)
)

const formatNumber = (n) => Number(n).toFixed(2)

const submitForm = () => {
  const mappedProducts = products.value.map(p => ({
    product_id: p.product_id,
    quantity: p.qty,
    unit_price: p.unit_price,
    total: p.total
  }))

  router.post(route('product-release-notes.store'), {
    ...form.value,
    products: mappedProducts
  }, {
    onSuccess: async () => {
      // Log create activity
      await logActivity('create', 'product_release_notes', {
        ptr_id: form.value.ptr_id,
        user_id: form.value.user_id,
        release_date: form.value.release_date,
        products_count: mappedProducts.length,
      });
      
      close();
    },
  })
}
</script>
