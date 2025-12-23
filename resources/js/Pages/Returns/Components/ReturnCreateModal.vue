<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-75">
    <div class="relative w-full max-w-6xl p-6 mx-4 my-8 bg-black border-4 border-blue-600 rounded-lg max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-white">Create Sales Return</h2>
        <button @click="closeModal" class="text-white hover:text-gray-300">
          <i class="text-2xl fas fa-times"></i>
        </button>
      </div>

      <!-- Return Type Selection (Simple Toggle) -->
      <div class="bg-gray-900 rounded-lg p-4 shadow mb-6">
        <div class="flex items-center justify-between">
          <div class="inline-flex bg-gray-800 rounded-lg p-1">
            <button
              type="button"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium',
                returnType === 1 ? 'bg-green-600 text-white' : 'text-gray-300 hover:text-white'
              ]"
              @click="returnType = 1"
            >
              🔄 Product Return
            </button>
            <button
              type="button"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium',
                returnType === 2 ? 'bg-blue-600 text-white' : 'text-gray-300 hover:text-white'
              ]"
              @click="returnType = 2"
            >
              💰 Cash Refund
            </button>
          </div>
          <div class="hidden md:block text-xs text-gray-400">
            <span v-if="returnType === 1">Step 1: Select items • Step 2: Review & Submit</span>
            <span v-else>Step 1: Enter refund • Step 2: Review & Submit</span>
          </div>
        </div>
      </div>

      <!-- Cash Return Details -->
      <div v-if="returnType === 2" class="bg-blue-900 rounded-lg p-6 shadow-lg mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">💳 Cash Refund Process</h3>
        
        <!-- Step 1: Select Products -->
        <div class="mb-6 p-4 bg-blue-800 rounded-lg">
          <div class="flex items-center gap-2 mb-3">
            <span class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm">1</span>
            <h4 class="text-white font-semibold">Select Sales for Refund</h4>
          </div>
          <p class="text-gray-300 text-sm mb-3">Choose which sale(s) to refund by selecting products below</p>
        </div>

        <!-- Step 2: Refund Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Refund Amount *</label>
            <div class="relative">
              <span class="absolute left-3 top-3 text-gray-400">{{ currencySymbol }}</span>
              <input
                v-model.number="refundAmount"
                type="number"
                step="0.01"
                min="0"
                placeholder="0.00"
                class="w-full pl-8 px-3 py-2 bg-gray-800 text-white border border-gray-600 rounded-lg font-semibold"
                required
              />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Refund Method *</label>
            <select
              v-model="refundMethod"
              class="w-full px-3 py-2 bg-gray-800 text-white border border-gray-600 rounded-lg"
              required
            >
              <option value="cash">💵 Cash</option>
              <option value="card">💳 Card</option>
              <option value="cheque">📄 Cheque</option>
              <option value="bank_transfer">🏦 Bank Transfer</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Reason (Optional)</label>
            <input
              v-model="notes"
              type="text"
              placeholder="Reason for refund..."
              class="w-full px-3 py-2 bg-gray-800 text-white border border-gray-600 rounded-lg"
            />
          </div>
        </div>

        <!-- Summary -->
        <div v-if="refundAmount > 0" class="p-4 bg-green-900 rounded-lg border border-green-700">
          <div class="flex justify-between items-center">
            <div>
              <p class="text-gray-300 text-sm">Total Refund Amount</p>
              <p class="text-white font-semibold">Method: {{ formatRefundMethod(refundMethod) }}</p>
            </div>
            <div class="text-right">
              <p class="text-green-300 text-3xl font-bold">{{ currencySymbol }} {{ refundAmount.toFixed(2) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Selected Products & Bill Summary Side by Side -->
      <div v-if="returnType === 1 && selectedProducts.length > 0" class="flex gap-4 mb-6">
        <!-- Selected Products (Left Side) -->
        <div class="flex-1 bg-green-900 rounded-lg p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Selected Products ({{ selectedProducts.length }})</h3>
            <div class="flex gap-2">
              <div class="text-white text-sm px-4 py-2 bg-green-700 rounded">
                Total Refund: {{ currencySymbol }} {{ calculateTotalRefund() }}
              </div>
              <button
                @click="clearSelection"
                class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700"
              >
                Clear All
              </button>
            </div>
          </div>

          <div class="overflow-x-auto bg-black/30 rounded-lg border border-green-800">
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
                  <td class="px-3 py-2 text-center">{{ currencySymbol }} {{ product.formatted_price }}</td>
                  <td class="px-3 py-2 text-center font-semibold text-green-300">
                    {{ currencySymbol }} {{ ((product.return_quantity || 0) * parseFloat(product.price || 0)).toFixed(2) }}
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

          <div class="mt-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Notes (Optional)</label>
            <input
              v-model="notes"
              type="text"
              placeholder="Reason for return..."
              class="w-full px-3 py-2 bg-gray-800 text-white border border-gray-600 rounded-lg"
            />
          </div>
        </div>

        <!-- Bill Summary (Right Side) -->
        <div class="w-96 h-300 bg-gray-900 rounded-xl p-5 border border-gray-800 shadow-lg">
          <h3 class="text-xl font-semibold text-white mb-4">Bill Summary</h3>

          <div class="space-y-3 text-sm text-gray-200">
            <div class="flex items-center justify-between">
              <span>Total Return Value</span>
              <span class="text-white font-semibold">{{ currencySymbol }} {{ returnTotal.toFixed(2) }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span>Replacement Value</span>
              <span class="text-white font-semibold">{{ currencySymbol }} {{ replacementTotal.toFixed(2) }}</span>
            </div>
            <div class="flex items-center justify-between text-lg font-bold pt-2 border-t border-gray-800">
              <span>{{ balanceLabel }}</span>
              <span :class="balance > 0 ? 'text-red-400' : balance < 0 ? 'text-green-400' : 'text-white'">
                {{ currencySymbol }} {{ Math.abs(balance).toFixed(2) }}
              </span>
            </div>
          </div>

          <div v-if="returnType === 1 && balance > 0" class="mt-5 space-y-3">
            <div class="pt-3 border-t border-gray-800">
              <label class="block text-sm font-medium text-gray-300 mb-2">Payment Amount</label>
              <div class="flex gap-2">
                <input
                  v-model.number="paymentAmount"
                  type="number"
                  step="0.01"
                  min="0"
                  :max="balance"
                  placeholder="0.00"
                  class="flex-1 px-3 py-2 bg-gray-800 text-white border border-gray-600 rounded-lg"
                />
                <button
                  @click="showPaymentModal = true"
                  class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold"
                >
                  Add Payment
                </button>
              </div>
              <div v-if="paymentStatusText" class="mt-2 text-sm" :class="paymentSatisfied ? 'text-green-400' : 'text-yellow-400'">
                {{ paymentStatusText }}
              </div>
            </div>
          </div>

          <div class="mt-5">
            <button
              @click="printDraftBill"
              class="w-full px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 flex items-center justify-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
              Print Bill
            </button>
          </div>

          <div class="mt-4 text-xs text-gray-400 text-center">
            <div>Shortcuts: F9 (Complete) | ESC (Close)</div>
          </div>
        </div>
      </div>

      <!-- Replacement Products (Optional, collapsible) -->
      <div v-if="returnType === 1" class="bg-gray-900 rounded-lg p-4 mb-28 max-w-2xl">
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-3 text-white">
            <input type="checkbox" v-model="enableReplacement" class="w-4 h-4" />
            Add replacement products (exchange)
          </label>
          <span v-if="enableReplacement" class="text-xs text-white px-3 py-1 rounded"
                :class="replacementQtyMatches ? 'bg-green-700' : 'bg-red-700'">
            Qty: Return {{ totalReturnQty }} vs Replacement {{ totalReplacementQty }}
          </span>
        </div>

        <div v-if="enableReplacement" class="mt-4 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
              <input v-model="repSearch" type="text" placeholder="Search by name or barcode"
                     class="w-full px-3 py-2 bg-gray-800 text-white border border-gray-700 rounded" />
              <div v-if="repSearch" class="mt-2 max-h-40 overflow-y-auto bg-gray-800 border border-gray-700 rounded">
                <button v-for="p in filteredShopProducts" :key="p.id"
                        class="w-full text-left px-3 py-2 hover:bg-gray-700 text-white"
                        @click="selectReplacement(p)">
                  <div class="font-medium">{{ p.name }}</div>
                  <div class="text-xs text-gray-300">{{ p.barcode }} • In stock: {{ p.shop_quantity }}</div>
                </button>
                <div v-if="filteredShopProducts.length === 0" class="px-3 py-2 text-sm text-gray-400">No matches</div>
              </div>
            </div>
            <div>
              <input v-model.number="repQty" type="number" min="1" placeholder="Qty"
                     class="w-full px-3 py-2 bg-gray-800 text-white border border-gray-700 rounded" />
            </div>
            <div class="flex items-end">
              <button @click="addReplacementFromSearch"
                      class="w-full px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                Add
              </button>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-white text-sm">
              <thead class="bg-gray-800">
                <tr>
                  <th class="px-3 py-2">Product</th>
                  <th class="px-3 py-2 text-center">Qty</th>
                  <th class="px-3 py-2 text-center">Unit Price</th>
                  <th class="px-3 py-2 text-center">Total</th>
                  <th class="px-3 py-2 text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in replacementProducts" :key="item.product_id" class="border-b border-gray-800">
                  <td class="px-3 py-2">
                    <div class="font-medium">{{ item.name }}</div>
                    <div class="text-xs text-gray-400">{{ item.barcode }}</div>
                  </td>
                  <td class="px-3 py-2 text-center">
                    <input v-model.number="item.quantity" type="number" min="1"
                           class="w-20 px-2 py-1 bg-gray-800 text-white border border-gray-700 rounded text-center" />
                  </td>
                  <td class="px-3 py-2 text-center">
                    <input v-model.number="item.unit_price" type="number" step="0.01" min="0"
                           class="w-24 px-2 py-1 bg-gray-800 text-white border border-gray-700 rounded text-center" />
                  </td>
                  <td class="px-3 py-2 text-center font-semibold">
                    {{ currencySymbol }} {{ ((item.quantity || 0) * parseFloat(item.unit_price || 0)).toFixed(2) }}
                  </td>
                  <td class="px-3 py-2 text-center">
                    <button @click="removeReplacement(item.product_id)"
                            class="px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                      Remove
                    </button>
                  </td>
                </tr>
                <tr v-if="replacementProducts.length === 0">
                  <td colspan="5" class="px-3 py-3 text-center text-gray-400">No replacement products added</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Sticky Footer Summary -->
      <div class="fixed left-0 right-0 bottom-0 z-50 px-6 py-3 border-t border-gray-800 bg-black/80 backdrop-blur">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
          <div class="text-sm text-gray-300">
            <template v-if="returnType === 1">
              Returned Qty: <span class="text-white font-semibold">{{ totalReturnQty }}</span>
              <span v-if="enableReplacement" class="ml-3">Replacement Qty: <span :class="replacementQtyMatches ? 'text-green-400' : 'text-red-400'" class="font-semibold">{{ totalReplacementQty }}</span></span>
              <span class="ml-3">Return Value: <span class="text-white font-semibold">{{ currencySymbol }} {{ returnTotal.toFixed(2) }}</span></span>
              <span class="ml-3">Replacement Value: <span class="text-white font-semibold">{{ currencySymbol }} {{ replacementTotal.toFixed(2) }}</span></span>
              <span class="ml-3">{{ balanceLabel }}: <span :class="balance > 0 ? 'text-red-400' : balance < 0 ? 'text-green-400' : 'text-white'" class="font-semibold">{{ currencySymbol }} {{ Math.abs(balance).toFixed(2) }}</span></span>
            </template>
            <template v-else>
              Selected Products: <span class="text-white font-semibold">{{ selectedProducts.length }}</span>
              <span class="ml-3">Refund Amount: <span class="text-blue-300 font-semibold">{{ currencySymbol }} {{ (refundAmount || 0).toFixed(2) }}</span></span>
              <span class="ml-3">Method: <span class="text-blue-300 font-semibold">{{ formatRefundMethod(refundMethod) }}</span></span>
            </template>
          </div>
          <div class="flex items-center gap-3">
            <button @click="closeModal" class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">Cancel</button>
            <button @click="submitReturn" :disabled="!canSubmit() || processing" class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50">
              {{ processing ? 'Creating...' : 'Complete Return' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Selected Products for Cash Refund -->
      <div v-if="returnType === 2 && selectedProducts.length > 0" class="bg-blue-900 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Selected Products ({{ selectedProducts.length }})</h3>
        <div class="overflow-x-auto rounded-lg border border-blue-800">
          <table class="w-full text-white text-sm">
            <thead class="bg-blue-800">
              <tr>
                <th class="px-3 py-2">Product</th>
                <th class="px-3 py-2">Sale Info</th>
                <th class="px-3 py-2 text-center">Qty</th>
                <th class="px-3 py-2 text-center">Unit Price</th>
                <th class="px-3 py-2 text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in selectedProducts" :key="product.id" class="border-b border-blue-800 bg-blue-950">
                <td class="px-3 py-2">
                  <div class="font-medium">{{ product.product_name }}</div>
                  <div class="text-xs text-gray-300">{{ product.product_barcode }}</div>
                </td>
                <td class="px-3 py-2">
                  <div class="text-xs">{{ product.sale_no }}</div>
                  <div class="text-xs text-gray-300">{{ product.customer_name }}</div>
                </td>
                <td class="px-3 py-2 text-center">{{ product.return_quantity || product.quantity_sold }}</td>
                <td class="px-3 py-2 text-center">{{ currencySymbol }} {{ product.formatted_price }}</td>
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
                <td class="px-3 py-2 text-center">{{ currencySymbol }} {{ product.formatted_price }}</td>
                <td class="px-3 py-2 text-center">
                  <button
                    v-if="!isSelected(product.id)"
                    @click="openReturnQtyModal(product)"
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
      <!-- <div class="flex justify-end gap-4">
        <button
          @click="closeModal"
          class="px-6 py-2 text-white bg-gray-600 rounded hover:bg-gray-700"
        >
          Cancel
        </button>
        <button
          @click="submitReturn"
          :disabled="!canSubmit() || processing"
          class="px-6 py-2 text-white bg-green-600 rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ processing ? 'Creating...' : 'Create Return' }}
        </button>
      </div> -->
    </div>
  </div>

  <!-- Payment Method Modal -->
  <div v-if="showPaymentModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-75">
    <div class="bg-gray-900 rounded-lg p-8 w-full max-w-md border border-gray-800 shadow-2xl">
      <!-- Header -->
      <h3 class="text-2xl font-bold text-white mb-2">Add Payment Method</h3>
      
      <!-- Remaining Balance -->
      <div class="mb-6">
        <p class="text-gray-300 text-sm">Remaining: <span class="text-red-400 font-bold text-lg">({{ currencySymbol }}) {{ balance.toFixed(2) }}</span></p>
      </div>

      <!-- Payment Method -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-300 mb-3">Payment Method</label>
        <select
          v-model="selectedPaymentMethod"
          class="w-full px-4 py-3 bg-gray-800 text-white border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none"
        >
          <option value="cash">💵 Cash</option>
          <option value="card">💳 Card</option>
          <option value="cheque">📄 Cheque</option>
          <option value="bank_transfer">🏦 Bank Transfer</option>
        </select>
      </div>

      <!-- Payment Amount -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-300 mb-3">Amount ({{ currencySymbol }})</label>
        <input
          v-model.number="paymentModalAmount"
          type="number"
          step="0.01"
          :max="balance"
          class="w-full px-4 py-3 bg-gray-800 text-white border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none"
          placeholder="0.00"
        />
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-3">
        <button
          @click="confirmPayment"
          class="flex-1 px-4 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition"
        >
          Add Payment
        </button>
        <button
          @click="showPaymentModal = false"
          class="flex-1 px-4 py-3 bg-gray-700 text-white font-semibold rounded-lg hover:bg-gray-600 transition"
        >
          Close
        </button>
      </div>
    </div>
  </div>

  <!-- Return Quantity Modal -->
  <div v-if="showReturnQtyModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-75">
    <div class="bg-gray-900 rounded-lg p-8 w-full max-w-md border border-gray-800 shadow-2xl">
      <!-- Header -->
      <h3 class="text-2xl font-bold text-white mb-6">Enter Return Quantity</h3>
      
      <!-- Product Info -->
      <div v-if="currentProductToAdd" class="mb-6 p-4 bg-gray-800 rounded-lg">
        <p class="text-gray-300 text-sm mb-1"><span class="font-semibold">{{ currentProductToAdd.product_name }}</span></p>
        <p class="text-gray-400 text-xs mb-3">{{ currentProductToAdd.product_barcode }}</p>
        <div class="flex justify-between text-sm">
          <span class="text-gray-400">Sold Qty:</span>
          <span class="text-white font-semibold">{{ currentProductToAdd.quantity_sold }}</span>
        </div>
        <div class="flex justify-between text-sm mt-2">
          <span class="text-gray-400">Unit Price:</span>
          <span class="text-white font-semibold">{{ currencySymbol }} {{ currentProductToAdd.formatted_price }}</span>
        </div>
      </div>

      <!-- Return Quantity Input -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-300 mb-3">Return Quantity</label>
        <input
          v-model.number="tempReturnQuantity"
          type="number"
          min="1"
          :max="currentProductToAdd?.quantity_sold || 1"
          class="w-full px-4 py-3 bg-gray-800 text-white border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none"
        />
      </div>

      <!-- Total Price Display -->
      <div v-if="tempReturnQuantity > 0" class="mb-6 p-4 bg-green-900 rounded-lg">
        <div class="flex justify-between items-center">
          <span class="text-gray-300 font-medium">Total Price:</span>
          <span class="text-green-300 text-2xl font-bold">{{ currencySymbol }} {{ (tempReturnQuantity * parseFloat(currentProductToAdd?.price || 0)).toFixed(2) }}</span>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-3">
        <button
          @click="confirmAddProduct"
          class="flex-1 px-4 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition"
        >
          Add Product
        </button>
        <button
          @click="closeReturnQtyModal"
          class="flex-1 px-4 py-3 bg-gray-700 text-white font-semibold rounded-lg hover:bg-gray-600 transition"
        >
          Cancel
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { logActivity } from '@/composables/useActivityLog'

const props = defineProps({
  open: Boolean,
  salesProducts: Object,
  shopProducts: Array,
})

const emit = defineEmits(['update:open', 'success'])

const page = usePage()
const returnType = ref(1) // 1 = Product Return, 2 = Cash Return
const searchQuery = ref('')
const dateFrom = ref('')
const dateTo = ref('')
const selectedProducts = ref([])
const replacementProducts = ref([])
const enableReplacement = ref(false)
const paymentAmount = ref(0)
const paymentApplied = ref(false)
const refundAmount = ref(0)
const refundMethod = ref('cash')
const notes = ref('')
const processing = ref(false)

// Payment Modal
const showPaymentModal = ref(false)
const selectedPaymentMethod = ref('cash')
const paymentModalAmount = ref(0)

// Return Quantity Modal
const showReturnQtyModal = ref(false)
const currentProductToAdd = ref(null)
const tempReturnQuantity = ref(1)

const currencySymbol = computed(() => props.currencySymbol || 'Rs.')

// Auto-update refund amount when selected products change
watch(selectedProducts, (newProducts) => {
  if (returnType.value === 2 && newProducts.length > 0) {
    // Calculate total from selected products
    const total = newProducts.reduce((sum, p) => {
      return sum + ((p.return_quantity || 0) * parseFloat(p.price || 0))
    }, 0)
    refundAmount.value = parseFloat(total.toFixed(2))
  }
}, { deep: true })

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

// Replacement search helpers
const repSearch = ref('')
const repQty = ref(1)
const filteredShopProducts = computed(() => {
  let ps = props.shopProducts || []
  if (repSearch.value) {
    const q = repSearch.value.toLowerCase()
    ps = ps.filter(p => (p.name||'').toLowerCase().includes(q) || (p.barcode||'').toLowerCase().includes(q))
  }
  return ps.slice(0, 20) // limit list
})

const addReplacementFromSearch = () => {
  if (!repSearch.value) return
  const match = (props.shopProducts || []).find(p => (p.name||'').toLowerCase().includes(repSearch.value.toLowerCase()) || (p.barcode||'').toLowerCase().includes(repSearch.value.toLowerCase()))
  if (!match) {
    alert('No matching product found in shop.')
    return
  }
  replacementProducts.value.push({
    product_id: match.id,
    name: match.name,
    barcode: match.barcode,
    quantity: repQty.value || 1,
    unit_price: parseFloat(match.retail_price || 0)
  })
  repSearch.value = ''
  repQty.value = 1
}

const selectReplacement = (p) => {
  repSearch.value = `${p.name}`
}

const removeReplacement = (productId) => {
  replacementProducts.value = replacementProducts.value.filter(p => p.product_id !== productId)
}

const calculateTotalRefund = () => {
  return selectedProducts.value.reduce((total, product) => {
    return total + ((product.return_quantity || 0) * parseFloat(product.price || 0))
  }, 0).toFixed(2)
}

const totalReturnQty = computed(() => selectedProducts.value.reduce((s,p)=> s + (parseInt(p.return_quantity)||0), 0))
const totalReplacementQty = computed(() => replacementProducts.value.reduce((s,p)=> s + (parseInt(p.quantity)||0), 0))
const replacementQtyMatches = computed(() => totalReplacementQty.value === totalReturnQty.value)

const returnTotal = computed(() => selectedProducts.value.reduce((total, product) => {
  return total + ((product.return_quantity || 0) * parseFloat(product.price || 0))
}, 0))

const replacementTotal = computed(() => replacementProducts.value.reduce((total, item) => {
  return total + ((item.quantity || 0) * parseFloat(item.unit_price || 0))
}, 0))

const balance = computed(() => replacementTotal.value - returnTotal.value)

const balanceLabel = computed(() => {
  if (balance.value > 0) return 'Payment from customer'
  if (balance.value < 0) return 'Refund to customer'
  return 'Settled'
})

const paymentSatisfied = computed(() => {
  if (balance.value <= 0) return true
  return paymentApplied.value && paymentAmount.value >= balance.value
})

const paymentStatusText = computed(() => {
  if (balance.value <= 0) {
    return balance.value < 0
      ? `Refund customer ${currencySymbol.value} ${Math.abs(balance.value).toFixed(2)}`
      : 'No payment needed'
  }
  if (paymentApplied.value && paymentAmount.value >= balance.value) {
    return 'Payment recorded'
  }
  return `Payment required: ${currencySymbol.value} ${balance.value.toFixed(2)}`
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

const formatRefundMethod = (method) => {
  return {
    cash: '💵 Cash',
    card: '💳 Card',
    cheque: '📄 Cheque',
    bank_transfer: '🏦 Bank Transfer'
  }[method] || method
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

const openReturnQtyModal = (product) => {
  currentProductToAdd.value = product
  tempReturnQuantity.value = 1
  showReturnQtyModal.value = true
}

const closeReturnQtyModal = () => {
  showReturnQtyModal.value = false
  currentProductToAdd.value = null
  tempReturnQuantity.value = 1
}

const confirmAddProduct = () => {
  if (!tempReturnQuantity.value || tempReturnQuantity.value < 1) {
    alert('Please enter a valid return quantity.')
    return
  }
  
  if (tempReturnQuantity.value > (currentProductToAdd.value?.quantity_sold || 0)) {
    alert(`Return quantity cannot exceed sold quantity of ${currentProductToAdd.value.quantity_sold}`)
    return
  }
  
  // Add product with the specified return quantity
  selectedProducts.value.push({
    ...currentProductToAdd.value,
    return_quantity: tempReturnQuantity.value
  })
  
  closeReturnQtyModal()
}

const clearSelection = () => {
  selectedProducts.value = []
  replacementProducts.value = []
}

const canSubmit = () => {
  if (returnType.value === 1) {
    // Product Return: Must have selected products
    return selectedProducts.value.length > 0 && paymentSatisfied.value
  } else {
    // Cash Return: Must have refund amount and method
    return refundAmount.value > 0 && refundMethod.value && selectedProducts.value.length > 0
  }
}

const buildReceiptHtml = (payload) => {
  const bill = (page.props.billSetting || {})
  const allowed = ['58mm', '80mm', '112mm', '210mm']
  const rawSize = (bill.print_size || '80mm').toString()
  const width = allowed.includes(rawSize) ? rawSize : '80mm'

  const header = {
    title: bill.company_name || 'SALES RETURN',
    address: bill.address,
    phones: [bill.mobile_1, bill.mobile_2].filter(Boolean).join(' / '),
    email: bill.email,
    website: bill.website_url,
    logo: bill.logo_path,
  }

  const { returnNo, date, customer, invoice, typeText, returnedItems, replacementItems, returnedTotal, replacementTotal, paymentLabel, paymentAmount } = payload

  return `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Return Receipt - ${returnNo}</title>
      <style>
        @page { size: ${width} auto; margin: 0; }
        @media print { body{ margin:0; padding:0 } }
        *{ margin:0; padding:0; box-sizing:border-box }
        body{ font-family: Arial, Helvetica, sans-serif; font-size:13px; width:${width}; padding:6mm 5mm; color:#000; line-height:1.4; font-weight:600 }
        .header{text-align:center; margin-bottom:8px; padding-bottom:8px; border-bottom:2px dashed #000}
        .header h1{font-size:16px; font-weight:900; margin-bottom:4px}
        .info{margin:8px 0; font-size:12px}
        .info-row{display:flex; justify-content:space-between; margin:2px 0}
        .items-table{width:100%; margin:8px 0; font-size:12px; border-collapse:collapse}
        .items-table th{text-align:left; border-bottom:2px solid #000; padding:3px 2px}
        .items-table td{padding:3px 2px; border-bottom:1px dotted #000}
        .item-name{width:38%; word-wrap:break-word}
        .item-qty{width:12%; text-align:center}
        .item-price{width:25%; text-align:right}
        .item-total{width:25%; text-align:right}
        .section-title{margin-top:4px; font-weight:900}
        .totals{margin-top:8px; font-size:12px}
        .total-row{display:flex; justify-content:space-between; margin:3px 0}
        .total-row.grand{font-size:15px; font-weight:900; border-top:2px solid #000; border-bottom:2px solid #000; padding:6px 0; margin:8px 0}
        .footer{text-align:center; margin-top:12px; padding-top:8px; border-top:2px dashed #000; font-size:12px}
      </style>
    </head>
    <body>
      <div class="receipt-container">
        <div class="header">
          ${header.logo ? `<div style="margin-bottom:6px;"><img src="/storage/${header.logo}" alt="logo" style="max-height:40px; max-width:100%; object-fit:contain;"/></div>` : ''}
          <h1>${header.title}</h1>
          ${header.address ? `<p>${header.address}</p>` : ''}
          ${header.phones ? `<p>Tel: ${header.phones}</p>` : ''}
          ${header.email ? `<p>${header.email}</p>` : ''}
          ${header.website ? `<p>${header.website}</p>` : ''}
        </div>

        <div class="info">
          <div class="info-row"><span><strong>Return No:</strong></span><span>${returnNo}</span></div>
          <div class="info-row"><span><strong>Date:</strong></span><span>${date}</span></div>
          <div class="info-row"><span><strong>Customer:</strong></span><span>${customer}</span></div>
          <div class="info-row"><span><strong>Invoice:</strong></span><span>${invoice}</span></div>
          <div class="info-row"><span><strong>Type:</strong></span><span>${typeText}</span></div>
        </div>

        <div class="section-title">Returned Items</div>
        <table class="items-table">
          <thead>
            <tr>
              <th class="item-name">Item</th>
              <th class="item-qty">Qty</th>
              <th class="item-price">Price</th>
              <th class="item-total">Total</th>
            </tr>
          </thead>
          <tbody>
            ${returnedItems.map(item => `
              <tr>
                <td class="item-name">${item.name}</td>
                <td class="item-qty">${item.qty}</td>
                <td class="item-price">${item.price.toFixed(2)}</td>
                <td class="item-total">${item.total.toFixed(2)}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>

        ${replacementItems.length ? `
          <div class="section-title">Replacement Items</div>
          <table class="items-table">
            <thead>
              <tr>
                <th class="item-name">Item</th>
                <th class="item-qty">Qty</th>
                <th class="item-price">Price</th>
                <th class="item-total">Total</th>
              </tr>
            </thead>
            <tbody>
              ${replacementItems.map(item => `
                <tr>
                  <td class="item-name">${item.name}</td>
                  <td class="item-qty">${item.qty}</td>
                  <td class="item-price">${item.price.toFixed(2)}</td>
                  <td class="item-total">${item.total.toFixed(2)}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        ` : ''}

        <div class="totals">
          <div class="total-row"><span>Returned Total:</span><span>${page.props.currency || ''} ${returnedTotal.toFixed(2)}</span></div>
          <div class="total-row"><span>Replacement Total:</span><span>${page.props.currency || ''} ${replacementTotal.toFixed(2)}</span></div>
          <div class="total-row grand"><span>${paymentLabel.toUpperCase()}:</span><span>${page.props.currency || ''} ${paymentAmount.toFixed(2)}</span></div>
        </div>

        <div class="footer"><p><strong>${bill.footer_description || 'Thank you!'}</strong></p><p style="margin-top:6px; font-size:9px;">Powered by POS System</p></div>
      </div>

      <script type="text/javascript">
        let printExecuted = false;
        window.onload = function(){ setTimeout(function(){ if(!printExecuted){ printExecuted = true; window.print(); } }, 300); };
        window.onafterprint = function(){ setTimeout(function(){ window.close(); }, 200); };
        setTimeout(function(){ if(!window.closed){ window.close(); } }, 5000);
      <\/script>
    </body>
    </html>
  `
}

const printDraftBill = () => {
  const returnNo = `RET-DRAFT-${Date.now()}`
  const date = new Date().toLocaleDateString('en-GB')
  const customer = selectedProducts.value[0]?.customer_name || 'Walk-in Customer'
  const invoice = selectedProducts.value[0]?.sale_no || 'N/A'
  const returnedItems = selectedProducts.value.map(p => ({
    name: p.product_name,
    qty: p.return_quantity || 0,
    price: parseFloat(p.price || 0),
    total: (p.return_quantity || 0) * parseFloat(p.price || 0)
  }))
  const replacementItems = replacementProducts.value.map(p => ({
    name: p.name,
    qty: p.quantity || 0,
    price: parseFloat(p.unit_price || 0),
    total: (p.quantity || 0) * parseFloat(p.unit_price || 0)
  }))

  const payload = {
    returnNo,
    date,
    customer,
    invoice,
    typeText: returnType.value === 2 ? 'Cash Refund' : 'Product Return',
    returnedItems,
    replacementItems,
    returnedTotal: returnTotal.value,
    replacementTotal: replacementTotal.value,
    paymentLabel: balanceLabel.value,
    paymentAmount: Math.abs(balance.value),
  }

  const html = buildReceiptHtml(payload)
  const w = window.open('', '_blank', 'width=320,height=640')
  if (!w) { alert('Please allow pop-ups to print receipt'); return }
  w.document.write(html)
  w.document.close()
}
const closeModal = () => {
  emit('update:open', false)
  clearSelection()
  returnType.value = 1
  searchQuery.value = ''
  dateFrom.value = ''
  dateTo.value = ''
  paymentAmount.value = 0
  paymentApplied.value = false
  refundAmount.value = 0
  refundMethod.value = 'cash'
  notes.value = ''
}

const confirmPayment = () => {
  if (!paymentModalAmount.value || paymentModalAmount.value <= 0) {
    alert('Please enter a valid payment amount.')
    return
  }
  
  if (paymentModalAmount.value > balance.value) {
    alert(`Payment amount cannot exceed the remaining balance of ${currencySymbol.value} ${balance.value.toFixed(2)}`)
    return
  }
  
  // Set the payment amount from modal
  paymentAmount.value = paymentModalAmount.value
  paymentApplied.value = true
  
  // Close the modal
  showPaymentModal.value = false
  
  // Reset modal fields
  paymentModalAmount.value = 0
  selectedPaymentMethod.value = 'cash'
}

const submitReturn = () => {
  if (!canSubmit()) return

  // Require payment if customer needs to pay
  if (returnType.value === 1 && balance.value > 0 && !paymentSatisfied.value) {
    alert(`Please add payment of at least ${currencySymbol.value} ${balance.value.toFixed(2)} before completing.`)
    return
  }

  // Validation for product returns
  if (returnType.value === 1) {
    const invalidProducts = selectedProducts.value.filter(p => 
      !p.return_quantity || p.return_quantity < 1 || p.return_quantity > p.quantity_sold
    )

    if (invalidProducts.length > 0) {
      alert('Please enter valid return quantities for all selected products.')
      return
    }
  }

  // Validation for cash returns
  if (returnType.value === 2) {
    if (!refundAmount.value || refundAmount.value <= 0) {
      alert('Please enter a valid refund amount.')
      return
    }
    if (!refundMethod.value) {
      alert('Please select a refund method.')
      return
    }
  }

  processing.value = true

  const postData = {
    return_type: returnType.value,
    refund_amount: returnType.value === 2 ? refundAmount.value : null,
    refund_method: returnType.value === 2 ? refundMethod.value : null,
    notes: notes.value,
    payment_amount: balance.value > 0 ? paymentAmount.value : null,
    selected_products: selectedProducts.value.map(p => ({
      sales_product_id: p.id,
      return_quantity: p.return_quantity
    })),
    replacement_products: replacementProducts.value.map(p => ({
      product_id: p.product_id,
      quantity: p.quantity,
      unit_price: p.unit_price
    }))
  }

  router.post(route('return.create-from-sales'), postData, {
    onSuccess: async () => {
      await logActivity('create', 'sales_returns', {
        return_type: returnType.value === 1 ? 'Product Return' : 'Cash Return',
        products_count: selectedProducts.value.length,
        total_quantity: selectedProducts.value.reduce((sum, p) => sum + parseInt(p.return_quantity), 0),
        refund_amount: returnType.value === 2 ? refundAmount.value : calculateTotalRefund()
      });
      emit('success')
      closeModal()
      processing.value = false
    },
    onError: (errors) => {
      processing.value = false
      console.error('Return creation failed:', errors)
      alert('Failed to create return. Please check the form and try again.')
    }
  })
}
</script>

<style scoped>
/* Add any custom styles if needed */
</style>
