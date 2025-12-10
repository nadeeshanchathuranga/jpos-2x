<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-75">
    <div class="relative w-full max-w-6xl p-6 mx-4 my-8 bg-black border-4 border-blue-600 rounded-lg max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-white">Create Product Return</h2>
        <button @click="closeModal" class="text-white hover:text-gray-300">
          <i class="text-2xl fas fa-times"></i>
        </button>
      </div>

      <!-- Search Sales Products -->
      <div class="bg-gray-900 rounded-lg p-6 shadow-lg mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">🔍 Find Sales Products</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-gray-300 mb-2">Search</label>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Customer, Sale No, Product..."
              class="w-full px-3 py-2 bg-gray-800 text-white border border-gray-600 rounded-lg"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">From Date</label>
            <input
              v-model="dateFrom"
              type="date"
              class="w-full px-3 py-2 bg-gray-800 text-white border border-gray-600 rounded-lg"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">To Date</label>
            <input
              v-model="dateTo"
              type="date"
              class="w-full px-3 py-2 bg-gray-800 text-white border border-gray-600 rounded-lg"
            />
          </div>
          <div class="flex items-end">
            <button
              @click="searchProducts"
              class="w-full px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Search
            </button>
          </div>
        </div>
      </div>

      <!-- Selected Products -->
      <div v-if="selectedProducts.length > 0" class="bg-green-900 rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-white">Selected Products ({{ selectedProducts.length }})</h3>
          <button
            @click="clearSelection"
            class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700"
          >
            Clear All
          </button>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-white text-sm">
            <thead class="bg-green-800">
              <tr>
                <th class="px-3 py-2">Product</th>
                <th class="px-3 py-2">Sale Info</th>
                <th class="px-3 py-2 text-center">Sold Qty</th>
                <th class="px-3 py-2 text-center">Return Qty</th>
                <th class="px-3 py-2 text-center">Unit Price</th>
                <th class="px-3 py-2 text-center">Refund</th>
                <th class="px-3 py-2 text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in selectedProducts" :key="product.id" class="border-b border-green-800">
                <td class="px-3 py-2">
                  <div class="font-medium">{{ product.product_name }}</div>
                  <div class="text-xs text-gray-300">{{ product.product_barcode }}</div>
                </td>
                <td class="px-3 py-2">
                  <div class="text-xs">{{ product.sale_no }}</div>
                  <div class="text-xs text-gray-300">{{ product.customer_name }}</div>
                </td>
                <td class="px-3 py-2 text-center">{{ product.quantity_sold }}</td>
                <td class="px-3 py-2 text-center">
                  <input
                    v-model.number="product.return_quantity"
                    type="number"
                    min="1"
                    :max="product.quantity_sold"
                    class="w-20 px-2 py-1 bg-gray-800 text-white border border-gray-600 rounded text-center"
                  />
                </td>
                <td class="px-3 py-2 text-center">Rs. {{ product.formatted_price }}</td>
                <td class="px-3 py-2 text-center font-semibold text-green-300">
                  Rs. {{ ((product.return_quantity || 0) * parseFloat(product.price || 0)).toFixed(2) }}
                </td>
                <td class="px-3 py-2 text-center">
                  <button
                    @click="removeProduct(product.id)"
                    class="px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700"
                  >
                    Remove
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Available Products -->
      <div class="bg-gray-900 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Available Sales Products</h3>
        <div class="overflow-x-auto max-h-96">
          <table class="w-full text-white text-sm">
            <thead class="bg-gray-800 sticky top-0">
              <tr>
                <th class="px-3 py-2">Product</th>
                <th class="px-3 py-2">Sale No</th>
                <th class="px-3 py-2">Customer</th>
                <th class="px-3 py-2 text-center">Qty Sold</th>
                <th class="px-3 py-2 text-center">Price</th>
                <th class="px-3 py-2 text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in filteredProducts" :key="product.id" class="border-b border-gray-700 hover:bg-gray-800">
                <td class="px-3 py-2">
                  <div class="font-medium">{{ product.product_name }}</div>
                  <div class="text-xs text-gray-400">{{ product.product_barcode }}</div>
                </td>
                <td class="px-3 py-2">
                  <div class="font-medium">{{ product.sale_no }}</div>
                  <div class="text-xs text-gray-400">{{ product.sale_date_formatted }}</div>
                </td>
                <td class="px-3 py-2">
                  <div>{{ product.customer_name }}</div>
                  <div class="text-xs text-gray-400">{{ product.customer_phone || '' }}</div>
                </td>
                <td class="px-3 py-2 text-center">{{ product.quantity_sold }}</td>
                <td class="px-3 py-2 text-center">Rs. {{ product.formatted_price }}</td>
                <td class="px-3 py-2 text-center">
                  <button
                    v-if="!isSelected(product.id)"
                    @click="addProduct(product)"
                    class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700"
                  >
                    + Add
                  </button>
                  <span v-else class="px-3 py-1 bg-green-600 text-white text-xs rounded">
                    Selected
                  </span>
                </td>
              </tr>
              <tr v-if="filteredProducts.length === 0">
                <td colspan="6" class="px-3 py-8 text-center text-gray-400">
                  <div class="text-4xl mb-2">🛒</div>
                  <div class="text-lg font-semibold mb-1">No sales products available</div>
                  <div class="text-sm">Try adjusting your search filters or date range</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end gap-4">
        <button
          @click="closeModal"
          class="px-6 py-2 text-white bg-gray-600 rounded hover:bg-gray-700"
        >
          Cancel
        </button>
        <button
          @click="submitReturn"
          :disabled="selectedProducts.length === 0 || processing"
          class="px-6 py-2 text-white bg-green-600 rounded hover:bg-green-700 disabled:opacity-50"
        >
          {{ processing ? 'Creating...' : 'Create Return' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  open: Boolean,
  salesProducts: Object,
})

const emit = defineEmits(['update:open', 'success'])

const searchQuery = ref('')
const dateFrom = ref('')
const dateTo = ref('')
const selectedProducts = ref([])
const processing = ref(false)

const filteredProducts = computed(() => {
  let products = props.salesProducts?.data || []
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    products = products.filter(p => 
      p.product_name?.toLowerCase().includes(query) ||
      p.sale_no?.toLowerCase().includes(query) ||
      p.customer_name?.toLowerCase().includes(query) ||
      p.product_barcode?.toLowerCase().includes(query)
    )
  }
  
  if (dateFrom.value) {
    products = products.filter(p => p.sale_date >= dateFrom.value)
  }
  
  if (dateTo.value) {
    products = products.filter(p => p.sale_date <= dateTo.value)
  }
  
  return products
})

const searchProducts = () => {
  router.get(route('return.index'), {
    sales_search: searchQuery.value,
    sales_date_from: dateFrom.value,
    sales_date_to: dateTo.value,
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['salesProducts']
  })
}

const isSelected = (productId) => {
  return selectedProducts.value.some(p => p.id === productId)
}

const addProduct = (product) => {
  selectedProducts.value.push({
    ...product,
    return_quantity: 1
  })
}

const removeProduct = (productId) => {
  selectedProducts.value = selectedProducts.value.filter(p => p.id !== productId)
}

const clearSelection = () => {
  selectedProducts.value = []
}

const closeModal = () => {
  emit('update:open', false)
  clearSelection()
  searchQuery.value = ''
  dateFrom.value = ''
  dateTo.value = ''
}

const submitReturn = () => {
  if (selectedProducts.value.length === 0) return

  const invalidProducts = selectedProducts.value.filter(p => 
    !p.return_quantity || p.return_quantity < 1 || p.return_quantity > p.quantity_sold
  )

  if (invalidProducts.length > 0) {
    alert('Please enter valid return quantities for all selected products.')
    return
  }

  processing.value = true

  router.post(route('return.create-from-sales'), {
    selected_products: selectedProducts.value.map(p => ({
      sales_product_id: p.id,
      return_quantity: p.return_quantity
    }))
  }, {
    onSuccess: () => {
      emit('success')
      closeModal()
      processing.value = false
    },
    onError: (errors) => {
      console.error('Return creation errors:', errors)
      processing.value = false
    }
  })
}
</script>
