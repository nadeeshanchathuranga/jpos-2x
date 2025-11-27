<template>
  <div v-if="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-900 rounded-lg p-6 w-full max-w-5xl max-h-screen overflow-y-auto">

      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-white">View PRN Details</h2>
        <button @click="close" class="text-gray-400 hover:text-white">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- PRN DETAILS -->
      <div class="bg-gray-800 rounded-lg p-4 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">PRN Information</h3>
        <div class="grid grid-cols-2 gap-4">
          
          <div>
            <label class="block text-gray-400 text-sm mb-1">Release Date</label>
            <p class="text-white">{{ formatDate(prn.release_date) }}</p>
          </div>

          <div>
            <label class="block text-gray-400 text-sm mb-1">Status</label>
            <span :class="['px-3 py-1 rounded text-sm font-semibold', prn.status === 0 ? 'bg-yellow-500 text-black' : 'bg-green-500 text-white']">
              {{ getStatusLabel(prn.status) }}
            </span>
          </div>

          <div v-if="prn.user">
            <label class="block text-gray-400 text-sm mb-1">User</label>
            <p class="text-white">{{ prn.user.name }}</p>
          </div>

          <div v-if="prn.ptr">
            <label class="block text-gray-400 text-sm mb-1">PTR No</label>
            <p class="text-white">{{ prn.ptr.transfer_no }}</p>
          </div>

          <div class="col-span-2" v-if="prn.remark">
            <label class="block text-gray-400 text-sm mb-1">Remark</label>
            <p class="text-white">{{ prn.remark }}</p>
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
                <th class="px-4 py-2 text-right">Unit Price</th>
                <th class="px-4 py-2 text-right">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="(item, index) in prn.prn_products" 
                :key="index"
                class="border-b border-gray-700"
              >
                <td class="px-4 py-3">{{ index + 1 }}</td>
                <td class="px-4 py-3">{{ item.product?.name || 'N/A' }}</td>
                <td class="px-4 py-3 text-right">{{ formatNumber(item.quantity) }}</td>
                <td class="px-4 py-3 text-right">Rs. {{ formatNumber(item.unit_price) }}</td>
                <td class="px-4 py-3 text-right">Rs. {{ formatNumber(item.total) }}</td>
              </tr>
              <tr v-if="!prn.prn_products || prn.prn_products.length === 0">
                <td colspan="5" class="px-4 py-3 text-center text-gray-400">
                  No products found
                </td>
              </tr>
            </tbody>
            <tfoot class="bg-gray-700">
              <tr>
                <td colspan="4" class="px-4 py-3 text-right font-bold">Grand Total:</td>
                <td class="px-4 py-3 text-right font-bold text-lg">
                  Rs. {{ formatNumber(grandTotal) }}
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

const props = defineProps({
  open: Boolean,
  prn: Object,
})

const emit = defineEmits(['update:open'])

const close = () => {
  emit('update:open', false)
}

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

const grandTotal = computed(() => {
  if (!props.prn?.prn_products) return 0
  return props.prn.prn_products.reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0)
})
</script>
