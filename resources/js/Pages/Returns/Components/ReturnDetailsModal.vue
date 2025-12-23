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
                          <td class="px-3 py-3 text-center">({{ page.props.currency || '' }}) {{ product.formatted_price }}</td>
                          <td class="px-3 py-3 text-center font-semibold">({{ page.props.currency || '' }}) {{ product.formatted_total }}</td>
                        </tr>
                      </tbody>
                      <tfoot class="bg-gray-600">
                        <tr>
                          <td colspan="3" class="px-3 py-3 text-right font-semibold">Total Refund:</td>
                          <td class="px-3 py-3 text-center font-bold text-green-400">({{ page.props.currency || '' }}) {{ returnData.total_refund_formatted }}</td>
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
                <button
                  v-if="returnData"
                  @click="printReturnReceipt(returnData)"
                  class="ml-3 px-6 py-2 bg-accent text-white rounded-lg hover:bg-accent"
                >
                  Print Receipt
                </button>
              </div>
              
              <!-- Net Payment Summary -->
              <div v-if="returnData && (returnData.payment_due_label || returnData.payment_due_amount !== undefined)" class="mt-6">
                <div class="bg-gray-700 p-4 rounded-lg flex items-center justify-between">
                  <div class="text-white font-semibold">Net Payment</div>
                  <div class="text-right">
                    <div class="text-sm text-gray-300">{{ returnData.payment_due_label || 'Settled' }}</div>
                    <div class="text-xl font-bold" :class="(returnData.payment_due_label || '').includes('Refund') ? 'text-green-400' : 'text-yellow-300'">
                      ({{ page.props.currency || '' }}) {{ returnData.payment_due_formatted || Number(returnData.payment_due_amount || 0).toFixed(2) }}
                    </div>
                  </div>
                </div>
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
import { usePage } from '@inertiajs/vue3'

const page = usePage()

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

const printReturnReceipt = (ret) => {
  const bill = (page.props.billSetting || {})
  const allowed = ['58mm', '80mm', '112mm', '210mm']
  const rawSize = (bill.print_size || '80mm').toString()
  const width = allowed.includes(rawSize) ? rawSize : '80mm'

  const returnNo = ret.return_no || `RET-${ret.id}`
  const date = ret.return_date_formatted || ret.return_date || ''
  const customer = ret.customer_name || 'Walk-in Customer'
  const invoice = ret.sale_no || 'N/A'

  const returnedItems = (ret.products || []).map(p => ({
    name: p.product_name,
    qty: p.quantity || 0,
    price: parseFloat(p.price || 0),
    total: parseFloat(p.total || 0),
  }))

  const returnedTotal = parseFloat(ret.returned_total || ret.total_refund || returnedItems.reduce((s,i)=>s+i.total,0))

  // Replacement items are not in returnData.products, derive from server-provided summary if present
  const replacementTotal = parseFloat(ret.replacement_total || 0)
  const paymentLabel = ret.payment_due_label || 'Settled'
  const paymentAmount = parseFloat(ret.payment_due_amount || 0).toFixed(2)

  const currency = page.props.currency || ''

  const receiptContent = `
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
          ${bill.logo_path ? `<div style="margin-bottom:6px;"><img src="/storage/${bill.logo_path}" alt="logo" style="max-height:40px; max-width:100%; object-fit:contain;"/></div>` : ''}
          <h1>${bill.company_name || 'SALES RETURN'}</h1>
          ${bill.address ? `<p>${bill.address}</p>` : ''}
          ${bill.mobile_1 || bill.mobile_2 ? `<p>Tel: ${[bill.mobile_1, bill.mobile_2].filter(Boolean).join(' / ')}</p>` : ''}
          ${bill.email ? `<p>${bill.email}</p>` : ''}
          ${bill.website_url ? `<p>${bill.website_url}</p>` : ''}
        </div>

        <div class="info">
          <div class="info-row"><span><strong>Return No:</strong></span><span>${returnNo}</span></div>
          <div class="info-row"><span><strong>Date:</strong></span><span>${date}</span></div>
          <div class="info-row"><span><strong>Customer:</strong></span><span>${customer}</span></div>
          <div class="info-row"><span><strong>Invoice:</strong></span><span>${invoice}</span></div>
          <div class="info-row"><span><strong>Type:</strong></span><span>${ret.return_type === 2 ? 'Cash Refund' : 'Product Return'}</span></div>
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

        <div class="totals">
          <div class="total-row"><span>Returned Total:</span><span>${currency} ${returnedTotal.toFixed(2)}</span></div>
          <div class="total-row"><span>Replacement Total:</span><span>${currency} ${replacementTotal.toFixed(2)}</span></div>
          <div class="total-row grand"><span>${paymentLabel.toUpperCase()}:</span><span>${currency} ${paymentAmount}</span></div>
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

  const w = window.open('', '_blank', 'width=320,height=640')
  if (!w) { alert('Please allow pop-ups to print receipt'); return }
  w.document.write(receiptContent)
  w.document.close()
}
</script>