<!--
  Product View Modal Component
  
  Purpose: Display detailed product information in a modal dialog with barcode printing functionality
  
  Features:
  - Complete product information display organized in sections
  - Barcode generation and printing with customizable quantity
  - Print multiple barcodes (1-100) in a formatted label layout
  - Image preview if product has an image
  - Color-coded sections for different information types
  - Responsive grid layout
  
  Sections Displayed:
  1. Basic Information: Name, Barcode, Status, Brand, Category, Type
  2. Pricing Information: Purchase/Wholesale/Retail prices, Discount, Tax
  3. Inventory & Units: Quantity, Low Stock Alert, Units (Purchase/Sales/Transfer)
  4. Unit Conversion Rates: Purchase→Transfer, Purchase→Sales, Transfer→Sales
  5. Additional Information: Return allowed, Created date
  
  Barcode Printing:
  - Uses JsBarcode library for CODE128 format
  - Generates printable labels with barcode, product name, and price
  - Each label is 35mm x 22mm (standard label size)
  - Opens print dialog in new window with formatted labels
  
  Data Flow:
  - Receives product data via 'product' prop
  - Emits 'update:open' to control modal visibility
  - No data modification, read-only view
-->
<template>
  <Teleport to="body">
    <!-- Modal Overlay with Click-Outside-to-Close -->
    <div
      v-if="open"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
      @click.self="closeModal"
    >
      <!-- Modal Container with Scrollable Content -->
      <div class="relative w-full max-w-6xl max-h-[90vh] overflow-y-auto p-6 bg-gray-800 rounded-lg shadow-xl">
        <!-- Modal Header with Title and Close Button -->
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-white">Product Details</h2>
          <button
            @click="closeModal"
            class="text-gray-400 transition hover:text-white"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Barcode Print Quantity Section -->
        <div class="p-4 mb-6 bg-gray-700 rounded-lg">
          <label class="block mb-2 text-sm font-semibold text-white">
            Number of Barcodes to Print
          </label>
          <div class="flex gap-3">
            <!-- Quantity Input (1-100) -->
            <input
              v-model.number="barcodeQuantity"
              type="number"
              min="1"
              max="100"
              class="flex-1 px-4 py-2 text-white bg-gray-600 border border-gray-500 rounded focus:outline-none focus:border-blue-500"
              placeholder="Enter quantity (1-100)"
            />
            <!-- Print Button with Current Quantity Display -->
            <button
              @click="printBarcode"
              class="px-6 py-2 text-white transition bg-green-600 rounded hover:bg-green-700"
            >
              Print Barcode ({{ barcodeQuantity }})
            </button>
          </div>
        </div>

        <!-- Product Image Display (if available) -->
        <div v-if="product?.image" class="flex justify-center p-4 mb-6 bg-gray-700 rounded-lg">
          <img :src="`/storage/${product.image}`" :alt="product.name" class="max-w-xs rounded-lg shadow-lg" />
        </div>

        <div class="space-y-6 text-white">
          <!-- SECTION 1: Basic Information -->
          <div>
            <h3 class="mb-4 text-lg font-semibold text-blue-400">Basic Information</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Product Name</p>
                <p class="text-base font-medium">{{ product?.name || 'N/A' }}</p>
              </div>
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Barcode</p>
                <p class="text-base font-medium">{{ product?.barcode || 'N/A' }}</p>
              </div>
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Status</p>
                <span
                  :class="{
                    'bg-red-500 text-white px-3 py-1 rounded text-sm': product?.status == 0,
                    'bg-green-500 text-white px-3 py-1 rounded text-sm': product?.status == 1
                  }"
                >
                  {{ product?.status == 1 ? 'Active' : 'Inactive' }}
                </span>
              </div>
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Brand</p>
                <p class="text-base font-medium">{{ product?.brand?.name || 'N/A' }}</p>
              </div>
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Category</p>
                <p class="text-base font-medium">{{ product?.category?.name || 'N/A' }}</p>
              </div>
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Type</p>
                <p class="text-base font-medium">{{ product?.type?.name || 'N/A' }}</p>
              </div>
            </div>
          </div>

          <!-- SECTION 2: Pricing Information -->
          <div>
            <h3 class="mb-4 text-lg font-semibold text-green-400">Pricing Information</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Purchase Price</p>
                <p class="text-lg font-bold text-green-300">Rs. {{ formatPrice(product?.purchase_price) }}</p>
              </div>
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Wholesale Price</p>
                <p class="text-lg font-bold text-blue-300">Rs. {{ formatPrice(product?.wholesale_price) }}</p>
              </div>
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Retail Price</p>
                <p class="text-lg font-bold text-yellow-300">Rs. {{ formatPrice(product?.retail_price) }}</p>
              </div>
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Discount</p>
                <p class="text-base font-medium">{{ product?.discount?.name || 'No Discount' }}</p>
              </div>
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Tax</p>
                <p class="text-base font-medium">{{ product?.tax?.name || 'No Tax' }}</p>
              </div>
            </div>
          </div>

          <!-- SECTION 3: Inventory & Units -->
          <div>
            <h3 class="mb-4 text-lg font-semibold text-yellow-400">Inventory & Units</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Current Quantity</p>
                <p class="text-xl font-bold text-yellow-300">{{ product?.qty || '0' }}</p>
              </div>
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Storage Stock Quantity</p>
                <p class="text-xl font-bold text-blue-300">{{ product?.storage_stock_qty || '0' }}</p>
              </div>
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Low Stock Alert Level</p>
                <p class="text-base font-medium">{{ product?.low_stock_margin || 'Not Set' }}</p>
              </div>
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Measurement Unit</p>
                <p class="text-base font-medium">{{ product?.measurement_unit?.name || 'N/A' }}</p>
              </div>
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Purchase Unit</p>
                <p class="text-base font-medium">{{ product?.purchase_unit?.name || 'N/A' }}</p>
              </div>
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Sales Unit</p>
                <p class="text-base font-medium">{{ product?.sales_unit?.name || 'N/A' }}</p>
              </div>
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Transfer Unit</p>
                <p class="text-base font-medium">{{ product?.transfer_unit?.name || 'N/A' }}</p>
              </div>
            </div>
          </div>

          <!-- SECTION 4: Unit Conversion Rates -->
          <div>
            <h3 class="mb-4 text-lg font-semibold text-purple-400">Unit Conversion Rates</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Purchase → Transfer Rate</p>
                <p class="text-base font-medium">{{ product?.purchase_to_transfer_rate || '0' }}</p>
              </div>
              
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Transfer → Sales Rate</p>
                <p class="text-base font-medium">{{ product?.transfer_to_sales_rate || '0' }}</p>
              </div>
            </div>
          </div>

          <!-- SECTION 5: Additional Information -->
          <div>
            <h3 class="mb-4 text-lg font-semibold text-pink-400">Additional Information</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Return Product Allowed</p>
                <p class="text-base font-medium">
                  <span :class="product?.return_product ? 'text-green-400' : 'text-red-400'">
                    {{ product?.return_product ? 'Yes' : 'No' }}
                  </span>
                </p>
              </div>
              <div class="p-3 bg-gray-700 rounded-lg">
                <p class="text-xs text-gray-400">Created At</p>
                <p class="text-base font-medium">{{ formatDate(product?.created_at) }}</p>
              </div>
            </div>
          </div>

          <!-- Quantity and Low Stock Status -->
          <div>
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-400">Current Quantity</label>
              <p class="text-lg text-white">{{ product.qty }}</p>
            </div>

            <!-- Low Stock Margin with Warning Indicator -->
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-400">Low Stock Alert Level</label>
              <p class="text-lg text-white">
                {{ product.low_stock_margin || 'Not Set' }}
                <!-- Display warning if stock is low -->
                <span v-if="product.qty <= product.low_stock_margin" class="ml-2 text-sm text-red-500">
                  ⚠️ Low Stock!
                </span>
              </p>
            </div>
          </div>
        </div>

        <!-- Modal Footer with Close Button -->
        <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-gray-700">
          <button
            @click="closeModal"
            class="px-6 py-2 text-white transition bg-gray-600 rounded hover:bg-gray-700"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
/**
 * Product View Modal Component Script
 * 
 * Handles product details display and barcode printing functionality
 * Uses JsBarcode library for generating CODE128 barcodes
 */

import { ref, watch, nextTick } from 'vue';
import JsBarcode from 'jsbarcode';

/**
 * Component Props
 * @property {Boolean} open - Controls modal visibility
 * @property {Object} product - Product data to display
 */
const props = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
  product: {
    type: Object,
    required: false,
    default: null,
  },
});

/**
 * Component Emits
 * @event update:open - Emitted when modal needs to close
 */
const emit = defineEmits(['update:open']);

/**
 * Reactive State Variables
 * 
 * barcodeElement: Reference to SVG element for barcode rendering
 * barcodeQuantity: Number of barcode labels to print (1-100)
 */
const barcodeElement = ref(null);
const barcodeQuantity = ref(1);

/**
 * Close Modal Handler
 * Emits event to parent component to update modal state
 */
const closeModal = () => {
  emit('update:open', false);
};

/**
 * Format Price for Display
 * Converts numeric price to fixed 2 decimal format
 * 
 * @param {Number} price - Raw price value
 * @returns {String} Formatted price (e.g., "123.45")
 */
const formatPrice = (price) => {
  if (!price) return '0.00';
  return parseFloat(price).toFixed(2);
};

/**
 * Format Date for Display
 * Converts date string to readable format
 * 
 * @param {String} date - Date string from database
 * @returns {String} Formatted date (e.g., "December 2, 2025")
 */
const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

/**
 * Generate Barcode
 * Uses JsBarcode to render barcode in SVG element
 * Called when modal opens or product changes
 */
const generateBarcode = () => {
  nextTick(() => {
    if (barcodeElement.value && props.product?.barcode) {
      try {
        JsBarcode(barcodeElement.value, props.product.barcode, {
          format: 'CODE128',
          width: 2,
          height: 80,
          displayValue: false,
        });
      } catch (error) {
        console.error('Barcode generation error:', error);
      }
    }
  });
};

/**
 * Print Barcode Labels
 * Generates multiple barcode labels and opens print dialog
 * Each label includes: barcode, product name, and retail price
 * Label size: 35mm x 22mm (standard label size)
 * 
 * Process:
 * 1. Validate product has barcode
 * 2. Clamp quantity between 1-100
 * 3. Generate HTML for all labels
 * 4. Open new window with formatted print layout
 * 5. Use JsBarcode to render all barcodes
 * 6. Trigger print dialog
 */
const printBarcode = () => {
  // Validate barcode exists
  if (!props.product?.barcode) {
    alert('No barcode available for this product');
    return;
  }

  // Clamp quantity between 1 and 100
  const quantity = Math.min(Math.max(barcodeQuantity.value || 1, 1), 100);
  
  // Generate HTML for all barcode labels
  let barcodesHTML = '';
  for (let i = 0; i < quantity; i++) {
    barcodesHTML += `
      <div class="barcode-item">
        <svg id="printBarcode${i}"></svg>
        <p class="barcode-number">${props.product?.barcode || ''}</p>
        <p class="product-name">${props.product?.name || ''}</p>
        <p class="product-price">Rs. ${formatPrice(props.product?.retail_price)}</p>
      </div>
    `;
  }

  // Open new print window
  const printWindow = window.open('', '', 'width=800,height=600');
  
  // Generate complete HTML document for printing
  const barcodeHTML = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Print Barcodes - ${props.product?.name || 'Product'}</title>
      <style>
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
        }
        body {
          margin: 10px;
          font-family: Arial, sans-serif;
        }
        .barcodes-container {
          display: flex;
          flex-wrap: wrap;
          gap: 5mm;
          padding: 5mm;
        }
        .barcode-item {
          width: 35mm;
          height: 22mm;
          text-align: center;
          padding: 1mm;
          border: 1px solid #ddd;
          page-break-inside: avoid;
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
          overflow: hidden;
        }
        .barcode-item svg {
          max-width: 32mm;
          max-height: 12mm;
        }
        .barcode-item p {
          margin: 0;
          line-height: 1.2;
        }
        .product-name {
          font-size: 7px;
          font-weight: bold;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
          max-width: 32mm;
          margin-top: 1mm;
        }
        .barcode-number {
          font-size: 7px;
          font-weight: bold;
          margin-top: 0.5mm;
        }
        .product-price {
          font-size: 8px;
          font-weight: bold;
          margin-top: 0.5mm;
        }
        @media print {
          body {
            margin: 0;
          }
          .barcode-item {
            border: 1px solid #ccc;
          }
        }
      </style>
    </head>
    <body>
      <div class="barcodes-container">
        ${barcodesHTML}
      </div>
      <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
      <script>
        window.onload = function() {
          for (let i = 0; i < ${quantity}; i++) {
            try {
              JsBarcode("#printBarcode" + i, "${props.product?.barcode || ''}", {
                format: "CODE128",
                width: 1.5,
                height: 30,
                displayValue: false,
                margin: 0
              });
            } catch (e) {
              console.error('Barcode generation error:', e);
            }
          }
          setTimeout(function() {
            window.print();
          }, 500);
        };
      <\/script>
    </body>
    </html>
  `;
  printWindow.document.write(barcodeHTML);
  printWindow.document.close();
};

/**
 * Watch for Modal Open State Changes
 * When modal opens, generate barcode and reset quantity to 1
 */
watch(() => props.open, (newVal) => {
  if (newVal) {
    generateBarcode();
    barcodeQuantity.value = 1; // Reset quantity when modal opens
  }
});

/**
 * Watch for Product Changes
 * Regenerate barcode when product data changes (if modal is open)
 */
watch(() => props.product, () => {
  if (props.open) {
    generateBarcode();
  }
});
</script>