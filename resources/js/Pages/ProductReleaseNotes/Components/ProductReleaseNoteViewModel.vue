<template>
  <div v-if="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-900 rounded-lg p-6 w-full max-w-5xl max-h-screen overflow-y-auto">

      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-white">View Product Release Note Details</h2>
        <button @click="close" class="text-gray-400 hover:text-white">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- PRODUCT RELEASE NOTE DETAILS -->
      <div class="bg-gray-800 rounded-lg p-4 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Release Note Information</h3>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-gray-400 text-sm mb-1">Release Date</label>
            <p class="text-white">{{ formatDate(productReleaseNote.release_date) }}</p>
          </div>

          <div>
            <label class="block text-gray-400 text-sm mb-1">Status</label>
            <span :class="['px-3 py-1 rounded text-sm font-semibold', productReleaseNote.status === 0 ? 'bg-yellow-500 text-black' : 'bg-green-500 text-white']">
              {{ getStatusLabel(productReleaseNote.status) }}
            </span>
          </div>

          <div v-if="productReleaseNote.user">
            <label class="block text-gray-400 text-sm mb-1">User</label>
            <p class="text-white">{{ productReleaseNote.user.name }}</p>
          </div>

          <div v-if="productReleaseNote.product_transfer_request">
            <label class="block text-gray-400 text-sm mb-1">PTR No</label>
            <p class="text-white">{{ productReleaseNote.product_transfer_request.product_transfer_request_no  }}</p>
          </div>

          <div class="col-span-2" v-if="productReleaseNote.remark">
            <label class="block text-gray-400 text-sm mb-1">Remark</label>
            <p class="text-white">{{ productReleaseNote.remark }}</p>
          </div>
        </div>
      </div>

      <!-- PRODUCTS TABLE -->
      <div class="bg-gray-800 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-white mb-4">Products</h3>
        
        <div class="overflow-x-auto">
          <table class="w-full text-white text-sm">
            <thead class="bg-blue-600">
              <tr>
                <th class="px-4 py-2 text-left">#</th>
                <th class="px-4 py-2 text-left">Product Name</th>
                <th class="px-4 py-2 text-right">Quantity</th>
                <th class="px-4 py-2 text-right">Unit Price ({{ currencySymbol }})</th>
                <th class="px-4 py-2 text-right">Total ({{ currencySymbol }})</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="(item, index) in getProducts()" 
                :key="index"
                class="border-b border-gray-700"
              >
                <td class="px-4 py-3">{{ index + 1 }}</td>
                <td class="px-4 py-3">{{ getProductName(item) }}</td>
                <td class="px-4 py-3 text-right">{{ formatNumber(item.quantity) }}</td>
                <td class="px-4 py-3 text-right">{{ formatNumber(getUnitPrice(item)) }}</td>
                <td class="px-4 py-3 text-right">{{ formatNumber(getTotal(item)) }}</td>
              </tr>
              <tr v-if="getProducts().length === 0">
                <td colspan="5" class="px-4 py-3 text-center text-gray-400">
                  No products found
                </td>
              </tr>
            </tbody>
            <tfoot class="bg-gray-700">
              <tr>
                <td colspan="4" class="px-4 py-3 text-right font-bold">Grand Total:</td>
                <td class="px-4 py-3 text-right font-bold text-lg">
                  {{ currencySymbol }} {{ formatNumber(grandTotal) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div class="flex justify-end mt-6">
        <button @click="close" class="px-6 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">
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
  productReleaseNote: Object,
})

const emit = defineEmits(['update:open'])

const close = () => {
  emit('update:open', false)
}

// Get currency symbol from page props
const currencySymbol = computed(() => {
  return page.props.currencySymbol?.currency_symbol || page.props.currency || '$'
})

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const formatNumber = (number) => {
  return parseFloat(number || 0).toFixed(2)
}

const getStatusLabel = (status) => {
  const labels = {
    0: 'Pending',
    1: 'Released'
  }
  return labels[status] || 'Unknown'
}

// Get products array from product release note
const getProducts = () => {
  return props.productReleaseNote?.product_release_note_products || []
}

// Get product name
const getProductName = (item) => {
  return item.product?.name || item.product_name || 'N/A'
}

// Get unit price
const getUnitPrice = (item) => {
  return item.unit_price ?? item.price ?? 0
}

// Get total
const getTotal = (item) => {
  if (item.total !== undefined) return item.total
  const qty = parseFloat(item.quantity || 0)
  const price = parseFloat(getUnitPrice(item))
  return qty * price
}

const grandTotal = computed(() => {
  return getProducts().reduce((sum, item) => sum + (parseFloat(getTotal(item)) || 0), 0)
})
</script>