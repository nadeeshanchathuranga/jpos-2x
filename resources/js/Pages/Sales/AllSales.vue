<template>
  <AppLayout>
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
          <button
            @click="$inertia.visit(route('dashboard'))"
            class="px-4 py-2 text-white bg-accent rounded hover:bg-accent"
          >
            Back
          </button>
          <h1 class="text-3xl font-bold text-white">Sales History</h1>
        </div>
        <div class="text-white">
          <span class="px-4 py-2 bg-accent rounded">
            Total: {{ sales.total }} records
          </span>
        </div>
      </div>

      <!-- View Sale Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/60" @click="closeModal"></div>
        <div class="relative w-full max-w-2xl mx-4 bg-dark border-2 border-accent rounded-lg overflow-auto" style="max-height:90vh;">
          <div class="p-4">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h2 class="text-2xl font-bold text-white">Sale Details</h2>
                <div class="text-sm text-gray-300">Invoice: <strong class="text-accent">{{ selectedSale?.invoice_no }}</strong></div>
              </div>
              <div class="flex items-center gap-2">
                <button @click="printReceipt(selectedSale)" class="px-3 py-1 bg-green-600 rounded text-white">Print</button>
                <button @click="closeModal" class="px-3 py-1 bg-gray-700 rounded text-white">Close</button>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm text-gray-300 mb-4">
              <div><strong>Customer:</strong> {{ selectedSale?.customer ? selectedSale.customer.name : 'Walk-in' }}</div>
              <div><strong>Date:</strong> {{ formatDate(selectedSale?.sale_date) }}</div>
              <div><strong>Type:</strong> {{ getSaleType(selectedSale?.type) }}</div>
              <div><strong>Total:</strong> {{ formatCurrency(selectedSale?.total_amount) }}</div>
            </div>

            <div class="overflow-x-auto bg-gray-900 rounded">
              <table class="w-full text-left text-white">
                <thead class="bg-accent">
                  <tr>
                    <th class="px-4 py-2">Item</th>
                    <th class="px-4 py-2">Qty</th>
                    <th class="px-4 py-2 text-right">Price</th>
                    <th class="px-4 py-2 text-right">Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in selectedSale?.products || []" :key="item.id" class="border-b border-gray-700">
                    <td class="px-4 py-2">{{ (item.product && item.product.name) || item.product_name || 'Unknown' }}</td>
                    <td class="px-4 py-2">{{ item.quantity }}</td>
                    <td class="px-4 py-2 text-right">{{ formatCurrency(item.price) }}</td>
                    <td class="px-4 py-2 text-right">{{ formatCurrency(item.total || (item.price * item.quantity)) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="mt-4 text-sm text-gray-300">
              <div class="flex justify-end gap-4">
                <div class="text-right">
                  <div>Subtotal: <strong>{{ formatCurrency(selectedSale?.total_amount) }}</strong></div>
                  <div>Discount: <strong>{{ formatCurrency(selectedSale?.discount) }}</strong></div>
                  <div class="mt-2">Net: <strong>{{ formatCurrency(selectedSale?.net_amount) }}</strong></div>
                  <div>Paid: <strong>{{ formatCurrency((selectedSale?.net_amount || 0) - (selectedSale?.balance || 0)) }}</strong></div>
                  <div :class="selectedSale && selectedSale.balance > 0 ? 'text-red-400 font-bold' : 'text-green-400'">Balance: <strong>{{ formatCurrency(selectedSale?.balance) }}</strong></div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <div class="overflow-hidden bg-dark border-4 border-accent rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-accent">
              <tr>
                <th class="px-6 py-3">#</th>
                <th class="px-6 py-3">Invoice No</th>
                <th class="px-6 py-3">Customer</th>
                <th class="px-6 py-3">Products</th>
                <th class="px-6 py-3">Type</th>
                <th class="px-6 py-3 text-right">Total</th>
                <th class="px-6 py-3 text-right">Discount</th>
                <th class="px-6 py-3 text-right">Net Amount</th>
                <th class="px-6 py-3 text-right">Balance</th>
                <th class="px-6 py-3">Sale Date</th>
                <th class="px-6 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(sale, index) in sales.data"
                :key="sale.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
                <td class="px-6 py-4">
                  {{ (sales.current_page - 1) * sales.per_page + index + 1 }}
                </td>
                <td class="px-6 py-4">
                  <strong class="text-accent">{{ sale.invoice_no }}</strong>
                </td>
                <td class="px-6 py-4">
                  {{ sale.customer ? sale.customer.name : 'Walk-in' }}
                </td>
                <td class="px-6 py-4 max-w-xl">
                  <div class="text-sm text-gray-300">
                    {{ formatProducts(sale.products) }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span
                    :class="{
                      'bg-green-500 text-white px-3 py-1 rounded': sale.type === 1,
                      'bg-blue-500 text-white px-3 py-1 rounded': sale.type === 2
                    }"
                  >
                    {{ getSaleType(sale.type) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">{{ formatCurrency(sale.total_amount) }}</td>
                <td class="px-6 py-4 text-right text-red-400">{{ formatCurrency(sale.discount) }}</td>
                <td class="px-6 py-4 text-right">
                  <strong>{{ formatCurrency(sale.net_amount) }}</strong>
                </td>
                <td class="px-6 py-4 text-right" :class="sale.balance > 0 ? 'text-red-400 font-bold' : 'text-green-400'">
                  {{ formatCurrency(sale.balance) }}
                </td>
                <td class="px-6 py-4 text-gray-400">
                  {{ formatDate(sale.sale_date) }}
                </td>
                <td class="px-6 py-4">
                  <div class="flex gap-2">
                    <button
                      @click="viewSale(sale)"
                      class="px-3 py-1 bg-blue-600 rounded text-white text-sm"
                    >
                      View
                    </button>
                    <!-- <button
                      @click="printReceipt(sale)"
                      class="px-3 py-1 bg-green-600 rounded text-white text-sm"
                    >
                      Print
                    </button> -->
                  </div>
                </td>
              </tr>
              <tr v-if="!sales.data || sales.data.length === 0">
                <td colspan="11" class="px-6 py-4 text-center text-gray-400">
                  No sales found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="sales.links">
          <div class="text-sm text-gray-400">
            Showing {{ sales.from }} to {{ sales.to }} of {{ sales.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in sales.links"
              :key="link.label"
              @click="link.url ? router.visit(link.url) : null"
              :disabled="!link.url"
              :class=" [
                'px-3 py-1 rounded',
                link.active
                  ? 'bg-accent text-white'
                  : link.url
                  ? 'bg-gray-700 text-white hover:bg-gray-600'
                  : 'bg-gray-800 text-gray-500 cursor-not-allowed'
              ]"
              v-html="link.label"
            ></button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { router } from "@inertiajs/vue3";
import { ref } from 'vue';

defineProps({
    sales: {
        type: Object,
        required: true,
    }
});

const formatCurrency = (amount) => {
  const num = Number(amount) || 0;
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(num);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getSaleType = (type) => {
    return type === 1 ? 'Retail' : type === 2 ? 'Wholesale' : 'Unknown';
};

const formatProducts = (products) => {
  if (!products || products.length === 0) return '-';
  return products.map(p => {
    const name = p.product && p.product.name ? p.product.name : (p.product_name || 'Unknown');
    return `${name} x${p.quantity}`;
  }).join(', ');
};

const showModal = ref(false);
const selectedSale = ref(null);

const viewSale = (sale) => {
  selectedSale.value = sale;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedSale.value = null;
};

const getPaymentTypeText = (type) => {
  return ['Cash', 'Card', 'Credit'][type] || '-';
};

const printReceipt = (sale) => {
  const s = sale && sale.value ? sale.value : sale;
  const invoice = s?.invoice_no || '';
  const saleDate = formatDate(s?.sale_date) || '';
  const customer = s?.customer && s.customer.name ? s.customer.name : 'Walk-in';
  const items = (s?.products || []).map(p => ({
    product_name: p.product && p.product.name ? p.product.name : (p.product_name || 'Unknown'),
    quantity: p.quantity || 0,
    price: parseFloat(p.price) || 0
  }));

  const subtotal = parseFloat(s?.total_amount) || items.reduce((sum, i) => sum + i.price * i.quantity, 0);
  const discount = parseFloat(s?.discount) || 0;
  const net = parseFloat(s?.net_amount) || (subtotal - discount);
  const balance = parseFloat(s?.balance) || 0;
  const paid = (net - balance).toFixed(2);

  const receiptContent = `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Receipt - ${invoice}</title>
      <style>
        @page { size: 80mm auto; margin: 0; }
        @media print { body{ margin:0; padding:0 } }
        *{ margin:0; padding:0; box-sizing:border-box }
        body{ font-family: Arial, Helvetica, sans-serif; font-size:13px; width:80mm; padding:6mm 5mm; color:#000; line-height:1.4; font-weight:600 }
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
        .totals{margin-top:8px; font-size:12px}
        .total-row{display:flex; justify-content:space-between; margin:3px 0}
        .total-row.grand{font-size:15px; font-weight:900; border-top:2px solid #000; border-bottom:2px solid #000; padding:6px 0; margin:8px 0}
        .footer{text-align:center; margin-top:12px; padding-top:8px; border-top:2px dashed #000; font-size:12px}
      </style>
    </head>
    <body>
      <div class="receipt-container">
        <div class="header">
          <h1>SALES RECEIPT</h1>
          <p>Your Company Name</p>
          <p>Address Line 1, City</p>
          <p>Tel: +94 XX XXX XXXX</p>
        </div>

        <div class="info">
          <div class="info-row"><span><strong>Invoice:</strong></span><span>${invoice}</span></div>
          <div class="info-row"><span><strong>Date:</strong></span><span>${saleDate}</span></div>
          <div class="info-row"><span><strong>Customer:</strong></span><span>${customer}</span></div>
          <div class="info-row"><span><strong>Payment:</strong></span><span>-</span></div>
        </div>

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
            ${items.map(item => `
              <tr>
                <td class="item-name">${item.product_name}</td>
                <td class="item-qty">${item.quantity}</td>
                <td class="item-price">${item.price.toFixed(2)}</td>
                <td class="item-total">${(item.price * item.quantity).toFixed(2)}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>

        <div class="totals">
          <div class="total-row"><span>Subtotal:</span><span>Rs. ${subtotal.toFixed(2)}</span></div>
          <div class="total-row"><span>Discount:</span><span>Rs. ${discount.toFixed(2)}</span></div>
          <div class="total-row grand"><span>GRAND TOTAL:</span><span>Rs. ${net.toFixed(2)}</span></div>
          <div class="total-row"><span>Paid Amount:</span><span>Rs. ${paid}</span></div>
          <div class="total-row" style="font-weight:bold"><span>${Math.abs(balance) > 0 ? 'Balance Due:' : 'Change:'}</span><span>Rs. ${Math.abs(balance).toFixed(2)}</span></div>
        </div>

        <div class="footer"><p><strong>Thank you for your business!</strong></p><p>Please visit us again!</p><p style="margin-top:6px; font-size:9px;">Powered by POS System</p></div>
      </div>

      <script type="text/javascript">
        let printExecuted = false;
        window.onload = function(){ setTimeout(function(){ if(!printExecuted){ printExecuted = true; window.print(); } }, 300); };
        window.onafterprint = function(){ setTimeout(function(){ window.close(); }, 200); };
        setTimeout(function(){ if(!window.closed){ window.close(); } }, 5000);
      <\/script>
    </body>
    </html>
  `;

  const w = window.open('', '_blank', 'width=320,height=640');
  if (!w) { alert('Please allow pop-ups to print receipt'); return; }
  w.document.write(receiptContent);
  w.document.close();
};
</script>
