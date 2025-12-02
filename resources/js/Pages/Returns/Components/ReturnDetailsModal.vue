<template>
  <TransitionRoot appear :show="open" as="template">
    <Dialog as="div" @close="closeModal" class="relative z-10">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/25" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-full p-4 text-center">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel
              class="w-full max-w-4xl p-6 overflow-hidden text-left align-middle transition-all transform bg-gray-800 shadow-xl rounded-2xl"
            >
              <DialogTitle
                as="h3"
                class="text-2xl font-bold text-white mb-6"
              >
                Return Details #{{ returnData?.id }}
              </DialogTitle>

              <div v-if="returnData" class="space-y-6">
                <!-- Return Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="bg-gray-700 p-4 rounded-lg">
                    <h3 class="font-semibold text-white mb-3">Return Information</h3>
                    <div class="space-y-2 text-sm">
                      <div><span class="text-gray-400">Return ID:</span> <span class="text-white">#{{ returnData.id }}</span></div>
                      <div><span class="text-gray-400">Return Date:</span> <span class="text-white">{{ returnData.return_date_formatted }}</span></div>
                      <div><span class="text-gray-400">Status:</span> 
                        <span
                          :class="{
                            'text-yellow-400': returnData.status_color === 'yellow',
                            'text-green-400': returnData.status_color === 'green',
                            'text-red-400': returnData.status_color === 'red',
                            'text-gray-400': returnData.status_color === 'gray'
                          }"
                          class="font-semibold"
                        >
                          {{ returnData.status_text }}
                        </span>
                      </div>
                    </div>
                  </div>
                  <div class="bg-gray-700 p-4 rounded-lg">
                    <h3 class="font-semibold text-white mb-3">Customer & Sale Info</h3>
                    <div class="space-y-2 text-sm">
                      <div><span class="text-gray-400">Customer:</span> <span class="text-white">{{ returnData.customer_name || 'Walk-in Customer' }}</span></div>
                      <div v-if="returnData.customer_phone"><span class="text-gray-400">Phone:</span> <span class="text-white">{{ returnData.customer_phone }}</span></div>
                      <div><span class="text-gray-400">Sale No:</span> <span class="text-white">{{ returnData.sale_no || 'N/A' }}</span></div>
                      <div><span class="text-gray-400">Processed by:</span> <span class="text-white">{{ returnData.user_name }}</span></div>
                    </div>
                  </div>
                </div>

                <!-- Products -->
                <div class="bg-gray-700 p-4 rounded-lg">
                  <h3 class="font-semibold text-white mb-4">Returned Products</h3>
                  <div class="overflow-x-auto">
                    <table class="w-full text-sm text-white">
                      <thead class="bg-gray-600">
                        <tr>
                          <th class="px-3 py-2 text-left">Product</th>
                          <th class="px-3 py-2 text-center">Quantity</th>
                          <th class="px-3 py-2 text-center">Unit Price</th>
                          <th class="px-3 py-2 text-center">Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="product in returnData.products"
                          :key="product.id"
                          class="border-b border-gray-600"
                        >
                          <td class="px-3 py-3">
                            <div class="font-medium">{{ product.product_name }}</div>
                            <div class="text-gray-400 text-xs">{{ product.product_barcode }}</div>
                          </td>
                          <td class="px-3 py-3 text-center">{{ product.quantity }}</td>
                          <td class="px-3 py-3 text-center">${{ product.formatted_price }}</td>
                          <td class="px-3 py-3 text-center font-semibold">${{ product.formatted_total }}</td>
                        </tr>
                      </tbody>
                      <tfoot class="bg-gray-600">
                        <tr>
                          <td colspan="3" class="px-3 py-3 text-right font-semibold">Total Refund:</td>
                          <td class="px-3 py-3 text-center font-bold text-green-400">${{ returnData.total_refund_formatted }}</td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>

                <!-- Status Update Actions -->
                <div v-if="returnData.status === 0" class="flex gap-3">
                  <button
                    @click="updateStatus(1)"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                  >
                    ✅ Approve Return
                  </button>
                  <button
                    @click="updateStatus(2)"
                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                  >
                    ❌ Reject Return
                  </button>
                </div>
              </div>

              <div class="mt-6 flex justify-end">
                <button
                  @click="closeModal"
                  class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition"
                >
                  Close
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'

const props = defineProps({
  open: Boolean,
  returnData: Object,
})

const emit = defineEmits(['close', 'update-status'])

const closeModal = () => {
  emit('close')
}

const updateStatus = (status) => {
  emit('update-status', props.returnData, status)
  closeModal()
}
</script>